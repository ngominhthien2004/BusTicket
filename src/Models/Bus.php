<?php

namespace Ct27501Project\Models;

class Bus
{
    private $pdo;

    // Properties mapping to buses table
    public $bus_id;
    public $license_plate;
    public $driver_name;
    public $seat_count;
    public $bus_type;
    public $seat_price;

    // Related data
    public $seats = [];
    public $schedules = [];

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?: PDO();
    }

    /**
     * Tìm xe theo ID
     */
    public static function find($id)
    {
        $bus = new self();
        $stmt = $bus->pdo->prepare("SELECT * FROM buses WHERE bus_id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($data) {
            $bus->fillFromArray($data);
            return $bus;
        }
        return null;
    }

    public static function getAvailableBuses($departure_time = null, $arrival_time = null)
    {
        $bus = new self();

        $sql = "SELECT DISTINCT b.* FROM buses b";
        $params = [];

        if ($departure_time && $arrival_time) {
            $sql .= " WHERE b.bus_id NOT IN (
                SELECT DISTINCT s.bus_id FROM schedules s
                WHERE s.bus_id IS NOT NULL
                AND (
                    (s.departure_time < ? AND s.arrival_time > ?)
                    OR (s.departure_time < ? AND s.arrival_time > ?)
                    OR (s.departure_time >= ? AND s.arrival_time <= ?)
                )
            )";

            // Kiểm tra: 
            // 1. Schedule đang chạy khi trip mới bắt đầu
            // 2. Schedule đang chạy khi trip mới kết thúc  
            // 3. Schedule nằm hoàn toàn trong trip mới
            $params = [
                $arrival_time,
                $departure_time,    // Case 1
                $arrival_time,
                $arrival_time,      // Case 2
                $departure_time,
                $arrival_time     // Case 3
            ];
        }

        $sql .= " ORDER BY b.license_plate";

        try {
            $stmt = $bus->pdo->prepare($sql);
            $stmt->execute($params);

            $buses = [];
            while ($data = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $busObj = new self();
                $busObj->fillFromArray($data);
                $buses[] = $busObj;
            }

            error_log('SQL Query: ' . $sql);
            error_log('Parameters: ' . print_r($params, true));
            error_log('Found buses: ' . count($buses));

            return $buses;
        } catch (\Exception $e) {
            error_log('Error in getAvailableBuses: ' . $e->getMessage());
            error_log('SQL: ' . $sql);
            error_log('Params: ' . print_r($params, true));

            // Fallback: trả về tất cả xe nếu có lỗi
            return self::all();
        }
    }

    /**
     * Lấy tất cả xe
     */
    public static function all($limit = null, $offset = 0)
    {
        $bus = new self();
        $sql = "SELECT * FROM buses ORDER BY license_plate";

        if ($limit) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }

        $stmt = $bus->pdo->query($sql);
        $buses = [];

        while ($data = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $busObj = new self($bus->pdo);
            $busObj->fillFromArray($data);
            $buses[] = $busObj;
        }

        return $buses;
    }

    /**
     * Tạo xe mới
     */



    private function createBus()
    {
        try {
            // Insert bus (không dùng transaction cho đơn giản)
            $stmt = $this->pdo->prepare("
            INSERT INTO buses (license_plate, driver_name, seat_count, bus_type)
            VALUES (?, ?, ?, ?)
        ");

            $result = $stmt->execute([
                $this->license_plate,
                $this->driver_name,
                $this->seat_count,
                $this->bus_type
            ]);

            if ($result) {
                $this->bus_id = $this->pdo->lastInsertId();
                return true;
            }

            return false;
        } catch (\Exception $e) {
            throw new \Exception('Lỗi khi tạo xe: ' . $e->getMessage());
        }
    }

    private function validateBusData()
    {
        if (empty($this->license_plate)) {
            throw new \Exception('Biển số xe không được để trống');
        }
        if (empty($this->driver_name)) {
            throw new \Exception('Tên tài xế không được để trống');
        }
        if (empty($this->seat_count) || $this->seat_count <= 0) {
            throw new \Exception('Số ghế phải lớn hơn 0');
        }
        if (empty($this->bus_type)) {
            throw new \Exception('Loại xe không được để trống');
        }
    }

    /**
     * Cập nhật thông tin xe
     */
    public function update($data)
    {
        $fields = [];
        $values = [];

        $allowedFields = ['license_plate', 'driver_name', 'seat_count', 'bus_type'];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $this->bus_id;

        $stmt = $this->pdo->prepare("
            UPDATE buses SET " . implode(', ', $fields) . "
            WHERE bus_id = ?
        ");

        return $stmt->execute($values);
    }

    public function save()
    {
        try {
            // Validate dữ liệu
            $this->validateBusData();

            if (isset($this->bus_id) && $this->bus_id > 0) {
                return $this->update($this->toArray());
            } else {
                return $this->createBus();
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Xóa xe
     */
    public function delete()
    {
        try {
            $this->pdo->beginTransaction();

            // Xóa ghế trước
            $stmt = $this->pdo->prepare("DELETE FROM seats WHERE bus_id = ?");
            $stmt->execute([$this->bus_id]);

            // Xóa xe
            $stmt = $this->pdo->prepare("DELETE FROM buses WHERE bus_id = ?");
            $result = $stmt->execute([$this->bus_id]);

            $this->pdo->commit();
            return $result;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Lấy tất cả ghế của xe
     */
    public function getSeats()
    {
        if (empty($this->seats)) {
            $seat = new Seat($this->pdo);
            $this->seats = $seat->getByBusId($this->bus_id);
        }
        return $this->seats;
    }

    /**
     * Lấy lịch trình của xe
     */
    public function getSchedules($dateFrom = null, $dateTo = null)
    {
        $sql = "
            SELECT s.*, r.start_point, r.end_point, r.distance_km
            FROM schedules s
            JOIN routes r ON s.route_id = r.route_id
            WHERE s.bus_id = ?
        ";
        $params = [$this->bus_id];

        if ($dateFrom) {
            $sql .= " AND DATE(s.departure_time) >= ?";
            $params[] = $dateFrom;
        }

        if ($dateTo) {
            $sql .= " AND DATE(s.departure_time) <= ?";
            $params[] = $dateTo;
        }

        $sql .= " ORDER BY s.departure_time";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Kiểm tra xe có sẵn sàng cho lịch trình không
     */
    public function isAvailable($departureTime, $arrivalTime, $excludeScheduleId = null)
    {
        $sql = "
            SELECT COUNT(*) FROM schedules
            WHERE bus_id = ?
            AND (
                (departure_time BETWEEN ? AND ?)
                OR (arrival_time BETWEEN ? AND ?)
                OR (departure_time <= ? AND arrival_time >= ?)
            )
        ";
        $params = [
            $this->bus_id,
            $departureTime,
            $arrivalTime,
            $departureTime,
            $arrivalTime,
            $departureTime,
            $arrivalTime
        ];

        if ($excludeScheduleId) {
            $sql .= " AND schedule_id != ?";
            $params[] = $excludeScheduleId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() == 0;
    }

    /**
     * Lấy thống kê của xe
     */
    public function getStats()
    {
        $stats = [
            'total_trips' => 0,
            'total_bookings' => 0,
            'total_revenue' => 0,
            'seat_utilization' => 0
        ];

        // Tổng số chuyến
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM schedules WHERE bus_id = ?
        ");
        $stmt->execute([$this->bus_id]);
        $stats['total_trips'] = $stmt->fetchColumn();

        // Tổng số booking và doanh thu
        $stmt = $this->pdo->prepare("
            SELECT COUNT(b.booking_id) as total_bookings, 
                   COALESCE(SUM(b.total_price), 0) as total_revenue
            FROM bookings b
            JOIN schedules s ON b.schedule_id = s.schedule_id
            WHERE s.bus_id = ? AND b.status != 'cancelled'
        ");
        $stmt->execute([$this->bus_id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $stats['total_bookings'] = $result['total_bookings'];
        $stats['total_revenue'] = $result['total_revenue'];

        // Tỷ lệ sử dụng ghế
        if ($this->seat_count > 0 && $stats['total_trips'] > 0) {
            $totalPossibleSeats = $this->seat_count * $stats['total_trips'];
            $stmt = $this->pdo->prepare("
                SELECT COUNT(bd.seat_id)
                FROM booking_details bd
                JOIN bookings b ON bd.booking_id = b.booking_id
                JOIN schedules s ON b.schedule_id = s.schedule_id
                WHERE s.bus_id = ? AND b.status != 'cancelled'
            ");
            $stmt->execute([$this->bus_id]);
            $bookedSeats = $stmt->fetchColumn();
            $stats['seat_utilization'] = round(($bookedSeats / $totalPossibleSeats) * 100, 2);
        }

        return $stats;
    }

    /**
     * Tìm kiếm xe
     */
    public static function search($conditions, $limit = null, $offset = 0)
    {
        $bus = new self();
        $sql = "SELECT * FROM buses WHERE 1=1";
        $params = [];

        if (!empty($conditions['license_plate'])) {
            $sql .= " AND license_plate LIKE ?";
            $params[] = "%{$conditions['license_plate']}%";
        }

        if (!empty($conditions['driver_name'])) {
            $sql .= " AND driver_name LIKE ?";
            $params[] = "%{$conditions['driver_name']}%";
        }

        if (!empty($conditions['seat_count'])) {
            $sql .= " AND seat_count = ?";
            $params[] = $conditions['seat_count'];
        }

        if (!empty($conditions['bus_type'])) {
            $sql .= " AND bus_type = ?";
            $params[] = $conditions['bus_type'];
        }

        $sql .= " ORDER BY license_plate";

        if ($limit) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }

        $stmt = $bus->pdo->prepare($sql);
        $stmt->execute($params);
        $buses = [];

        while ($data = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $busObj = new self($bus->pdo);
            $busObj->fillFromArray($data);
            $buses[] = $busObj;
        }

        return $buses;
    }

    /**
     * Lấy số lượng xe
     */
    public static function count($conditions = [])
    {
        $bus = new self();
        $sql = "SELECT COUNT(*) FROM buses WHERE 1=1";
        $params = [];

        if (!empty($conditions['bus_type'])) {
            $sql .= " AND bus_type = ?";
            $params[] = $conditions['bus_type'];
        }

        $stmt = $bus->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public function hasSeats($bus_id)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM seats WHERE bus_id = ?");
        $stmt->execute([$bus_id]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Điền dữ liệu từ mảng
     */
    private function fillFromArray($data)
    {
        $this->bus_id = $data['bus_id'] ?? null;
        $this->license_plate = $data['license_plate'] ?? null;
        $this->driver_name = $data['driver_name'] ?? null;
        $this->seat_count = $data['seat_count'] ?? null;
        $this->bus_type = $data['bus_type'] ?? null;
        $this->seat_price = $data['seat_price'] ?? null;
    }

    /**
     * Chuyển đổi thành mảng
     */
    public function toArray()
    {
        return [
            'bus_id' => $this->bus_id,
            'license_plate' => $this->license_plate,
            'driver_name' => $this->driver_name,
            'seat_count' => $this->seat_count,
            'bus_type' => $this->bus_type,
            'seats' => $this->seats,
            'schedules' => $this->schedules
        ];
    }
}
