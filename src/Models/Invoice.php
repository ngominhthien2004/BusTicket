<?php

namespace Ct27501Project\Models;

class Invoice
{
    private $pdo;

    // Properties mapping to payments table
    public $payment_id;
    public $booking_id;
    public $payment_method;
    public $payment_time;
    public $amount;
    public $status;
    public $transaction_code;

    // Related booking data
    public $booking_time;
    public $user_name;
    public $user_email;
    public $user_phone;
    public $route_start;
    public $route_end;
    public $departure_time;
    public $arrival_time;
    public $bus_license_plate;
    public $seats = [];

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?: PDO();
    }

    /**
     * Tìm hóa đơn theo ID
     */
    public static function find($id)
    {
        $invoice = new self();
        $stmt = $invoice->pdo->prepare("
            SELECT p.*, b.booking_time, b.total_price as booking_total,
                   u.full_name as user_name, u.email as user_email, u.phone_number as user_phone,
                   r.start_point as route_start, r.end_point as route_end,
                   s.departure_time, s.arrival_time,
                   bus.license_plate as bus_license_plate
            FROM payments p
            JOIN bookings b ON p.booking_id = b.booking_id
            JOIN users u ON b.user_id = u.user_id
            JOIN schedules s ON b.schedule_id = s.schedule_id
            JOIN routes r ON s.route_id = r.route_id
            JOIN buses bus ON s.bus_id = bus.bus_id
            WHERE p.payment_id = ?
        ");
        $stmt->execute([$id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($data) {
            $invoice->fillFromArray($data);
            $invoice->loadSeats();
            return $invoice;
        }
        return null;
    }

    /**
     * Tìm hóa đơn theo booking ID
     */
    public static function findByBookingId($bookingId)
    {
        $invoice = new self();
        $stmt = $invoice->pdo->prepare("
            SELECT p.*, b.booking_time, b.total_price as booking_total,
                   u.full_name as user_name, u.email as user_email, u.phone_number as user_phone,
                   r.start_point as route_start, r.end_point as route_end,
                   s.departure_time, s.arrival_time,
                   bus.license_plate as bus_license_plate
            FROM payments p
            JOIN bookings b ON p.booking_id = b.booking_id
            JOIN users u ON b.user_id = u.user_id
            JOIN schedules s ON b.schedule_id = s.schedule_id
            JOIN routes r ON s.route_id = r.route_id
            JOIN buses bus ON s.bus_id = bus.bus_id
            WHERE p.booking_id = ?
            ORDER BY p.payment_time DESC
            LIMIT 1
        ");
        $stmt->execute([$bookingId]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($data) {
            $invoice->fillFromArray($data);
            $invoice->loadSeats();
            return $invoice;
        }
        return null;
    }

    /**
     * Lấy tất cả hóa đơn
     */
    public static function all($limit = null, $offset = 0)
    {
        $invoice = new self();
        $sql = "
            SELECT p.*, b.booking_time, b.total_price as booking_total,
                   u.full_name as user_name, u.email as user_email, u.phone_number as user_phone,
                   r.start_point as route_start, r.end_point as route_end,
                   s.departure_time, s.arrival_time,
                   bus.license_plate as bus_license_plate
            FROM payments p
            JOIN bookings b ON p.booking_id = b.booking_id
            JOIN users u ON b.user_id = u.user_id
            JOIN schedules s ON b.schedule_id = s.schedule_id
            JOIN routes r ON s.route_id = r.route_id
            JOIN buses bus ON s.bus_id = bus.bus_id
            ORDER BY p.payment_time DESC
        ";

        if ($limit) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }

        $stmt = $invoice->pdo->query($sql);
        $invoices = [];

        while ($data = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $invoiceObj = new self($invoice->pdo);
            $invoiceObj->fillFromArray($data);
            $invoiceObj->loadSeats();
            $invoices[] = $invoiceObj;
        }

        return $invoices;
    }

    /**
     * Lấy hóa đơn theo user ID
     */
    public static function getByUserId($userId)
    {
        $invoice = new self();
        $stmt = $invoice->pdo->prepare("
            SELECT p.*, b.booking_time, b.total_price as booking_total,
                   u.full_name as user_name, u.email as user_email, u.phone_number as user_phone,
                   r.start_point as route_start, r.end_point as route_end,
                   s.departure_time, s.arrival_time,
                   bus.license_plate as bus_license_plate
            FROM payments p
            JOIN bookings b ON p.booking_id = b.booking_id
            JOIN users u ON b.user_id = u.user_id
            JOIN schedules s ON b.schedule_id = s.schedule_id
            JOIN routes r ON s.route_id = r.route_id
            JOIN buses bus ON s.bus_id = bus.bus_id
            WHERE b.user_id = ?
            ORDER BY p.payment_time DESC
        ");
        $stmt->execute([$userId]);
        $invoices = [];

        while ($data = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $invoiceObj = new self($invoice->pdo);
            $invoiceObj->fillFromArray($data);
            $invoiceObj->loadSeats();
            $invoices[] = $invoiceObj;
        }

        return $invoices;
    }

    /**
     * Tạo hóa đơn mới
     */
    public function create($data)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO payments (booking_id, payment_method, amount, status, transaction_code, payment_time)
            VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
        ");

        $result = $stmt->execute([
            $data['booking_id'],
            $data['payment_method'],
            $data['amount'],
            $data['status'] ?? 'pending',
            $data['transaction_code'] ?? null
        ]);

        if ($result) {
            $this->payment_id = $this->pdo->lastInsertId();
            $this->booking_id = $data['booking_id'];
            $this->payment_method = $data['payment_method'];
            $this->amount = $data['amount'];
            $this->status = $data['status'] ?? 'pending';
            $this->transaction_code = $data['transaction_code'] ?? null;
        }

        return $result;
    }

    /**
     * Cập nhật trạng thái thanh toán
     */
    public function updateStatus($status, $transactionCode = null)
    {
        $stmt = $this->pdo->prepare("
            UPDATE payments 
            SET status = ?, transaction_code = COALESCE(?, transaction_code)
            WHERE payment_id = ?
        ");

        $result = $stmt->execute([$status, $transactionCode, $this->payment_id]);

        if ($result) {
            $this->status = $status;
            if ($transactionCode) {
                $this->transaction_code = $transactionCode;
            }

            // Cập nhật trạng thái booking nếu thanh toán thành công
            if ($status === 'success') {
                $this->updateBookingStatus('paid');
            }
        }

        return $result;
    }

    /**
     * Cập nhật trạng thái booking
     */
    private function updateBookingStatus($status)
    {
        $stmt = $this->pdo->prepare("
            UPDATE bookings SET status = ?
            WHERE booking_id = ?
        ");
        return $stmt->execute([$status, $this->booking_id]);
    }

    /**
     * Tìm kiếm hóa đơn
     */
    public static function search($conditions, $limit = null, $offset = 0)
    {
        $invoice = new self();
        $sql = "
            SELECT p.*, b.booking_time, b.total_price as booking_total,
                   u.full_name as user_name, u.email as user_email, u.phone_number as user_phone,
                   r.start_point as route_start, r.end_point as route_end,
                   s.departure_time, s.arrival_time,
                   bus.license_plate as bus_license_plate
            FROM payments p
            JOIN bookings b ON p.booking_id = b.booking_id
            JOIN users u ON b.user_id = u.user_id
            JOIN schedules s ON b.schedule_id = s.schedule_id
            JOIN routes r ON s.route_id = r.route_id
            JOIN buses bus ON s.bus_id = bus.bus_id
            WHERE 1=1
        ";

        $params = [];

        if (!empty($conditions['user_id'])) {
            $sql .= " AND b.user_id = ?";
            $params[] = $conditions['user_id'];
        }

        if (!empty($conditions['status'])) {
            $sql .= " AND p.status = ?";
            $params[] = $conditions['status'];
        }

        if (!empty($conditions['payment_method'])) {
            $sql .= " AND p.payment_method = ?";
            $params[] = $conditions['payment_method'];
        }

        if (!empty($conditions['date_from'])) {
            $sql .= " AND DATE(p.payment_time) >= ?";
            $params[] = $conditions['date_from'];
        }

        if (!empty($conditions['date_to'])) {
            $sql .= " AND DATE(p.payment_time) <= ?";
            $params[] = $conditions['date_to'];
        }

        if (!empty($conditions['user_name'])) {
            $sql .= " AND u.full_name LIKE ?";
            $params[] = "%{$conditions['user_name']}%";
        }

        $sql .= " ORDER BY p.payment_time DESC";

        if ($limit) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }

        $stmt = $invoice->pdo->prepare($sql);
        $stmt->execute($params);
        $invoices = [];

        while ($data = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $invoiceObj = new self($invoice->pdo);
            $invoiceObj->fillFromArray($data);
            $invoiceObj->loadSeats();
            $invoices[] = $invoiceObj;
        }

        return $invoices;
    }

    /**
     * Lấy thống kê thanh toán
     */
    public static function getStats($dateFrom = null, $dateTo = null)
    {
        $invoice = new self();
        $sql = "
            SELECT 
                COUNT(*) as total_payments,
                SUM(CASE WHEN status = 'success' THEN amount ELSE 0 END) as total_revenue,
                SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_amount,
                COUNT(CASE WHEN status = 'success' THEN 1 END) as successful_payments,
                COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_payments
            FROM payments p
            WHERE 1=1
        ";

        $params = [];

        if ($dateFrom) {
            $sql .= " AND DATE(p.payment_time) >= ?";
            $params[] = $dateFrom;
        }

        if ($dateTo) {
            $sql .= " AND DATE(p.payment_time) <= ?";
            $params[] = $dateTo;
        }

        $stmt = $invoice->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
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
     * Điền dữ liệu từ mảng
     */
    private function fillFromArray($data)
    {
        $this->payment_id = $data['payment_id'] ?? null;
        $this->booking_id = $data['booking_id'] ?? null;
        $this->payment_method = $data['payment_method'] ?? null;
        $this->payment_time = $data['payment_time'] ?? null;
        $this->amount = $data['amount'] ?? null;
        $this->status = $data['status'] ?? null;
        $this->transaction_code = $data['transaction_code'] ?? null;

        // Related data
        $this->booking_time = $data['booking_time'] ?? null;
        $this->user_name = $data['user_name'] ?? null;
        $this->user_email = $data['user_email'] ?? null;
        $this->user_phone = $data['user_phone'] ?? null;
        $this->route_start = $data['route_start'] ?? null;
        $this->route_end = $data['route_end'] ?? null;
        $this->departure_time = $data['departure_time'] ?? null;
        $this->arrival_time = $data['arrival_time'] ?? null;
        $this->bus_license_plate = $data['bus_license_plate'] ?? null;
    }

    /**
     * Chuyển đổi thành mảng
     */
    public function toArray()
    {
        return [
            'payment_id' => $this->payment_id,
            'booking_id' => $this->booking_id,
            'payment_method' => $this->payment_method,
            'payment_time' => $this->payment_time,
            'amount' => $this->amount,
            'status' => $this->status,
            'transaction_code' => $this->transaction_code,
            'booking_time' => $this->booking_time,
            'user_name' => $this->user_name,
            'user_email' => $this->user_email,
            'user_phone' => $this->user_phone,
            'route_start' => $this->route_start,
            'route_end' => $this->route_end,
            'departure_time' => $this->departure_time,
            'arrival_time' => $this->arrival_time,
            'bus_license_plate' => $this->bus_license_plate,
            'seats' => $this->seats
        ];
    }
}
