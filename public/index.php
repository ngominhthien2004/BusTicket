<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/db.php';

use Bramus\Router\Router;

define('APPNAME', 'Busticket');

$router = new Router();

require_once __DIR__ . '/../routes/web.php';
$router->run();
