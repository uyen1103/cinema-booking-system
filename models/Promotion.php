<?php
require_once __DIR__ . '/../config/database.php';

class Promotion {
    private PDO $conn;
    private string $table = 'promotions';
    private array $columns = [];

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->syncSchema();
        $this->columns = $this->fetchColumns();
    }

    private function fetchColumns(): array {
        $columns = [];
        $rows = $this->conn->query("SHOW COLUMNS FROM {$this->table}")->fetchAll();
        foreach ($rows as $row) {
            $columns[strtolower($row['Field'])] = true;
        }
        return $columns;
    }

    private function hasColumn(string $column): bool {
        return isset($this->columns[strtolower($column)]);
    }

    private function addColumnIfMissing(string $column, string $definition): void {
        $existing = $this->fetchColumns();
        if (!isset($existing[strtolower($column)])) {
            $this->conn->exec("ALTER TABLE {$this->table} ADD COLUMN {$column} {$definition}");
        }
    }

    private function syncSchema(): void {
        $this->addColumnIfMissing('code', "VARCHAR(30) NULL");
        $this->addColumnIfMissing('promo_code', "VARCHAR(30) NULL");
        $this->addColumnIfMissing('title', "VARCHAR(160) NULL");
        $this->addColumnIfMissing('min_order_amount', "DECIMAL(12,2) NOT NULL DEFAULT 0");
        $this->addColumnIfMissing('min_tickets', "INT DEFAULT 1");
        $this->addColumnIfMissing('min_amount', "DECIMAL(12,2) DEFAULT 0");
        $this->addColumnIfMissing('max_discount', "DECIMAL(12,2) NULL");
        $this->addColumnIfMissing('usage_limit', "INT NULL");
        $this->addColumnIfMissing('used_count', "INT NOT NULL DEFAULT 0");
        $this->addColumnIfMissing('budget', "DECIMAL(12,2) NOT NULL DEFAULT 0");
        $this->addColumnIfMissing('description', "TEXT NULL");
        $this->addColumnIfMissing('image_path', "VARCHAR(255) NULL");
        $this->addColumnIfMissing('status', "TINYINT NOT NULL DEFAULT 1");
        $this->addColumnIfMissing('created_at', "TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP");
        $this->addColumnIfMissing('updated_at', "TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");

        try {
            $columns = $this->fetchColumns();
            if (isset($columns['promo_code'])) {
                $this->conn->exec("UPDATE {$this->table} SET code = COALESCE(NULLIF(code, ''), promo_code), promo_code = COALESCE(NULLIF(promo_code, ''), code)");
            }
            $this->conn->exec("UPDATE {$this->table} SET title = COALESCE(NULLIF(title, ''), CONCAT('Khuyến mãi ', COALESCE(code, promo_code, promotion_id)))");
        } catch (Throwable $e) {
        }
    }

    public function getAll(array $filters = []): array {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        if (!empty($filters['keyword'])) {
            $sql .= " AND (COALESCE(code,promo_code) LIKE :keyword OR title LIKE :keyword)";
            $params[':keyword'] = '%' . trim($filters['keyword']) . '%';
        }
        if (isset($filters['status']) && $filters['status'] !== '') {
            $sql .= " AND status = :status";
            $params[':status'] = (int) $filters['status'];
        }
        $orderColumn = $this->hasColumn('created_at') ? 'created_at' : 'promotion_id';
        $sql .= " ORDER BY {$orderColumn} DESC, promotion_id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE promotion_id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function getByCode($promoCode): ?array {
        $sql = "SELECT * FROM {$this->table}
                WHERE COALESCE(code, promo_code) = :promo_code
                  AND start_date <= NOW()
                  AND end_date >= NOW()
                  AND (status = 1 OR status IS NULL)
                  AND (usage_limit IS NULL OR used_count < usage_limit)
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':promo_code' => strtoupper(trim($promoCode))]);
        $promo = $stmt->fetch() ?: null;
        if (!$promo) {
            return null;
        }
        $promo['promo_code'] = $promo['code'] ?? $promo['promo_code'] ?? '';
        $promo['min_amount'] = $promo['min_amount'] ?? $promo['min_order_amount'] ?? 0;
        return $promo;
    }

    public function getActivePromotions(): array {
        $sql = "SELECT *, COALESCE(code, promo_code) AS promo_code
                FROM {$this->table}
                WHERE start_date <= NOW() AND end_date >= NOW() AND (status = 1 OR status IS NULL)
                ORDER BY start_date ASC";
        return $this->conn->query($sql)->fetchAll();
    }

    public function create(array $data): bool {
        $sql = "INSERT INTO {$this->table}
                (code, promo_code, title, discount_type, discount_value, min_order_amount, min_amount, max_discount, usage_limit, used_count, budget, start_date, end_date, description, image_path, status)
                VALUES (:code, :promo_code, :title, :discount_type, :discount_value, :min_order_amount, :min_amount, :max_discount, :usage_limit, :used_count, :budget, :start_date, :end_date, :description, :image_path, :status)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($this->mapData($data));
    }

    public function update(int $id, array $data): bool {
        $sql = "UPDATE {$this->table}
                SET code = :code,
                    promo_code = :promo_code,
                    title = :title,
                    discount_type = :discount_type,
                    discount_value = :discount_value,
                    min_order_amount = :min_order_amount,
                    min_amount = :min_amount,
                    max_discount = :max_discount,
                    usage_limit = :usage_limit,
                    used_count = :used_count,
                    budget = :budget,
                    start_date = :start_date,
                    end_date = :end_date,
                    description = :description,
                    image_path = :image_path,
                    status = :status
                WHERE promotion_id = :promotion_id";
        $params = $this->mapData($data);
        $params[':promotion_id'] = $id;
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE promotion_id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function codeExists(string $code, ?int $ignoreId = null): bool {
        $sql = "SELECT promotion_id FROM {$this->table} WHERE COALESCE(code, promo_code) = :code";
        $params = [':code' => strtoupper(trim($code))];
        if ($ignoreId) {
            $sql .= " AND promotion_id <> :ignore_id";
            $params[':ignore_id'] = $ignoreId;
        }
        $sql .= ' LIMIT 1';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetch();
    }

    public function incrementUsedCount(?int $promotionId): void {
        if (!$promotionId) return;
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET used_count = COALESCE(used_count,0) + 1 WHERE promotion_id = :id");
        $stmt->execute([':id' => $promotionId]);
    }

    public function decrementUsedCount(?int $promotionId): void {
        if (!$promotionId) return;
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET used_count = CASE WHEN COALESCE(used_count,0) > 0 THEN used_count - 1 ELSE 0 END WHERE promotion_id = :id");
        $stmt->execute([':id' => $promotionId]);
    }

    public function getStats(): array {
        $row = $this->conn->query("SELECT 
            COUNT(*) AS total_promotions,
            SUM(CASE WHEN status = 1 OR status IS NULL THEN 1 ELSE 0 END) AS active_promotions,
            SUM(COALESCE(used_count, 0)) AS total_used,
            SUM(COALESCE(budget, 0)) AS total_budget
            FROM {$this->table}")->fetch() ?: [];
        return [
            'total_promotions' => (int) ($row['total_promotions'] ?? 0),
            'active_promotions' => (int) ($row['active_promotions'] ?? 0),
            'total_used' => (int) ($row['total_used'] ?? 0),
            'total_budget' => (float) ($row['total_budget'] ?? 0),
        ];
    }

    private function mapData(array $data): array {
        $code = strtoupper(trim($data['code'] ?? $data['promo_code'] ?? ''));
        return [
            ':code' => $code,
            ':promo_code' => $code,
            ':title' => trim($data['title'] ?? ('Khuyến mãi ' . $code)),
            ':discount_type' => $data['discount_type'] ?? 'percent',
            ':discount_value' => (float) ($data['discount_value'] ?? 0),
            ':min_order_amount' => (float) ($data['min_order_amount'] ?? $data['min_amount'] ?? 0),
            ':min_amount' => (float) ($data['min_amount'] ?? $data['min_order_amount'] ?? 0),
            ':max_discount' => ($data['max_discount'] ?? '') !== '' ? (float) $data['max_discount'] : null,
            ':usage_limit' => ($data['usage_limit'] ?? '') !== '' ? (int) $data['usage_limit'] : null,
            ':used_count' => (int) ($data['used_count'] ?? 0),
            ':budget' => (float) ($data['budget'] ?? 0),
            ':start_date' => $data['start_date'] ?? date('Y-m-d H:i:s'),
            ':end_date' => $data['end_date'] ?? date('Y-m-d H:i:s', strtotime('+30 day')),
            ':description' => ($data['description'] ?? '') ?: null,
            ':image_path' => ($data['image_path'] ?? '') ?: null,
            ':status' => (int) ($data['status'] ?? 1),
        ];
    }
}
?>