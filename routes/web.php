<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../controllers/CustomerAuthController.php';
require_once __DIR__ . '/../controllers/AdminAuthController.php';
require_once __DIR__ . '/../controllers/OAuthController.php';
require_once __DIR__ . '/../controllers/MovieController.php';
require_once __DIR__ . '/../controllers/BookingController.php';
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../controllers/RoomController.php';
require_once __DIR__ . '/../controllers/ShowtimeController.php';
require_once __DIR__ . '/../controllers/PromotionController.php';
require_once __DIR__ . '/../controllers/OrderController.php';
require_once __DIR__ . '/../controllers/ReportController.php';

$customerAuth = new CustomerAuthController();
$adminAuth = new AdminAuthController();
$oauth = new OAuthController();
$movieController = new MovieController();
$bookingController = new BookingController();
$userController = new UserController();
$roomController = new RoomController();
$showtimeController = new ShowtimeController();
$promotionController = new PromotionController();
$orderController = new OrderController();
$reportController = new ReportController();

$requestedAction = trim((string) ($_GET['action'] ?? 'home'));
if ($requestedAction === '' || $requestedAction === '/' || $requestedAction === 'index.php') {
    $requestedAction = 'home';
}

$legacyAliases = [
    'dashboard' => 'admin_dashboard',
    'reports' => 'admin_reports',
    'employees' => 'admin_employees',
    'create_employee' => 'admin_create_employee',
    'store_user' => 'admin_store_user',
    'edit_employee' => 'admin_edit_employee',
    'update_user' => 'admin_update_user',
    'delete_user' => 'admin_delete_user',
    'toggle_status_user' => 'admin_toggle_status_user',
    'customers' => 'admin_customers',
    'create_customer' => 'admin_create_customer',
    'edit_customer' => 'admin_edit_customer',
    'movies' => 'admin_movies',
    'create_movie' => 'admin_create_movie',
    'store_movie' => 'admin_store_movie',
    'edit_movie' => 'admin_edit_movie',
    'update_movie' => 'admin_update_movie',
    'delete_movie' => 'admin_delete_movie',
    'showtimes' => 'admin_showtimes',
    'create_showtime' => 'admin_create_showtime',
    'store_showtime' => 'admin_store_showtime',
    'edit_showtime' => 'admin_edit_showtime',
    'update_showtime' => 'admin_update_showtime',
    'delete_showtime' => 'admin_delete_showtime',
    'rooms' => 'admin_rooms',
    'create_room' => 'admin_create_room',
    'store_room' => 'admin_store_room',
    'edit_room' => 'admin_edit_room',
    'update_room' => 'admin_update_room',
    'delete_room' => 'admin_delete_room',
    'room_seats' => 'admin_room_seats',
    'generate_seats' => 'admin_generate_seats',
    'toggle_seat' => 'admin_toggle_seat',
    'create_promotion' => 'admin_create_promotion',
    'store_promotion' => 'admin_store_promotion',
    'edit_promotion' => 'admin_edit_promotion',
    'update_promotion' => 'admin_update_promotion',
    'delete_promotion' => 'admin_delete_promotion',
    'orders' => 'admin_orders',
    'create_order' => 'admin_create_order',
    'store_order' => 'admin_store_order',
    'order_detail' => 'admin_order_detail',
    'update_order_status' => 'admin_update_order_status',
    'approve_order' => 'admin_approve_order',
    'cancel_order' => 'admin_cancel_order',
    'booking-history' => 'history',
];

$action = $legacyAliases[$requestedAction] ?? $requestedAction;

function redirect_to(string $url): void {
    header('Location: ' . $url);
    exit;
}

function ensure_customer_guest(): void {
    if (isEmployeeLoggedIn()) {
        redirect_to(admin_url('admin_dashboard'));
    }
    if (isCustomerLoggedIn()) {
        redirect_to(customer_url('home'));
    }
}

