<?php
require_once __DIR__ . '/../config/database.php';

class Employee {
    private PDO $conn;
    private string $table = 'employees';
    private string $legacyTable = 'users';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    private function normalizeRow(array $row): array {
        $row['user_id'] = (int) ($row['employee_id'] ?? 0);
        $row['account_scope'] = 'employee';
        return $row;
    }

    private function normalizeStatus(?string $status): string {
        return in_array($status, ['working', 'leave', 'inactive', 'blocked', 'resigned', 'active'], true)
            ? $status
            : 'working';
    }

    private function legacyTableExists(): bool {
        try {
            $stmt = $this->conn->query("SHOW TABLES LIKE '{$this->legacyTable}'");
            return (bool) $stmt->fetchColumn();
        } catch (Throwable $e) {
            return false;
        }
    }

    private function findLegacyEmployeeByLogin(string $identifier): ?array {
        if (!$this->legacyTableExists()) {
            return null;
        }

        $stmt = $this->conn->prepare("SELECT * FROM {$this->legacyTable} WHERE role IN ('admin', 'staff') AND (email = :identifier OR COALESCE(phone, '') = :identifier) LIMIT 1");
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
            (full_name, email, password, phone, birthday, address, avatar, position, branch_name, hire_date, role, status)
            VALUES (:full_name, :email, :password, :phone, :birthday, :address, :avatar, :position, :branch_name, :hire_date, :role, :status)");
        $stmt->execute([
            ':full_name' => $legacy['full_name'] ?? 'Nhân sự hệ thống',
            ':email' => $legacy['email'] ?? '',
            ':password' => $legacy['password'] ?? '',
            ':phone' => $legacy['phone'] ?? null,
            ':birthday' => $legacy['birthday'] ?? null,
            ':address' => $legacy['address'] ?? null,
            ':avatar' => $legacy['avatar'] ?? 'assets/images/default-avatar.svg',
            ':position' => $legacy['position'] ?? 'Nhân viên',
            ':branch_name' => $legacy['branch_name'] ?? 'Cinema Central',
            ':hire_date' => $legacy['hire_date'] ?? date('Y-m-d'),
            ':role' => in_array($legacy['role'] ?? 'staff', ['admin', 'staff'], true) ? $legacy['role'] : 'staff',
            ':status' => $this->normalizeStatus($legacy['status'] ?? 'working'),
        ]);

        return $this->findByEmail((string) ($legacy['email'] ?? ''));
    }

    public function getPositionOptions(): array {
        return ['Quản lý', 'Nhân viên', 'Nhân viên bán vé', 'Kỹ thuật viên', 'Giám sát ca', 'CSKH'];
    }

    public function getBranchOptions(): array {
        return ['Cinema Central Lê Lợi', 'Cinema Central Landmark', 'Cinema Central Phú Mỹ Hưng', 'Cinema Central Đà Nẵng'];
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
        $sql = "SELECT employee_id FROM {$this->table} WHERE email = :email";
        $params = [':email' => $email];
        if ($ignoreId !== null) {
            $sql .= ' AND employee_id <> :ignore_id';
            $params[':ignore_id'] = $ignoreId;
        }
        $sql .= ' LIMIT 1';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetch();
    }

