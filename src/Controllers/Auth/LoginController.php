<?php

namespace Ct27501Project\Controllers\Auth;

require_once __DIR__ . '/../../functions.php';

use Ct27501Project\Models\User;
use Ct27501Project\Controllers\Controller;

class LoginController extends Controller
{
    public function create()
    {
        if (AUTHGUARD()->isUserLoggedIn()) {
            redirect('/');
        }

        // Store redirect URL if provided
        $redirectUrl = $_GET['redirect'] ?? null;

        $data = [
            'messages' => session_get_once('messages'),
            'old' => $this->getSavedFormValues(),
            'errors' => session_get_once('errors'),
            'redirect_url' => $redirectUrl
        ];

        $this->sendPage('auth/login', $data);
    }

    public function store()
    {
        $user_credentials = $this->filterUserCredentials($_POST);

        $errors = [];

        // Kiểm tra email có hợp lệ không
        if (!$user_credentials['email']) {
            $errors['email'] = 'Email không hợp lệ.';
        }

        // Kiểm tra password có được nhập không
        if (empty($user_credentials['password'])) {
            $errors['password'] = 'Mật khẩu không được để trống.';
        }

        // Nếu có lỗi validation, return sớm
        if (!empty($errors)) {
            $this->saveFormValues($_POST, ['password']);
            redirect('/login', ['errors' => $errors]);
            return;
        }

        // Tìm user trong database theo email
        $user = (new User(PDO()))->where('email', $user_credentials['email']);

        if (!$user) {
            // Người dùng không tồn tại trong database
            $errors['email'] = 'Email hoặc mật khẩu không chính xác.';
        } else if (AUTHGUARD()->login($user, $user_credentials)) {
            // Đăng nhập thành công: user tồn tại và mật khẩu đúng
            error_log('Login successful for user: ' . $user->email);
            $redirectUrl = $_POST['redirect_url'] ?? '/';
            // Clear the redirect URL from session
            unset($_SESSION['redirect_url']);

            if ($user->role === 'admin') {
                redirect('/manage_allRoute');
            } else {
                redirect('/');
            }
            exit();
        } else {
            // Sai mật khẩu: user tồn tại nhưng password sai
            $errors['password'] = 'Email hoặc mật khẩu không chính xác.';
        }

        // Đăng nhập không thành công: lưu giá trị trong form, trừ password
        $this->saveFormValues($_POST, ['password']);
        redirect('/login', ['errors' => $errors]);
    }

    public function destroy()
    {
        AUTHGUARD()->logout();
        redirect('/login', ['messages' => ['Đăng xuất thành công!']]);
    }

    protected function filterUserCredentials(array $data)
    {
        return [
            'email' => filter_var($data['email'], FILTER_VALIDATE_EMAIL),
            'password' => $data['password'] ?? null
        ];
    }
}
