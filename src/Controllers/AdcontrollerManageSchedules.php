<?php

namespace Ct27501Project\Controllers;

use Ct27501Project\Controllers\Controller;
use Ct27501Project\Models\Schedule;
use Ct27501Project\Models\Route;
use Ct27501Project\Models\Bus;
use Exception;

class AdControllerManageSchedules extends Controller
{
    public function __construct()
    {
        parent::__construct();

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

    function getAllSchedules()
    {
        $filters = [
            'route_start' => $_GET['route_start'] ?? '',
            'route_end' => $_GET['route_end'] ?? '',
            'departure_time' => $_GET['departure_time'] ?? '',
            'seat_count' => $_GET['seat_count'] ?? '',
        ];
        $schedule = new Schedule(PDO());
        if (!empty($filters['route_start']) || !empty($filters['route_end']) || !empty($filters['departure_time']) || !empty($filters['seat_count'])) {
            // Tìm kiếm lịch trình theo filter
            $schedules = $schedule->search($filters);
        } else {
            // Nếu không có filter, lấy tất cả lịch trình
            $schedules = $schedule->all();
        }

        $data = [
            'schedules' => $schedules,
            'filters' => $filters
        ];
        $this->sendPage('/admin/allSchedules', $data);
    }

    function getBusAvailable($departure_time = null, $arrival_time = null)
    {
        try {
            // Lấy parameters từ GET request
            $departure_time = $_GET['departure_time'] ?? $departure_time;
            $arrival_time = $_GET['arrival_time'] ?? $arrival_time;
            $route_id = $_GET['route_id'] ?? null;

            // Validate input
            if (!$departure_time || !$arrival_time) {
                $_SESSION['error'] = 'Vui lòng chọn đầy đủ thời gian khởi hành và đến';
                redirect('/create_schedule' . ($route_id ? '/' . $route_id : ''));
                return;
            }

            // kiem tra các ràng buộc về thời gian
            if (strtotime($departure_time) >= strtotime($arrival_time)) {
                $_SESSION['error'] = 'Thời gian đến phải sau thời gian khởi hành';
                redirect('/create_schedule' . ($route_id ? '/' . $route_id : ''));
                return;
            }

            // Lấy danh sách xe có thể sử dụng trong khoảng thời gian này
            $buses = Bus::getAvailableBuses($departure_time, $arrival_time);

            // Lấy thông tin route nếu có
            $route = null;
            if ($route_id) {
                $route = Route::find($route_id);
            }



            $data = [
                'buses' => $buses,
                'route' => $route,
                'departure_time' => $departure_time,
                'arrival_time' => $arrival_time,
                'selected_route_id' => $route_id
            ];


            error_log('Available buses found: ' . count($buses));

            return $this->sendPage('/admin/addSchedule', $data);
        } catch (\Exception $e) {
            error_log('Error in getBusAvailable: ' . $e->getMessage());
            $_SESSION['error'] = 'Lỗi khi tìm xe khả dụng: ' . $e->getMessage();
            redirect('/create_schedule');
        }
    }


    function addSchedule($route_id = null)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $requiredFields = ['route_id', 'bus_id', 'departure_time', 'arrival_time', 'price'];
                foreach ($requiredFields as $field) {
                    if (empty($_POST[$field])) {
                        throw new Exception("Trường {$field} không được để trống");
                    }
                }

                // Validate thời gian
                if (strtotime($_POST['departure_time']) >= strtotime($_POST['arrival_time'])) {
                    throw new Exception('Thời gian đến phải sau thời gian khởi hành');
                }

                // Kiểm tra xe có khả dụng không
                $bus = Bus::find($_POST['bus_id']);
                if (!$bus) {
                    throw new Exception('Không tìm thấy xe');
                }

                // Tạo schedule mới (cần implement Schedule model)
                $schedule = new Schedule(PDO());
                $result = $schedule->create([
                    'route_id' => $_POST['route_id'],
                    'bus_id' => $_POST['bus_id'],
                    'departure_time' => $_POST['departure_time'],
                    'arrival_time' => $_POST['arrival_time'],
                    'price' => $_POST['price']
                ]);

                if ($result) {
                    $_SESSION['success'] = 'Tạo lịch trình thành công!';
                    redirect('/manage_allSchedules');
                } else {
                    throw new Exception('Không thể tạo lịch trình');
                }
            } catch (Exception $e) {
                $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();

                $queryParams = http_build_query($_POST);
                redirect('/create_schedule' . ($route_id ? '/' . $route_id : '') . '?' . $queryParams);
            }
        }


        try {
            // Lấy route được chọn
            $route = null;
            if ($route_id) {
                $route = Route::find($route_id);
                if (!$route) {
                    $_SESSION['error'] = 'Không tìm thấy tuyến xe';
                    redirect('/manage_allRoute');
                    return;
                }
            }

            // Lấy tất cả routes cho dropdown
            $routes = Route::all();

            // Lấy tất cả xe (sẽ được filter khi chọn thời gian)
            $buses = Bus::all();

            $data = [
                'buses' => $buses,
                'routes' => $routes,
                'route' => $route,
                'selected_route_id' => $route_id
            ];

            return $this->sendPage('/admin/addSchedule', $data);
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
            redirect('/manage_allRoute');
        }
    }

    function deleteSchedule($id)
    {
        try {
            if (empty($id)) {
                $_SESSION['error'] = 'ID lịch trình không hợp lệ';
                redirect('/manage_allSchedules');
                return;
            }
            $schedule = Schedule::find($id);
            if (!$schedule) {
                $_SESSION['error'] = 'Không tìm thấy lịch trình';
                redirect('/manage_allSchedules');
                return;
            }
            $result = $schedule->delete($id);

            if ($result) {
                $_SESSION['success'] = 'Xoá lịch trình thành công';
                redirect('/manage_allSchedules');
            } else {
                $_SESSION['error'] = 'Không thể xoá lịch trình này';
                redirect('/manage_allSchedules');
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
        }
    }
}
