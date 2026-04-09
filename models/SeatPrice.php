<?php
require_once 'config/database.php';

class SeatPrice {
    private $conn;
    private $table_name = "SeatPrices";

    public $seat_price_id;
    public $seat_type;
    public $price_multiplier;
    public $description;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Get all seat prices
    public function getAll() {
        $sql = "SELECT * FROM $this->table_name ORDER BY seat_price_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get price by seat type
    public function getByType($seat_type) {
        $sql = "SELECT * FROM $this->table_name WHERE seat_type = :seat_type";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":seat_type", $seat_type);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update seat price
    public function update() {
        $sql = "UPDATE $this->table_name 
        SET price_multiplier=:price_multiplier, description=:description 
        WHERE seat_type=:seat_type";
        $stmt = $this->conn->prepare($sql);

        $this->price_multiplier = htmlspecialchars(strip_tags($this->price_multiplier));
        $this->description = htmlspecialchars(strip_tags($this->description ?? ''));
        $this->seat_type = htmlspecialchars(strip_tags($this->seat_type));

        $stmt->bindParam(":price_multiplier", $this->price_multiplier);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":seat_type", $this->seat_type);

        return $stmt->execute();
    }

    // Get price multiplier for a seat type
    public static function getPriceMultiplier($seat_type) {
        $database = new Database();
        $conn = $database->getConnection();
        
        $sql = "SELECT price_multiplier FROM SeatPrices WHERE seat_type = :seat_type";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":seat_type", $seat_type);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $result['price_multiplier'] : 1.00;
    }
}
?>
