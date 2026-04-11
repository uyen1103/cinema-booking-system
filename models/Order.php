<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Promotion.php';
require_once __DIR__ . '/Ticket.php';

class Order {
    private PDO $conn;
    private string $table = 'orders';
    private array $columns = [];
    private Promotion $promotionModel;
    private Ticket $ticketModel;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->promotionModel = new Promotion();
        $this->ticketModel = new Ticket();
        $this->syncPromotionSchema();
        $this->syncSchema();
        $this->columns = $this->fetchColumns($this->table);
    }

    private function fetchColumns(string $table): array {
        $columns = [];
        $rows = $this->conn->query("SHOW COLUMNS FROM {$table}")->fetchAll();
        foreach ($rows as $row) {
            $columns[strtolower($row['Field'])] = true;
        }
        return $columns;
    }

    private function addColumnIfMissing(string $table, string $column, string $definition): void {
        $existing = $this->fetchColumns($table);
        if (!isset($existing[strtolower($column)])) {
            $this->conn->exec("ALTER TABLE {$table} ADD COLUMN {$column} {$definition}");
        }
    }

    private function syncPromotionSchema(): void {
        try {
            $hasPromotions = (bool) $this->conn->query("SHOW TABLES LIKE 'promotions'")->fetchColumn();
            if (!$hasPromotions) return;
            $this->addColumnIfMissing('promotions', 'code', "VARCHAR(30) NULL");
            $this->addColumnIfMissing('promotions', 'title', "VARCHAR(160) NULL");
            $this->addColumnIfMissing('promotions', 'used_count', "INT NOT NULL DEFAULT 0");
            $this->addColumnIfMissing('promotions', 'budget', "DECIMAL(12,2) NOT NULL DEFAULT 0");
            $promotionColumns = $this->fetchColumns('promotions');
            if (isset($promotionColumns['promo_code'])) {
                $this->conn->exec("UPDATE promotions SET code = COALESCE(NULLIF(code, ''), promo_code)");
            }
            $this->conn->exec("UPDATE promotions SET title = COALESCE(NULLIF(title, ''), CONCAT('Khuyến mãi ', COALESCE(code, promotion_id)))");
        } catch (Throwable $e) {
        }
    }

    private function syncSchema(): void {
        $this->addColumnIfMissing($this->table, 'payment_method', "VARCHAR(30) NOT NULL DEFAULT 'cash'");
        $this->addColumnIfMissing($this->table, 'payment_status', "VARCHAR(20) NOT NULL DEFAULT 'pending'");
        $this->addColumnIfMissing($this->table, 'notes', "VARCHAR(255) NULL");
        $this->addColumnIfMissing($this->table, 'created_at', "TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP");
        $this->addColumnIfMissing($this->table, 'updated_at', "TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");

        try {
            $this->conn->exec("ALTER TABLE {$this->table} MODIFY COLUMN order_status VARCHAR(20) NOT NULL DEFAULT 'pending'");
        } catch (Throwable $e) {
        }

        try {
            $hasPayments = (bool) $this->conn->query("SHOW TABLES LIKE 'payments'")->fetchColumn();
            if ($hasPayments) {
                $this->conn->exec("UPDATE {$this->table} o
                    LEFT JOIN payments pm ON pm.order_id = o.order_id
                    SET o.payment_method = COALESCE(pm.payment_method, o.payment_method),
                        o.payment_status = CASE WHEN pm.payment_status = 'success' THEN 'paid' WHEN pm.payment_status IS NOT NULL THEN pm.payment_status ELSE o.payment_status END");
            }
            $this->conn->exec("UPDATE {$this->table} SET order_status = 'completed' WHERE order_status = 'paid'");
        } catch (Throwable $e) {
        }
    }

    public function create($userId, $promotionId, $orderCode, $totalAmount, $discountAmount, $finalAmount): int|false {
        $sql = "INSERT INTO {$this->table}
                (user_id, promotion_id, order_code, order_date, total_amount, discount_amount, final_amount, payment_method, payment_status, order_status)
                VALUES (:user_id, :promotion_id, :order_code, NOW(), :total_amount, :discount_amount, :final_amount, 'cash', 'pending', 'pending')";
        $stmt = $this->conn->prepare($sql);
        $ok = $stmt->execute([
            ':user_id' => $userId,
            ':promotion_id' => $promotionId ?: null,
            ':order_code' => $orderCode,
            ':total_amount' => $totalAmount,
            ':discount_amount' => $discountAmount,
            ':final_amount' => $finalAmount,
        ]);
        return $ok ? (int) $this->conn->lastInsertId() : false;
    }

    public function createManual(array $data): int|false {
        $sql = "INSERT INTO {$this->table}
                (user_id, promotion_id, order_code, order_date, total_amount, discount_amount, final_amount, payment_method, payment_status, order_status, notes)
                VALUES (:user_id, :promotion_id, :order_code, NOW(), :total_amount, :discount_amount, :final_amount, :payment_method, :payment_status, :order_status, :notes)";
        $stmt = $this->conn->prepare($sql);
        $ok = $stmt->execute([
            ':user_id' => (int) $data['user_id'],
            ':promotion_id' => !empty($data['promotion_id']) ? (int) $data['promotion_id'] : null,
            ':order_code' => $data['order_code'],
            ':total_amount' => (float) $data['total_amount'],
            ':discount_amount' => (float) $data['discount_amount'],
            ':final_amount' => (float) $data['final_amount'],
            ':payment_method' => $data['payment_method'] ?? 'cash',
            ':payment_status' => $data['payment_status'] ?? 'pending',
            ':order_status' => $data['order_status'] ?? 'pending',
            ':notes' => !empty($data['notes']) ? $data['notes'] : null,
        ]);
        if (!$ok) {
            return false;
        }
        $orderId = (int) $this->conn->lastInsertId();
        $isPaidNow = in_array($data['payment_status'] ?? '', ['paid', 'success'], true) || in_array($data['order_status'] ?? '', ['completed', 'paid'], true);
        if ($isPaidNow) {
            $this->promotionModel->incrementUsedCount($data['promotion_id'] ?? null);
        }
        return $orderId;
    }

    public function getAll(array $filters = []): array {
        $sql = "SELECT o.*, u.full_name, u.email, p.code AS promotion_code, COUNT(t.ticket_id) AS ticket_count
                FROM {$this->table} o
                INNER JOIN users u ON u.user_id = o.user_id
                LEFT JOIN promotions p ON p.promotion_id = o.promotion_id
                LEFT JOIN tickets t ON t.order_id = o.order_id
                WHERE 1=1";
        $params = [];
        if (!empty($filters['keyword'])) {
            $sql .= " AND (o.order_code LIKE :keyword OR u.full_name LIKE :keyword OR u.email LIKE :keyword)";
            $params[':keyword'] = '%' . trim($filters['keyword']) . '%';
        }
        if (!empty($filters['order_status'])) {
            if ($filters['order_status'] === 'completed') {
                $sql .= " AND o.order_status IN ('completed', 'paid')";
            } else {
                $sql .= " AND o.order_status = :order_status";
                $params[':order_status'] = $filters['order_status'];
            }
        }
        if (!empty($filters['payment_status'])) {
            if ($filters['payment_status'] === 'paid') {
                $sql .= " AND o.payment_status IN ('paid', 'success')";
            } else {
                $sql .= " AND o.payment_status = :payment_status";
                $params[':payment_status'] = $filters['payment_status'];
            }
        }
        if (!empty($filters['date'])) {
            $sql .= " AND DATE(o.order_date) = :order_date";
            $params[':order_date'] = $filters['date'];
        }
        $sql .= ' GROUP BY o.order_id ORDER BY o.order_date DESC, o.order_id DESC';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $this->normalizeOrders($stmt->fetchAll());
    }

    public function getById($id): ?array {
        $sql = "SELECT o.*, u.full_name, u.email, u.phone, u.address,
                       p.code AS promotion_code, p.title AS promotion_title, COALESCE(p.promo_code, p.code) AS promo_code,
                       p.discount_type, p.discount_value
                FROM {$this->table} o
                INNER JOIN users u ON u.user_id = o.user_id
                LEFT JOIN promotions p ON p.promotion_id = o.promotion_id
                WHERE o.order_id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        $order = $stmt->fetch();
        if (!$order) {
            return null;
        }
        $order = $this->normalizeOrder($order);
        $order['tickets'] = $this->getTicketsByOrderId((int) $id);
        return $order;
    }

    public function getOrdersByUser($userId): array {
        $stmt = $this->conn->prepare("SELECT o.*, COALESCE(p.code, p.promo_code) AS promo_code
                FROM {$this->table} o
                LEFT JOIN promotions p ON o.promotion_id = p.promotion_id
                WHERE o.user_id = :user_id
                ORDER BY o.order_date DESC");
        $stmt->execute([':user_id' => $userId]);
        return $this->normalizeOrders($stmt->fetchAll());
    }

    public function getTicketsByOrderId(int $orderId): array {
        $roomNameColumn = 'name';
        $rowCol = 'row_name';
        try {
            $roomColumns = $this->fetchColumns('rooms');
            if (isset($roomColumns['room_name'])) {
                $roomNameColumn = 'room_name';
            }
            $seatColumns = $this->fetchColumns('seats');
            if (isset($seatColumns['seat_row'])) {
                $rowCol = 'seat_row';
            }
        } catch (Throwable $e) {
        }

        $sql = "SELECT t.*, s.show_date, s.start_time, m.title AS movie_title, r.{$roomNameColumn} AS room_name,
                       CONCAT(se.{$rowCol}, se.seat_number) AS seat_label
                FROM tickets t
                INNER JOIN showtimes s ON s.showtime_id = t.showtime_id
                INNER JOIN movies m ON m.movie_id = s.movie_id
                INNER JOIN rooms r ON r.room_id = s.room_id
                INNER JOIN seats se ON se.seat_id = t.seat_id
                WHERE t.order_id = :order_id
                ORDER BY t.ticket_id ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':order_id' => $orderId]);
        return $stmt->fetchAll();
    }

    public function updateStatus($id, $data): bool {
        $existing = $this->getById((int) $id);
        if (!$existing) {
            return false;
        }

        if (is_string($data)) {
            $status = $data === 'paid' ? 'completed' : $data;
            $paymentStatus = $data === 'paid' ? 'paid' : 'pending';
            $stmt = $this->conn->prepare("UPDATE {$this->table} SET order_status = :status, payment_status = :payment_status WHERE order_id = :order_id");
            $ok = $stmt->execute([
                ':status' => $status,
                ':payment_status' => $paymentStatus,
                ':order_id' => $id,
            ]);
            if ($ok && $paymentStatus === 'paid' && ($existing['payment_status'] ?? '') !== 'paid') {
                $this->ticketModel->markPaid($id);
                $this->promotionModel->incrementUsedCount($existing['promotion_id'] ?? null);
            }
            return $ok;
        }

        $newOrderStatus = $data['order_status'] === 'completed' ? 'completed' : $data['order_status'];
        $newPaymentStatus = $data['payment_status'];
        $sql = "UPDATE {$this->table}
                SET order_status = :order_status,
                    payment_status = :payment_status,
                    payment_method = :payment_method,
                    notes = :notes
                WHERE order_id = :order_id";
        $stmt = $this->conn->prepare($sql);
        $ok = $stmt->execute([
            ':order_status' => $newOrderStatus,
            ':payment_status' => $newPaymentStatus,
            ':payment_method' => $data['payment_method'],
            ':notes' => $data['notes'] ?: null,
            ':order_id' => $id,
        ]);

        if ($ok) {
            $wasPaid = in_array(($existing['payment_status'] ?? ''), ['paid', 'success'], true) || in_array(($existing['order_status'] ?? ''), ['completed', 'paid'], true);
            $isPaidNow = in_array($newPaymentStatus, ['paid', 'success'], true) || in_array($newOrderStatus, ['completed', 'paid'], true);
            if ($isPaidNow && !$wasPaid) {
                $this->ticketModel->markPaid($id);
                $this->promotionModel->incrementUsedCount($existing['promotion_id'] ?? null);
            }
            if ($newOrderStatus === 'cancelled') {
                $this->ticketModel->markCancelled($id);
            }
        }
        return $ok;
    }

    public function approveOrder(int $orderId): bool {
        $order = $this->getById($orderId);
        if (!$order) return false;
        return $this->updateStatus($orderId, [
            'order_status' => 'completed',
            'payment_status' => 'paid',
            'payment_method' => $order['payment_method'] ?: 'cash',
            'notes' => trim(($order['notes'] ?? '') . ' | Duyệt vé bởi admin'),
        ]);
    }

    public function markCancelled($orderId): bool {
        return $this->cancelOrder((int) $orderId, 'Hủy từ yêu cầu hủy vé');
    }

    public function cancelOrder(int $orderId, string $note = ''): bool {
        $order = $this->getById($orderId);
        if (!$order) return false;
        $stmt = $this->conn->prepare("UPDATE {$this->table}
            SET order_status = 'cancelled', payment_status = CASE WHEN payment_status = 'paid' THEN 'refunded' ELSE payment_status END,
                notes = :notes WHERE order_id = :order_id");
        $ok = $stmt->execute([
            ':notes' => trim(($order['notes'] ?? '') . ($note ? ' | ' . $note : '')),
            ':order_id' => $orderId,
        ]);
        if ($ok) {
            $this->ticketModel->markCancelled($orderId);
            if (in_array(($order['payment_status'] ?? ''), ['paid', 'success'], true)) {
                $this->promotionModel->decrementUsedCount($order['promotion_id'] ?? null);
            }
        }
        return $ok;
    }

    public function updatePromotion($orderId, $promotionId, $discountAmount, $finalAmount): bool {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET promotion_id = :promotion_id, discount_amount = :discount_amount, final_amount = :final_amount WHERE order_id = :order_id");
        return $stmt->execute([
            ':promotion_id' => $promotionId,
            ':discount_amount' => $discountAmount,
            ':final_amount' => $finalAmount,
            ':order_id' => $orderId,
        ]);
    }

    public function getStats(): array {
        $sql = "SELECT
                    COUNT(*) AS total_orders,
                    SUM(CASE WHEN order_status IN ('completed', 'paid') THEN 1 ELSE 0 END) AS completed_orders,
                    SUM(CASE WHEN order_status = 'pending' THEN 1 ELSE 0 END) AS pending_orders,
                    SUM(CASE WHEN order_status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_orders,
                    SUM(CASE WHEN payment_status IN ('paid', 'success') THEN final_amount ELSE 0 END) AS paid_revenue
                FROM {$this->table}";
        $row = $this->conn->query($sql)->fetch() ?: [];
        return [
            'total_orders' => (int) ($row['total_orders'] ?? 0),
            'completed_orders' => (int) ($row['completed_orders'] ?? 0),
            'pending_orders' => (int) ($row['pending_orders'] ?? 0),
            'cancelled_orders' => (int) ($row['cancelled_orders'] ?? 0),
            'paid_revenue' => (float) ($row['paid_revenue'] ?? 0),
        ];
    }

    public function getMonthlyRevenue(int $year): array {
        $stmt = $this->conn->prepare("SELECT MONTH(order_date) AS month_num, SUM(final_amount) AS revenue
                FROM {$this->table}
                WHERE YEAR(order_date) = :year AND payment_status IN ('paid', 'success') AND order_status <> 'cancelled'
                GROUP BY MONTH(order_date)
                ORDER BY MONTH(order_date)");
        $stmt->execute([':year' => $year]);
        return $stmt->fetchAll();
    }

    public function getCustomersForSelect(): array {
        return $this->conn->query("SELECT user_id, full_name, email FROM users WHERE role = 'customer' AND status IN ('active','working') ORDER BY full_name")->fetchAll();
    }

    private function normalizeOrders(array $orders): array {
        return array_map(fn($order) => $this->normalizeOrder($order), $orders);
    }

    private function normalizeOrder(array $order): array {
        if (($order['order_status'] ?? '') === 'paid') {
            $order['order_status'] = 'completed';
        }
        if (($order['payment_status'] ?? '') === 'success') {
            $order['payment_status'] = 'paid';
        }
        $order['notes'] = $order['notes'] ?? '';
        $order['payment_method'] = $order['payment_method'] ?? 'cash';
        return $order;
    }
}
?>