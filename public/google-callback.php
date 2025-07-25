<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Models/PDOFactory.php';
require_once __DIR__ . '/../src/Models/User.php';
require_once __DIR__ . '/../src/functions.php';

use Ct27501Project\Models\PDOFactory;
use Ct27501Project\Models\User;

session_start();
$client = new Google\Client();
$client->setClientId("154065793858-it6p2gdttr4pev1auq7pr5s8ksu1tpug.apps.googleusercontent.com");
$client->setClientSecret("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");
$client->setRedirectUri("http://localhost/google-callback.php");

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $_SESSION['access_token'] = $token;
    $client->setAccessToken($token);

    $oauth2 = new Google\Service\Oauth2($client);
    $userInfo = $oauth2->userinfo->get();

    // Kết nối database và tạo/tìm user
    require_once __DIR__ . '/../config/db.php';
    $userModel = new User($PDO);

    // Tìm hoặc tạo user từ thông tin Google
    $user = $userModel->findOrCreateByGoogle([
        'id' => $userInfo->id,
        'name' => $userInfo->name,
        'email' => $userInfo->email
    ]);    // Lưu thông tin user vào session theo định dạng SessionGuard
    $_SESSION['user_id'] = $user->user_id;
    $_SESSION['user'] = [
        'user_id' => $user->user_id,
        'id' => $userInfo->id,
        'name' => $userInfo->name,
        'email' => $userInfo->email,
        'picture' => $userInfo->picture,
        'role' => $user->role
    ];

    // kiểm tra session đã được lưu chưa
    error_log('Google login success - User ID: ' . $user->user_id);
    error_log('Google login - $_SESSION[user_id]: ' . ($_SESSION['user_id'] ?? 'NOT SET'));
    error_log('Google login - Session user: ' . print_r($_SESSION['user'], true));
    error_log('Google login - AUTHGUARD check: ' . (AUTHGUARD()->isUserLoggedIn() ? 'LOGGED IN' : 'NOT LOGGED IN'));

    // Chuyển hướng về trang chủ
    header('Location: /');
    exit();
} else {
    header('Location: /login');
    exit();
}
