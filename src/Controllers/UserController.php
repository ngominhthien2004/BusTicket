<?php

namespace Ct27501Project\Controllers;


require_once __DIR__ . '/../functions.php';

use Ct27501Project\Controllers\Controller;
use Ct27501Project\Models\Booking;

class UserController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function account_page()
    {
        if (!AUTHGUARD()->isUserLoggedIn()) {
            redirect('/login');
        }

        $user = AUTHGUARD()->user();
        $data = [
            'user' => $user,
            'messages' => session_get_once('messages'),
            'errors' => session_get_once('errors'),
            'old' => $this->getSavedFormValues()
        ];

        return $this->sendPage('users/account', $data);
    }

    public function update_account()
    {
        if (!AUTHGUARD()->isUserLoggedIn()) {
            redirect('/login');
        }

        $user = AUTHGUARD()->user();
        $data = $this->validateAccountData($_POST);

        if (isset($data['errors'])) {
            $this->saveFormValues($_POST);
            redirect('/account', ['errors' => $data['errors']]);
            return;
        }

        // Handle avatar upload
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadAvatar($_FILES['avatar'], $user->user_id);
            if (!$uploadResult) {
                redirect('/account', ['errors' => ['Không thể tải lên ảnh đại diện. Vui lòng thử lại.']]);
                return;
            }
        }

        // Cập nhật thông tin user
        $user->full_name = $data['full_name'];
        $user->phone_number = $data['phone_number'];
        $user->email = $data['email'];

        if ($user->save()) {
            redirect('/account', ['messages' => ['Cập nhật thông tin thành công!']]);
        } else {
            redirect('/account', ['errors' => ['Có lỗi xảy ra khi cập nhật thông tin!']]);
        }
    }

    public function change_password_page()
    {
        if (!AUTHGUARD()->isUserLoggedIn()) {
            redirect('/login');
        }

        $data = [
            'messages' => session_get_once('messages'),
            'errors' => session_get_once('errors')
        ];

        return $this->sendPage('users/change_password', $data);
    }

    public function change_password_action()
    {
        if (!AUTHGUARD()->isUserLoggedIn()) {
            redirect('/login');
        }

        $user = AUTHGUARD()->user();
        $errors = [];

        // Validate input
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validate current password
        if (empty($currentPassword)) {
            $errors[] = 'Vui lòng nhập mật khẩu hiện tại';
        } else {
            // Check if current password is correct
            $isCurrentPasswordValid = false;

            if (!empty($user->password)) {
                // Check if password is hashed
                if (password_get_info($user->password)['algo'] !== null) {
                    // Password is hashed - use password_verify
                    $isCurrentPasswordValid = password_verify($currentPassword, $user->password);
                } else {
                    // Password is plain text - direct comparison
                    $isCurrentPasswordValid = ($currentPassword === $user->password);
                }
            }

            if (!$isCurrentPasswordValid) {
                $errors[] = 'Mật khẩu hiện tại không chính xác';
            }
        }

        // Validate new password
        if (empty($newPassword)) {
            $errors[] = 'Vui lòng nhập mật khẩu mới';
        } elseif (strlen($newPassword) < 6) {
            $errors[] = 'Mật khẩu mới phải có ít nhất 6 ký tự';
        }

        // Validate confirm password
        if (empty($confirmPassword)) {
            $errors[] = 'Vui lòng xác nhận mật khẩu mới';
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = 'Xác nhận mật khẩu không khớp';
        }

        // Check if new password is different from current
        if (!empty($currentPassword) && !empty($newPassword) && $currentPassword === $newPassword) {
            $errors[] = 'Mật khẩu mới phải khác mật khẩu hiện tại';
        }

        if (!empty($errors)) {
            redirect('/change_password', ['errors' => $errors]);
            return;
        }

        // Update password
        $user->password = password_hash($newPassword, PASSWORD_DEFAULT);

        if ($user->save()) {
            redirect('/change_password', ['messages' => ['Đổi mật khẩu thành công!']]);
        } else {
            redirect('/change_password', ['errors' => ['Có lỗi xảy ra khi đổi mật khẩu. Vui lòng thử lại.']]);
        }
    }

    public function user_history()
    {
        if (!AUTHGUARD()->isUserLoggedIn()) {
            redirect('/login');
        }

        $user = AUTHGUARD()->user();
        $bookingModel = new Booking(PDO());

        // Lấy filters từ GET parameters
        $filters = [
            'date' => $_GET['date'] ?? '',
            'route' => $_GET['route'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];

        // Lấy dữ liệu
        $bookings = $bookingModel->getUserBookings($user->user_id, $filters);
        $stats = $bookingModel->getBookingStats($user->user_id);
        $routes = $bookingModel->getUniqueRoutes($user->user_id);

        $data = [
            'user' => $user,
            'bookings' => $bookings,
            'stats' => $stats,
            'routes' => $routes,
            'filters' => $filters
        ];

        return $this->sendPage('users/history', $data);
    }

    public function index_page()
    {
        // Lấy dữ liệu cần thiết cho trang index nếu có
        $this->sendPage('users/index');
    }

    protected function validateAccountData(array $data)
    {
        $errors = [];
        $user = AUTHGUARD()->user();

        if (empty($data['full_name'])) {
            $errors['full_name'] = 'Vui lòng nhập họ và tên';
        }

        if (empty($data['phone_number'])) {
            $errors['phone_number'] = 'Vui lòng nhập số điện thoại';
        } elseif (!preg_match('/^[0-9]{10,11}$/', $data['phone_number'])) {
            $errors['phone_number'] = 'Số điện thoại không hợp lệ';
        }

        if (empty($data['email'])) {
            $errors['email'] = 'Vui lòng nhập email';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không hợp lệ';
        } else {
            // Check if email is already in use by another user
            $existingUser = USER()->where('email', $data['email']);
            if ($existingUser->user_id && $existingUser->user_id != $user->user_id) {
                $errors['email'] = 'Email này đã được sử dụng bởi tài khoản khác';
            }
        }

        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        return $data;
    }
}
