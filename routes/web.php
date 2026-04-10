<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/OAuthController.php';
require_once __DIR__ . '/../controllers/MovieController.php';
require_once __DIR__ . '/../controllers/BookingController.php';
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../controllers/RoomController.php';
require_once __DIR__ . '/../controllers/ShowtimeController.php';
require_once __DIR__ . '/../controllers/PromotionController.php';
require_once __DIR__ . '/../controllers/OrderController.php';
require_once __DIR__ . '/../controllers/ReportController.php';

$auth = new AuthController();
$oauth = new OAuthController();
$movieController = new MovieController();
$bookingController = new BookingController();
$userController = new UserController();
$roomController = new RoomController();
$showtimeController = new ShowtimeController();
$promotionController = new PromotionController();
$orderController = new OrderController();
$reportController = new ReportController();

$action = $_GET['action'] ?? 'home';

function require_admin_access(string $fallback = 'home'): void {
    if (!isAdmin()) {
        set_flash('danger', 'Bạn không có quyền truy cập khu vực quản trị.');
        header('Location: ' . app_url($fallback));
        exit;
    }
}

function route_admin(callable $callback): void {
    require_admin_access();
    $callback();
}

switch ($action) {
    case 'home':
        $movieController->home();
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

    # Admin dashboard and reports
    case 'dashboard':
    case 'admin_dashboard':
        route_admin(fn() => $reportController->dashboard());
        break;
    case 'reports':
    case 'admin_reports':
        route_admin(fn() => $reportController->reports());
        break;

    # Admin users
    case 'admin_employees':
    case 'employees':
        route_admin(fn() => $userController->indexEmployee());
        break;
    case 'admin_create_employee':
    case 'create_employee':
        route_admin(fn() => $userController->create('staff'));
        break;
    case 'admin_customers':
    case 'customers':
        route_admin(fn() => $userController->indexCustomer());
        break;
    case 'admin_create_customer':
    case 'create_customer':
        route_admin(fn() => $userController->create('customer'));
        break;
    case 'admin_store_user':
    case 'store_user':
        route_admin(fn() => $userController->store());
        break;
    case 'admin_edit_employee':
    case 'edit_employee':
    case 'admin_edit_customer':
    case 'edit_customer':
        if (isset($_GET['id'])) {
            route_admin(fn() => $userController->edit((int) $_GET['id']));
        }
        break;
    case 'admin_update_user':
    case 'update_user':
        route_admin(fn() => $userController->update());
        break;
    case 'admin_delete_user':
    case 'delete_user':
        route_admin(fn() => $userController->delete());
        break;
    case 'admin_toggle_status_user':
    case 'toggle_status_user':
        route_admin(fn() => $userController->toggleStatus());
        break;

    # Admin movies
    case 'admin_movies':
    case 'movies':
        route_admin(fn() => $movieController->index());
        break;
    case 'admin_create_movie':
    case 'create_movie':
        route_admin(fn() => $movieController->create());
        break;
    case 'admin_store_movie':
    case 'store_movie':
        route_admin(fn() => $movieController->store());
        break;
    case 'admin_edit_movie':
    case 'edit_movie':
        if (isset($_GET['id'])) {
            route_admin(fn() => $movieController->edit((int) $_GET['id']));
        }
        break;
    case 'admin_update_movie':
    case 'update_movie':
        route_admin(fn() => $movieController->update());
        break;
    case 'admin_delete_movie':
    case 'delete_movie':
        route_admin(fn() => $movieController->delete());
        break;

    # Admin showtimes
    case 'admin_showtimes':
    case 'showtimes':
        route_admin(fn() => $showtimeController->index());
        break;
    case 'admin_create_showtime':
    case 'create_showtime':
        route_admin(fn() => $showtimeController->create());
        break;
    case 'admin_store_showtime':
    case 'store_showtime':
        route_admin(fn() => $showtimeController->store());
        break;
    case 'admin_edit_showtime':
    case 'edit_showtime':
        if (isset($_GET['id'])) {
            route_admin(fn() => $showtimeController->edit((int) $_GET['id']));
        }
        break;
    case 'admin_update_showtime':
    case 'update_showtime':
        route_admin(fn() => $showtimeController->update());
        break;
    case 'admin_delete_showtime':
    case 'delete_showtime':
        route_admin(fn() => $showtimeController->delete());
        break;

    # Admin rooms
    case 'admin_rooms':
    case 'rooms':
        route_admin(fn() => $roomController->index());
        break;
    case 'admin_create_room':
    case 'create_room':
        route_admin(fn() => $roomController->create());
        break;
    case 'admin_store_room':
    case 'store_room':
        route_admin(fn() => $roomController->store());
        break;
    case 'admin_edit_room':
    case 'edit_room':
        if (isset($_GET['id'])) {
            route_admin(fn() => $roomController->edit((int) $_GET['id']));
        }
        break;
    case 'admin_update_room':
    case 'update_room':
        route_admin(fn() => $roomController->update());
        break;
    case 'admin_delete_room':
    case 'delete_room':
        route_admin(fn() => $roomController->delete());
        break;
    case 'admin_room_seats':
    case 'room_seats':
        if (isset($_GET['id'])) {
            route_admin(fn() => $roomController->showSeats((int) $_GET['id']));
        }
        break;
    case 'admin_generate_seats':
    case 'generate_seats':
        route_admin(fn() => $roomController->generateSeats());
        break;
    case 'admin_toggle_seat':
    case 'toggle_seat':
        route_admin(fn() => $roomController->toggleSeat());
        break;

    # Admin promotions (prefixed to avoid collision with public promotions page)
    case 'admin_promotions':
        route_admin(fn() => $promotionController->index());
        break;
    case 'admin_create_promotion':
    case 'create_promotion':
        route_admin(fn() => $promotionController->create());
        break;
    case 'admin_store_promotion':
    case 'store_promotion':
        route_admin(fn() => $promotionController->store());
        break;
    case 'admin_edit_promotion':
    case 'edit_promotion':
        if (isset($_GET['id'])) {
            route_admin(fn() => $promotionController->edit((int) $_GET['id']));
        }
        break;
    case 'admin_update_promotion':
    case 'update_promotion':
        route_admin(fn() => $promotionController->update());
        break;
    case 'admin_delete_promotion':
    case 'delete_promotion':
        route_admin(fn() => $promotionController->delete());
        break;

    # Admin orders and ticket moderation
    case 'admin_orders':
    case 'orders':
        route_admin(fn() => $orderController->index());
        break;
    case 'admin_create_order':
    case 'create_order':
        route_admin(fn() => $orderController->create());
        break;
    case 'admin_store_order':
    case 'store_order':
        route_admin(fn() => $orderController->store());
        break;
    case 'admin_order_detail':
    case 'order_detail':
        if (isset($_GET['id'])) {
            route_admin(fn() => $orderController->detail((int) $_GET['id']));
        }
        break;
    case 'admin_update_order_status':
    case 'update_order_status':
        route_admin(fn() => $orderController->updateStatus());
        break;
    case 'admin_approve_order':
    case 'approve_order':
        route_admin(fn() => $orderController->approve());
        break;
    case 'admin_cancel_order':
    case 'cancel_order':
        route_admin(fn() => $orderController->cancel());
        break;
    case 'admin_cancellation_requests':
        route_admin(fn() => $orderController->cancellations());
        break;
    case 'admin_approve_cancel':
        route_admin(fn() => $orderController->approveCancellation());
        break;

    default:
        $movieController->home();
        break;
}