function ensure_customer_context(bool $allowGuests = true): void {
    if (isEmployeeLoggedIn()) {
        set_flash('warning', 'Tài khoản quản trị không thể truy cập chức năng khách hàng.');
        redirect_to(admin_url('admin_dashboard'));
    }
    if (!$allowGuests && !isCustomerLoggedIn()) {
        set_flash('warning', 'Vui lòng đăng nhập bằng tài khoản khách hàng để tiếp tục.');
        redirect_to(customer_url('login'));
    }
}

function ensure_employee_guest(): void {
    if (isEmployeeLoggedIn()) {
        redirect_to(admin_url('admin_dashboard'));
    }
    if (isCustomerLoggedIn()) {
        redirect_to(customer_url('home'));
    }
}

function ensure_admin_context(): void {
    if (!isEmployeeLoggedIn()) {
        set_flash('warning', 'Vui lòng đăng nhập bằng tài khoản quản trị để tiếp tục.');
        redirect_to(admin_url('admin_login'));
    }
    if (!isAdmin()) {
        set_flash('danger', 'Bạn không có quyền truy cập khu vực quản trị.');
        redirect_to(customer_url('home'));
    }
}

function route_customer_public(callable $callback): void {
    ensure_customer_context(true);
    $callback();
}

function route_customer_auth(callable $callback): void {
    ensure_customer_context(false);
    $callback();
}

function route_customer_guest(callable $callback): void {
    ensure_customer_guest();
    $callback();
}

function route_admin_guest(callable $callback): void {
    ensure_employee_guest();
    $callback();
}

function route_admin(callable $callback): void {
    ensure_admin_context();
    $callback();
}

$publicCustomerRoutes = [
    'home' => fn() => $movieController->home(),
    'movie' => fn() => $movieController->detail(),
    'select-showtime' => fn() => $movieController->selectShowtime(),
    'book' => fn() => $movieController->book(),
    'checkout' => fn() => $movieController->checkout(),
    'booking-success' => fn() => $movieController->success(),
    'theaters' => fn() => $movieController->theaters(),
    'promotions' => fn() => $movieController->promotions(),
];

$customerGuestRoutes = [
    'register' => fn() => $customerAuth->register(),
    'login' => fn() => $customerAuth->login(),
    'forgot-password' => fn() => $customerAuth->forgotPassword(),
    'login-google' => fn() => $oauth->googleLogin(),
    'google-callback' => fn() => $oauth->googleCallback(),
];

$customerAuthRoutes = [
    'history' => fn() => $bookingController->history(),
    'cancel-booking' => fn() => $bookingController->cancelRequest(),
    'profile' => fn() => $customerAuth->profile(),
    'edit-profile' => fn() => $customerAuth->editProfile(),
    'change-password' => fn() => $customerAuth->changePassword(),
    'vouchers' => fn() => $customerAuth->vouchers(),
    'link-bank-account' => fn() => $customerAuth->linkBankAccount(),
    'logout' => fn() => $customerAuth->logout(),
];

$employeeGuestRoutes = [
    'admin_login' => fn() => $adminAuth->login(),
];

