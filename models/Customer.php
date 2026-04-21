<?php
require_once __DIR__ . '/../config/database.php';

class Customer {
    private PDO $conn;
    private string $table = 'customers';
    private string $legacyTable = 'users';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    private function normalizeRow(array $row): array {
        $row['user_id'] = (int) ($row['customer_id'] ?? 0);
        $row['role'] = 'customer';
        $row['account_scope'] = 'customer';
        return $row;
    }

    private function normalizeStatus(?string $status): string {
        return in_array($status, ['active', 'inactive', 'blocked'], true) ? $status : 'active';
    }

    private function legacyTableExists(): bool {
        try {
            $stmt = $this->conn->query("SHOW TABLES LIKE '{$this->legacyTable}'");
            return (bool) $stmt->fetchColumn();
        } catch (Throwable $e) {
            return false;
        }
    }

    private function findLegacyCustomerByLogin(string $identifier): ?array {
        if (!$this->legacyTableExists()) {
            return null;
        }

        $stmt = $this->conn->prepare("SELECT * FROM {$this->legacyTable} WHERE role = 'customer' AND (email = :identifier OR COALESCE(phone, '') = :identifier) LIMIT 1");
        $stmt->execute([':identifier' => trim($identifier)]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    private function syncFromLegacyUser(array $legacy): ?array {
        $existing = $this->findByEmail((string) ($legacy['email'] ?? ''));
        if ($existing) {
            return $existing;
        }

        $stmt = $this->conn->prepare("INSERT INTO {$this->table}
            (full_name, email, password, phone, birthday, address, avatar, bank_account, e_wallet_account, status, oauth_provider, oauth_id)
            VALUES (:full_name, :email, :password, :phone, :birthday, :address, :avatar, :bank_account, :e_wallet_account, :status, :oauth_provider, :oauth_id)");
        $stmt->execute([
            ':full_name' => $legacy['full_name'] ?? 'Khách hàng',
            ':email' => $legacy['email'] ?? '',
            ':password' => $legacy['password'] ?? '',
            ':phone' => $legacy['phone'] ?? null,
            ':birthday' => $legacy['birthday'] ?? null,
            ':address' => $legacy['address'] ?? null,
            ':avatar' => $legacy['avatar'] ?? 'assets/images/default-avatar.svg',
            ':bank_account' => $legacy['bank_account'] ?? null,
            ':e_wallet_account' => $legacy['e_wallet_account'] ?? null,
            ':status' => $this->normalizeStatus($legacy['status'] ?? 'active'),
            ':oauth_provider' => $legacy['oauth_provider'] ?? null,
            ':oauth_id' => $legacy['oauth_id'] ?? null,
        ]);

        return $this->findByEmail((string) ($legacy['email'] ?? ''));
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

    public function emailExists(string $email, ?int $ignoreId = null): bool {
        $sql = "SELECT customer_id FROM {$this->table} WHERE email = :email";
        $params = [':email' => $email];
        if ($ignoreId !== null) {
            $sql .= ' AND customer_id <> :ignore_id';
            $params[':ignore_id'] = $ignoreId;
        }
        $sql .= ' LIMIT 1';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetch();
    }

    public function register(array $data): bool {
        $stmt = $this->conn->prepare("INSERT INTO {$this->table}
            (full_name, email, password, phone, birthday, address, bank_account, e_wallet_account, status, avatar)
            VALUES (:full_name, :email, :password, :phone, :birthday, :address, :bank_account, :e_wallet_account, :status, :avatar)");

        return $stmt->execute([
            ':full_name' => $data['full_name'],
            ':email' => $data['email'],
            ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
            ':phone' => $data['phone'] ?: null,
            ':birthday' => $data['birthday'] ?: null,
            ':address' => $data['address'] ?: null,
            ':bank_account' => $data['bank_account'] ?? null,
            ':e_wallet_account' => $data['e_wallet_account'] ?? null,
            ':status' => $this->normalizeStatus($data['status'] ?? 'active'),
            ':avatar' => $data['avatar'] ?: 'assets/images/default-avatar.svg',
        ]);
    }

    public function authenticate(string $identifier, string $password): ?array {
        $customer = $this->findByLogin($identifier);
        if ($customer && password_verify($password, $customer['password']) && !in_array($customer['status'], ['inactive', 'blocked'], true)) {
            return $customer;
        }

        $legacy = $this->findLegacyCustomerByLogin($identifier);
        if (!$legacy) {
            return null;
        }
        if (!password_verify($password, $legacy['password'] ?? '')) {
            return null;
        }
        if (in_array($legacy['status'] ?? 'active', ['inactive', 'blocked'], true)) {
            return null;
        }

        return $this->syncFromLegacyUser($legacy);
    }

    public function findByLogin(string $identifier): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE email = :identifier OR COALESCE(phone, '') = :identifier LIMIT 1");
        $stmt->execute([':identifier' => trim($identifier)]);
        $row = $stmt->fetch();
        if ($row) {
            return $this->normalizeRow($row);
        }

        $legacy = $this->findLegacyCustomerByLogin($identifier);
        return $legacy ? $this->syncFromLegacyUser($legacy) : null;
    }

    public function findByEmail(string $email): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        return $this->normalizeRow($row);
    }

    public function getById(int $customerId): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE customer_id = :id LIMIT 1");
        $stmt->execute([':id' => $customerId]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        return $this->normalizeRow($row);
    }

    public function getAll(array $filters = []): array {
        $sql = "SELECT customer_id, full_name, email, phone, birthday, address, bank_account, e_wallet_account, status, avatar, created_at
                FROM {$this->table} WHERE 1=1";
        $params = [];

        if (!empty($filters['keyword'])) {
            $sql .= " AND (full_name LIKE :keyword OR email LIKE :keyword OR COALESCE(phone, '') LIKE :keyword OR CAST(customer_id AS CHAR) LIKE :keyword)";
            $params[':keyword'] = '%' . trim($filters['keyword']) . '%';
        }
        if (!empty($filters['status'])) {
            $sql .= " AND status = :status";
            $params[':status'] = $filters['status'];
        }

        $sql .= " ORDER BY created_at DESC, customer_id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return array_map(fn(array $row) => $this->normalizeRow($row), $stmt->fetchAll());
    }

    public function getStats(): array {
        $stmt = $this->conn->query("SELECT COUNT(*) AS total,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_count,
            SUM(CASE WHEN status IN ('inactive', 'blocked') THEN 1 ELSE 0 END) AS inactive_count
            FROM {$this->table}");
        $row = $stmt->fetch() ?: [];
        return [
            'total' => (int) ($row['total'] ?? 0),
            'active_count' => (int) ($row['active_count'] ?? 0),
            'inactive_count' => (int) ($row['inactive_count'] ?? 0),
            'leave_count' => 0,
        ];
    }

    public function createByAdmin(array $data): bool {
        $stmt = $this->conn->prepare("INSERT INTO {$this->table}
            (full_name, email, phone, birthday, address, password, bank_account, e_wallet_account, status, avatar)
            VALUES (:full_name, :email, :phone, :birthday, :address, :password, :bank_account, :e_wallet_account, :status, :avatar)");

        return $stmt->execute([
            ':full_name' => $data['full_name'],
            ':email' => $data['email'],
            ':phone' => $data['phone'] ?: null,
            ':birthday' => $data['birthday'] ?: null,
            ':address' => $data['address'] ?: null,
            ':password' => $data['password'],
            ':bank_account' => $data['bank_account'] ?? null,
            ':e_wallet_account' => $data['e_wallet_account'] ?? null,
            ':status' => $this->normalizeStatus($data['status'] ?? 'active'),
            ':avatar' => $data['avatar'] ?: 'assets/images/default-avatar.svg',
        ]);
    }

    public function adminUpdate(int $customerId, array $data): bool {
        $existing = $this->getById($customerId);
        if (!$existing) {
            return false;
        }

        $allowedColumns = ['full_name', 'email', 'phone', 'birthday', 'address', 'status', 'avatar', 'password', 'bank_account', 'e_wallet_account'];
        $fields = [];
        $params = [':id' => $customerId];
        foreach ($data as $key => $value) {
            if (!in_array($key, $allowedColumns, true)) {
                continue;
            }
            if ($key === 'status') {
                $value = $this->normalizeStatus((string) $value);
            }
            $fields[] = "{$key} = :{$key}";
            $params[":{$key}"] = $value;
        }

        if (empty($fields)) {
            return false;
        }

        $stmt = $this->conn->prepare("UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE customer_id = :id");
        return $stmt->execute($params);
    }

    public function countOrders(int $customerId): int {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM orders WHERE customer_id = :id");
        $stmt->execute([':id' => $customerId]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    public function countCancellationRequests(int $customerId): int {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM cancellation_requests WHERE customer_id = :id");
        $stmt->execute([':id' => $customerId]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    public function canDelete(int $customerId): bool {
        return $this->countOrders($customerId) === 0 && $this->countCancellationRequests($customerId) === 0;
    }

    public function delete(int $customerId): bool {
        if (!$this->canDelete($customerId)) {
            return false;
        }
        try {
            $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE customer_id = :id");
            return $stmt->execute([':id' => $customerId]);
        } catch (Throwable $e) {
            return false;
        }
    }

    public function toggleStatus(int $customerId): bool {
        $customer = $this->getById($customerId);
        if (!$customer) {
            return false;
        }
        $newStatus = in_array($customer['status'], ['blocked', 'inactive'], true) ? 'active' : 'blocked';
        return $this->adminUpdate($customerId, ['status' => $newStatus]);
    }

    public function updateProfile(int $customerId, array $data): bool {
        $stmt = $this->conn->prepare("UPDATE {$this->table}
            SET full_name = :full_name,
                email = :email,
                phone = :phone,
                birthday = :birthday,
                address = :address,
                bank_account = :bank_account,
                e_wallet_account = :e_wallet_account
            WHERE customer_id = :id");

        return $stmt->execute([
            ':full_name' => $data['full_name'],
            ':email' => $data['email'],
            ':phone' => $data['phone'] ?: null,
            ':birthday' => $data['birthday'] ?: null,
            ':address' => $data['address'] ?: null,
            ':bank_account' => $data['bank_account'] ?? null,
            ':e_wallet_account' => $data['e_wallet_account'] ?? null,
            ':id' => $customerId,
        ]);
    }

    public function changePassword(int $customerId, string $oldPassword, string $newPassword): bool|array {
        $customer = $this->getById($customerId);
        if (!$customer || !password_verify($oldPassword, $customer['password'])) {
            return false;
        }
        $passwordErrors = $this->validatePassword($newPassword);
        if ($passwordErrors) {
            return $passwordErrors;
        }
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET password = :password WHERE customer_id = :id");
        return $stmt->execute([
            ':password' => password_hash($newPassword, PASSWORD_DEFAULT),
            ':id' => $customerId,
        ]);
    }

    public function findByOAuth(string $provider, string $oauthId): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE oauth_provider = :provider AND oauth_id = :oauth_id LIMIT 1");
        $stmt->execute([':provider' => $provider, ':oauth_id' => $oauthId]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        return $this->normalizeRow($row);
    }

    public function registerOAuth(array $data, string $provider, string $oauthId): bool {
        $stmt = $this->conn->prepare("INSERT INTO {$this->table}
            (full_name, email, password, phone, birthday, address, bank_account, e_wallet_account, status, avatar, oauth_provider, oauth_id)
            VALUES (:full_name, :email, :password, :phone, :birthday, :address, :bank_account, :e_wallet_account, :status, :avatar, :oauth_provider, :oauth_id)");

        return $stmt->execute([
            ':full_name' => $data['full_name'],
            ':email' => $data['email'],
            ':password' => password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT),
            ':phone' => $data['phone'] ?: null,
            ':birthday' => $data['birthday'] ?: null,
            ':address' => $data['address'] ?: null,
            ':bank_account' => null,
            ':e_wallet_account' => null,
            ':status' => 'active',
            ':avatar' => $data['avatar'] ?: 'assets/images/default-avatar.svg',
            ':oauth_provider' => $provider,
            ':oauth_id' => $oauthId,
        ]);
    }

    public function linkOAuthAccount(int $customerId, string $provider, string $oauthId): bool {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET oauth_provider = :provider, oauth_id = :oauth_id WHERE customer_id = :id");
        return $stmt->execute([
            ':provider' => $provider,
            ':oauth_id' => $oauthId,
            ':id' => $customerId,
        ]);
    }
}
