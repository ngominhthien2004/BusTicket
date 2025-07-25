<?php

namespace Ct27501Project\Controllers;

use Ct27501Project\Controllers\Controller;
use Ct27501Project\Models\Booking;
use PDO;

class AdControllerManageBooking extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    // Hiển thị danh sách booking chờ xác nhận chuyển khoản
    public function index()
    {
        global $PDO;
        // Lấy các booking có payment_method là bank_transfer và status là booked hoặc pending
        $stmt = $PDO->prepare("
            SELECT b.*, u.full_name as user_name, u.email as user_email, u.phone_number as user_phone,
                   r.start_point as route_start, r.end_point as route_end,
                   s.departure_time,
                   STRING_AGG(seats.seat_number, ', ') as seat_numbers
            FROM bookings b
            LEFT JOIN users u ON b.user_id = u.user_id
            LEFT JOIN schedules s ON b.schedule_id = s.schedule_id
            LEFT JOIN routes r ON s.route_id = r.route_id
            LEFT JOIN booking_details bd ON b.booking_id = bd.booking_id
            LEFT JOIN seats ON bd.seat_id = seats.seat_id
            WHERE b.payment_method = 'bank_transfer' AND b.status IN ('booked', 'pending')
            GROUP BY b.booking_id, u.full_name, u.email, u.phone_number, r.start_point, r.end_point, s.departure_time
            ORDER BY b.booking_time DESC
        ");
        $stmt->execute();
        $bookings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $booking = new Booking($PDO);
            $booking->fillFromArray($row);
            $bookings[] = $booking;
        }

        return $this->sendPage('admin/manageBooking', [
            'bookings' => $bookings
        ]);
    }

    // Xác nhận thanh toán chuyển khoản cho booking
    public function confirmPayment($booking_id)
    {
        global $PDO;
        // Tìm booking
        $booking = Booking::find($booking_id);
        if (!$booking) {
            $_SESSION['error'] = 'Không tìm thấy booking!';
            redirect('/manageBooking');
        }

        try {
            $PDO->beginTransaction();

            // Cập nhật trạng thái booking thành completed
            $stmt = $PDO->prepare("UPDATE bookings SET status = 'completed' WHERE booking_id = ?");
            $stmt->execute([$booking_id]);

            // Tạo payment record
            $stmt = $PDO->prepare("
                INSERT INTO payments (booking_id, payment_method, payment_time, amount, status, transaction_code)
                VALUES (?, ?, CURRENT_TIMESTAMP, ?, 'completed', ?)
            ");
            $transaction_code = 'ADMIN_CONFIRM_' . $booking_id . '_' . time();
            $stmt->execute([
                $booking_id,
                'bank_transfer',
                $booking->total_price,
                $transaction_code
            ]);

            $PDO->commit();
            $_SESSION['success'] = 'Xác nhận thanh toán thành công!';
        } catch (\Exception $e) {
            $PDO->rollBack();
            $_SESSION['error'] = 'Có lỗi xảy ra khi xác nhận thanh toán!';
        }
        redirect('/manageBooking');
    }
}
