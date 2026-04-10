<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private PDO $conn;
    private string $table = 'users';
    private array $columns = [];

    public ?int $user_id = null;
    public string $full_name = '';
    public string $email = '';
    public string $password = '';
    public ?string $phone = null;
    public ?string $birthday = null;
    public ?string $address = null;
    public ?string $bank_account = null;
    public string $role = 'customer';
    public string $status = 'active';
    public ?string $avatar = null;
    public ?string $position = null;

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
        $this->addColumnIfMissing('phone', "VARCHAR(20) NULL AFTER email");
        $this->addColumnIfMissing('birthday', "DATE NULL AFTER phone");
        $this->addColumnIfMissing('address', "VARCHAR(255) NULL AFTER birthday");
        $this->addColumnIfMissing('bank_account', "VARCHAR(50) NULL AFTER address");
        $this->addColumnIfMissing('avatar', "VARCHAR(255) NULL AFTER status");
        $this->addColumnIfMissing('position', "VARCHAR(100) NULL AFTER role");
        $this->addColumnIfMissing('branch_name', "VARCHAR(150) NULL AFTER position");
        $this->addColumnIfMissing('hire_date', "DATE NULL AFTER branch_name");
        $this->addColumnIfMissing('created_at', "TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP");
        $this->addColumnIfMissing('oauth_provider', "VARCHAR(50) NULL AFTER avatar");
        $this->addColumnIfMissing('oauth_id', "VARCHAR(100) NULL AFTER oauth_provider");

        try {
            $this->conn->exec("ALTER TABLE {$this->table} MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'active'");
        } catch (Throwable $e) {
        }
    }

    public function register(): bool {
        $sql = "INSERT INTO {$this->table}
                (full_name, email, password, phone, birthday, address, role, status, avatar)
                VALUES (:full_name, :email, :password, :phone, :birthday, :address, :role, :status, :avatar)";
        $stmt = $this->conn->prepare($sql);
        $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);

        return $stmt->execute([
            ':full_name' => $this->full_name,
            ':email' => $this->email,
            ':password' => $hashedPassword,
            ':phone' => $this->phone,
            ':birthday' => $this->birthday ?: null,
            ':address' => $this->address ?: null,
            ':role' => $this->role,
            ':status' => $this->normalizeStatus($this->status, $this->role),
            ':avatar' => $this->avatar ?: 'assets/images/default-avatar.svg',
        ]);
    }

    public function login(): bool {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $this->email]);
        $row = $stmt->fetch();

        if (!$row || !password_verify($this->password, $row['password'])) {
            return false;
        }

        if (in_array($row['status'], ['inactive', 'blocked'], true)) {
            return false;
        }

        $this->user_id = (int) $row['user_id'];
        $this->full_name = $row['full_name'];
        $this->email = $row['email'];
        $this->phone = $row['phone'] ?? null;
        $this->birthday = $row['birthday'] ?? null;
        $this->address = $row['address'] ?? null;
        $this->bank_account = $row['bank_account'] ?? null;
        $this->role = $row['role'];
        $this->status = $row['status'];
        $this->avatar = $row['avatar'] ?? null;
        $this->position = $row['position'] ?? null;
        return true;
    }

    public function update(): bool {
        $sql = "UPDATE {$this->table}
                SET full_name = :full_name,
                    email = :email,
                    phone = :phone,
                    birthday = :birthday,
                    address = :address,
                    bank_account = :bank_account
                WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':full_name' => $this->full_name,
            ':email' => $this->email,
            ':phone' => $this->phone,
            ':birthday' => $this->birthday ?: null,
            ':address' => $this->address ?: null,
            ':bank_account' => $this->bank_account ?: null,
            ':user_id' => $this->user_id,
        ]);
    }

    public function getUserById(int $userId): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE user_id = :id LIMIT 1");
        $stmt->execute([':id' => $userId]);
        return $stmt->fetch() ?: null;
    }

    public function getById(int $userId): ?array {
        return $this->getUserById($userId);
    }

    public function emailExists(string $email, ?int $ignoreId = null): bool {
        $sql = "SELECT user_id FROM {$this->table} WHERE email = :email";
        $params = [':email' => $email];
        if ($ignoreId) {
            $sql .= " AND user_id <> :ignore_id";
            $params[':ignore_id'] = $ignoreId;
        }
        $sql .= ' LIMIT 1';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetch();
    }

    public function changePassword(int $userId, string $oldPassword, string $newPassword): bool|array {
        $stmt = $this->conn->prepare("SELECT password FROM {$this->table} WHERE user_id = :user_id LIMIT 1");
        $stmt->execute([':user_id' => $userId]);
        $row = $stmt->fetch();
        if (!$row || !password_verify($oldPassword, $row['password'])) {
            return false;
        }

        $passwordErrors = $this->validatePassword($newPassword);
        if (!empty($passwordErrors)) {
            return $passwordErrors;
        }

        $stmt = $this->conn->prepare("UPDATE {$this->table} SET password = :password WHERE user_id = :user_id");
        return $stmt->execute([
            ':password' => password_hash($newPassword, PASSWORD_DEFAULT),
            ':user_id' => $userId,
        ]);
    }

    public function validatePassword(string $password): array {
        $errors = [];
        if (strlen($password) < 8) $errors[] = 'Mật khẩu phải có ít nhất 8 ký tự.';
        if (!preg_match('/[A-Z]/', $password)) $errors[] = 'Mật khẩu phải có ít nhất 1 ký tự viết hoa.';
        if (!preg_match('/[a-z]/', $password)) $errors[] = 'Mật khẩu phải có ít nhất 1 ký tự viết thường.';
        if (!preg_match('/\d/', $password)) $errors[] = 'Mật khẩu phải có ít nhất 1 chữ số.';
        if (!preg_match('/[^a-zA-Z\d]/', $password)) $errors[] = 'Mật khẩu phải có ít nhất 1 ký tự đặc biệt.';
        return $errors;
    }

    public function findByOAuth($provider, $oauthId): ?array {
        if (!$this->hasColumn('oauth_provider') || !$this->hasColumn('oauth_id')) {
            return null;
        }

        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE oauth_provider = :provider AND oauth_id = :oauth_id LIMIT 1");
        $stmt->execute([
            ':provider' => $provider,
            ':oauth_id' => $oauthId,
        ]);
        return $stmt->fetch() ?: null;
    }

    public function findByEmail($email): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public function registerOAuth($provider, $oauthId): bool {
        $sql = "INSERT INTO {$this->table}
                (full_name, email, password, phone, birthday, address, role, status, avatar, oauth_provider, oauth_id)
                VALUES (:full_name, :email, :password, :phone, :birthday, :address, :role, :status, :avatar, :provider, :oauth_id)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':full_name' => $this->full_name,
            ':email' => $this->email,
            ':password' => password_hash($this->password, PASSWORD_DEFAULT),
            ':phone' => $this->phone,
            ':birthday' => $this->birthday ?: null,
            ':address' => $this->address ?: null,
            ':role' => $this->role,
            ':status' => $this->normalizeStatus($this->status, $this->role),
            ':avatar' => $this->avatar ?: 'assets/images/default-avatar.svg',
            ':provider' => $provider,
            ':oauth_id' => $oauthId,
        ]);
    }

    public function linkOAuthAccount(int $userId, string $provider, string $oauthId): bool {
        if (!$this->hasColumn('oauth_provider') || !$this->hasColumn('oauth_id')) {
            return false;
        }
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET oauth_provider = :provider, oauth_id = :oauth_id WHERE user_id = :user_id");
        return $stmt->execute([
            ':provider' => $provider,
            ':oauth_id' => $oauthId,
            ':user_id' => $userId,
        ]);
    }

    public function create(array $data): bool {
        $sql = "INSERT INTO {$this->table}
                (full_name, email, phone, password, role, position, birthday, address, branch_name, hire_date, status, avatar)
                VALUES (:full_name, :email, :phone, :password, :role, :position, :birthday, :address, :branch_name, :hire_date, :status, :avatar)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':full_name' => trim($data['full_name']),
            ':email' => trim($data['email']),
            ':phone' => $data['phone'] ?: null,
            ':password' => $data['password'],
            ':role' => $data['role'],
            ':position' => $data['position'] ?: null,
            ':birthday' => $data['birthday'] ?: null,
            ':address' => $data['address'] ?: null,
            ':branch_name' => $data['branch_name'] ?: null,
            ':hire_date' => $data['hire_date'] ?: null,
            ':status' => $this->normalizeStatus($data['status'] ?? 'active', $data['role']),
            ':avatar' => $data['avatar'] ?: 'assets/images/default-avatar.svg',
        ]);
    }

    public function adminUpdate(int $id, array $data): bool {
        $allowedColumns = ['full_name', 'email', 'phone', 'birthday', 'address', 'position', 'branch_name', 'hire_date', 'status', 'avatar', 'password'];
        $fields = [];
        $params = [':user_id' => $id];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowedColumns, true)) {
                $fields[] = "{$key} = :{$key}";
                $params[":{$key}"] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $stmt = $this->conn->prepare("UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE user_id = :user_id");
        return $stmt->execute($params);
    }

    public function delete(int $userId): bool {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE user_id = :id");
        return $stmt->execute([':id' => $userId]);
    }

    public function toggleStatus(int $userId): bool {
        $user = $this->getById($userId);
        if (!$user) {
            return false;
        }

        $newStatus = match ($user['role']) {
            'staff' => in_array($user['status'], ['working', 'active'], true) ? 'inactive' : 'working',
            'customer' => in_array($user['status'], ['blocked', 'inactive'], true) ? 'active' : 'blocked',
            default => $user['status'] === 'active' ? 'inactive' : 'active',
        };

        $stmt = $this->conn->prepare("UPDATE {$this->table} SET status = :status WHERE user_id = :id");
        return $stmt->execute([
            ':status' => $newStatus,
            ':id' => $userId,
        ]);
    }

    public function getAllByRole(string $role, array $filters = []): array {
        $sql = "SELECT * FROM {$this->table} WHERE role = :role";
        $params = [':role' => $role];

        if (!empty($filters['keyword'])) {
            $sql .= " AND (full_name LIKE :keyword OR email LIKE :keyword OR COALESCE(phone, '') LIKE :keyword OR CAST(user_id AS CHAR) LIKE :keyword)";
            $params[':keyword'] = '%' . trim($filters['keyword']) . '%';
        }
        if (!empty($filters['status'])) {
            $sql .= " AND status = :status";
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['position'])) {
            $sql .= " AND position = :position";
            $params[':position'] = $filters['position'];
        }

        $orderColumn = $this->hasColumn('created_at') ? 'created_at' : 'user_id';
        $sql .= " ORDER BY {$orderColumn} DESC, user_id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getStatsByRole(string $role): array {
        $stmt = $this->conn->prepare("SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN status IN ('active', 'working') THEN 1 ELSE 0 END) AS active_count,
                SUM(CASE WHEN status IN ('inactive', 'blocked') THEN 1 ELSE 0 END) AS inactive_count,
                SUM(CASE WHEN status IN ('leave', 'resigned') THEN 1 ELSE 0 END) AS leave_count
            FROM {$this->table}
            WHERE role = :role");
        $stmt->execute([':role' => $role]);
        $row = $stmt->fetch() ?: [];

        return [
            'total' => (int) ($row['total'] ?? 0),
            'active_count' => (int) ($row['active_count'] ?? 0),
            'inactive_count' => (int) ($row['inactive_count'] ?? 0),
            'leave_count' => (int) ($row['leave_count'] ?? 0),
        ];
    }

    public function getPositionOptions(): array {
        return ['Quản lý', 'Nhân viên', 'Nhân viên bán vé', 'Kỹ thuật viên', 'Giám sát ca', 'CSKH'];
    }

    public function getBranchOptions(): array {
        return ['Cinema Central Lê Lợi', 'Cinema Central Landmark', 'Cinema Central Phú Mỹ Hưng', 'Cinema Central Đà Nẵng'];
    }

    public function countCustomers(): int {
        $stmt = $this->conn->query("SELECT COUNT(*) FROM {$this->table} WHERE role = 'customer'");
        return (int) $stmt->fetchColumn();
    }

    private function normalizeStatus(string $status, string $role): string {
        if ($role === 'staff') {
            return in_array($status, ['working', 'leave', 'inactive', 'resigned'], true) ? $status : 'working';
        }
        if ($role === 'customer') {
            return in_array($status, ['active', 'inactive', 'blocked'], true) ? $status : 'active';
        }
        return in_array($status, ['active', 'inactive'], true) ? $status : 'active';
    }
}
?>