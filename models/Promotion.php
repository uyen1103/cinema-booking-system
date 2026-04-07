<?php
require_once 'config/database.php';

class Promotion {
    private $conn;
    private $table_name = 'Promotions';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getByCode($promo_code) {
        $sql = "SELECT promotion_id, promo_code, discount_type, discount_value, description, min_tickets, min_amount, applicable_seat_types, start_date, end_date FROM $this->table_name WHERE promo_code = :promo_code AND start_date <= NOW() AND end_date >= NOW() LIMIT 0,1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':promo_code', $promo_code);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getActivePromotions() {
        $sql = "SELECT promotion_id, promo_code, discount_type, discount_value, description, min_tickets, min_amount, applicable_seat_types, end_date FROM $this->table_name WHERE start_date <= NOW() AND end_date >= NOW() ORDER BY start_date ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
