<?php
require_once __DIR__ . '/../models/Order.php'; // Model đơn hàng dùng cho lịch sử, hủy và duyệt đơn
require_once __DIR__ . '/../models/Ticket.php'; // Model vé dùng để truy vấn và thay đổi trạng thái vé
require_once __DIR__ . '/../models/CancellationRequest.php'; // Model yêu cầu hủy vé dùng cho gửi và duyệt hủy

// Controller xử lý lịch sử đặt vé, hủy vé của khách hàng và duyệt yêu cầu hủy từ admin
// - Hiển thị lịch sử đặt vé cho khách hàng
// - Xử lý yêu cầu hủy vé và gửi thông báo
// - Admin duyệt hoặc từ chối yêu cầu hủy vé
class BookingController {
    private Order $orderModel;
    private Ticket $ticketModel;
    private CancellationRequest $cancellationModel;

    public function __construct() {
        $this->orderModel = new Order();
        $this->ticketModel = new Ticket();
        $this->cancellationModel = new CancellationRequest();
    }

    // Hiển thị view trang admin dành cho đơn hàng và yêu cầu hủy
    private function renderAdmin(string $viewPath, array $data = []): void {
        extract($data);
        ob_start();
        include __DIR__ . "/../views/admin/orders/{$viewPath}.php";
        $content = ob_get_clean();
        include __DIR__ . '/../views/layouts/admin_layout.php';
    }

    // Chuyển hướng người dùng tới URL khác
    private function redirect(string $url): void {
        header('Location: ' . $url);
        exit;
    }

    // Chức năng 4.3.7: Xem lịch sử đặt vé
    // - Tải danh sách đơn đặt vé của khách hàng và hiển thị chi tiết các vé đã đặt
    public function history(): void {
        if (!isCustomerLoggedIn()) {
            $this->redirect(customer_url('login'));
        }

        $orders = $this->orderModel->getOrdersByCustomerId(currentCustomerId());
        $uniqueOrders = [];
        $ticketMap = [];
        $cancellationMap = [];

        foreach ($orders as $order) {
            if (!isset($uniqueOrders[$order['order_id']])) {
                $uniqueOrders[$order['order_id']] = $order;
                $ticketMap[$order['order_id']] = $this->ticketModel->getTicketsByOrder((int) $order['order_id']);
                $cancellationMap[$order['order_id']] = $this->cancellationModel->getByOrder((int) $order['order_id']);
            }
        }

        $orders = array_values($uniqueOrders);
        include __DIR__ . '/../views/booking/history.php';
    }

    // Chức năng 4.3.8: Hủy đặt vé
    // - Khách hàng gửi yêu cầu hủy vé cho đơn đã thanh toán
    public function cancelRequest(): void {
        if (!isCustomerLoggedIn()) {
            $this->redirect(customer_url('login'));
        }

        $order_id = (int) ($_GET['order_id'] ?? 0);
        $order = $this->orderModel->getById($order_id);
        if (!$order || (int) ($order['customer_id'] ?? 0) !== currentCustomerId()) {
            $this->redirect(customer_url('history'));
        }

        // Chỉ cho phép yêu cầu hủy với đơn đã thanh toán và chưa bị hủy
        $isPaidOrder = in_array($order['payment_status'] ?? '', ['paid', 'success'], true)
            || in_array($order['order_status'] ?? '', ['completed', 'paid'], true);
        if (!$isPaidOrder || ($order['order_status'] ?? '') === 'cancelled') {
            set_flash('danger', 'Chỉ vé đã thanh toán mới có thể gửi yêu cầu hủy.');
            $this->redirect(customer_url('history'));
        }

        $existingRequest = $this->cancellationModel->getByOrder($order_id);
        $errors = [];
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Lấy lý do hủy vé và kiểm tra dữ liệu đầu vào
            $reason = trim($_POST['reason'] ?? '');
            if ($reason === '') {
                $errors[] = 'Vui lòng nhập lý do hủy vé.';
            }
            if ($existingRequest && in_array($existingRequest['status'] ?? '', ['pending', 'approved'], true)) {
                $errors[] = 'Đơn này đã có yêu cầu hủy đang chờ hoặc đã được duyệt.';
            }

            if (empty($errors)) {
                // Tạo yêu cầu hủy vé và lưu trạng thái pending
                if ($this->cancellationModel->createRequest($order_id, currentCustomerId(), $reason)) {
                    $success = 'Yêu cầu hủy vé đã được gửi. Nhân viên sẽ xử lý sớm.';
                    $existingRequest = $this->cancellationModel->getByOrder($order_id);
                } else {
                    $errors[] = 'Không thể gửi yêu cầu. Vui lòng thử lại.';
                }
            }
        }

        include __DIR__ . '/../views/booking/cancel.php';
    }

    // Chức năng admin: Quản lý các yêu cầu hủy vé từ khách hàng
    public function cancellationRequests(): void {
        if (!isEmployeeLoggedIn() || !isAdmin()) {
            $this->redirect('index.php');
        }

        $requests = $this->cancellationModel->getAll();
        $this->renderAdmin('cancellations', [
            'requests' => $requests,
            'activeMenu' => 'orders',
            'breadcrumb' => 'Yêu cầu hủy vé',
            'pageTitle' => 'Yêu cầu hủy vé',
        ]);
    }

    // Chức năng 4.3.11: Duyệt hủy vé
    // - Nhân viên/Quản trị viên duyệt hoặc từ chối yêu cầu hủy vé của khách hàng
    public function approveCancellation(): void {
        if (!isEmployeeLoggedIn() || !isAdmin()) {
            $this->redirect('index.php');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $request_id = (int) ($_POST['request_id'] ?? 0);
            $decision = $_POST['decision'] ?? '';
            $request = $this->cancellationModel->getById($request_id);

            if ($request && in_array($decision, ['approved', 'rejected'], true)) {
                // Cập nhật trạng thái yêu cầu hủy vé và ghi lại ai xử lý
                $this->cancellationModel->updateStatus($request_id, $decision, currentEmployeeId(), trim($_POST['admin_note'] ?? ''));
                if ($decision === 'approved') {
                    // Nếu duyệt yêu cầu, hủy đơn liên quan và cập nhật trạng thái vé
                    $this->orderModel->cancelOrder((int) $request['order_id'], 'Duyệt yêu cầu hủy vé từ khách hàng', currentEmployeeId());
                    set_flash('success', 'Đã duyệt yêu cầu hủy và cập nhật doanh thu/tình trạng vé.');
                } else {
                    set_flash('success', 'Đã từ chối yêu cầu hủy vé.');
                }
            } else {
                set_flash('danger', 'Yêu cầu hủy vé không hợp lệ.');
            }
        }

        $this->redirect(admin_url('admin_cancellation_requests'));
    }
}
?>
