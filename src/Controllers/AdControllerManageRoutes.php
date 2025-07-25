<?php

namespace Ct27501Project\Controllers;

use Ct27501Project\Controllers\Controller;
use Ct27501Project\Models\Route;
use Exception;

class AdControllerManageRoutes extends Controller
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

    function getAllRoutes()
    {
        $filters = [
            'start_point' => $_GET['start_point'] ?? '',
            'end_point' => $_GET['end_point'] ?? '',
            'distance_km' => $_GET['distance_km'] ?? '',
        ];

        // Debug - log để kiểm tra
        error_log('Search filters: ' . print_r($filters, true));
        //tim kiem tuyen theo filter
        if (!empty($filters['start_point']) || !empty($filters['end_point']) || !empty($filters['distance_km'])) {
            error_log('Using search method');
            $routes = Route::search($filters);
        } else {
            error_log('Using all() method');
            //neu khong co filter, lay tat ca tuyen
            $routes = Route::all();
        }
        $data = [
            'routes' => $routes,
            'filters' => $filters
        ];
        $this->sendPage('/admin/allRoutes', $data);
    }

    function addRoute()
    {
        $this->sendPage('/admin/addRoute');
    }

    function createRoute()
    {
        $route = new Route(PDO());
        //lay du lieu tu form
        $start_point = $_POST['start_point'] ?? '';
        $end_point = $_POST['end_point'] ?? '';
        $distance_km = $_POST['distance_km'] ?? '';

        //kiem tra du lieu dau vao
        if (empty($start_point) || empty($end_point) || empty($distance_km)) {
            $_SESSION['error'] = 'Vui lòng nhập đầy đủ thông tin tuyến xe.';
            redirect('/manage_addRoute');
        }
        //kiem tra khoang cach
        if (!is_numeric($distance_km) || $distance_km <= 0) {
            $_SESSION['error'] = 'Khoảng cách phải là một số dương.';
            redirect('/manage_addRoute');
        }
        //kiem tra xem diem dau va diem cuoi co trung nhau khong
        if (strtolower($start_point) === strtolower($end_point)) {
            $_SESSION['error'] = 'Điểm đầu và điểm cuối không được trùng nhau.';
            redirect('/manage_addRoute');
        }
        //kiem tra xem tuyen da ton tai chua
        if ($route->exists($start_point, $end_point)) {
            $_SESSION['error'] = 'Tuyến xe đã tồn tại.';
            redirect('/manage_addRoute');
        }
        //tao tuyen xe moi
        try {
            $route->create([
                'start_point' => $start_point,
                'end_point' => $end_point,
                'distance_km' => $distance_km
            ]);
            $_SESSION['success'] = 'Tuyến xe đã được tạo thành công.';
            redirect('/manage_allRoute');
        } catch (Exception $e) {
            $_SESSION['error'] = 'Đã có lỗi xảy ra. Vui lòng thử lại.';
            redirect('/manage_addRoute');
        }
    }

    function deleteRoute($id)
    {
        $route = new Route(PDO());
        //kiem tra xem tuyen co lich trinh nao khong
        if ($route->hasSchedules($id)) {
            $_SESSION['error'] = 'Không thể xoá tuyến này vì nó đã có lịch trình.';
            redirect('/manage_allRoute');
        }

        //xoa tuyen
        try {
            $route->delete($id);
            $_SESSION['success'] = 'Tuyến xe đã được xoá thành công.';
            redirect('/manage_allRoute');
        } catch (Exception $e) {
            $_SESSION['error'] = 'Đã có lỗi xảy ra khi xoá tuyến. Vui lòng thử lại.';
            error_log('Error deleting route: ' . $e->getMessage());
            redirect('/manage_allRoute');
        }
    }

    function editRoute($id)
    {
        $route = new Route(PDO());
        //lay thong tin tuyen
        $routeData = $route->find($id);
        if (!$routeData) {
            $_SESSION['error'] = 'Không tìm thấy tuyến xe.';
            redirect('/manage_allRoute');
        }
        //gui du lieu toi view
        $this->sendPage('/admin/editRoute', ['route' => $routeData]);
    }

    function updateRoute($id)
    {
        $route = new Route(PDO());
        //Lay du lieu tu form
        $start_point = $_POST['start_point'] ?? '';
        $end_point = $_POST['end_point'] ?? '';
        $distance_km = $_POST['distance_km'] ?? '';
        //kiem tra du lieu dau vao
        if (empty($start_point) || empty($end_point) || empty($distance_km)) {
            $_SESSION['error'] = 'Vui lòng nhập đầy đủ thông tin tuyến xe.';
            redirect('/manage_editRoute/' . $id);
        }

        //kiem tra khoang cach
        if (!is_numeric($distance_km) || $distance_km <= 0) {
            $_SESSION['error'] = 'Khoảng cách phải là một số dương.';
            redirect('/manage_editRoute/' . $id);
        }

        //kiem tra xem diem dau va diem cuoi co trung nhau khong
        if (strtolower($start_point) === strtolower($end_point)) {
            $_SESSION['error'] = 'Điểm đầu và điểm cuối không được trùng nhau.';
            redirect('/manage_editRoute/' . $id);
        }

        //cap nhat tuyen xe
        try {
            $route->update([
                'route_id' => $id,
                'start_point' => $start_point,
                'end_point' => $end_point,
                'distance_km' => $distance_km
            ]);
            $_SESSION['success'] = 'Tuyến xe đã được cập nhật thành công.';
            redirect('/manage_allRoute');
        } catch (Exception $e) {
            $_SESSION['error'] = 'Đã có lỗi xảy ra. Vui lòng thử lại.';
            redirect('/manage_editRoute/' . $id);
        }
    }
}
