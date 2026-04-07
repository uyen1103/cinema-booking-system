<?php
require_once 'models/Order.php';
require_once 'models/Ticket.php';
require_once 'models/CancellationRequest.php';

class BookingController {
    private $orderModel;
    private $ticketModel;
    private $cancellationModel;

    public function __construct() {
        $this->orderModel = new Order();
        $this->ticketModel = new Ticket();
        $this->cancellationModel = new CancellationRequest();
    }

    public function history() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: web.php?action=login');
            exit;
        }

        $orders = $this->orderModel->getOrdersByUser($_SESSION['user_id']);
        $uniqueOrders = [];
        $ticketMap = [];
        $cancellationMap = [];

        foreach ($orders as $order) {
            if (!isset($uniqueOrders[$order['order_id']])) {
                $uniqueOrders[$order['order_id']] = $order;
                $ticketMap[$order['order_id']] = $this->ticketModel->getTicketsByOrder($order['order_id']);
                $cancellationMap[$order['order_id']] = $this->cancellationModel->getByOrder($order['order_id']);
            }
        }

        $orders = array_values($uniqueOrders);

        include 'views/booking/history.php';
    }

    public function cancelRequest() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: web.php?action=login');
            exit;
        }

        $order_id = intval($_GET['order_id'] ?? 0);
        $order = $this->orderModel->getById($order_id);

        if (!$order || $order['user_id'] != $_SESSION['user_id']) {
            header('Location: web.php?action=history');
            exit;
        }

        $existingRequest = $this->cancellationModel->getByOrder($order_id);
        $errors = [];
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $reason = trim($_POST['reason'] ?? '');
            if (empty($reason)) {
                $errors[] = 'Vui lòng nhập lý do hủy vé.';
            }

            if (empty($existingRequest) && empty($errors)) {
                if ($this->cancellationModel->createRequest($order_id, $_SESSION['user_id'], $reason)) {
                    $success = 'Yêu cầu hủy vé đã được gửi. Nhân viên sẽ xử lý sớm.';
                    $existingRequest = $this->cancellationModel->getByOrder($order_id);
                } else {
                    $errors[] = 'Không thể gửi yêu cầu. Vui lòng thử lại.';
                }
            }
        }

        include 'views/booking/cancel.php';
    }

    public function cancellationRequests() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: index.php');
            exit;
        }

        $requests = $this->cancellationModel->getPendingRequests();
        include 'views/booking/cancellations.php';
    }

    public function approveCancellation() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: index.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $request_id = intval($_POST['request_id'] ?? 0);
            $decision = $_POST['decision'] ?? '';
            $request = $this->cancellationModel->getById($request_id);

            if ($request && in_array($decision, ['approved', 'rejected'])) {
                $this->cancellationModel->updateStatus($request_id, $decision);

                if ($decision === 'approved') {
                    $this->orderModel->markCancelled($request['order_id']);
                    $this->ticketModel->markCancelled($request['order_id']);
                }
            }
        }

        header('Location: web.php?action=cancellation-requests');
        exit;
    }
}
