<?php
require_once __DIR__ . '/../vendor/autoload.php';

session_start();

// cau hinh OAuth2 client

$client = new Google\Client();
$client->setClientId("154065793858-it6p2gdttr4pev1auq7pr5s8ksu1tpug.apps.googleusercontent.com");
$client->setClientSecret("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");
$client->setRedirectUri("http://localhost/google-callback.php");
$client->addScope("email");
$client->addScope("profile");

//chuyen huong den trang gooogle de dang nhap
$authUrl = $client->createAuthUrl();
header('Location:' . filter_var($authUrl, FILTER_SANITIZE_URL));
exit;