$adminRoutes = [
    'admin_logout' => fn() => $adminAuth->logout(),
    'admin_profile' => fn() => $adminAuth->profile(),
    'admin_edit_profile' => fn() => $adminAuth->editProfile(),
    'admin_change_password' => fn() => $adminAuth->changePassword(),
    'admin_dashboard' => fn() => $reportController->dashboard(),
    'admin_reports' => fn() => $reportController->reports(),
    'admin_employees' => fn() => $userController->indexEmployee(),
    'admin_create_employee' => fn() => $userController->create('staff'),
    'admin_customers' => fn() => $userController->indexCustomer(),
    'admin_create_customer' => fn() => $userController->create('customer'),
    'admin_store_user' => fn() => $userController->store(),
    'admin_edit_employee' => function () use ($userController): void {
        if (!isset($_GET['id'])) {
            redirect_to(admin_url('admin_employees'));
        }
        $userController->edit((int) $_GET['id']);
    },
    'admin_edit_customer' => function () use ($userController): void {
        if (!isset($_GET['id'])) {
            redirect_to(admin_url('admin_customers'));
        }
        $userController->edit((int) $_GET['id']);
    },
    'admin_update_user' => fn() => $userController->update(),
    'admin_delete_user' => fn() => $userController->delete(),
    'admin_toggle_status_user' => fn() => $userController->toggleStatus(),
    'admin_movies' => fn() => $movieController->index(),
    'admin_create_movie' => fn() => $movieController->create(),
    'admin_store_movie' => fn() => $movieController->store(),
    'admin_edit_movie' => function () use ($movieController): void {
        if (!isset($_GET['id'])) {
            redirect_to(admin_url('admin_movies'));
        }
        $movieController->edit((int) $_GET['id']);
    },
    'admin_update_movie' => fn() => $movieController->update(),
    'admin_delete_movie' => fn() => $movieController->delete(),
    'admin_showtimes' => fn() => $showtimeController->index(),
    'admin_create_showtime' => fn() => $showtimeController->create(),
    'admin_store_showtime' => fn() => $showtimeController->store(),
    'admin_edit_showtime' => function () use ($showtimeController): void {
        if (!isset($_GET['id'])) {
            redirect_to(admin_url('admin_showtimes'));
        }
        $showtimeController->edit((int) $_GET['id']);
    },
    'admin_update_showtime' => fn() => $showtimeController->update(),
    'admin_delete_showtime' => fn() => $showtimeController->delete(),
    'admin_rooms' => fn() => $roomController->index(),
    'admin_create_room' => fn() => $roomController->create(),
    'admin_store_room' => fn() => $roomController->store(),
    'admin_edit_room' => function () use ($roomController): void {
        if (!isset($_GET['id'])) {
            redirect_to(admin_url('admin_rooms'));
        }
        $roomController->edit((int) $_GET['id']);
    },
    'admin_update_room' => fn() => $roomController->update(),
    'admin_delete_room' => fn() => $roomController->delete(),
    'admin_room_seats' => function () use ($roomController): void {
        if (!isset($_GET['id'])) {
            redirect_to(admin_url('admin_rooms'));
        }
        $roomController->showSeats((int) $_GET['id']);
    },
    'admin_generate_seats' => fn() => $roomController->generateSeats(),
    'admin_toggle_seat' => fn() => $roomController->toggleSeat(),
    'admin_promotions' => fn() => $promotionController->index(),
    'admin_create_promotion' => fn() => $promotionController->create(),
    'admin_store_promotion' => fn() => $promotionController->store(),
    'admin_edit_promotion' => function () use ($promotionController): void {
        if (!isset($_GET['id'])) {
            redirect_to(admin_url('admin_promotions'));
        }
        $promotionController->edit((int) $_GET['id']);
    },
    'admin_update_promotion' => fn() => $promotionController->update(),
    'admin_delete_promotion' => fn() => $promotionController->delete(),
    'admin_orders' => fn() => $orderController->index(),
    'admin_create_order' => fn() => $orderController->create(),
    'admin_store_order' => fn() => $orderController->store(),
    'admin_order_detail' => function () use ($orderController): void {
        if (!isset($_GET['id'])) {
            redirect_to(admin_url('admin_orders'));
        }
        $orderController->detail((int) $_GET['id']);
    },
    'admin_update_order_status' => fn() => $orderController->updateStatus(),
    'admin_approve_order' => fn() => $orderController->approve(),
    'admin_cancel_order' => fn() => $orderController->cancel(),
    'admin_cancellation_requests' => fn() => $orderController->cancellations(),
    'admin_approve_cancel' => fn() => $orderController->approveCancellation(),
];

if (isset($publicCustomerRoutes[$action])) {
    route_customer_public($publicCustomerRoutes[$action]);
    return;
}

if (isset($customerGuestRoutes[$action])) {
    route_customer_guest($customerGuestRoutes[$action]);
    return;
}

if (isset($customerAuthRoutes[$action])) {
    route_customer_auth($customerAuthRoutes[$action]);
    return;
}

if (isset($employeeGuestRoutes[$action])) {
    route_admin_guest($employeeGuestRoutes[$action]);
    return;
}

if (isset($adminRoutes[$action])) {
    route_admin($adminRoutes[$action]);
    return;
}

route_customer_public(fn() => $movieController->home());
