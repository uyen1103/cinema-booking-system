<?php
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Ticket.php';
require_once __DIR__ . '/../models/CancellationRequest.php';

class BookingController {
    private Order $orderModel;
    private Ticket $ticketModel;
    private CancellationRequest $cancellationModel;

    public function __construct() {
        $this->orderModel = new Order();
        $this->ticketModel = new Ticket();
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
        header('Location: ' . $url);
        exit;
    }

    public function history(): void {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect(app_url('login'));
        }

        $orders = $this->orderModel->getOrdersByUser((int) $_SESSION['user_id']);
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

    public function cancelRequest(): void {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect(app_url('login'));
        }

        $order_id = (int) ($_GET['order_id'] ?? 0);
        $order = $this->orderModel->getById($order_id);
        if (!$order || (int) $order['user_id'] !== (int) $_SESSION['user_id']) {
            $this->redirect(app_url('history'));
        }

        $isPaidOrder = in_array($order['payment_status'] ?? '', ['paid', 'success'], true)
            || in_array($order['order_status'] ?? '', ['completed', 'paid'], true);
        if (!$isPaidOrder || ($order['order_status'] ?? '') === 'cancelled') {
            set_flash('danger', 'Chỉ vé đã thanh toán mới có thể gửi yêu cầu hủy.');
            $this->redirect(app_url('history'));
        }

        $existingRequest = $this->cancellationModel->getByOrder($order_id);
        $errors = [];
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $reason = trim($_POST['reason'] ?? '');
            if ($reason === '') {
                $errors[] = 'Vui lòng nhập lý do hủy vé.';
            }
            if ($existingRequest && in_array($existingRequest['status'] ?? '', ['pending', 'approved'], true)) {
                $errors[] = 'Đơn này đã có yêu cầu hủy đang chờ hoặc đã được duyệt.';
            }

            if (empty($errors)) {
                if ($this->cancellationModel->createRequest($order_id, (int) $_SESSION['user_id'], $reason)) {
                    $success = 'Yêu cầu hủy vé đã được gửi. Nhân viên sẽ xử lý sớm.';
                    $existingRequest = $this->cancellationModel->getByOrder($order_id);
                } else {
                    $errors[] = 'Không thể gửi yêu cầu. Vui lòng thử lại.';
                }
            }
        }

        include __DIR__ . '/../views/booking/cancel.php';
    }

    public function cancellationRequests(): void {
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'staff'], true)) {
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

    public function approveCancellation(): void {
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'staff'], true)) {
            $this->redirect('index.php');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $request_id = (int) ($_POST['request_id'] ?? 0);
            $decision = $_POST['decision'] ?? '';
            $request = $this->cancellationModel->getById($request_id);

            if ($request && in_array($decision, ['approved', 'rejected'], true)) {
                $this->cancellationModel->updateStatus($request_id, $decision);
                if ($decision === 'approved') {
                    $this->orderModel->cancelOrder((int) $request['order_id'], 'Duyệt yêu cầu hủy vé từ khách hàng');
                    set_flash('success', 'Đã duyệt yêu cầu hủy và cập nhật doanh thu/tình trạng vé.');
                } else {
                    set_flash('success', 'Đã từ chối yêu cầu hủy vé.');
                }
            } else {
                set_flash('danger', 'Yêu cầu hủy vé không hợp lệ.');
            }
        }

        $this->redirect('?action=cancellation-requests');
    }
}
?>
