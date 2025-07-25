<?php

namespace Ct27501Project\Models;

class Seat
{
    private $pdo;

    // Properties mapping to seats table
    public $seat_id;
    public $bus_id;
    public $seat_number;

    // Related data properties
    public $bus_license_plate;
    public $bus_type;
    public $is_booked = false;
    public $booking_id = null;
    public $user_name = null;

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?: PDO();
    }

    /**
     * Tìm ghế theo ID
     */
    public static function find($id)
    {
        $seat = new self();
        $stmt = $seat->pdo->prepare("
            SELECT s.*, b.license_plate as bus_license_plate, b.bus_type
            FROM seats s
            LEFT JOIN buses b ON s.bus_id = b.bus_id
            WHERE s.seat_id = ?
        ");
        $stmt->execute([$id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($data) {
            $seat->fillFromArray($data);
            return $seat;
        }
        return null;
    }

    /**
     * Lấy tất cả ghế theo bus ID
     */
    public function getByBusId($busId)
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM seats 
            WHERE bus_id = ? 
            ORDER BY seat_number
        ");
        $stmt->execute([$busId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Lấy ghế trống cho một chuyến xe
     */
    public static function getAvailableSeats($scheduleId)
    {
        $seat = new self();
        $stmt = $seat->pdo->prepare("
            SELECT s.*, b.license_plate as bus_license_plate, b.bus_type
            FROM seats s
            JOIN buses b ON s.bus_id = b.bus_id
            JOIN schedules sc ON sc.bus_id = b.bus_id
            WHERE sc.schedule_id = ?
            AND s.seat_id NOT IN (
                SELECT bd.seat_id
                FROM booking_details bd
                JOIN bookings bk ON bd.booking_id = bk.booking_id
                WHERE bk.schedule_id = ? AND bk.status != 'cancelled'
            )
            ORDER BY s.seat_number
        ");
        $stmt->execute([$scheduleId, $scheduleId]);
        $seats = [];

        while ($data = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $seatObj = new self($seat->pdo);
            $seatObj->fillFromArray($data);
            $seats[] = $seatObj;
        }

        return $seats;
    }

    /**
     * Lấy ghế đã đặt cho một chuyến xe
     */
    public static function getBookedSeats($scheduleId)
    {
        $seat = new self();
        $stmt = $seat->pdo->prepare("
            SELECT s.*, b.license_plate as bus_license_plate, b.bus_type,
                   bk.booking_id, bk.user_id, u.full_name as user_name
            FROM seats s
            JOIN booking_details bd ON s.seat_id = bd.seat_id
            JOIN bookings bk ON bd.booking_id = bk.booking_id
            JOIN users u ON bk.user_id = u.user_id
            JOIN buses b ON s.bus_id = b.bus_id
            WHERE bk.schedule_id = ? AND bk.status != 'cancelled'
            ORDER BY s.seat_number
        ");
        $stmt->execute([$scheduleId]);
        $seats = [];

        while ($data = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $seatObj = new self($seat->pdo);
            $seatObj->fillFromArray($data);
            $seatObj->is_booked = true;
            $seatObj->booking_id = $data['booking_id'];
            $seatObj->user_name = $data['user_name'];
            $seats[] = $seatObj;
        }

        return $seats;
    }

    /**
     * Tạo ghế mới
     */
    public function create($data)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO seats (bus_id, seat_number)
            VALUES (?, ?)
        ");

        $result = $stmt->execute([
            $data['bus_id'],
            $data['seat_number']
        ]);

        if ($result) {
            $this->seat_id = $this->pdo->lastInsertId();
            $this->bus_id = $data['bus_id'];
            $this->seat_number = $data['seat_number'];
        }

        return $result;
    }

    /**
     * Tạo nhiều ghế cho xe bus
     */
    public static function createSeatsForBus($busId, $seatCount, $seatNames = [])
    {
        $seat = new self();

        try {
            $stmt = $seat->pdo->prepare("
            INSERT INTO seats (bus_id, seat_number)
            VALUES (?, ?)
        ");

            $successCount = 0;

            for ($i = 1; $i <= $seatCount; $i++) {
                $seatNumber = $seatNames[$i - 1] ?? $i;

                if ($stmt->execute([$busId, $seatNumber])) {
                    $successCount++;
                }
            }

            return $successCount === $seatCount;
        } catch (\Exception $e) {
            error_log('Error creating seats: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cập nhật thông tin ghế
     */

    public static function updateSeats($busId, $seatCount, $seatNames = [])
    {
        $seat = new self();

        try {
            // Bắt đầu transaction
            $seat->pdo->beginTransaction();

            // Xóa tất cả ghế cũ
            $deleteStmt = $seat->pdo->prepare("DELETE FROM seats WHERE bus_id = ?");
            $deleteStmt->execute([$busId]);

            // Tạo lại ghế mới
            $insertStmt = $seat->pdo->prepare("
            INSERT INTO seats (bus_id, seat_number) 
            VALUES (?, ?)
        ");

            for ($i = 1; $i <= $seatCount; $i++) {
                $seatNumber = $seatNames[$i - 1] ?? $i;
                $insertStmt->execute([$busId, $seatNumber]);
            }

            // Commit transaction
            $seat->pdo->commit();
            return true;
        } catch (\Exception $e) {
            // Rollback nếu có lỗi
            $seat->pdo->rollBack();
            error_log('Error updating seats: ' . $e->getMessage());
            return false;
        }
    }


    /**
     * Xóa ghế
     */
    public function delete()
    {
        $stmt = $this->pdo->prepare("DELETE FROM seats WHERE seat_id = ?");
        return $stmt->execute([$this->seat_id]);
    }

    /**
     * Kiểm tra ghế có thể đặt được không
     */
    public function isAvailable($scheduleId)
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM booking_details bd
            JOIN bookings b ON bd.booking_id = b.booking_id
            WHERE bd.seat_id = ? AND b.schedule_id = ? AND b.status != 'cancelled'
        ");
        $stmt->execute([$this->seat_id, $scheduleId]);
        return $stmt->fetchColumn() == 0;
    }

    /**
     * Điền dữ liệu từ mảng
     */
    private function fillFromArray($data)
    {
        $this->seat_id = $data['seat_id'] ?? null;
        $this->bus_id = $data['bus_id'] ?? null;
        $this->seat_number = $data['seat_number'] ?? null;
        $this->bus_license_plate = $data['bus_license_plate'] ?? null;
        $this->bus_type = $data['bus_type'] ?? null;
    }

    /**
     * Chuyển đổi thành mảng
     */
    public function toArray()
    {
        return [
            'seat_id' => $this->seat_id,
            'bus_id' => $this->bus_id,
            'seat_number' => $this->seat_number,
            'bus_license_plate' => $this->bus_license_plate,
            'bus_type' => $this->bus_type,
            'is_booked' => $this->is_booked,
            'booking_id' => $this->booking_id
        ];
    }
}
