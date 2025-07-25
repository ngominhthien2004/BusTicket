<?php

namespace Ct27501Project\Controllers;

require_once __DIR__ . '/../functions.php';

use Ct27501Project\Controllers\Controller;
use Ct27501Project\Models\Route;
use Ct27501Project\Models\Schedule;
use Ct27501Project\Models\Bus;
use Ct27501Project\Models\Seat;
use Ct27501Project\Models\Booking;
use Exception;

class BookingController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function showBookingPage()
    {
        // Check if user is logged in
        if (!AUTHGUARD()->isUserLoggedIn()) {
            redirect('/login', ['messages' => ['Vui lòng đăng nhập để đặt vé.']]);
            return;
        }

        $routeId = $_GET['route_id'] ?? null;
        $scheduleId = $_GET['schedule_id'] ?? null;

        if (!$routeId) {
            redirect('/', ['messages' => ['Vui lòng chọn tuyến đường để đặt vé.']]);
            return;
        }

        try {
            global $PDO;

            // Get route information
            $route = Route::find($routeId);
            if (!$route) {
                redirect('/', ['messages' => ['Tuyến đường không tồn tại.']]);
                return;
            }

            // Get available schedules for this route
            $scheduleModel = new Schedule($PDO);
            $schedules = $scheduleModel->getByRouteId($routeId);

            // If no schedules exist, create sample data
            if (empty($schedules)) {
                $this->createSampleScheduleData($routeId);
                $schedules = $scheduleModel->getByRouteId($routeId);
            }

            $selectedSchedule = null;
            $bus = null;
            $seats = [];

            if ($scheduleId) {
                $selectedSchedule = Schedule::find($scheduleId);
                if ($selectedSchedule && $selectedSchedule->route_id == $routeId) {
                    // Get bus information
                    $bus = Bus::find($selectedSchedule->bus_id);

                    // Get seats for this bus
                    $seatModel = new Seat($PDO);
                    $allSeats = $seatModel->getByBusId($selectedSchedule->bus_id);

                    // If no seats exist, create them
                    if (empty($allSeats)) {
                        $this->createSampleSeats($selectedSchedule->bus_id);
                        $allSeats = $seatModel->getByBusId($selectedSchedule->bus_id);
                    }

                    // Get booked seats for this schedule
                    $bookingModel = new Booking($PDO);
                    $bookedSeats = $bookingModel->getBookedSeats($scheduleId);
                    $bookedSeatIds = array_column($bookedSeats, 'seat_id');

                    // Mark seats as booked
                    foreach ($allSeats as &$seat) {
                        $seat['is_booked'] = in_array($seat['seat_id'], $bookedSeatIds);
                    }
                    $seats = $allSeats;
                }
            } else if (!empty($schedules)) {
                // If no schedule is selected but schedules exist, select the first one
                $firstSchedule = $schedules[0];
                redirect("/booking?route_id={$routeId}&schedule_id={$firstSchedule['schedule_id']}");
                return;
            }

            return $this->sendPage('users/booking', [
                'route' => $route,
                'schedules' => $schedules,
                'selectedSchedule' => $selectedSchedule,
                'bus' => $bus,
                'seats' => $seats,
                'user' => AUTHGUARD()->user()
            ]);
        } catch (Exception $e) {
            error_log("Error in showBookingPage: " . $e->getMessage());
            redirect('/', ['messages' => ['Có lỗi xảy ra khi tải trang đặt vé.']]);
        }
    }

    private function createSampleScheduleData($routeId)
    {
        global $PDO;

        try {
            // Create a sample bus if none exists
            $stmt = $PDO->query("SELECT COUNT(*) FROM buses");
            $busCount = $stmt->fetchColumn();

            if ($busCount == 0) {
                $stmt = $PDO->prepare("
                    INSERT INTO buses (license_plate, driver_name, seat_count, bus_type) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute(['51B-12345', 'Nguyễn Văn A', 40, 'Giường nằm']);
                $busId = $PDO->lastInsertId();
            } else {
                $stmt = $PDO->query("SELECT bus_id FROM buses LIMIT 1");
                $busId = $stmt->fetchColumn();
            }

            // Create sample schedule
            $stmt = $PDO->prepare("
                INSERT INTO schedules (route_id, bus_id, departure_time, arrival_time, price) 
                VALUES (?, ?, ?, ?, ?)
            ");

            $departureTime = date('Y-m-d H:i:s', strtotime('+1 day 08:00:00'));
            $arrivalTime = date('Y-m-d H:i:s', strtotime('+1 day 18:00:00'));

            $stmt->execute([$routeId, $busId, $departureTime, $arrivalTime, 250000]);
        } catch (Exception $e) {
            error_log("Error creating sample schedule: " . $e->getMessage());
        }
    }

    private function createSampleSeats($busId)
    {
        global $PDO;

        try {
            // Create seats for the bus (40 seats total)
            $stmt = $PDO->prepare("INSERT INTO seats (bus_id, seat_number) VALUES (?, ?)");

            // Tầng dưới: A1-A20
            for ($i = 1; $i <= 20; $i++) {
                $stmt->execute([$busId, 'A' . str_pad($i, 2, '0', STR_PAD_LEFT)]);
            }

            // Tầng trên: B1-B20  
            for ($i = 1; $i <= 20; $i++) {
                $stmt->execute([$busId, 'B' . str_pad($i, 2, '0', STR_PAD_LEFT)]);
            }
        } catch (Exception $e) {
            error_log("Error creating sample seats: " . $e->getMessage());
        }
    }

    public function processBooking()
    {
        // Check if user is logged in
        if (!AUTHGUARD()->isUserLoggedIn()) {
            redirect('/login', ['messages' => ['Vui lòng đăng nhập để đặt vé.']]);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/', ['messages' => ['Phương thức không hợp lệ.']]);
            return;
        }

        try {
            global $PDO;

            $scheduleId = $_POST['schedule_id'] ?? null;
            $seatIds = $_POST['seat_ids'] ?? [];
            $paymentMethod = $_POST['payment_method'] ?? 'cash';

            if (!$scheduleId || empty($seatIds)) {
                redirect('/booking?schedule_id=' . $scheduleId, ['messages' => ['Vui lòng chọn ghế và lịch trình.']]);
                return;
            }

            // Get schedule and price
            $schedule = Schedule::find($scheduleId);
            if (!$schedule) {
                redirect('/', ['messages' => ['Lịch trình không tồn tại.']]);
                return;
            }

            // Check if seats are still available
            $bookingModel = new Booking($PDO);
            $bookedSeats = $bookingModel->getBookedSeats($scheduleId);
            $bookedSeatIds = array_column($bookedSeats, 'seat_id');

            foreach ($seatIds as $seatId) {
                if (in_array($seatId, $bookedSeatIds)) {
                    redirect('/booking?schedule_id=' . $scheduleId, ['messages' => ['Một số ghế đã được đặt. Vui lòng chọn ghế khác.']]);
                    return;
                }
            }

            // Calculate total price
            $totalPrice = count($seatIds) * $schedule->price;

            // Create booking
            $bookingData = [
                'user_id' => AUTHGUARD()->user()->user_id,
                'schedule_id' => $scheduleId,
                'status' => 'booked',
                'payment_method' => $paymentMethod,
                'total_price' => $totalPrice,
                'seat_ids' => $seatIds
            ];

            $ticketModel = TICKET();
            $bookingId = $ticketModel->create($bookingData);

            if ($bookingId) {
                redirect('/ticket_lookup?booking_id=' . $bookingId, ['messages' => ['Đặt vé thành công! Mã đặt vé: ' . $bookingId]]);
            } else {
                redirect('/booking?schedule_id=' . $scheduleId, ['messages' => ['Có lỗi xảy ra khi đặt vé. Vui lòng thử lại.']]);
            }
        } catch (Exception $e) {
            error_log("Error in processBooking: " . $e->getMessage());
            redirect('/booking', ['messages' => ['Có lỗi xảy ra khi đặt vé.']]);
        }
    }
}
