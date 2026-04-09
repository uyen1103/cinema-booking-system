<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once 'controllers/AuthController.php';
require_once 'controllers/OAuthController.php';
require_once 'controllers/MovieController.php';
require_once 'controllers/BookingController.php';


$auth = new AuthController();
$oauth = new OAuthController();
$movieController = new MovieController();
$bookingController = new BookingController();


$action = $_GET['action'] ?? 'home';

switch ($action) {
    case 'home':
        $movieController->index();
        break;

    case 'movie':
        $movieController->detail();
        break;

    case 'select-showtime':
        $movieController->selectShowtime();
        break;

    case 'book':
        $movieController->book();
        break;

    case 'checkout':
        $movieController->checkout();
        break;

    case 'booking-success':
        $movieController->success();
        break;

    case 'booking-history':
    case 'history':
        $bookingController->history();
        break;

    case 'cancel-booking':
        $bookingController->cancelRequest();
        break;

    case 'cancellation-requests':
        $bookingController->cancellationRequests();
        break;

    case 'approve-cancel':
        $bookingController->approveCancellation();
        break;

    case 'register':
        $auth->register();
        break;

    case 'login':
        $auth->login();
        break;

    case 'profile':
        $auth->profile();
        break;

    case 'edit-profile':
        $auth->editProfile();
        break;

    case 'vouchers':
        $auth->vouchers();
        break;

    case 'forgot-password':
        $auth->forgotPassword();
        break;

    case 'logout':
        $auth->logout();
        break;

    case 'login-google':
        $oauth->googleLogin();
        break;

    case 'google-callback':
        $oauth->googleCallback();
        break;

    case 'theaters':
        $movieController->theaters();
        break;

    case 'promotions':
        $movieController->promotions();
        break;

    default:
        $movieController->index();
        break;
}