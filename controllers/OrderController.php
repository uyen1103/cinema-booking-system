<?php
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Promotion.php';
require_once __DIR__ . '/../models/Movie.php';
require_once __DIR__ . '/../models/Showtime.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Ticket.php';
require_once __DIR__ . '/../models/SeatPrice.php';
require_once __DIR__ . '/../models/CancellationRequest.php';

class OrderController {
    private Order $orderModel;
    private Promotion $promotionModel;
    private Movie $movieModel;
    private Showtime $showtimeModel;
    private User $userModel;
    private Ticket $ticketModel;
    private SeatPrice $seatPriceModel;
    private CancellationRequest $cancellationModel;

    public function __construct() {
        $this->orderModel = new Order();
        $this->promotionModel = new Promotion();
        $this->movieModel = new Movie();
        $this->showtimeModel = new Showtime();
        $this->userModel = new User();
        $this->ticketModel = new Ticket();
        $this->seatPriceModel = new SeatPrice();
        $this->cancellationModel = new CancellationRequest();
    }

    private function renderAdmin(string $viewPath, array $data = []): void {
        extract($data);
        ob_start();
        include __DIR__ . "/../views/admin/orders/{$viewPath}.php";
        $content = ob_get_clean();
        include __DIR__ . '/../views/layouts/admin_layout.php';
    }

    private function redirect(string $url): void {
        header("Location: {$url}");
        exit;
    }

    public function index(): void {
        $filters = [
            'keyword' => trim($_GET['keyword'] ?? ''),
            'order_status' => $_GET['order_status'] ?? '',
            'payment_status' => $_GET['payment_status'] ?? '',
            'date' => $_GET['date'] ?? '',
        ];

        $this->renderAdmin('index', [
            'orders' => $this->orderModel->getAll($filters),
            'stats' => $this->orderModel->getStats(),
            'pendingCancellationCount' => $this->cancellationModel->countPending(),
            'filters' => $filters,
            'activeMenu' => 'orders',
            'breadcrumb' => 'Quản lý hóa đơn',
            'pageTitle' => 'Quản lý hóa đơn',
        ]);
    }

