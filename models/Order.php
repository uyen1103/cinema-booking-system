<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Promotion.php';
require_once __DIR__ . '/Ticket.php';
require_once __DIR__ . '/Payment.php';

class Order {
    private PDO $conn;
    private string $table = 'orders';
    private Promotion $promotionModel;
    private Ticket $ticketModel;
    private Payment $paymentModel;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->promotionModel = new Promotion();
        $this->ticketModel = new Ticket();
        $this->paymentModel = new Payment();
        $this->syncSchema();
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

    private function syncSchema(): void {
        if (!(bool)$this->conn->query("SHOW TABLES LIKE 'orders'")->fetchColumn()) {
            $this->conn->exec("CREATE TABLE orders (
                order_id INT AUTO_INCREMENT PRIMARY KEY,
                customer_id INT NOT NULL,
                promotion_id INT NULL,
                order_code VARCHAR(30) NOT NULL,
                order_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                total_amount DECIMAL(12,2) NOT NULL,
                discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
                final_amount DECIMAL(12,2) NOT NULL,
                payment_method VARCHAR(30) NOT NULL DEFAULT 'cash',
                payment_status VARCHAR(20) NOT NULL DEFAULT 'pending',
                order_status VARCHAR(20) NOT NULL DEFAULT 'pending',
                notes VARCHAR(255) NULL,
                created_by_employee_id INT NULL,
                updated_by_employee_id INT NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_order_code (order_code)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }

        $this->addColumnIfMissing($this->table, 'customer_id', 'INT NULL');
        $this->addColumnIfMissing($this->table, 'payment_method', "VARCHAR(30) NOT NULL DEFAULT 'cash'");
        $this->addColumnIfMissing($this->table, 'payment_status', "VARCHAR(20) NOT NULL DEFAULT 'pending'");
        $this->addColumnIfMissing($this->table, 'notes', 'VARCHAR(255) NULL');
        $this->addColumnIfMissing($this->table, 'created_by_employee_id', 'INT NULL');
        $this->addColumnIfMissing($this->table, 'updated_by_employee_id', 'INT NULL');
        $this->addColumnIfMissing($this->table, 'created_at', 'TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP');
        $this->addColumnIfMissing($this->table, 'updated_at', 'TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

        try {
            $this->conn->exec("ALTER TABLE {$this->table} MODIFY COLUMN order_status VARCHAR(20) NOT NULL DEFAULT 'pending'");
        } catch (Throwable $e) {}

        try {
            $orderCols = $this->fetchColumns($this->table);
            if (isset($orderCols['user_id'])) {
                $this->conn->exec("UPDATE {$this->table} SET customer_id = COALESCE(customer_id, user_id)");
                if ((bool) $this->conn->query("SHOW TABLES LIKE 'users'")->fetchColumn() && (bool) $this->conn->query("SHOW TABLES LIKE 'customers'")->fetchColumn()) {
                    $this->conn->exec("UPDATE {$this->table} o
                        INNER JOIN users u ON u.user_id = o.user_id AND u.role = 'customer'
                        INNER JOIN customers c ON c.email = u.email
                        SET o.customer_id = COALESCE(o.customer_id, c.customer_id)");
                    $this->conn->exec("UPDATE {$this->table} o
                        INNER JOIN customers c ON c.customer_id = o.customer_id
                        INNER JOIN users u ON u.email = c.email AND u.role = 'customer'
                        SET o.user_id = COALESCE(o.user_id, u.user_id)");
                }
            }
            if ((bool)$this->conn->query("SHOW TABLES LIKE 'payments'")->fetchColumn()) {
                $this->conn->exec("UPDATE {$this->table} o
                    LEFT JOIN payments pm ON pm.order_id = o.order_id
                    SET o.payment_method = COALESCE(pm.payment_method, o.payment_method),
                        o.payment_status = CASE WHEN pm.payment_status = 'success' THEN 'paid' WHEN pm.payment_status IS NOT NULL THEN pm.payment_status ELSE o.payment_status END");
            }
            $this->conn->exec("UPDATE {$this->table} SET order_status = 'completed' WHERE order_status = 'paid'");
        } catch (Throwable $e) {}
    }

    private function hasOrderColumn(string $column): bool {
        $orderCols = $this->fetchColumns($this->table);
        return isset($orderCols[strtolower($column)]);
    }

    private function resolveLegacyCustomerUserId(int $customerId): ?int {
        if ($customerId <= 0 || !$this->hasOrderColumn('user_id')) {
            return null;
        }
        try {
            if (!(bool) $this->conn->query("SHOW TABLES LIKE 'customers'")->fetchColumn() || !(bool) $this->conn->query("SHOW TABLES LIKE 'users'")->fetchColumn()) {
                return null;
            }
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

    private function syncPaymentRow(int $orderId, string $paymentMethod, string $paymentStatus, float $amount): void {
        $this->paymentModel->upsertForOrder($orderId, $paymentMethod, $amount, $paymentStatus);
    }

    public function create($customerId, $promotionId, $orderCode, $totalAmount, $discountAmount, $finalAmount): int|false {
        $legacyUserId = $this->resolveLegacyCustomerUserId((int) $customerId);
        if ($legacyUserId !== null) {
            $sql = "INSERT INTO {$this->table}
                    (customer_id, user_id, promotion_id, order_code, order_date, total_amount, discount_amount, final_amount, payment_method, payment_status, order_status)
                    VALUES (:customer_id, :user_id, :promotion_id, :order_code, NOW(), :total_amount, :discount_amount, :final_amount, 'cash', 'pending', 'pending')";
        } else {
            $sql = "INSERT INTO {$this->table}
                    (customer_id, promotion_id, order_code, order_date, total_amount, discount_amount, final_amount, payment_method, payment_status, order_status)
                    VALUES (:customer_id, :promotion_id, :order_code, NOW(), :total_amount, :discount_amount, :final_amount, 'cash', 'pending', 'pending')";
        }
        $stmt = $this->conn->prepare($sql);
        $params = [
            ':customer_id' => $customerId,
            ':promotion_id' => $promotionId ?: null,
            ':order_code' => $orderCode,
            ':total_amount' => $totalAmount,
            ':discount_amount' => $discountAmount,
            ':final_amount' => $finalAmount,
        ];
        if ($legacyUserId !== null) {
            $params[':user_id'] = $legacyUserId;
        }
        $ok = $stmt->execute($params);
        if (!$ok) {
            return false;
        }
        $orderId = (int) $this->conn->lastInsertId();
        $this->syncPaymentRow($orderId, 'cash', 'pending', (float) $finalAmount);
        return $orderId;
    }

    public function deleteIfNoTickets(int $orderId): void {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM tickets WHERE order_id = :order_id");
            $stmt->execute([':order_id' => $orderId]);
            if ((int) $stmt->fetchColumn() === 0) {
                $this->conn->prepare("DELETE FROM payments WHERE order_id = :order_id")->execute([':order_id' => $orderId]);
                $this->conn->prepare("DELETE FROM {$this->table} WHERE order_id = :order_id")->execute([':order_id' => $orderId]);
            }
        } catch (Throwable $e) {
        }
    }

    public function createManual(array $data): int|false {
        $paymentMethod = $data['payment_method'] ?? 'cash';
        $paymentStatus = $this->paymentModel->normalizeStatus((string) ($data['payment_status'] ?? 'pending'));
        $requestedOrderStatus = ($data['order_status'] ?? 'pending') === 'paid' ? 'completed' : ($data['order_status'] ?? 'pending');
        $orderStatus = $requestedOrderStatus;
        if ($orderStatus === 'completed' && !in_array($paymentStatus, ['paid', 'success'], true)) {
            $orderStatus = 'pending';
        }
        if ($orderStatus === 'cancelled' && !in_array($paymentStatus, ['refunded', 'failed'], true)) {
            $paymentStatus = in_array($paymentStatus, ['paid', 'success'], true) ? 'refunded' : 'failed';
        }

        $customerId = (int) ($data['customer_id'] ?? 0);
        $legacyUserId = $this->resolveLegacyCustomerUserId($customerId);
        if ($legacyUserId !== null) {
            $sql = "INSERT INTO {$this->table}
                    (customer_id, user_id, promotion_id, order_code, order_date, total_amount, discount_amount, final_amount, payment_method, payment_status, order_status, notes, created_by_employee_id)
                    VALUES (:customer_id, :user_id, :promotion_id, :order_code, NOW(), :total_amount, :discount_amount, :final_amount, :payment_method, :payment_status, :order_status, :notes, :created_by_employee_id)";
        } else {
            $sql = "INSERT INTO {$this->table}
                    (customer_id, promotion_id, order_code, order_date, total_amount, discount_amount, final_amount, payment_method, payment_status, order_status, notes, created_by_employee_id)
                    VALUES (:customer_id, :promotion_id, :order_code, NOW(), :total_amount, :discount_amount, :final_amount, :payment_method, :payment_status, :order_status, :notes, :created_by_employee_id)";
        }
        $stmt = $this->conn->prepare($sql);
        $params = [
            ':customer_id' => $customerId,
            ':promotion_id' => !empty($data['promotion_id']) ? (int) $data['promotion_id'] : null,
            ':order_code' => $data['order_code'],
            ':total_amount' => (float) ($data['total_amount'] ?? 0),
            ':discount_amount' => (float) ($data['discount_amount'] ?? 0),
            ':final_amount' => (float) ($data['final_amount'] ?? 0),
            ':payment_method' => $this->paymentModel->normalizeMethod($paymentMethod),
            ':payment_status' => $this->paymentModel->normalizeStatus($paymentStatus),
            ':order_status' => $orderStatus,
            ':notes' => $data['notes'] ?: null,
            ':created_by_employee_id' => $data['created_by_employee_id'] ?? null,
        ];
        if ($legacyUserId !== null) {
            $params[':user_id'] = $legacyUserId;
        }
        $ok = $stmt->execute($params);
        if (!$ok) {
            return false;
        }
        $orderId = (int) $this->conn->lastInsertId();
        $this->syncPaymentRow($orderId, (string) $paymentMethod, (string) $paymentStatus, (float) ($data['final_amount'] ?? 0));
        return $orderId;
    }

    public function getAll(array $filters = []): array {
        $sql = "SELECT o.*,
                       COALESCE(c.customer_id, o.customer_id, o.user_id, 0) AS user_id,
                       COALESCE(c.full_name, u.full_name, 'Khách hàng') AS full_name,
                       COALESCE(c.email, u.email, '') AS email,
                       COALESCE(c.phone, u.phone, '') AS phone,
                       COALESCE(p.code, p.promo_code) AS promo_code,
                       COALESCE(p.code, p.promo_code) AS promotion_code,
                       COALESCE(p.title, '') AS promotion_title,
                       COUNT(t.ticket_id) AS ticket_count,
                       SUM(CASE WHEN t.ticket_status <> 'cancelled' THEN t.price ELSE 0 END) AS ticket_total
                FROM {$this->table} o
                LEFT JOIN customers c ON c.customer_id = o.customer_id
                LEFT JOIN users u ON u.user_id = o.user_id AND u.role = 'customer'
                LEFT JOIN promotions p ON p.promotion_id = o.promotion_id
                LEFT JOIN tickets t ON t.order_id = o.order_id
                WHERE 1=1";
        $params = [];
        if (!empty($filters['keyword'])) {
            $sql .= " AND (o.order_code LIKE :keyword OR COALESCE(c.full_name, u.full_name, '') LIKE :keyword OR COALESCE(c.email, u.email, '') LIKE :keyword)";
            $params[':keyword'] = '%' . trim($filters['keyword']) . '%';
        }
        if (!empty($filters['order_status'])) {
            $sql .= " AND o.order_status = :order_status";
            $params[':order_status'] = $filters['order_status'];
        }
        if (!empty($filters['payment_status'])) {
            $paymentStatus = $filters['payment_status'];
            if ($paymentStatus === 'paid') {
                $sql .= " AND o.payment_status IN ('paid', 'success')";
            } else {
                $sql .= " AND o.payment_status = :payment_status";
                $params[':payment_status'] = $paymentStatus;
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
        $sql = "SELECT o.*,
                       COALESCE(c.customer_id, o.customer_id, o.user_id, 0) AS user_id,
                       COALESCE(c.full_name, u.full_name, 'Khách hàng') AS full_name,
                       COALESCE(c.email, u.email, '') AS email,
                       COALESCE(c.phone, u.phone, '') AS phone,
                       COALESCE(c.address, u.address, '') AS address,
                       COALESCE(c.bank_account, u.bank_account, '') AS bank_account,
                       COALESCE(c.e_wallet_account, u.e_wallet_account, '') AS e_wallet_account,
                       COALESCE(p.code, p.promo_code) AS promotion_code,
                       COALESCE(p.title, '') AS promotion_title,
                       COALESCE(p.promo_code, p.code) AS promo_code,
                       p.discount_type, p.discount_value
                FROM {$this->table} o
                LEFT JOIN customers c ON c.customer_id = o.customer_id
                LEFT JOIN users u ON u.user_id = o.user_id AND u.role = 'customer'
                LEFT JOIN promotions p ON p.promotion_id = o.promotion_id
                WHERE o.order_id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        $order = $stmt->fetch();
        if (!$order) return null;
        $order = $this->normalizeOrder($order);
        $order['tickets'] = $this->getTicketsByOrderId((int) $id);
        $order['payment'] = $this->paymentModel->getByOrderId((int) $id);
        return $order;
    }

    public function getOrdersByCustomerId(int $customerId): array {
        $stmt = $this->conn->prepare("SELECT o.*,
                COALESCE(c.customer_id, o.customer_id, o.user_id, 0) AS user_id,
                COALESCE(c.full_name, u.full_name, 'Khách hàng') AS full_name,
                COALESCE(c.email, u.email, '') AS email,
                COALESCE(c.phone, u.phone, '') AS phone,
                COALESCE(p.code, p.promo_code) AS promo_code,
                COALESCE(p.code, p.promo_code) AS promotion_code,
                COALESCE(p.title, '') AS promotion_title
                FROM {$this->table} o
                LEFT JOIN customers c ON c.customer_id = o.customer_id
                LEFT JOIN users u ON u.user_id = o.user_id AND u.role = 'customer'
                LEFT JOIN promotions p ON o.promotion_id = p.promotion_id
                WHERE o.customer_id = :customer_id OR (o.customer_id IS NULL AND o.user_id = :legacy_user_id)
                ORDER BY o.order_date DESC");
        $legacyUserId = $this->resolveLegacyCustomerUserId($customerId) ?? 0;
        $stmt->execute([':customer_id' => $customerId, ':legacy_user_id' => $legacyUserId]);
        return $this->normalizeOrders($stmt->fetchAll());
    }

    public function getTicketsByOrderId(int $orderId): array {
        $rowCol = 'row_name';
        try {
            $seatColumns = $this->fetchColumns('seats');
            if (isset($seatColumns['seat_row'])) $rowCol = 'seat_row';
        } catch (Throwable $e) {}

        $sql = "SELECT t.*, s.show_date, s.start_time, m.title AS movie_title, COALESCE(NULLIF(r.room_name, ''), r.name) AS room_name,
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
        if (!$existing) return false;

        if (is_string($data)) {
            $status = $data === 'paid' ? 'completed' : $data;
            $paymentStatus = $data === 'paid' ? 'paid' : 'pending';
            $paymentMethod = $existing['payment_method'] ?? 'cash';
            $stmt = $this->conn->prepare("UPDATE {$this->table} SET order_status = :status, payment_status = :payment_status, payment_method = :payment_method WHERE order_id = :order_id");
            $ok = $stmt->execute([':status' => $status, ':payment_status' => $paymentStatus, ':payment_method' => $paymentMethod, ':order_id' => $id]);
            if ($ok) {
                $this->syncPaymentRow((int) $id, (string) $paymentMethod, (string) $paymentStatus, (float) ($existing['final_amount'] ?? 0));
                if ($paymentStatus === 'paid' && !in_array(($existing['payment_status'] ?? ''), ['paid', 'success'], true)) {
                    $this->ticketModel->markPaid($id);
                    $this->promotionModel->incrementUsedCount($existing['promotion_id'] ?? null);
                }
            }
            return $ok;
        }

        $newOrderStatus = ($data['order_status'] ?? 'pending') === 'paid' ? 'completed' : ($data['order_status'] ?? 'pending');
        $newPaymentStatus = $this->paymentModel->normalizeStatus((string) ($data['payment_status'] ?? 'pending'));
        $paymentMethod = $this->paymentModel->normalizeMethod((string) ($data['payment_method'] ?? 'cash'));
        if ($newOrderStatus === 'completed' && !in_array($newPaymentStatus, ['paid', 'success'], true)) {
            $newOrderStatus = 'pending';
        }
        if ($newOrderStatus === 'cancelled' && !in_array($newPaymentStatus, ['refunded', 'failed'], true)) {
            $newPaymentStatus = in_array(($existing['payment_status'] ?? 'pending'), ['paid', 'success'], true) ? 'refunded' : 'failed';
        }
        $sql = "UPDATE {$this->table}
                SET order_status = :order_status,
                    payment_status = :payment_status,
                    payment_method = :payment_method,
                    notes = :notes,
                    updated_by_employee_id = :updated_by_employee_id
                WHERE order_id = :order_id";
        $stmt = $this->conn->prepare($sql);
        $ok = $stmt->execute([
            ':order_status' => $newOrderStatus,
            ':payment_status' => $newPaymentStatus,
            ':payment_method' => $paymentMethod,
            ':notes' => $data['notes'] ?: null,
            ':updated_by_employee_id' => $data['updated_by_employee_id'] ?? null,
            ':order_id' => $id,
        ]);

        if ($ok) {
            $this->syncPaymentRow((int) $id, $paymentMethod, $newPaymentStatus, (float) ($existing['final_amount'] ?? 0));
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


    public function canApproveOrder(int $orderId): bool {
        $order = $this->getById($orderId);
        if (!$order) {
            return false;
        }
        if (($order['order_status'] ?? '') !== 'pending') {
            return false;
        }
        return in_array(($order['payment_status'] ?? ''), ['paid', 'success'], true);
    }

    public function canCancelOrder(int $orderId): bool {
        $order = $this->getById($orderId);
        if (!$order) {
            return false;
        }
        return ($order['order_status'] ?? '') !== 'cancelled';
    }

    public function getPendingApprovalCount(): int {
        $stmt = $this->conn->query("SELECT COUNT(*) FROM {$this->table} WHERE order_status = 'pending' AND payment_status IN ('paid', 'success')");
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    public function approveOrder(int $orderId, ?int $employeeId = null): bool {
        if (!$this->canApproveOrder($orderId)) return false;
        $order = $this->getById($orderId);
        if (!$order) return false;
        return $this->updateStatus($orderId, [
            'order_status' => 'completed',
            'payment_status' => 'paid',
            'payment_method' => $order['payment_method'] ?: 'cash',
            'notes' => trim(($order['notes'] ?? '') . ' | Duyệt vé bởi admin'),
            'updated_by_employee_id' => $employeeId,
        ]);
    }

    public function markCancelled($orderId): bool {
        return $this->cancelOrder((int) $orderId, 'Hủy từ yêu cầu hủy vé');
    }

    public function cancelOrder(int $orderId, string $note = '', ?int $employeeId = null): bool {
        $order = $this->getById($orderId);
        if (!$order) return false;
        $newPaymentStatus = in_array(($order['payment_status'] ?? ''), ['paid', 'success'], true) ? 'refunded' : 'failed';
        if (!$this->canCancelOrder($orderId)) {
            return false;
        }
        $stmt = $this->conn->prepare("UPDATE {$this->table}
            SET order_status = 'cancelled', payment_status = :payment_status,
                notes = :notes, updated_by_employee_id = :updated_by_employee_id WHERE order_id = :order_id");
        $ok = $stmt->execute([
            ':payment_status' => $newPaymentStatus,
            ':notes' => trim(($order['notes'] ?? '') . ($note ? ' | ' . $note : '')),
            ':updated_by_employee_id' => $employeeId,
            ':order_id' => $orderId,
        ]);
        if ($ok) {
            $this->ticketModel->markCancelled($orderId);
            $this->syncPaymentRow($orderId, (string) ($order['payment_method'] ?? 'cash'), $newPaymentStatus, (float) ($order['final_amount'] ?? 0));
            if (in_array(($order['payment_status'] ?? ''), ['paid', 'success'], true)) {
                $this->promotionModel->decrementUsedCount($order['promotion_id'] ?? null);
            }
        }
        return $ok;
    }

    public function updatePromotion($orderId, $promotionId, $discountAmount, $finalAmount): bool {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET promotion_id = :promotion_id, discount_amount = :discount_amount, final_amount = :final_amount WHERE order_id = :order_id");
        $ok = $stmt->execute([
            ':promotion_id' => $promotionId,
            ':discount_amount' => $discountAmount,
            ':final_amount' => $finalAmount,
            ':order_id' => $orderId,
        ]);
        if ($ok) {
            $order = $this->getById((int) $orderId);
            if ($order) {
                $this->syncPaymentRow((int) $orderId, (string) ($order['payment_method'] ?? 'cash'), (string) ($order['payment_status'] ?? 'pending'), (float) $finalAmount);
            }
        }
        return $ok;
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
            'approval_pending_orders' => $this->getPendingApprovalCount(),
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
        return $this->conn->query("SELECT customer_id, full_name, email FROM customers WHERE status = 'active' ORDER BY full_name")->fetchAll();
    }

    private function normalizeOrders(array $orders): array {
        return array_map(fn($order) => $this->normalizeOrder($order), $orders);
    }

    private function normalizeOrder(array $order): array {
        $order['order_id'] = (int) ($order['order_id'] ?? 0);
        $order['customer_id'] = (int) ($order['customer_id'] ?? $order['user_id'] ?? 0);
        $order['user_id'] = (int) ($order['customer_id'] ?? 0);
        $order['order_code'] = (string) ($order['order_code'] ?? '');
        $order['order_date'] = $order['order_date'] ?? null;
        $order['full_name'] = (string) ($order['full_name'] ?? 'Khách hàng');
        $order['email'] = (string) ($order['email'] ?? '');
        $order['phone'] = (string) ($order['phone'] ?? '');
        $order['address'] = (string) ($order['address'] ?? '');
        $order['promotion_code'] = (string) ($order['promotion_code'] ?? $order['promo_code'] ?? '');
        $order['promo_code'] = (string) ($order['promo_code'] ?? $order['promotion_code'] ?? '');
        $order['promotion_title'] = (string) ($order['promotion_title'] ?? '');
        $order['total_amount'] = (float) ($order['total_amount'] ?? 0);
        $order['discount_amount'] = (float) ($order['discount_amount'] ?? 0);
        $order['final_amount'] = (float) ($order['final_amount'] ?? 0);
        $order['ticket_count'] = (int) ($order['ticket_count'] ?? 0);
        $order['ticket_total'] = (float) ($order['ticket_total'] ?? 0);
        $order['payment_method'] = (string) ($order['payment_method'] ?? 'cash');
        $order['payment_status'] = $this->paymentModel->normalizeStatus((string) ($order['payment_status'] ?? 'pending'));
        $order['order_status'] = (string) ($order['order_status'] ?? 'pending');
        $order['notes'] = (string) ($order['notes'] ?? '');
        return $order;
    }
}
?>
