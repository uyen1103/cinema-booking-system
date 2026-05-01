<?php
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Promotion.php';
require_once __DIR__ . '/../models/Movie.php';
require_once __DIR__ . '/../models/Showtime.php';
require_once __DIR__ . '/../models/Customer.php';
require_once __DIR__ . '/../models/Ticket.php';
require_once __DIR__ . '/../models/SeatPrice.php';
require_once __DIR__ . '/../models/CancellationRequest.php';

class OrderController {
    private Order $orderModel;
    private Promotion $promotionModel;
    private Movie $movieModel;
    private Showtime $showtimeModel;
    private Customer $customerModel;
    private Ticket $ticketModel;
    private SeatPrice $seatPriceModel;
    private CancellationRequest $cancellationModel;

    // Khoi tao cac model phuc vu quan ly hoa don.
    public function __construct() {
        $this->orderModel = new Order();
        $this->promotionModel = new Promotion();
        $this->movieModel = new Movie();
        $this->showtimeModel = new Showtime();
        $this->customerModel = new Customer();
        $this->ticketModel = new Ticket();
        $this->seatPriceModel = new SeatPrice();
        $this->cancellationModel = new CancellationRequest();
    }

    // Render view admin cua module hoa don.
    private function renderAdmin(string $viewPath, array $data = []): void {
        extract($data);
        ob_start();
        include __DIR__ . "/../views/admin/orders/{$viewPath}.php";
        $content = ob_get_clean();
        include __DIR__ . '/../views/layouts/admin_layout.php';
    }

    // Redirect den URL chi dinh.
    private function redirect(string $url): void {
        header("Location: {$url}");
        exit;
    }

    // Danh sach hoa don va bo loc.
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

    // Hien thi form tao don ve thu cong.
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

