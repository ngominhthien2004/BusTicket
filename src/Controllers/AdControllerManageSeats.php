<?php

namespace Ct27501Project\Controllers;

use Ct27501Project\Controllers\Controller;
use Ct27501Project\Models\Bus;
use Ct27501Project\Models\Seat;
use Exception;

class AdControllerManageSeats extends Controller
{
    public function __construct()
    {
        parent::__construct();
        //kiem tra xem user dang nhap co quyen admin khong
        $this->checkAminPermission();
    }

    private function checkAminPermission()
    {
        if (!AUTHGUARD()->isUserLoggedIn()) {
            redirect('/login');
        }

        $user = AUTHGUARD()->user();
        if ($user->role !== 'admin') {
            redirect('/');
        }
    }

    public function createSeatsForBus()
    {
        $busId = $_POST['bus_id'] ?? null;
        $seatCount = $_POST['seat_count'] ?? 0;
        $seatNames = $_POST['seat_names'] ?? [];

        if (!$busId || $seatCount <= 0) {
            error_log('Invalid bus ID or seat count');
            return;
        }

        try {
            // Tạo ghế cho xe bus
            Seat::createSeatsForBus($busId, $seatCount, $seatNames);
            $_SESSION['success'] = 'Ghế đã được tạo thành công cho xe bus.';
        } catch (Exception $e) {
            error_log('Error creating seats: ' . $e->getMessage());
            $_SESSION['error'] = 'Đã xảy ra lỗi khi tạo ghế cho xe bus.';
        }
        redirect('/manage_allBus');
    }

    public function updateSeats()
    {
        $busId = $_POST['bus_id'] ?? null;
        $seatCount = $_POST['seat_count'] ?? 0;
        $seatNames = $_POST['seat_names'] ?? [];

        if (!$busId || $seatCount <= 0) {
            error_log('Invalid bus ID or seat count');
            return;
        }

        try {
            // Cập nhật ghế cho xe bus
            Seat::updateSeats($busId, $seatCount, $seatNames);
            $_SESSION['success'] = 'Ghế đã được cập nhật thành công cho xe bus.';
        } catch (Exception $e) {
            error_log('Error updating seats: ' . $e->getMessage());
            $_SESSION['error'] = 'Đã xảy ra lỗi khi cập nhật ghế cho xe bus.';
        }
        redirect('/manage_allBus');
    }
}
