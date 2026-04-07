<?php
require_once 'config/database.php';

class Order {
    private $conn;
    private $table_name = 'Orders';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create($user_id, $promotion_id, $order_code, $total_amount, $discount_amount, $final_amount) {
        $sql = "INSERT INTO $this->table_name SET user_id = :user_id, promotion_id = :promotion_id, order_code = :order_code, order_date = NOW(), total_amount = :total_amount, discount_amount = :discount_amount, final_amount = :final_amount, order_status = 'pending'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        if ($promotion_id !== null) {
            $stmt->bindParam(':promotion_id', $promotion_id, PDO::PARAM_INT);
        } else {
            $stmt->bindValue(':promotion_id', null, PDO::PARAM_NULL);
        }
        $stmt->bindParam(':order_code', $order_code);
        $stmt->bindParam(':total_amount', $total_amount);
        $stmt->bindParam(':discount_amount', $discount_amount);
        $stmt->bindParam(':final_amount', $final_amount);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    public function getById($order_id) {
        $sql = "SELECT o.*, p.promo_code, p.discount_type, p.discount_value FROM $this->table_name o LEFT JOIN Promotions p ON o.promotion_id = p.promotion_id WHERE o.order_id = :order_id LIMIT 0,1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getOrdersByUser($user_id) {
        $sql = "SELECT o.*, p.promo_code FROM $this->table_name o LEFT JOIN Promotions p ON o.promotion_id = p.promotion_id WHERE o.user_id = :user_id ORDER BY o.order_date DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($order_id, $status) {
        $sql = "UPDATE $this->table_name SET order_status = :status WHERE order_id = :order_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function markCancelled($order_id) {
        $sql = "UPDATE $this->table_name SET order_status = 'cancelled' WHERE order_id = :order_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function updatePromotion($order_id, $promotion_id, $discount_amount, $final_amount) {
        $sql = "UPDATE $this->table_name SET promotion_id = :promotion_id, discount_amount = :discount_amount, final_amount = :final_amount WHERE order_id = :order_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':promotion_id', $promotion_id, PDO::PARAM_INT);
        $stmt->bindParam(':discount_amount', $discount_amount);
        $stmt->bindParam(':final_amount', $final_amount);
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
