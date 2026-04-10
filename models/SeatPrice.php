<?php
require_once __DIR__ . '/../config/database.php';

class SeatPrice {
    private PDO $conn;
    private string $table = 'seat_prices';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->ensureSchema();
    }

    private function ensureSchema(): void {
        try {
            $hasLower = (bool) $this->conn->query("SHOW TABLES LIKE 'seat_prices'")->fetchColumn();
            $hasCamel = (bool) $this->conn->query("SHOW TABLES LIKE 'SeatPrices'")->fetchColumn();
            if (!$hasLower && !$hasCamel) {
                $this->conn->exec("CREATE TABLE seat_prices (
                    seat_price_id INT AUTO_INCREMENT PRIMARY KEY,
                    seat_type VARCHAR(20) NOT NULL UNIQUE,
                    price_multiplier DECIMAL(5,2) NOT NULL,
                    description VARCHAR(255) NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                $this->conn->exec("INSERT INTO seat_prices (seat_type, price_multiplier, description) VALUES
                    ('standard', 1.00, 'Ghế thường'),
                    ('vip', 1.30, 'Ghế VIP'),
                    ('couple', 1.80, 'Ghế đôi')");
            } elseif (!$hasLower && $hasCamel) {
                $this->table = 'SeatPrices';
            }
        } catch (Throwable $e) {
        }
    }

    public function getAll(): array {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} ORDER BY seat_price_id");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getByType($seatType): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE seat_type = :seat_type LIMIT 1");
        $stmt->execute([':seat_type' => $seatType]);
        return $stmt->fetch() ?: null;
    }
}
?>