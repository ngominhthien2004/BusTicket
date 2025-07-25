<?php

namespace Ct27501Project\Models;

class Schedule
{
    private $pdo;

    // Properties mapping to schedules table
    public $schedule_id;
    public $route_id;
    public $bus_id;
    public $departure_time;
    public $arrival_time;
    public $price;

    // Related data
    public $route_start;
    public $route_end;
    public $distance_km;
    public $bus_license_plate;
    public $driver_name;
    public $seat_count;
    public $bus_type;
    public $available_seats = 0;
    public $booked_seats = 0;

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?: PDO();
    }

    /**
     * Tìm lịch trình theo ID
     */
    public static function find($id)
    {
        $schedule = new self();
        $stmt = $schedule->pdo->prepare("
            SELECT s.*, r.start_point as route_start, r.end_point as route_end, r.distance_km,
                   b.license_plate as bus_license_plate, b.driver_name, b.seat_count, b.bus_type
            FROM schedules s
            JOIN routes r ON s.route_id = r.route_id
            JOIN buses b ON s.bus_id = b.bus_id
            WHERE s.schedule_id = ?
        ");
        $stmt->execute([$id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($data) {
            $schedule->fillFromArray($data);
            $schedule->calculateSeatAvailability();
            return $schedule;
        }
        return null;
    }

    /**
     * Lấy tất cả lịch trình
     */
    public static function all($limit = null, $offset = 0)
    {
        $schedule = new self();
        $sql = "
            SELECT s.*, r.start_point as route_start, r.end_point as route_end, r.distance_km,
                   b.license_plate as bus_license_plate, b.driver_name, b.seat_count, b.bus_type
            FROM schedules s
            JOIN routes r ON s.route_id = r.route_id
            JOIN buses b ON s.bus_id = b.bus_id
            ORDER BY s.departure_time DESC
        ";

        if ($limit) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }

        $stmt = $schedule->pdo->query($sql);
        $schedules = [];

        while ($data = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $scheduleObj = new self($schedule->pdo);
            $scheduleObj->fillFromArray($data);
            $scheduleObj->calculateSeatAvailability();
            $schedules[] = $scheduleObj;
        }

        return $schedules;
    }

    /**
     * Tạo lịch trình mới
     */
    public function create($data)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO schedules (route_id, bus_id, departure_time, arrival_time, price)
            VALUES (?, ?, ?, ?, ?)
        ");

        $result = $stmt->execute([
            $data['route_id'],
            $data['bus_id'],
            $data['departure_time'],
            $data['arrival_time'],
            $data['price']
        ]);

        if ($result) {
            $this->schedule_id = $this->pdo->lastInsertId();
        }

        return $result;
    }

    /**
     * Cập nhật lịch trình
     */
    public function update($data)
    {
        $fields = [];
        $values = [];

        $allowedFields = ['route_id', 'bus_id', 'departure_time', 'arrival_time', 'price'];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $this->schedule_id;

        $stmt = $this->pdo->prepare("
            UPDATE schedules SET " . implode(', ', $fields) . "
            WHERE schedule_id = ?
        ");

        return $stmt->execute($values);
    }

    /**
     * Xóa lịch trình
     */
    public function delete()
    {
        $stmt = $this->pdo->prepare("DELETE FROM schedules WHERE schedule_id = ?");
        return $stmt->execute([$this->schedule_id]);
    }

    /**
     * Tìm kiếm lịch trình
     */
    public static function search($conditions, $limit = null, $offset = 0)
    {
        $schedule = new self();
        $sql = "
            SELECT s.*, r.start_point as route_start, r.end_point as route_end, r.distance_km,
                   b.license_plate as bus_license_plate, b.driver_name, b.seat_count, b.bus_type
            FROM schedules s
            JOIN routes r ON s.route_id = r.route_id
            JOIN buses b ON s.bus_id = b.bus_id
            WHERE 1=1
        ";
        $params = [];

        // Basic search conditions
        if (!empty($conditions['start_point'])) {
            $sql .= " AND r.start_point ILIKE ?";
            $params[] = "%{$conditions['start_point']}%";
        }

        if (!empty($conditions['end_point'])) {
            $sql .= " AND r.end_point ILIKE ?";
            $params[] = "%{$conditions['end_point']}%";
        }

        if (!empty($conditions['departure_date'])) {
            $sql .= " AND DATE(s.departure_time) = ?";
            $params[] = $conditions['departure_date'];
        }

        // Time range filter - Fixed for PostgreSQL
        if (!empty($conditions['time_range'])) {
            $timeRange = explode('-', $conditions['time_range']);
            if (count($timeRange) == 2) {
                $startTime = $timeRange[0];
                $endTime = $timeRange[1];
                $sql .= " AND EXTRACT(HOUR FROM s.departure_time) * 60 + EXTRACT(MINUTE FROM s.departure_time) >= ? 
                         AND EXTRACT(HOUR FROM s.departure_time) * 60 + EXTRACT(MINUTE FROM s.departure_time) < ?";

                // Convert time strings to minutes
                list($startHour, $startMin) = explode(':', $startTime);
                list($endHour, $endMin) = explode(':', $endTime);
                $startMinutes = (int)$startHour * 60 + (int)$startMin;
                $endMinutes = (int)$endHour * 60 + (int)$endMin;

                $params[] = $startMinutes;
                $params[] = $endMinutes;
            }
        }

        // Bus type filter
        if (!empty($conditions['bus_type'])) {
            $sql .= " AND b.bus_type = ?";
            $params[] = $conditions['bus_type'];
        }

        // Price range filter
        if (!empty($conditions['price_range'])) {
            $priceRange = explode('-', $conditions['price_range']);
            if (count($priceRange) == 2) {
                $minPrice = (int)$priceRange[0];
                $maxPrice = (int)$priceRange[1];
                if ($maxPrice == 1000000) {
                    // Handle "over 500,000" case
                    $sql .= " AND s.price >= ?";
                    $params[] = $minPrice;
                } else {
                    $sql .= " AND s.price >= ? AND s.price <= ?";
                    $params[] = $minPrice;
                    $params[] = $maxPrice;
                }
            }
        }

        $sql .= " ORDER BY s.departure_time";

        if ($limit) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }

        try {
            $stmt = $schedule->pdo->prepare($sql);
            $stmt->execute($params);
            $schedules = [];

            while ($data = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $scheduleObj = new self($schedule->pdo);
                $scheduleObj->fillFromArray($data);
                $scheduleObj->calculateSeatAvailability();
                $schedules[] = $scheduleObj;
            }

            return $schedules;
        } catch (\Exception $e) {
            error_log("Error in Schedule::search(): " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Params: " . print_r($params, true));
            return [];
        }
    }

    /**
     * Tính toán số ghế còn trống
     */
    private function calculateSeatAvailability()
    {
        if (!$this->schedule_id) {
            $this->available_seats = $this->seat_count ?? 0;
            return;
        }

        $stmt = $this->pdo->prepare("
            SELECT COUNT(bd.seat_id) as booked_count
            FROM booking_details bd
            JOIN bookings b ON bd.booking_id = b.booking_id
            WHERE b.schedule_id = ? AND b.status != 'cancelled'
        ");
        $stmt->execute([$this->schedule_id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->booked_seats = $result['booked_count'] ?? 0;
        $this->available_seats = max(0, ($this->seat_count ?? 0) - $this->booked_seats);
    }

    /**
     * Điền dữ liệu từ mảng
     */
    private function fillFromArray($data)
    {
        $this->schedule_id = $data['schedule_id'] ?? null;
        $this->route_id = $data['route_id'] ?? null;
        $this->bus_id = $data['bus_id'] ?? null;
        $this->departure_time = $data['departure_time'] ?? null;
        $this->arrival_time = $data['arrival_time'] ?? null;
        $this->price = $data['price'] ?? null;

        // Related data
        $this->route_start = $data['route_start'] ?? null;
        $this->route_end = $data['route_end'] ?? null;
        $this->distance_km = $data['distance_km'] ?? null;
        $this->bus_license_plate = $data['bus_license_plate'] ?? null;
        $this->driver_name = $data['driver_name'] ?? null;
        $this->seat_count = $data['seat_count'] ?? null;
        $this->bus_type = $data['bus_type'] ?? null;
    }

    public function ableToDelete()
    {
        try {
            $stmt = $this->pdo->prepare("
            SELECT 
                (SELECT COUNT(*) FROM bookings WHERE schedule_id = ?) AS booking_count,
                (? > NOW()) AS is_future_date
        ");
            $stmt->execute([$this->schedule_id, $this->departure_time]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $result['booking_count'] == 0 && $result['is_future_date'] == 1;
        } catch (\Exception $e) {
            error_log('Error in ableToDelete(): ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Chuyển đổi thành mảng
     */
    public function toArray()
    {
        return [
            'schedule_id' => $this->schedule_id,
            'route_id' => $this->route_id,
            'bus_id' => $this->bus_id,
            'departure_time' => $this->departure_time,
            'arrival_time' => $this->arrival_time,
            'price' => $this->price,
            'route_start' => $this->route_start,
            'route_end' => $this->route_end,
            'distance_km' => $this->distance_km,
            'bus_license_plate' => $this->bus_license_plate,
            'driver_name' => $this->driver_name,
            'seat_count' => $this->seat_count,
            'bus_type' => $this->bus_type,
            'available_seats' => $this->available_seats,
            'booked_seats' => $this->booked_seats
        ];
    }

    /**
     * Lấy lịch trình theo route_id
     */
    public function getByRouteId($routeId)
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM schedules 
            WHERE route_id = ? AND departure_time > NOW()
            ORDER BY departure_time ASC
        ");
        $stmt->execute([$routeId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách loại xe từ database
     */
    public static function getBusTypes()
    {
        try {
            $schedule = new self();
            $stmt = $schedule->pdo->query("
                SELECT DISTINCT b.bus_type 
                FROM buses b
                INNER JOIN schedules s ON b.bus_id = s.bus_id
                WHERE b.bus_type IS NOT NULL 
                ORDER BY b.bus_type
            ");

            $busTypes = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                if (!empty($row['bus_type'])) {
                    $busTypes[] = $row['bus_type'];
                }
            }
            return $busTypes;
        } catch (\Exception $e) {
            error_log("Error fetching bus types: " . $e->getMessage());
            return [];
        }
    }
}
