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

    private function normalizeRequest(array $row): array {
        $row['request_id'] = (int) ($row['request_id'] ?? 0);
        $row['order_id'] = (int) ($row['order_id'] ?? 0);
        $row['customer_id'] = (int) ($row['customer_id'] ?? $row['user_id'] ?? 0);
        $row['user_id'] = (int) ($row['customer_id'] ?? 0);
        $row['full_name'] = (string) ($row['full_name'] ?? 'Khách hàng');
        $row['email'] = (string) ($row['email'] ?? '');
        $row['order_code'] = (string) ($row['order_code'] ?? '');
        $row['order_status'] = (string) ($row['order_status'] ?? 'pending');
        $row['payment_status'] = (string) ($row['payment_status'] ?? 'pending');
        $row['final_amount'] = (float) ($row['final_amount'] ?? 0);
        $row['reason'] = (string) ($row['reason'] ?? '');
        $row['status'] = (string) ($row['status'] ?? 'pending');
        $row['processed_by_name'] = (string) ($row['processed_by_name'] ?? '');
        $row['processed_note'] = (string) ($row['processed_note'] ?? '');
        return $row;
    }

    private function hasColumn(string $table, string $column): bool {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name AND COLUMN_NAME = :column_name");
        $stmt->execute([':table_name' => $table, ':column_name' => $column]);
        return (int) $stmt->fetchColumn() > 0;
    }

    private function ensureSchema(): void {
        $hasTable = (bool) $this->conn->query("SHOW TABLES LIKE '{$this->table}'")->fetchColumn();
        if (!$hasTable) {
            $this->conn->exec("CREATE TABLE {$this->table} (
                request_id INT AUTO_INCREMENT PRIMARY KEY,
                order_id INT NOT NULL,
                customer_id INT NOT NULL,
                reason TEXT NOT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'pending',
                request_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                processed_by_employee_id INT NULL,
                processed_note VARCHAR(255) NULL,
                processed_at DATETIME NULL,
                INDEX idx_cancel_order (order_id),
                INDEX idx_cancel_customer (customer_id),
                INDEX idx_cancel_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }

        if (!$this->hasColumn($this->table, 'customer_id')) {
            $this->conn->exec("ALTER TABLE {$this->table} ADD COLUMN customer_id INT NULL AFTER order_id");
        }
        if (!$this->hasColumn($this->table, 'processed_by_employee_id')) {
            $this->conn->exec("ALTER TABLE {$this->table} ADD COLUMN processed_by_employee_id INT NULL AFTER request_date");
        }
        if (!$this->hasColumn($this->table, 'processed_note')) {
            $this->conn->exec("ALTER TABLE {$this->table} ADD COLUMN processed_note VARCHAR(255) NULL AFTER processed_by_employee_id");
        }
        if (!$this->hasColumn($this->table, 'processed_at')) {
            $this->conn->exec("ALTER TABLE {$this->table} ADD COLUMN processed_at DATETIME NULL AFTER processed_note");
        }

        try {
            if ($this->hasColumn($this->table, 'user_id')) {
                $this->conn->exec("UPDATE {$this->table} SET customer_id = COALESCE(customer_id, user_id)");
                if ((bool) $this->conn->query("SHOW TABLES LIKE 'users'")->fetchColumn() && (bool) $this->conn->query("SHOW TABLES LIKE 'customers'")->fetchColumn()) {
                    $this->conn->exec("UPDATE {$this->table} r
                        INNER JOIN users u ON u.user_id = r.user_id AND u.role = 'customer'
                        INNER JOIN customers c ON c.email = u.email
                        SET r.customer_id = COALESCE(r.customer_id, c.customer_id)");
                    $this->conn->exec("UPDATE {$this->table} r
                        INNER JOIN customers c ON c.customer_id = r.customer_id
                        INNER JOIN users u ON u.email = c.email AND u.role = 'customer'
                        SET r.user_id = COALESCE(r.user_id, u.user_id)");
                }
            }
        } catch (Throwable $e) {
        }
    }

    private function resolveLegacyCustomerUserId(int $customerId): ?int {
        if ($customerId <= 0 || !$this->hasColumn($this->table, 'user_id')) {
            return null;
        }
        try {
            $stmt = $this->conn->prepare("SELECT u.user_id
                FROM customers c
                INNER JOIN users u ON u.email = c.email AND u.role = 'customer'
                WHERE c.customer_id = :customer_id
                LIMIT 1");
            $stmt->execute([':customer_id' => $customerId]);
            $userId = $stmt->fetchColumn();
            return $userId !== false ? (int) $userId : null;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function createRequest($orderId, $customerId, $reason): bool {
        $existing = $this->getByOrder((int) $orderId);
        if ($existing && in_array($existing['status'] ?? '', ['pending', 'approved'], true)) {
            return false;
        }

        $legacyUserId = $this->resolveLegacyCustomerUserId((int) $customerId);
        if ($legacyUserId !== null) {
            $stmt = $this->conn->prepare("INSERT INTO {$this->table}
                (order_id, customer_id, user_id, reason, status, request_date)
                VALUES (:order_id, :customer_id, :user_id, :reason, 'pending', NOW())");
            return $stmt->execute([':order_id' => $orderId, ':customer_id' => $customerId, ':user_id' => $legacyUserId, ':reason' => $reason]);
        }

        $stmt = $this->conn->prepare("INSERT INTO {$this->table}
            (order_id, customer_id, reason, status, request_date)
            VALUES (:order_id, :customer_id, :reason, 'pending', NOW())");
        return $stmt->execute([':order_id' => $orderId, ':customer_id' => $customerId, ':reason' => $reason]);
    }

    public function getByOrder($orderId): ?array {
        $stmt = $this->conn->prepare("SELECT request_id, order_id, customer_id AS user_id, customer_id, reason, status, request_date, processed_by_employee_id, processed_note, processed_at
            FROM {$this->table} WHERE order_id = :order_id ORDER BY request_date DESC LIMIT 1");
        $stmt->execute([':order_id' => $orderId]);
        $row = $stmt->fetch();
        return $row ? $this->normalizeRequest($row) : null;
    }

    public function getPendingRequests(): array {
        return $this->getAll('pending');
    }

    public function getAll(?string $status = null): array {
        $sql = "SELECT r.request_id, r.order_id, r.customer_id AS user_id, r.customer_id, r.reason, r.status, r.request_date,
                       r.processed_by_employee_id, r.processed_note, r.processed_at,
                       COALESCE(c.full_name, u.full_name, 'Khách hàng') AS full_name,
                       COALESCE(c.email, u.email, '') AS email,
                       o.order_code, o.order_status, o.payment_status, o.final_amount,
                       e.full_name AS processed_by_name
                FROM {$this->table} r
                LEFT JOIN customers c ON r.customer_id = c.customer_id
                LEFT JOIN users u ON u.user_id = r.user_id AND u.role = 'customer'
                JOIN orders o ON r.order_id = o.order_id
                LEFT JOIN employees e ON e.employee_id = r.processed_by_employee_id";
        $params = [];
        if ($status !== null && $status !== '') {
            $sql .= " WHERE r.status = :status";
            $params[':status'] = $status;
        }
        $sql .= " ORDER BY CASE WHEN r.status = 'pending' THEN 0 ELSE 1 END, r.request_date DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return array_map(fn(array $row) => $this->normalizeRequest($row), $stmt->fetchAll());
    }

    public function countPending(): int {
        $stmt = $this->conn->query("SELECT COUNT(*) FROM {$this->table} WHERE status = 'pending'");
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    public function getById($requestId): ?array {
        $stmt = $this->conn->prepare("SELECT request_id, order_id, customer_id AS user_id, customer_id, reason, status, request_date, processed_by_employee_id, processed_note, processed_at
            FROM {$this->table} WHERE request_id = :request_id LIMIT 1");
        $stmt->execute([':request_id' => $requestId]);
        $row = $stmt->fetch();
        return $row ? $this->normalizeRequest($row) : null;
    }

    public function updateStatus($requestId, $status, ?int $employeeId = null, ?string $note = null): bool {
        $stmt = $this->conn->prepare("UPDATE {$this->table}
            SET status = :status,
                processed_by_employee_id = :employee_id,
                processed_note = :processed_note,
                processed_at = NOW()
            WHERE request_id = :request_id");
        return $stmt->execute([
            ':status' => $status,
            ':employee_id' => $employeeId,
            ':processed_note' => $note,
            ':request_id' => $requestId,
        ]);
    }
}
?>
