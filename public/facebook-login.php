<?php
require_once __DIR__ . '/../vendor/autoload.php';
session_start();

$fb = new \Facebook\Facebook([
    'app_id' => '1217008719905021',
    'app_secret' => '9562ec6263488152ff310cfed69f81d8',
    'default_graph_version' => 'v18.0',
]);

$helper = $fb->getRedirectLoginHelper();
//cac permission can thiet
$permissions = ['email', 'public_profile'];
//tao login URL
$loginUrl = $helper->getLoginUrl('http://localhost/facebook-callback.php', $permissions);
//chuyen huong den facebook
header('Location: ' . $loginUrl);
exit;
