<?php
require_once __DIR__ . '/../config/database.php';

class CancellationRequest {
    private PDO $conn;
    private string $table = 'cancellation_requests';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->ensureSchema();
    }

    private function ensureSchema(): void {
        try {
            $hasLower = (bool) $this->conn->query("SHOW TABLES LIKE 'cancellation_requests'")->fetchColumn();
            $hasCamel = (bool) $this->conn->query("SHOW TABLES LIKE 'CancellationRequests'")->fetchColumn();
            if (!$hasLower && !$hasCamel) {
                $this->conn->exec("CREATE TABLE cancellation_requests (
                    request_id INT AUTO_INCREMENT PRIMARY KEY,
                    order_id INT NOT NULL,
                    user_id INT NOT NULL,
                    reason TEXT NOT NULL,
                    status VARCHAR(20) DEFAULT 'pending',
                    request_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
                    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            } elseif (!$hasLower && $hasCamel) {
                $this->table = 'CancellationRequests';
            }
        } catch (Throwable $e) {
        }
    }

    public function createRequest($orderId, $userId, $reason): bool {
        $existing = $this->getByOrder((int) $orderId);
        if ($existing && in_array($existing['status'] ?? '', ['pending', 'approved'], true)) {
            return false;
        }

        $stmt = $this->conn->prepare("INSERT INTO {$this->table} SET order_id = :order_id, user_id = :user_id, reason = :reason, status = 'pending', request_date = NOW()");
        return $stmt->execute([':order_id' => $orderId, ':user_id' => $userId, ':reason' => $reason]);
    }

    public function getByOrder($orderId): ?array {
        $stmt = $this->conn->prepare("SELECT request_id, order_id, user_id, reason, status, request_date FROM {$this->table} WHERE order_id = :order_id ORDER BY request_date DESC LIMIT 1");
        $stmt->execute([':order_id' => $orderId]);
        return $stmt->fetch() ?: null;
    }

    public function getPendingRequests(): array {
        return $this->getAll('pending');
    }

    public function getAll(?string $status = null): array {
        $sql = "SELECT r.request_id, r.order_id, r.user_id, r.reason, r.status, r.request_date,
                       u.full_name, u.email, o.order_code, o.order_status, o.payment_status, o.final_amount
                FROM {$this->table} r
                JOIN users u ON r.user_id = u.user_id
                JOIN orders o ON r.order_id = o.order_id";
        $params = [];
        if ($status !== null && $status !== '') {
            $sql .= " WHERE r.status = :status";
            $params[':status'] = $status;
        }
        $sql .= " ORDER BY CASE WHEN r.status = 'pending' THEN 0 ELSE 1 END, r.request_date DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countPending(): int {
        $stmt = $this->conn->query("SELECT COUNT(*) FROM {$this->table} WHERE status = 'pending'");
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    public function getById($requestId): ?array {
        $stmt = $this->conn->prepare("SELECT request_id, order_id, user_id, reason, status, request_date FROM {$this->table} WHERE request_id = :request_id LIMIT 1");
        $stmt->execute([':request_id' => $requestId]);
        return $stmt->fetch() ?: null;
    }

    public function updateStatus($requestId, $status): bool {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET status = :status WHERE request_id = :request_id");
        return $stmt->execute([':status' => $status, ':request_id' => $requestId]);
    }
}
?>
