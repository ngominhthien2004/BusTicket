<?php

namespace Ct27501Project\Models;

class Ticket
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
    public $seats = [];

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?: PDO();
    }

    /**
     * Tìm vé theo ID
     */
    public static function find($id)
    {
        $ticket = new self();
        $stmt = $ticket->pdo->prepare("
            SELECT b.*, u.full_name as user_name, u.email as user_email, u.phone_number as user_phone,
                   r.start_point as route_start, r.end_point as route_end,
                   s.departure_time, s.arrival_time, s.price,
                   bus.license_plate as bus_license_plate, bus.driver_name
            FROM bookings b
            LEFT JOIN users u ON b.user_id = u.user_id
            LEFT JOIN schedules s ON b.schedule_id = s.schedule_id
            LEFT JOIN routes r ON s.route_id = r.route_id
            LEFT JOIN buses bus ON s.bus_id = bus.bus_id
            WHERE b.booking_id = ?
        ");
        $stmt->execute([$id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($data) {
            $ticket->fillFromArray($data);
            $ticket->loadSeats();
            return $ticket;
        }
        return null;
    }

    /**
     * Lấy tất cả vé
     */
    public static function all($limit = null, $offset = 0)
    {
        $ticket = new self();
        $sql = "
            SELECT b.*, u.full_name as user_name, u.email as user_email, u.phone_number as user_phone,
                   r.start_point as route_start, r.end_point as route_end,
                   s.departure_time, s.arrival_time, s.price,
                   bus.license_plate as bus_license_plate, bus.driver_name
            FROM bookings b
            LEFT JOIN users u ON b.user_id = u.user_id
            LEFT JOIN schedules s ON b.schedule_id = s.schedule_id
            LEFT JOIN routes r ON s.route_id = r.route_id
            LEFT JOIN buses bus ON s.bus_id = bus.bus_id
            ORDER BY b.booking_time DESC
        ";

        if ($limit) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }

        $stmt = $ticket->pdo->query($sql);
        $tickets = [];

        while ($data = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $ticketObj = new self($ticket->pdo);
            $ticketObj->fillFromArray($data);
            $ticketObj->loadSeats();
            $tickets[] = $ticketObj;
        }

        return $tickets;
    }

    /**
     * Lấy vé theo user ID
     */
    public static function getByUserId($userId)
    {
        $ticket = new self();
        $stmt = $ticket->pdo->prepare("
            SELECT b.*, u.full_name as user_name, u.email as user_email, u.phone_number as user_phone,
                   r.start_point as route_start, r.end_point as route_end,
                   s.departure_time, s.arrival_time, s.price,
                   bus.license_plate as bus_license_plate, bus.driver_name
            FROM bookings b
            LEFT JOIN users u ON b.user_id = u.user_id
            LEFT JOIN schedules s ON b.schedule_id = s.schedule_id
            LEFT JOIN routes r ON s.route_id = r.route_id
            LEFT JOIN buses bus ON s.bus_id = bus.bus_id
            WHERE b.user_id = ?
            ORDER BY b.booking_time DESC
        ");
        $stmt->execute([$userId]);
        $tickets = [];

        while ($data = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $ticketObj = new self($ticket->pdo);
            $ticketObj->fillFromArray($data);
            $ticketObj->loadSeats();
            $tickets[] = $ticketObj;
        }

        return $tickets;
    }

    /**
     * Tạo vé mới
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

    /**
     * Cập nhật vé
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
     * Hủy vé
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
     * Lấy thông tin thanh toán
     */
    public function getPayments()
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM payments
            WHERE booking_id = ?
            ORDER BY payment_time DESC
        ");
        $stmt->execute([$this->booking_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Tạo thanh toán
     */
    public function createPayment($paymentData)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO payments (booking_id, payment_method, amount, status, transaction_code, payment_time)
            VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
        ");

        return $stmt->execute([
            $this->booking_id,
            $paymentData['payment_method'],
            $paymentData['amount'],
            $paymentData['status'] ?? 'pending',
            $paymentData['transaction_code'] ?? null
        ]);
    }

    /**
     * Lấy số lượng vé theo điều kiện
     */
    public static function count($conditions = [])
    {
        $ticket = new self();
        $sql = "SELECT COUNT(*) FROM bookings";
        $params = [];

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $field => $value) {
                $where[] = "$field = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $stmt = $ticket->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    /**
     * Tìm kiếm vé theo điều kiện
     */
    public static function search($conditions, $limit = null, $offset = 0)
    {
        $ticket = new self();
        $sql = "
            SELECT b.*, u.full_name as user_name, u.email as user_email, u.phone_number as user_phone,
                   r.start_point as route_start, r.end_point as route_end,
                   s.departure_time, s.arrival_time, s.price,
                   bus.license_plate as bus_license_plate, bus.driver_name
            FROM bookings b
            LEFT JOIN users u ON b.user_id = u.user_id
            LEFT JOIN schedules s ON b.schedule_id = s.schedule_id
            LEFT JOIN routes r ON s.route_id = r.route_id
            LEFT JOIN buses bus ON s.bus_id = bus.bus_id
        ";

        $where = [];
        $params = [];

        if (!empty($conditions)) {
            foreach ($conditions as $field => $value) {
                if ($field === 'date_from') {
                    $where[] = "DATE(s.departure_time) >= ?";
                    $params[] = $value;
                } elseif ($field === 'date_to') {
                    $where[] = "DATE(s.departure_time) <= ?";
                    $params[] = $value;
                } elseif ($field === 'route') {
                    $where[] = "(r.start_point LIKE ? OR r.end_point LIKE ?)";
                    $params[] = "%$value%";
                    $params[] = "%$value%";
                } elseif ($field === 'phone_number') {
                    $where[] = "u.phone_number LIKE ?";
                    $params[] = "%$value%";
                } elseif ($field === 'email') {
                    $where[] = "u.email LIKE ?";
                    $params[] = "%$value%";
                } else {
                    $where[] = "b.$field = ?";
                    $params[] = $value;
                }
            }
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY b.booking_time DESC";

        if ($limit) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }

        $stmt = $ticket->pdo->prepare($sql);
        $stmt->execute($params);
        $tickets = [];

        while ($data = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $ticketObj = new self($ticket->pdo);
            $ticketObj->fillFromArray($data);
            $ticketObj->loadSeats();
            $tickets[] = $ticketObj;
        }

        return $tickets;
    }

    /**
     * Điền dữ liệu từ mảng
     */
    private function fillFromArray($data)
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
            'seats' => $this->seats
        ];
    }
}
