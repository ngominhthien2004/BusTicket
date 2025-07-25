<?php

use Ct27501Project\SessionGuard;

if (!function_exists('PDO')) {
    function PDO(): PDO
    {
        global $PDO;
        return $PDO;
    }
}

if (!function_exists('AUTHGUARD')) {
    function AUTHGUARD(): Ct27501Project\SessionGuard
    {
        global $AUTHGUARD;
        if (!$AUTHGUARD) {
            $AUTHGUARD = new Ct27501Project\SessionGuard();
        }
        return $AUTHGUARD;
    }
}

if (!function_exists('dd')) {
    function dd($var)
    {
        var_dump($var);
        exit();
    }
}

if (!function_exists('redirect')) {
    // Chuyển hướng đến một trang khác
    function redirect($location, array $data = [])
    {
        foreach ($data as $key => $value) {
            $_SESSION[$key] = $value;
        }

        header('Location: ' . $location, true, 302);
        exit();
    }
}

if (!function_exists('session_get_once')) {
    // Đọc và xóa một biến trong $_SESSION
    function session_get_once($name, $default = null)
    {
        $value = $default;
        if (isset($_SESSION[$name])) {
            $value = $_SESSION[$name];
            unset($_SESSION[$name]);
        }
        return $value;
    }
}

if (!function_exists('logout')) {
    function logout()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Clear any existing user data first
        AUTHGUARD()->logout();

        // Clear OAuth callback flags
        unset($_SESSION['facebook_callback_processed']);
        unset($_SESSION['google_callback_processed']);

        // Then redirect with a success message
        redirect('/');
    }
}

if (!function_exists('TICKET')) {
    function TICKET(): Ct27501Project\Models\Ticket
    {
        return new Ct27501Project\Models\Ticket(PDO());
    }
}

if (!function_exists('SEAT')) {
    function SEAT(): Ct27501Project\Models\Seat
    {
        return new Ct27501Project\Models\Seat(PDO());
    }
}

if (!function_exists('BUS')) {
    function BUS(): Ct27501Project\Models\Bus
    {
        return new Ct27501Project\Models\Bus(PDO());
    }
}

if (!function_exists('INVOICE')) {
    function INVOICE(): Ct27501Project\Models\Invoice
    {
        return new Ct27501Project\Models\Invoice(PDO());
    }
}

if (!function_exists('BOOKING')) {
    function BOOKING(): Ct27501Project\Models\Booking
    {
        return new Ct27501Project\Models\Booking(PDO());
    }
}

if (!function_exists('ROUTE')) {
    function ROUTE(): Ct27501Project\Models\Route
    {
        return new Ct27501Project\Models\Route(PDO());
    }
}

if (!function_exists('SCHEDULE')) {
    function SCHEDULE(): Ct27501Project\Models\Schedule
    {
        return new Ct27501Project\Models\Schedule(PDO());
    }
}

if (!function_exists('USER')) {
    function USER(): Ct27501Project\Models\User
    {
        return new Ct27501Project\Models\User(PDO());
    }
}

if (!function_exists('uploadAvatar')) {
    function uploadAvatar($file, $userId)
    {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            return false;
        }

        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxSize) {
            return false;
        }

        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/public/access/img/avatar/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = $userId . '.jpg';
        $uploadPath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return true;
        }

        return false;
    }
}

if (!function_exists('getAvatarPath')) {
    function getAvatarPath($userId)
    {
        $avatarPath = "/access/img/avatar/" . $userId . ".jpg";
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . "/public" . $avatarPath;

        if (file_exists($fullPath)) {
            return $avatarPath;
        }

        return "/access/img/avatar_default.png";
    }
}