    // Chức năng 4.3.10: Tạo đơn đặt vé từ admin
    // - Xác thực request POST từ form tạo đơn vé admin
    // - Thu thập khách hàng, suất chiếu, ghế đã chọn, phương thức và trạng thái thanh toán
    // - Kiểm tra hợp lệ dữ liệu nhập, ghế chưa bị đặt, suất chiếu tồn tại
    // - Tính tổng tiền theo loại ghế và áp dụng mã khuyến mãi nếu có
    // - Tạo đơn trong bảng orders trước, sau đó giữ chỗ ghế bằng ticket
    // - Nếu giữ chỗ ghế thất bại thì rollback đơn và thông báo lỗi
    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('admin_orders'));
        }

        $customerId = (int) ($_POST['customer_id'] ?? ($_POST['user_id'] ?? 0));
        $showtimeId = (int) ($_POST['showtime_id'] ?? 0);
        $selectedSeats = array_map('intval', $_POST['seat_ids'] ?? []);
        $paymentMethod = $_POST['payment_method'] ?? 'cash';
        $paymentStatus = $_POST['payment_status'] ?? 'pending';
        $orderStatus = $_POST['order_status'] ?? 'pending';
        if (!in_array($paymentMethod, ['cash', 'bank_transfer', 'momo', 'vnpay'], true)) {
            set_flash('danger', 'Phương thức thanh toán không hợp lệ.');
            $this->redirect(admin_url('admin_create_order', ['showtime_id' => $showtimeId]));
        }
        if ($paymentStatus === 'paid' && $orderStatus === 'completed') {
            $orderStatus = 'completed';
        } elseif ($paymentStatus === 'paid' && $orderStatus !== 'cancelled') {
            $orderStatus = 'pending';
        } elseif ($paymentStatus !== 'paid' && $orderStatus === 'completed') {
            $orderStatus = 'pending';
        }
        $notes = trim($_POST['notes'] ?? '');
        $promotionId = !empty($_POST['promotion_id']) ? (int) $_POST['promotion_id'] : null;

        if ($customerId <= 0 || $showtimeId <= 0 || empty($selectedSeats)) {
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
            'customer_id' => $customerId,
            'promotion_id' => $promotionId,
            'order_code' => $orderCode,
            'total_amount' => $totalAmount,
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount,
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentStatus,
            'order_status' => $orderStatus,
            'notes' => $notes,
            'created_by_employee_id' => currentEmployeeId(),
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
            'updated_by_employee_id' => currentEmployeeId(),
        ]);

        set_flash('success', 'Đã tạo đơn vé thành công.');
        $this->redirect(admin_url('admin_order_detail', ['id' => $orderId]));
    }

    // Chi tiet hoa don.
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

    // Cap nhat trang thai don va thanh toan.
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

        $data['updated_by_employee_id'] = currentEmployeeId();

        if ($this->orderModel->updateStatus($id, $data)) {
            set_flash('success', 'Đã cập nhật hóa đơn.');
        } else {
            set_flash('danger', 'Không thể cập nhật hóa đơn.');
        }

        $this->redirect(admin_url('admin_order_detail', ['id' => $id]));
    }

    // Duyet ve da thanh toan.
    public function approve(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('admin_orders'));
        }
        $id = (int) ($_POST['order_id'] ?? 0);
        if (!$this->orderModel->canApproveOrder($id)) {
            set_flash('danger', 'Chỉ có thể duyệt các đơn đã thanh toán và đang chờ xử lý.');
        } elseif ($this->orderModel->approveOrder($id, currentEmployeeId())) {
            set_flash('success', 'Đã duyệt vé và ghi nhận doanh thu.');
        } else {
            set_flash('danger', 'Không thể duyệt vé.');
        }
        $this->redirect(admin_url('admin_order_detail', ['id' => $id]));
    }


    // Danh sach yeu cau huy ve.
    public function cancellations(): void {
        $status = trim($_GET['status'] ?? '');
        $requests = $status !== '' ? $this->cancellationModel->getAll($status) : $this->cancellationModel->getAll();

        $this->renderAdmin('cancellations', [
            'requests' => $requests,
            'pendingCancellationCount' => $this->cancellationModel->countPending(),
            'selectedStatus' => $status,
            'activeMenu' => 'orders',
            'breadcrumb' => 'Kiểm duyệt yêu cầu hủy vé',
            'pageTitle' => 'Kiểm duyệt yêu cầu hủy vé',
        ]);
    }

    // Chức năng 4.3.11: Duyệt hủy vé của admin
    // - Xác thực request POST từ trang duyệt yêu cầu hủy
    // - Lấy thông tin request hủy và kiểm tra trạng thái còn đang chờ xử lý
    // - Cập nhật trạng thái yêu cầu thành approved/rejected và ghi admin xử lý
    // - Nếu approved thì gọi hàm hủy đơn tương ứng, trả ghế và cập nhật doanh thu
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

        if (!$this->cancellationModel->updateStatus($requestId, $decision, currentEmployeeId(), trim($_POST['admin_note'] ?? ''))) {
            set_flash('danger', 'Không thể cập nhật yêu cầu hủy vé.');
            $this->redirect(admin_url('admin_cancellation_requests'));
        }

        if ($decision === 'approved') {
            if ($this->orderModel->cancelOrder((int) $request['order_id'], 'Duyệt yêu cầu hủy vé từ bảng điều khiển', currentEmployeeId())) {
                set_flash('success', 'Đã duyệt yêu cầu hủy vé. Danh sách và doanh thu đã được cập nhật.');
            } else {
                set_flash('danger', 'Đã cập nhật yêu cầu nhưng không thể hủy đơn liên quan.');
            }
        } else {
            set_flash('info', 'Đã từ chối yêu cầu hủy vé.');
        }

        $this->redirect(admin_url('admin_cancellation_requests'));
    }

    // Hủy đơn vé do admin yêu cầu
    // - Xác thực request POST từ trang chi tiết hóa đơn
    // - Kiểm tra đơn có thể hủy được chưa, tránh hủy lại đơn đã hủy
    // - Gọi model hủy đơn, trả lại trạng thái ghế/ticket và cập nhật doanh thu
    public function cancel(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('admin_orders'));
        }
        $id = (int) ($_POST['order_id'] ?? 0);
        $note = trim($_POST['cancel_note'] ?? 'Hủy bởi admin');
        if (!$this->orderModel->canCancelOrder($id)) {
            set_flash('danger', 'Đơn vé đã ở trạng thái hủy.');
        } elseif ($this->orderModel->cancelOrder($id, $note, currentEmployeeId())) {
            set_flash('success', 'Đã hủy vé/đơn và cập nhật lại doanh thu.');
        } else {
            set_flash('danger', 'Không thể hủy đơn vé.');
        }
        $this->redirect(admin_url('admin_order_detail', ['id' => $id]));
    }
}
?>
