<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/functions.php';

use Ct27501Project\Controllers\Auth\RegisterController;
use Ct27501Project\Controllers\Auth\LoginController;
use Ct27501Project\Controllers\HomePageController;
use Ct27501Project\Controllers\RouteController;
use Ct27501Project\Controllers\ScheduleController;
use Ct27501Project\Controllers\TicketController;
use Ct27501Project\Controllers\InvoiceController;
use Ct27501Project\Controllers\UserController;
use Ct27501Project\Controllers\AdControllerManageUser;
use Ct27501Project\Controllers\AdControllerManageRoutes;
use Ct27501Project\Controllers\AdControllerManageBus;
use Ct27501Project\Controllers\AdControllerManageSeats;
use Ct27501Project\Controllers\AdControllerManageSchedules;
use Ct27501Project\Controllers\BookingController;
use Ct27501Project\Controllers\AdControllerManageBooking;

$router->get('/register', function () {
    $controller = new RegisterController();
    return $controller->create();
});

$router->post('/register', function () {
    $controller = new RegisterController();
    return $controller->store();
});


$router->get('/login', function () {
    $controller = new LoginController();
    return $controller->create();
});

$router->post('/login', function () {
    $controller = new LoginController();
    return $controller->store();
});


$router->get('/auth/google', function () {
    header('Location: /google-login.php');
    exit();
});

$router->get('/google-callback', function () {
    require_once __DIR__ . '/../public/google-callback.php';
});


$router->get('/auth/facebook', function () {
    header('Location: /facebook-login.php');
    exit();
});

$router->get('facebook-callback', function () {
    require_once __DIR__ . '/../public/facebook-callback.php';
});



$router->get('/logout', function () {
    logout();
    redirect('/');
    return null;
});

$router->post('/logout', function () {
    logout();
    redirect('/');
    return null;
});

$router->get('/', function () {
    $controller = new RouteController();
    return $controller->home();
});

//route - Change to use ScheduleController
$router->get('/routes', function () {
    $controller = new ScheduleController();
    return $controller->index();
});

// Add API route for schedule details
$router->get('/api/schedule/(\d+)', function ($scheduleId) {
    $controller = new ScheduleController();
    return $controller->getScheduleDetails($scheduleId);
});


$router->get('/ticket_lookup', function () {
    $controller = new TicketController();
    return $controller->lookup_page();
});

$router->post('/ticket_lookup', function () {
    $controller = new TicketController();
    return $controller->lookup_page();
});



//invoice
$router->get('/invoice_lookup', function () {
    $controller = new InvoiceController();
    return $controller->lookup_page();
});

$router->post('/invoice_lookup', function () {
    $controller = new InvoiceController();
    return $controller->lookup_page();
});

$router->get('/invoice/(\d+)', function ($id) {
    $controller = new InvoiceController();
    return $controller->invoice_detail($id);
});

$router->get('/invoice_detail/(\d+)', function ($id) {
    $controller = new InvoiceController();
    return $controller->invoice_detail($id);
});


//account
$router->get('/account', function () {
    $controller = new UserController();
    return $controller->account_page();
});

$router->post('/account', function () {
    $controller = new UserController();
    return $controller->update_account();
});

$router->get('/user_history', function () {
    $controller = new UserController();
    return $controller->user_history();
});

$router->get('/change_password', function () {
    $controller = new UserController();
    return $controller->change_password_page();
});

$router->post('/change_password', function () {
    $controller = new UserController();
    return $controller->change_password_action();
});


//admin

//manage_user
$router->get('/manage_user', function () {
    $controller = new AdControllerManageUser();
    return $controller->getAllUsers();
});

$router->get('/manage_user/delete/(\d+)', function ($id) {
    $controller = new AdControllerManageUser();
    return $controller->deleteUser($id);
});

$router->post('/manage_user/update_role/(\d+)', function ($id) {
    $controller = new AdControllerManageUser();
    return $controller->updateUserRole($id);
});