    public function create(): void {
        $selectedShowtimeId = (int) ($_GET['showtime_id'] ?? 0);
        $selectedShowtime = $selectedShowtimeId ? $this->movieModel->getShowtimeById($selectedShowtimeId) : null;
        $seatMap = $selectedShowtime ? $this->movieModel->getSeatsForShowtime($selectedShowtimeId) : [];

        $this->renderAdmin('create', [
            'customers' => $this->orderModel->getCustomersForSelect(),
            'showtimes' => $this->showtimeModel->getAll(['status' => 1]),
            'promotions' => $this->promotionModel->getActivePromotions(),
            'selectedShowtime' => $selectedShowtime,
            'seatMap' => $seatMap,
            'seatPricesInfo' => $this->seatPriceModel->getAll(),
            'activeMenu' => 'orders',
            'breadcrumb' => 'Tạo đơn vé',
            'pageTitle' => 'Tạo đơn vé',
        ]);
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('admin_orders'));
        }

        $userId = (int) ($_POST['user_id'] ?? 0);
        $showtimeId = (int) ($_POST['showtime_id'] ?? 0);
        $selectedSeats = array_map('intval', $_POST['seat_ids'] ?? []);
        $paymentMethod = $_POST['payment_method'] ?? 'cash';
        $paymentStatus = $_POST['payment_status'] ?? 'pending';
        $orderStatus = $_POST['order_status'] ?? 'pending';
        $notes = trim($_POST['notes'] ?? '');
        $promotionId = !empty($_POST['promotion_id']) ? (int) $_POST['promotion_id'] : null;

        if ($userId <= 0 || $showtimeId <= 0 || empty($selectedSeats)) {
            set_flash('danger', 'Vui lòng chọn khách hàng, suất chiếu và ít nhất một ghế.');
            $this->redirect(admin_url('admin_create_order', ['showtime_id' => $showtimeId]));
        }

        $showtime = $this->movieModel->getShowtimeById($showtimeId);
        if (!$showtime) {
            set_flash('danger', 'Suất chiếu không tồn tại.');
            $this->redirect(admin_url('admin_create_order'));
        }

        $seatMap = $this->movieModel->getSeatsForShowtime($showtimeId);
        $seatLookup = [];
        foreach ($seatMap as $seat) {
            $seatLookup[(int) $seat['seat_id']] = $seat;
        }

        $totalAmount = 0;
        $seatPrices = [];
        foreach ($selectedSeats as $seatId) {
            if (!isset($seatLookup[$seatId]) || (int) ($seatLookup[$seatId]['reserved'] ?? 0) === 1) {
                set_flash('danger', 'Có ghế đã được đặt trước đó. Vui lòng chọn lại.');
                $this->redirect(admin_url('admin_create_order', ['showtime_id' => $showtimeId]));
            }
            $seatType = $seatLookup[$seatId]['seat_type'] ?? 'standard';
            $priceInfo = $this->seatPriceModel->getByType($seatType);
            $multiplier = $priceInfo ? (float) $priceInfo['price_multiplier'] : 1.0;
            $seatPrice = (float) $showtime['base_price'] * $multiplier;
            $seatPrices[$seatId] = $seatPrice;
            $totalAmount += $seatPrice;
        }

        $discountAmount = 0.0;
        if ($promotionId) {
            $promotion = $this->promotionModel->getById($promotionId);
            if ($promotion) {
                if (($promotion['discount_type'] ?? '') === 'percent') {
                    $discountAmount = round($totalAmount * ((float) $promotion['discount_value'] / 100), 2);
                    if (!empty($promotion['max_discount'])) {
                        $discountAmount = min($discountAmount, (float) $promotion['max_discount']);
                    }
                } else {
                    $discountAmount = min((float) $promotion['discount_value'], $totalAmount);
                }
            }
        }

        $finalAmount = max(0, $totalAmount - $discountAmount);
        $orderCode = 'ADM' . date('YmdHis') . rand(10, 99);
        $orderId = $this->orderModel->createManual([
            'user_id' => $userId,
            'promotion_id' => $promotionId,
            'order_code' => $orderCode,
            'total_amount' => $totalAmount,
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount,
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentStatus,
            'order_status' => $orderStatus,
            'notes' => $notes,
        ]);

        if (!$orderId) {
            set_flash('danger', 'Không thể tạo đơn vé.');
            $this->redirect(admin_url('admin_create_order', ['showtime_id' => $showtimeId]));
        }

        if (!$this->ticketModel->reserveTicketsWithPrice($orderId, $showtimeId, $seatPrices)) {
            $this->orderModel->cancelOrder($orderId, 'Rollback do lỗi tạo vé');
            set_flash('danger', 'Không thể tạo vé cho các ghế đã chọn.');
            $this->redirect(admin_url('admin_create_order', ['showtime_id' => $showtimeId]));
        }

        $this->orderModel->updateStatus($orderId, [
            'order_status' => $orderStatus,
            'payment_status' => $paymentStatus,
            'payment_method' => $paymentMethod,
            'notes' => $notes,
        ]);

        set_flash('success', 'Đã tạo đơn vé thành công.');
        $this->redirect(admin_url('admin_order_detail', ['id' => $orderId]));
    }

    public function detail(int $id): void {
        $order = $this->orderModel->getById($id);
        if (!$order) {
            set_flash('danger', 'Không tìm thấy hóa đơn.');
            $this->redirect(admin_url('admin_orders'));
        }

        $this->renderAdmin('detail', [
            'order' => $order,
            'cancellationRequest' => $this->cancellationModel->getByOrder($id),
            'activeMenu' => 'orders',
            'breadcrumb' => 'Chi tiết hóa đơn',
            'pageTitle' => 'Chi tiết hóa đơn',
        ]);
    }

    public function updateStatus(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('admin_orders'));
        }

        $id = (int) ($_POST['order_id'] ?? 0);
        $data = [
            'order_status' => $_POST['order_status'] ?? 'pending',
            'payment_status' => $_POST['payment_status'] ?? 'pending',
            'payment_method' => $_POST['payment_method'] ?? 'cash',
            'notes' => trim($_POST['notes'] ?? ''),
        ];

        if ($this->orderModel->updateStatus($id, $data)) {
            set_flash('success', 'Đã cập nhật hóa đơn.');
        } else {
            set_flash('danger', 'Không thể cập nhật hóa đơn.');
        }

        $this->redirect(admin_url('admin_order_detail', ['id' => $id]));
    }

    public function approve(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('admin_orders'));
        }
        $id = (int) ($_POST['order_id'] ?? 0);
        if ($this->orderModel->approveOrder($id)) {
            set_flash('success', 'Đã duyệt vé và ghi nhận doanh thu.');
        } else {
            set_flash('danger', 'Không thể duyệt vé.');
        }
        $this->redirect(admin_url('admin_order_detail', ['id' => $id]));
    }


    public function cancellations(): void {
        $status = trim($_GET['status'] ?? '');
        $requests = $status !== '' ? $this->cancellationModel->getAll($status) : $this->cancellationModel->getAll();

        $this->renderAdmin('cancellations', [
            'requests' => $requests,
            'pendingCancellationCount' => $this->cancellationModel->countPending(),
            'selectedStatus' => $status,
            'activeMenu' => 'dashboard',
            'breadcrumb' => 'Kiểm duyệt yêu cầu hủy vé',
            'pageTitle' => 'Kiểm duyệt yêu cầu hủy vé',
        ]);
    }

    public function approveCancellation(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('admin_cancellation_requests'));
        }

        $requestId = (int) ($_POST['request_id'] ?? 0);
        $decision = trim($_POST['decision'] ?? '');
        $request = $this->cancellationModel->getById($requestId);

        if (!$request || !in_array($decision, ['approved', 'rejected'], true)) {
            set_flash('danger', 'Yêu cầu hủy vé không hợp lệ.');
            $this->redirect(admin_url('admin_cancellation_requests'));
        }

        if (($request['status'] ?? '') !== 'pending') {
            set_flash('warning', 'Yêu cầu này đã được xử lý trước đó.');
            $this->redirect(admin_url('admin_cancellation_requests'));
        }

        if (!$this->cancellationModel->updateStatus($requestId, $decision)) {
            set_flash('danger', 'Không thể cập nhật yêu cầu hủy vé.');
            $this->redirect(admin_url('admin_cancellation_requests'));
        }

        if ($decision === 'approved') {
            $this->orderModel->cancelOrder((int) $request['order_id'], 'Duyệt yêu cầu hủy vé từ bảng điều khiển');
            set_flash('success', 'Đã duyệt yêu cầu hủy vé. Danh sách và doanh thu đã được cập nhật.');
        } else {
            set_flash('info', 'Đã từ chối yêu cầu hủy vé.');
        }

        $this->redirect(admin_url('admin_cancellation_requests'));
    }

    public function cancel(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('admin_orders'));
        }
        $id = (int) ($_POST['order_id'] ?? 0);
        $note = trim($_POST['cancel_note'] ?? 'Hủy bởi admin');
        if ($this->orderModel->cancelOrder($id, $note)) {
            set_flash('success', 'Đã hủy vé/đơn và cập nhật lại doanh thu.');
        } else {
            set_flash('danger', 'Không thể hủy đơn vé.');
        }
        $this->redirect(admin_url('admin_order_detail', ['id' => $id]));
    }
}
?>