    public function authenticate(string $identifier, string $password): ?array {
        $employee = $this->findByLogin($identifier);
        if ($employee && password_verify($password, $employee['password']) && !in_array($employee['status'], ['inactive', 'blocked', 'resigned'], true)) {
            return $employee;
        }

        $legacy = $this->findLegacyEmployeeByLogin($identifier);
        if (!$legacy) {
            return null;
        }
        if (!password_verify($password, $legacy['password'] ?? '')) {
            return null;
        }
        if (in_array($legacy['status'] ?? 'working', ['inactive', 'blocked', 'resigned'], true)) {
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

        $legacy = $this->findLegacyEmployeeByLogin($identifier);
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

    public function getById(int $employeeId): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE employee_id = :id LIMIT 1");
        $stmt->execute([':id' => $employeeId]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        return $this->normalizeRow($row);
    }

    public function getAll(string $role = 'staff', array $filters = []): array {
        $sql = "SELECT employee_id, full_name, email, phone, birthday, address, position, branch_name, hire_date, role, status, avatar, created_at
                FROM {$this->table} WHERE role = :role";
        $params = [':role' => $role];

        if (!empty($filters['keyword'])) {
            $sql .= " AND (full_name LIKE :keyword OR email LIKE :keyword OR COALESCE(phone, '') LIKE :keyword OR CAST(employee_id AS CHAR) LIKE :keyword)";
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

        $sql .= " ORDER BY created_at DESC, employee_id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return array_map(fn(array $row) => $this->normalizeRow($row), $stmt->fetchAll());
    }

    public function getStats(string $role = 'staff'): array {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total,
            SUM(CASE WHEN status IN ('working', 'active') THEN 1 ELSE 0 END) AS active_count,
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

    public function createByAdmin(array $data): bool {
        $stmt = $this->conn->prepare("INSERT INTO {$this->table}
            (full_name, email, phone, birthday, address, password, position, branch_name, hire_date, role, status, avatar)
            VALUES (:full_name, :email, :phone, :birthday, :address, :password, :position, :branch_name, :hire_date, :role, :status, :avatar)");
        return $stmt->execute([
            ':full_name' => $data['full_name'],
            ':email' => $data['email'],
            ':phone' => $data['phone'] ?: null,
            ':birthday' => $data['birthday'] ?: null,
            ':address' => $data['address'] ?: null,
            ':password' => $data['password'],
            ':position' => $data['position'] ?: null,
            ':branch_name' => $data['branch_name'] ?: null,
            ':hire_date' => $data['hire_date'] ?: null,
            ':role' => in_array(($data['role'] ?? 'staff'), ['admin', 'staff'], true) ? $data['role'] : 'staff',
            ':status' => $this->normalizeStatus($data['status'] ?? 'working'),
            ':avatar' => $data['avatar'] ?: 'assets/images/default-avatar.svg',
        ]);
    }

    public function adminUpdate(int $employeeId, array $data): bool {
        $existing = $this->getById($employeeId);
        if (!$existing) {
            return false;
        }
        $allowedColumns = ['full_name', 'email', 'phone', 'birthday', 'address', 'position', 'branch_name', 'hire_date', 'status', 'avatar', 'password', 'role'];
        $fields = [];
        $params = [':id' => $employeeId];
        foreach ($data as $key => $value) {
            if (!in_array($key, $allowedColumns, true)) {
                continue;
            }
            if ($key === 'status') {
                $value = $this->normalizeStatus((string) $value);
            }
            if ($key === 'role' && !in_array($value, ['admin', 'staff'], true)) {
                $value = 'staff';
            }
            $fields[] = "{$key} = :{$key}";
            $params[":{$key}"] = $value;
        }
        if (empty($fields)) {
            return false;
        }
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE employee_id = :id");
        return $stmt->execute($params);
    }

    public function countCreatedOrders(int $employeeId): int {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM orders WHERE created_by_employee_id = :id OR updated_by_employee_id = :id");
        $stmt->execute([':id' => $employeeId]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    public function countProcessedCancellationRequests(int $employeeId): int {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM cancellation_requests WHERE processed_by_employee_id = :id");
        $stmt->execute([':id' => $employeeId]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    public function countReports(int $employeeId): int {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM reports WHERE employee_id = :id");
        $stmt->execute([':id' => $employeeId]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    public function canDelete(int $employeeId): bool {
        return $this->countCreatedOrders($employeeId) === 0
            && $this->countProcessedCancellationRequests($employeeId) === 0
            && $this->countReports($employeeId) === 0;
    }

    public function delete(int $employeeId): bool {
        if (!$this->canDelete($employeeId)) {
            return false;
        }
        try {
            $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE employee_id = :id");
            return $stmt->execute([':id' => $employeeId]);
        } catch (Throwable $e) {
            return false;
        }
    }

    public function toggleStatus(int $employeeId): bool {
        $employee = $this->getById($employeeId);
        if (!$employee) {
            return false;
        }
        $newStatus = in_array($employee['status'], ['working', 'active'], true) ? 'inactive' : 'working';
        return $this->adminUpdate($employeeId, ['status' => $newStatus]);
    }

    public function updateProfile(int $employeeId, array $data): bool {
        $stmt = $this->conn->prepare("UPDATE {$this->table}
            SET full_name = :full_name,
                email = :email,
                phone = :phone,
                birthday = :birthday,
                address = :address
            WHERE employee_id = :id");

        return $stmt->execute([
            ':full_name' => $data['full_name'],
            ':email' => $data['email'],
            ':phone' => $data['phone'] ?: null,
            ':birthday' => $data['birthday'] ?: null,
            ':address' => $data['address'] ?: null,
            ':id' => $employeeId,
        ]);
    }

    public function changePassword(int $employeeId, string $oldPassword, string $newPassword): bool|array {
        $employee = $this->getById($employeeId);
        if (!$employee || !password_verify($oldPassword, $employee['password'])) {
            return false;
        }
        $passwordErrors = $this->validatePassword($newPassword);
        if ($passwordErrors) {
            return $passwordErrors;
        }
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET password = :password WHERE employee_id = :id");
        return $stmt->execute([
            ':password' => password_hash($newPassword, PASSWORD_DEFAULT),
            ':id' => $employeeId,
        ]);
    }
}
