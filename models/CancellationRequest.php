<?php
require_once 'config/database.php';

class CancellationRequest {
    private $conn;
    private $table_name = 'CancellationRequests';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function createRequest($order_id, $user_id, $reason) {
        $sql = "INSERT INTO $this->table_name SET order_id = :order_id, user_id = :user_id, reason = :reason, status = 'pending', request_date = NOW()";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':reason', $reason);
        return $stmt->execute();
    }

    public function getByOrder($order_id) {
        $sql = "SELECT request_id, order_id, user_id, reason, status, request_date FROM $this->table_name WHERE order_id = :order_id LIMIT 0,1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getPendingRequests() {
        $sql = "SELECT r.request_id, r.order_id, r.user_id, r.reason, r.status, r.request_date, u.full_name, o.order_code FROM $this->table_name r JOIN Users u ON r.user_id = u.user_id JOIN Orders o ON r.order_id = o.order_id WHERE r.status = 'pending' ORDER BY r.request_date DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($request_id) {
        $sql = "SELECT request_id, order_id, user_id, reason, status, request_date FROM $this->table_name WHERE request_id = :request_id LIMIT 0,1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateStatus($request_id, $status) {
        $sql = "UPDATE $this->table_name SET status = :status WHERE request_id = :request_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