//manage admin
$router->get('/manage_admin', function () {
    $controller = new AdControllerManageUser();
    return $controller->getAllAdmin();
});

$router->post('/manage_admin/update_role/(\d+)', function ($id) {
    $controller = new AdControllerManageUser();
    return $controller->updateUserRole($id);
});


//manageRoutes
$router->get('/manage_allRoute', function () {
    $controller = new AdControllerManageRoutes();
    return $controller->getAllRoutes();
});


$router->get('/manage_addRoute', function () {
    $controller = new AdControllerManageRoutes();
    return $controller->addRoute();
});

$router->post('/manage_addRoute', function () {
    $controller = new AdControllerManageRoutes();
    return $controller->createRoute();
});

$router->get('/manage_deleteRoute/delete/(\d+)', function ($id) {
    $controller = new AdControllerManageRoutes();
    return $controller->deleteRoute($id);
});

$router->get('/manage_editRoute/(\d+)', function ($id) {
    $controller = new AdControllerManageRoutes();
    return $controller->editRoute($id);
});

$router->post('/manage_editRoute/(\d+)', function ($id) {
    $controller = new AdControllerManageRoutes();
    return $controller->updateRoute($id);
});


$router->get('/manage_allBus', function () {
    $controller = new AdControllerManageBus();
    return $controller->getAllBus();
});


$router->get('/manage_addBus', function () {
    $controller = new AdControllerManageBus();
    return $controller->addBus();
});

$router->post('/manage_addBus', function () {
    $controller = new AdControllerManageBus();
    return $controller->addBus();
});


$router->get('/manage_editBus/(\d+)', function ($id) {
    $controller = new AdControllerManageBus();
    return $controller->editBus($id);
});

$router->post('/manage_editBus/(\d+)', function ($id) {
    $controller = new AdControllerManageBus();
    return $controller->updateBus($id);
});



//manage schedules

$router->get('/manage_allSchedules', function () {
    $controller = new AdControllerManageSchedules();
    return $controller->getAllSchedules();
});

// Route để tạo schedule (cả GET và POST)
$router->get('/create_schedule', function () {
    $controller = new AdControllerManageSchedules();
    return $controller->addSchedule();
});

$router->get('/create_schedule/(\d+)', function ($route_id) {
    $controller = new AdControllerManageSchedules();
    return $controller->addSchedule($route_id);
});

$router->post('/create_schedule', function () {
    $controller = new AdControllerManageSchedules();
    return $controller->addSchedule();
});

$router->post('/create_schedule/(\d+)', function ($route_id) {
    $controller = new AdControllerManageSchedules();
    return $controller->addSchedule($route_id);
});

$router->get('/manage_allSchedules/delete/(\d+)', function ($id) {
    $controller = new AdControllerManageSchedules();
    return $controller->deleteSchedule($id);
});

//seats
$router->post('/setting_seat', function () {
    $controller = new AdControllerManageSeats();
    return $controller->createSeatsForBus();
});

$router->post('/update_seats', function () {
    $controller = new AdControllerManageSeats();
    return $controller->updateSeats();
});
// Route để lấy xe khả dụng
$router->get('/get_available_buses', function () {
    $controller = new AdControllerManageSchedules();
    return $controller->getBusAvailable();
});

// Compatibility routes (cũ)
$router->get('/manage_addSchedule/(\d+)', function ($route_id) {
    redirect('/create_schedule/' . $route_id);
});

//booking
$router->get('/booking', function () {
    $controller = new BookingController();
    return $controller->showBookingPage();
});

$router->post('/booking', function () {
    $controller = new BookingController();
    return $controller->processBooking();
});

// Quản lý thanh toán booking (admin)
$router->get('/manageBooking', function () {
    $controller = new AdControllerManageBooking();
    return $controller->index();
});

$router->post('/manageBooking/confirm_payment/(\d+)', function ($booking_id) {
    $controller = new AdControllerManageBooking();
    return $controller->confirmPayment($booking_id);
});

// ONLY ONE $router->run() at the very end
$router->run();
