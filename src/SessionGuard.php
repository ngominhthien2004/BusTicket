<?php

namespace Ct27501Project;

use Ct27501Project\Models\User;

class SessionGuard
{
    protected $user;
    public function login(User $user, array $credentials)
    {
        // Kiểm tra user có tồn tại không
        if (!$user || !isset($user->password)) {
            error_log('Login failed: User not found or password field missing');
            return false;
        }

        // Kiểm tra password có được cung cấp không
        if (empty($credentials['password'])) {
            error_log('Login failed: Password not provided');
            return false;
        }

        // Kiểm tra xem password có được hash chưa
        $verified = false;
        if (password_get_info($user->password)['algo'] !== null) {
            // Password đã được hash - dùng password_verify
            $verified = password_verify($credentials['password'], $user->password);
        } else {
            // Password chưa được hash - so sánh trực tiếp (chỉ cho development)
            $verified = ($credentials['password'] === $user->password);
            if ($verified) {
                error_log('Warning: Plain text password detected for user: ' . $user->email);
            }
        }

        if ($verified) {
            $_SESSION['user_id'] = $user->user_id;
            error_log('Login successful for user ID: ' . $user->user_id);
        } else {
            error_log('Login failed: Password verification failed for user: ' . $user->email);
        }

        return $verified;
    }

    public function user()
    {
        if (!$this->user && $this->isUserLoggedIn()) {
            $this->user = (new User(PDO()))->where('user_id', $_SESSION['user_id']);
        }
        return $this->user;
    }

    public function logout()
    {
        // Clear user data
        $this->user = null;

        // Clear all session data
        $_SESSION = array();

        // Clear session cookie if it exists
        if (isset($_COOKIE[session_name()])) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // Destroy the session
        session_destroy();

        // Start new session for flash messages
        session_start();
    }

    public function isUserLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
}
