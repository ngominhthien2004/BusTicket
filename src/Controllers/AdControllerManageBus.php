<?php

namespace Ct27501Project\Controllers;

use Ct27501Project\Controllers\Controller;
use Ct27501Project\Models\Bus;
use Exception;

class AdControllerManageBus extends Controller
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

    function getAllBus()
    {
        $bus = new Bus(PDO());

        //lay filter tu get
        $filters = [
            'license_plate' => $_GET['license_plate'] ?? '',
            'driver_name' => $_GET['driver_name'] ?? '',
            'seat_count' => $_GET['seat_count'] ?? '',
            'bus_type' => $_GET['bus_type'] ?? '',
        ];

        // Debug - log để kiểm tra
        error_log('Search filters: ' . print_r($filters, true));

        //tim kiem xe theo filter
        if (!empty($filters['license_plate']) || !empty($filters['driver_name']) || !empty($filters['seat_count']) || !empty($filters['bus_type'])) {
            error_log('Using search method');
            $buses = $bus->search($filters);
        } else {
            error_log('Using all() method');
            //neu khong co filter, lay tat ca xe
            $buses = $bus->all();
        }

        error_log('Found ' . count($buses) . ' buses');
        $data = [
            'buses' => $buses,
            'filters' => $filters // Truyền filters để hiển thị lại trong form
        ];

        return $this->sendPage('/admin/allBus', $data);
    }


    function addBus()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $bus = BUS();
                $bus->license_plate = $_POST['license_plate'];
                $bus->driver_name = $_POST['driver_name'];
                $bus->seat_count = $_POST['seat_count'];
                $bus->bus_type = $_POST['bus_type'];

                $bus->save();

                $_SESSION['success'] = 'Thêm xe thành công!';
                redirect('/manage_allBus');
            } catch (Exception $e) {
                $_SESSION['error'] = 'Lỗi khi thêm xe: ' . $e->getMessage();
                error_log('Error adding bus: ' . $e->getMessage());
            }
        }

        return $this->sendPage('/admin/addBus');
    }

    function editBus($id)
    {
        try {
            $bus = BUS()->find($id);

            if (!$bus) {
                $_SESSION['error'] = 'Không tìm thấy xe bus';
                redirect('/manage_allBus');
                return;
            }

            return $this->sendPage('/admin/editBus', ['bus' => $bus]);
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi khi tải thông tin xe: ' . $e->getMessage();
            redirect('/manage_allBus');
        }
    }

    function updateBus($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $bus = BUS()->find($id);

                if (!$bus) {
                    throw new Exception('Không tìm thấy xe bus');
                }

                $bus->license_plate = $_POST['license_plate'];
                $bus->driver_name = $_POST['driver_name'];
                $bus->seat_count = $_POST['seat_count'];
                $bus->bus_type = $_POST['bus_type'];

                $bus->save();

                $_SESSION['success'] = 'Cập nhật xe thành công!';
                redirect('/manage_allBus');
            } catch (Exception $e) {
                $_SESSION['error'] = 'Lỗi khi cập nhật xe: ' . $e->getMessage();
                error_log('Error updating bus: ' . $e->getMessage());
                // Redirect về form edit với error
                redirect('/manage_editBus/' . $id);
            }
        }

        // Nếu không phải POST, redirect về GET route
        redirect('/manage_editBus/' . $id);
    }

    function searchBus()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $searchTerm = $_POST['search_term'] ?? '';
            $bus = new Bus();
            $buses = $bus->search($searchTerm);
            return $this->sendPage('/admin/allBus', ['buses' => $buses, 'search_term' => $searchTerm]);
        }

        // Nếu không phải POST, redirect về trang danh sách tất cả xe
        redirect('/manage_allBus');
    }
}
