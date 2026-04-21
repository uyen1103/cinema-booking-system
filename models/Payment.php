<?php
require_once __DIR__ . '/../config/database.php';

class Payment {
    private PDO $conn;
    private string $table = 'payments';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->ensureSchema();
    }

    private function fetchColumns(string $table): array {
        $columns = [];
        try {
            $rows = $this->conn->query("SHOW COLUMNS FROM {$table}")->fetchAll();
            foreach ($rows as $row) {
                $columns[strtolower($row['Field'])] = true;
            }
        } catch (Throwable $e) {
        }
        return $columns;
    }

    private function addColumnIfMissing(string $column, string $definition): void {
        $columns = $this->fetchColumns($this->table);
        if (!isset($columns[strtolower($column)])) {
            $this->conn->exec("ALTER TABLE {$this->table} ADD COLUMN {$column} {$definition}");
        }
    }

    private function ensureSchema(): void {
        $exists = (bool) $this->conn->query("SHOW TABLES LIKE '{$this->table}'")->fetchColumn();
        if (!$exists) {
            $this->conn->exec("CREATE TABLE {$this->table} (
                payment_id INT AUTO_INCREMENT PRIMARY KEY,
                order_id INT NOT NULL,
                payment_method VARCHAR(30) NOT NULL DEFAULT 'cash',
                amount_paid DECIMAL(12,2) NOT NULL DEFAULT 0,
                payment_status VARCHAR(20) NOT NULL DEFAULT 'pending',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_payment_order (order_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }

        $this->addColumnIfMissing('payment_method', "VARCHAR(30) NOT NULL DEFAULT 'cash'");
        $this->addColumnIfMissing('amount_paid', 'DECIMAL(12,2) NOT NULL DEFAULT 0');
        $this->addColumnIfMissing('payment_status', "VARCHAR(20) NOT NULL DEFAULT 'pending'");
        $this->addColumnIfMissing('created_at', 'DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addColumnIfMissing('updated_at', 'DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    }

    public function normalizeMethod(string $method): string {
        $method = strtolower(trim($method));
        return match ($method) {
            'wallet', 'momo' => 'momo',
            'qr', 'vnpay' => 'vnpay',
            'card', 'bank_transfer', 'bank' => 'bank_transfer',
            'zalopay' => 'zalopay',
            'cash' => 'cash',
            default => 'cash',
        };
    }

    public function normalizeStatus(string $status): string {
        $status = strtolower(trim($status));
        return match ($status) {
            'success', 'paid' => 'paid',
            'refunded' => 'refunded',
            'failed' => 'failed',
            default => 'pending',
        };
    }

    public function getByOrderId(int $orderId): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE order_id = :order_id LIMIT 1");
        $stmt->execute([':order_id' => $orderId]);
        return $stmt->fetch() ?: null;
    }

    public function upsertForOrder(int $orderId, string $paymentMethod, float $amountPaid, string $paymentStatus): bool {
        $existing = $this->getByOrderId($orderId);
        $payload = [
            ':order_id' => $orderId,
            ':payment_method' => $this->normalizeMethod($paymentMethod),
            ':amount_paid' => $amountPaid,
            ':payment_status' => $this->normalizeStatus($paymentStatus),
        ];

        if ($existing) {
            $stmt = $this->conn->prepare("UPDATE {$this->table}
                SET payment_method = :payment_method,
                    amount_paid = :amount_paid,
                    payment_status = :payment_status
                WHERE order_id = :order_id");
            return $stmt->execute($payload);
        }

        $stmt = $this->conn->prepare("INSERT INTO {$this->table}
            (order_id, payment_method, amount_paid, payment_status, created_at)
            VALUES (:order_id, :payment_method, :amount_paid, :payment_status, NOW())");
        return $stmt->execute($payload);
    }
}
