<?php
error_reporting(E_ALL & ~E_DEPRECATED);
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Models/PDOFactory.php';
require_once __DIR__ . '/../src/Models/User.php';
require_once __DIR__ . '/../src/functions.php';

use Ct27501Project\Models\PDOFactory;
use Ct27501Project\Models\User;
use Facebook\Facebook;

session_start();


if (isset($_SESSION['facebook_callback_processed'])) {
    header('Location: /');
    exit();
}


$fb = new \Facebook\Facebook([
    'app_id' => '1217008719905021',
    'app_secret' => '9562ec6263488152ff310cfed69f81d8',
    'default_graph_version' => 'v18.0',
]);

$helper = $fb->getRedirectLoginHelper();

try {
    $accessToken = $helper->getAccessToken();
} catch (Facebook\Exceptions\FacebookAuthenticationException $e) {
    error_log('Facebook Authentication Error: ' . $e->getMessage());
    header('Location: /login?error=facebook_auth_failed');
    exit();
}

if (! isset($accessToken)) {
    if ($helper->getError()) {
        error_log('Facebook OAuth Error: ' . $helper->getError());
        header('Location: /login?error=facebook_oauth_failed');
    } else {
        header('Location: /login?error=facebook_bad_request');
    }
    exit();
}

try {
    $response = $fb->get('/me?fields=id,name,email,picture', $accessToken);
    $userInfo = $response->getGraphUser();
    //ket noi database tim/tao user
    require_once __DIR__ . '/../config/db.php';
    $userModel = new User($PDO);

    $user = $userModel->findOrCreateByFacebook([
        'id' => $userInfo->getId(),
        'name' => $userInfo->getName(),
        'email' => $userInfo->getEmail(),
    ]);

    //luu thong tin user vao session theo sessionguard
    $_SESSION['user_id'] = $user->user_id;
    $_SESSION['user'] = [
        'user_id' => $user->user_id,
        'id' => $userInfo->getId(),
        'name' => $userInfo->getName(),
        'email' => $userInfo->getEmail(),
        'picture' => $userInfo->getPicture()->getUrl(),
        'role' => $user->role
    ];

    // Đánh dấu đã xử lý callback này
    $_SESSION['facebook_callback_processed'] = true;

    // Debug - kiểm tra session đã được lưu chưa
    error_log('Facebook login success - User ID: ' . $user->user_id);
    error_log('Facebook login - $_SESSION[user_id]: ' . ($_SESSION['user_id'] ?? 'NOT SET'));
    error_log('Facebook login - Session user: ' . print_r($_SESSION['user'], true));
    error_log('Facebook login - AUTHGUARD check: ' . (AUTHGUARD()->isUserLoggedIn() ? 'LOGGED IN' : 'NOT LOGGED IN'));

    //chuyen huong ve trang chu
    header('Location: /');
    exit();
} catch (Facebook\Exceptions\FacebookResponseException $e) {
    error_log('Facebook Graph Error: ' . $e->getMessage());
    header('Location: /login?error=facebook_graph_failed');
    exit();
} catch (Facebook\Exceptions\FacebookSDKException $e) {
    error_log('Facebook SDK Error: ' . $e->getMessage());
    header('Location: /login?error=facebook_sdk_failed');
    exit();
}
