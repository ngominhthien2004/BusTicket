<?php

namespace Ct27501Project\Models;

use PDO;

class Booking
{
    private $pdo;

    // Properties mapping to bookings table
    public $booking_id;
    public $user_id;
    public $schedule_id;
    public $booking_time;
    public $status;
    public $payment_method;
    public $total_price;

    // Related data properties
    public $user_name;
    public $user_email;
    public $user_phone;
    public $route_start;
    public $route_end;
    public $departure_time;
    public $arrival_time;
    public $bus_license_plate;
    public $driver_name;
    public $seat_numbers;
    public $seats = [];

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?: PDO();
    }

    /**
     * Tìm booking theo ID
     */
    public static function find($id)
    {
        $booking = new self();
        $stmt = $booking->pdo->prepare("
            SELECT b.*, u.full_name as user_name, u.email as user_email, u.phone_number as user_phone,
                   r.start_point as route_start, r.end_point as route_end,
                   s.departure_time, s.arrival_time, s.price,
                   bus.license_plate as bus_license_plate, bus.driver_name,
                   STRING_AGG(seats.seat_number, ', ') as seat_numbers
            FROM bookings b
            LEFT JOIN users u ON b.user_id = u.user_id
            LEFT JOIN schedules s ON b.schedule_id = s.schedule_id
            LEFT JOIN routes r ON s.route_id = r.route_id
            LEFT JOIN buses bus ON s.bus_id = bus.bus_id
            LEFT JOIN booking_details bd ON b.booking_id = bd.booking_id
            LEFT JOIN seats ON bd.seat_id = seats.seat_id
            WHERE b.booking_id = ?
            GROUP BY b.booking_id, b.booking_time, b.status, b.total_price, b.payment_method,
                     u.full_name, u.email, u.phone_number,
                     r.start_point, r.end_point, s.departure_time, s.arrival_time, s.price,
                     bus.license_plate, bus.driver_name
        ");
        $stmt->execute([$id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($data) {
            $booking->fillFromArray($data);
            $booking->loadSeats();
            return $booking;
        }
        return null;
    }

    /**
     * Lấy tất cả bookings
     */
    public static function all($limit = null, $offset = 0)
    {
        $booking = new self();
        $sql = "
            SELECT b.*, u.full_name as user_name, u.email as user_email, u.phone_number as user_phone,
                   r.start_point as route_start, r.end_point as route_end,
                   s.departure_time, s.arrival_time, s.price,
                   bus.license_plate as bus_license_plate, bus.driver_name,
                   STRING_AGG(seats.seat_number, ', ') as seat_numbers
            FROM bookings b
            LEFT JOIN users u ON b.user_id = u.user_id
            LEFT JOIN schedules s ON b.schedule_id = s.schedule_id
            LEFT JOIN routes r ON s.route_id = r.route_id
            LEFT JOIN buses bus ON s.bus_id = bus.bus_id
            LEFT JOIN booking_details bd ON b.booking_id = bd.booking_id
            LEFT JOIN seats ON bd.seat_id = seats.seat_id
            GROUP BY b.booking_id, b.booking_time, b.status, b.total_price, b.payment_method,
                     u.full_name, u.email, u.phone_number,
                     r.start_point, r.end_point, s.departure_time, s.arrival_time, s.price,
                     bus.license_plate, bus.driver_name
            ORDER BY b.booking_time DESC
        ";

        if ($limit) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }

        $stmt = $booking->pdo->query($sql);
        $bookings = [];

        while ($data = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $bookingObj = new self($booking->pdo);
            $bookingObj->fillFromArray($data);
            $bookingObj->loadSeats();
            $bookings[] = $bookingObj;
        }

        return $bookings;
    }

    /**
     * Tạo booking mới
     */
    public function create($data)
    {
        try {
            $this->pdo->beginTransaction();

            // Tạo booking
            $stmt = $this->pdo->prepare("
                INSERT INTO bookings (user_id, schedule_id, status, payment_method, total_price, booking_time)
                VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
            ");
            $stmt->execute([
                $data['user_id'],
                $data['schedule_id'],
                $data['status'] ?? 'booked',
                $data['payment_method'] ?? null,
                $data['total_price']
            ]);

            $this->booking_id = $this->pdo->lastInsertId();

            // Tạo booking_details cho từng ghế
            if (!empty($data['seat_ids'])) {
                $stmt = $this->pdo->prepare("
                    INSERT INTO booking_details (booking_id, seat_id)
                    VALUES (?, ?)
                ");
                foreach ($data['seat_ids'] as $seatId) {
                    $stmt->execute([$this->booking_id, $seatId]);
                }
            }

            $this->pdo->commit();
            return $this->booking_id;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function getUserBookings($userId, $filters = [])
    {
        $sql = "SELECT 
                    b.booking_id,
                    b.booking_time,
                    b.status,
                    b.total_price,
                    b.payment_method,
                    r.start_point,
                    r.end_point,
                    s.departure_time,
                    s.arrival_time,
                    s.price,
                    bus.license_plate,
                    bus.bus_type,
                    STRING_AGG(seats.seat_number, ', ') as seat_numbers
                FROM bookings b
                JOIN schedules s ON b.schedule_id = s.schedule_id
                JOIN routes r ON s.route_id = r.route_id
                JOIN buses bus ON s.bus_id = bus.bus_id
                LEFT JOIN booking_details bd ON b.booking_id = bd.booking_id
                LEFT JOIN seats ON bd.seat_id = seats.seat_id
                WHERE b.user_id = ?";

        $params = [$userId];

        // Thêm filters
        if (!empty($filters['date'])) {
            $sql .= " AND DATE(s.departure_time) = ?";
            $params[] = $filters['date'];
        }

        if (!empty($filters['route'])) {
            $sql .= " AND CONCAT(r.start_point, ' - ', r.end_point) LIKE ?";
            $params[] = '%' . $filters['route'] . '%';
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'completed') {
                $sql .= " AND s.departure_time < NOW()";
            } elseif ($filters['status'] === 'upcoming') {
                $sql .= " AND s.departure_time >= NOW()";
            } else {
                $sql .= " AND b.status = ?";
                $params[] = $filters['status'];
            }
        }

        $sql .= " GROUP BY b.booking_id, b.booking_time, b.status, b.total_price, b.payment_method, 
                          r.start_point, r.end_point, s.departure_time, s.arrival_time, s.price, 
                          bus.license_plate, bus.bus_type 
                  ORDER BY b.booking_time DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBookingStats($userId)
    {
        $sql = "SELECT 
                    COUNT(*) as total_bookings,
                    COALESCE(SUM(total_price), 0) as total_spent,
                    COUNT(CASE WHEN s.departure_time < NOW() AND b.status != 'cancelled' THEN 1 END) as completed_trips,
                    COUNT(CASE WHEN s.departure_time >= NOW() AND b.status != 'cancelled' THEN 1 END) as upcoming_trips,
                    COUNT(CASE WHEN b.status = 'cancelled' THEN 1 END) as cancelled_trips
                FROM bookings b
                JOIN schedules s ON b.schedule_id = s.schedule_id
                WHERE b.user_id = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUniqueRoutes($userId)
    {
        $sql = "SELECT DISTINCT CONCAT(r.start_point, ' - ', r.end_point) as route
                FROM bookings b
                JOIN schedules s ON b.schedule_id = s.schedule_id
                JOIN routes r ON s.route_id = r.route_id
                WHERE b.user_id = ?
                ORDER BY route";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Cập nhật booking
     */
    public function update($data)
    {
        $fields = [];
        $values = [];

        $allowedFields = ['status', 'payment_method', 'total_price'];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $this->booking_id;

        $stmt = $this->pdo->prepare("
            UPDATE bookings SET " . implode(', ', $fields) . "
            WHERE booking_id = ?
        ");

        return $stmt->execute($values);
    }

    /**
     * Hủy booking
     */
    public function cancel($reason = null)
    {
        try {
            $this->pdo->beginTransaction();

            // Cập nhật trạng thái booking
            $stmt = $this->pdo->prepare("
                UPDATE bookings SET status = 'cancelled'
                WHERE booking_id = ?
            ");
            $stmt->execute([$this->booking_id]);

            // Thêm vào bảng cancellations
            $stmt = $this->pdo->prepare("
                INSERT INTO cancellations (booking_id, cancel_reason, cancel_time)
                VALUES (?, ?, CURRENT_TIMESTAMP)
            ");
            $stmt->execute([$this->booking_id, $reason]);

            $this->pdo->commit();
            $this->status = 'cancelled';
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Tìm kiếm bookings
     */
    public static function search($conditions, $limit = null, $offset = 0)
    {
        $booking = new self();
        $sql = "
            SELECT b.*, u.full_name as user_name, u.email as user_email, u.phone_number as user_phone,
                   r.start_point as route_start, r.end_point as route_end,
                   s.departure_time, s.arrival_time, s.price,
                   bus.license_plate as bus_license_plate, bus.driver_name,
                   STRING_AGG(seats.seat_number, ', ') as seat_numbers
            FROM bookings b
            LEFT JOIN users u ON b.user_id = u.user_id
            LEFT JOIN schedules s ON b.schedule_id = s.schedule_id
            LEFT JOIN routes r ON s.route_id = r.route_id
            LEFT JOIN buses bus ON s.bus_id = bus.bus_id
            LEFT JOIN booking_details bd ON b.booking_id = bd.booking_id
            LEFT JOIN seats ON bd.seat_id = seats.seat_id
            WHERE 1=1
        ";

        $params = [];

        if (!empty($conditions['user_id'])) {
            $sql .= " AND b.user_id = ?";
            $params[] = $conditions['user_id'];
        }

        if (!empty($conditions['status'])) {
            $sql .= " AND b.status = ?";
            $params[] = $conditions['status'];
        }

        if (!empty($conditions['date_from'])) {
            $sql .= " AND DATE(s.departure_time) >= ?";
            $params[] = $conditions['date_from'];
        }

        if (!empty($conditions['date_to'])) {
            $sql .= " AND DATE(s.departure_time) <= ?";
            $params[] = $conditions['date_to'];
        }

        if (!empty($conditions['route'])) {
            $sql .= " AND (r.start_point LIKE ? OR r.end_point LIKE ?)";
            $params[] = "%{$conditions['route']}%";
            $params[] = "%{$conditions['route']}%";
        }

        $sql .= " GROUP BY b.booking_id, b.booking_time, b.status, b.total_price, b.payment_method,
                           u.full_name, u.email, u.phone_number,
                           r.start_point, r.end_point, s.departure_time, s.arrival_time, s.price,
                           bus.license_plate, bus.driver_name
                  ORDER BY b.booking_time DESC";

        if ($limit) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }

        $stmt = $booking->pdo->prepare($sql);
        $stmt->execute($params);
        $bookings = [];

        while ($data = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $bookingObj = new self($booking->pdo);
            $bookingObj->fillFromArray($data);
            $bookingObj->loadSeats();
            $bookings[] = $bookingObj;
        }

        return $bookings;
    }

    /**
     * Lấy số lượng bookings
     */
    public static function count($conditions = [])
    {
        $booking = new self();
        $sql = "SELECT COUNT(*) FROM bookings b";
        $params = [];

        if (!empty($conditions)) {
            $where = [];

            if (!empty($conditions['user_id'])) {
                $where[] = "b.user_id = ?";
                $params[] = $conditions['user_id'];
            }

            if (!empty($conditions['status'])) {
                $where[] = "b.status = ?";
                $params[] = $conditions['status'];
            }

            if (!empty($conditions['date_from'])) {
                $sql .= " JOIN schedules s ON b.schedule_id = s.schedule_id";
                $where[] = "DATE(s.departure_time) >= ?";
                $params[] = $conditions['date_from'];
            }

            if (!empty($where)) {
                $sql .= " WHERE " . implode(' AND ', $where);
            }
        }

        $stmt = $booking->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    /**
     * Lấy thống kê tổng quan
     */
    public static function getGeneralStats($dateFrom = null, $dateTo = null)
    {
        $booking = new self();
        $sql = "
            SELECT 
                COUNT(*) as total_bookings,
                COUNT(CASE WHEN status = 'booked' THEN 1 END) as booked_count,
                COUNT(CASE WHEN status = 'paid' THEN 1 END) as paid_count,
                COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_count,
                COALESCE(SUM(CASE WHEN status != 'cancelled' THEN total_price ELSE 0 END), 0) as total_revenue
            FROM bookings b
            JOIN schedules s ON b.schedule_id = s.schedule_id
            WHERE 1=1
        ";

        $params = [];

        if ($dateFrom) {
            $sql .= " AND DATE(s.departure_time) >= ?";
            $params[] = $dateFrom;
        }

        if ($dateTo) {
            $sql .= " AND DATE(s.departure_time) <= ?";
            $params[] = $dateTo;
        }

        $stmt = $booking->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Điền dữ liệu từ mảng
     */
    public function fillFromArray($data)
    {
        $this->booking_id = $data['booking_id'] ?? null;
        $this->user_id = $data['user_id'] ?? null;
        $this->schedule_id = $data['schedule_id'] ?? null;
        $this->booking_time = $data['booking_time'] ?? null;
        $this->status = $data['status'] ?? null;
        $this->payment_method = $data['payment_method'] ?? null;
        $this->total_price = $data['total_price'] ?? null;

        // Related data
        $this->user_name = $data['user_name'] ?? null;
        $this->user_email = $data['user_email'] ?? null;
        $this->user_phone = $data['user_phone'] ?? null;
        $this->route_start = $data['route_start'] ?? null;
        $this->route_end = $data['route_end'] ?? null;
        $this->departure_time = $data['departure_time'] ?? null;
        $this->arrival_time = $data['arrival_time'] ?? null;
        $this->bus_license_plate = $data['bus_license_plate'] ?? null;
        $this->driver_name = $data['driver_name'] ?? null;
        $this->seat_numbers = $data['seat_numbers'] ?? null;
    }

    /**
     * Tải thông tin ghế
     */
    private function loadSeats()
    {
        if (!$this->booking_id) return;

        $stmt = $this->pdo->prepare("
            SELECT s.seat_number, s.seat_id
            FROM booking_details bd
            JOIN seats s ON bd.seat_id = s.seat_id
            WHERE bd.booking_id = ?
            ORDER BY s.seat_number
        ");
        $stmt->execute([$this->booking_id]);
        $this->seats = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Chuyển đổi thành mảng
     */
    public function toArray()
    {
        return [
            'booking_id' => $this->booking_id,
            'user_id' => $this->user_id,
            'schedule_id' => $this->schedule_id,
            'booking_time' => $this->booking_time,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'total_price' => $this->total_price,
            'user_name' => $this->user_name,
            'user_email' => $this->user_email,
            'user_phone' => $this->user_phone,
            'route_start' => $this->route_start,
            'route_end' => $this->route_end,
            'departure_time' => $this->departure_time,
            'arrival_time' => $this->arrival_time,
            'bus_license_plate' => $this->bus_license_plate,
            'driver_name' => $this->driver_name,
            'seat_numbers' => $this->seat_numbers,
            'seats' => $this->seats
        ];
    }

    public function getBookedSeats($scheduleId)
    {
        $stmt = $this->pdo->prepare("
            SELECT DISTINCT bd.seat_id 
            FROM booking_details bd
            JOIN bookings b ON bd.booking_id = b.booking_id
            WHERE b.schedule_id = ? AND b.status != 'cancelled'
        ");
        $stmt->execute([$scheduleId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
