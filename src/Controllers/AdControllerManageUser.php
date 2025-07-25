<?php

namespace Ct27501Project\Controllers;

use Ct27501Project\Controllers\Controller;
use Ct27501Project\Models\User;
use Exception;

class AdControllerManageUser extends Controller
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

    public function getAllUsers()
    {
        $userModel = new User(PDO());

        // Lấy filters từ GET parameters
        $filters = [
            'email' => $_GET['email'] ?? '',
            'phone_number' => $_GET['phone_number'] ?? '',
            'role' => 'user'
        ];

        // Tìm kiếm users
        if (!empty($filters['email']) || !empty($filters['phone_number'])) {
            $users = $userModel->searchUsers($filters);
        } else {
            $users = $userModel->searchUsers($filters);
        }

        $data = [
            'users' => $users,
            'filters' => $filters // Truyền filters để hiển thị lại trong form
        ];

        return $this->sendPage('admin/manage_user', $data);
    }


    public function getAllAdmin()
    {
        $userModel = new User(PDO());

        // Lấy filters từ GET parameters
        $filters = [
            'email' => $_GET['email'] ?? '',
            'phone_number' => $_GET['phone_number'] ?? '',
            'role' => 'admin'
        ];

        // Tìm kiếm users
        if (!empty($filters['email']) || !empty($filters['phone_number'])) {
            $users = $userModel->searchUsers($filters);
        } else {
            $users = $userModel->searchUsers($filters);
        }

        $data = [
            'users' => $users,
            'filters' => $filters // Truyền filters để hiển thị lại trong form
        ];

        return $this->sendPage('admin/manage_admin', $data);
    }

    public function deleteUser($userId)
    {
        try {
            $userModel = new User(PDO());
            $user = $userModel->find($userId);
            if (!$user) {
                $_SESSION['error'] = 'Người dùng không tồn tại';
                redirect('/manage_user');
                return;
            }

            if ($user->role === 'admin') {
                $_SESSION['error'] = 'Không thể xóa tài khoản admin';
                redirect('/manage_user');
                return;
            }

            // Xóa user
            $result = $userModel->delete($userId);

            if ($result) {
                $_SESSION['success'] = 'Xóa người dùng thành công';
            } else {
                $_SESSION['error'] = 'Có lỗi xảy ra khi xóa người dùng';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Có lỗi xảy ra: ' . $e->getMessage();
        }
        redirect('/manage_user');
    }

    public function updateUserRole($userId)
    {
        try {
            // Lấy role mới từ POST request
            $newRole = $_POST['role'] ?? '';

            // Validate role
            if (!in_array($newRole, ['user', 'admin'])) {
                $_SESSION['error'] = 'Role không hợp lệ';
                redirect('/manage_user');
                return;
            }

            // Tìm user
            $user = User::find($userId);
            if (!$user) {
                $_SESSION['error'] = 'Người dùng không tồn tại';
                redirect('/manage_user');
                return;
            }

            // Cập nhật role
            $result = $user->update(['role' => $newRole]);

            if ($result) {
                $_SESSION['success'] = 'Cập nhật quyền thành công';
            } else {
                $_SESSION['error'] = 'Có lỗi xảy ra khi cập nhật quyền';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Có lỗi xảy ra: ' . $e->getMessage();
        }

        redirect('/manage_user');
    }
}
