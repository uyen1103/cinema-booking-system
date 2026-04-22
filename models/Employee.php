<?php
require_once __DIR__ . '/../config/database.php';

class Employee {
    private PDO $conn;
    private string $table = 'employees';
    private string $legacyTable = 'users';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->ensureSchema();
    }

    private function normalizeRow(array $row): array {
        $row['employee_id'] = (int) ($row['employee_id'] ?? $row['user_id'] ?? 0);
        $row['user_id'] = (int) ($row['employee_id'] ?? 0);
        $row['full_name'] = (string) ($row['full_name'] ?? 'Nhân sự hệ thống');
        $row['email'] = (string) ($row['email'] ?? '');
        $row['phone'] = (string) ($row['phone'] ?? '');
        $row['birthday'] = $row['birthday'] ?? null;
        $row['address'] = (string) ($row['address'] ?? '');
        $row['avatar'] = (string) ($row['avatar'] ?? 'assets/images/default-avatar.svg');
        $row['position'] = (string) ($row['position'] ?? 'Nhân viên');
        $row['branch_name'] = (string) ($row['branch_name'] ?? '');
        $row['hire_date'] = $row['hire_date'] ?? null;
        $row['role'] = in_array(($row['role'] ?? 'staff'), ['admin', 'staff'], true) ? $row['role'] : 'staff';
        $row['status'] = $this->normalizeStatus($row['status'] ?? 'working');
        $row['account_scope'] = 'employee';
        return $row;
    }

    private function normalizeStatus(?string $status): string {
        return in_array($status, ['working', 'leave', 'inactive', 'blocked', 'resigned', 'active'], true)
            ? $status
            : 'working';
    }

    private function tableExists(string $table): bool {
        try {
            $stmt = $this->conn->query("SHOW TABLES LIKE '{$table}'");
            return (bool) $stmt->fetchColumn();
        } catch (Throwable $e) {
            return false;
        }
    }

    private function hasColumn(string $table, string $column): bool {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name AND COLUMN_NAME = :column_name");
            $stmt->execute([':table_name' => $table, ':column_name' => $column]);
            return (int) $stmt->fetchColumn() > 0;
        } catch (Throwable $e) {
            return false;
        }
    }

    private function addColumnIfMissing(string $table, string $column, string $definition): void {
        if (!$this->hasColumn($table, $column)) {
            $this->conn->exec("ALTER TABLE {$table} ADD COLUMN {$column} {$definition}");
        }
    }

    private function ensureLegacyTable(): void {
        if (!$this->tableExists($this->legacyTable)) {
            $this->conn->exec("CREATE TABLE {$this->legacyTable} (
                user_id INT AUTO_INCREMENT PRIMARY KEY,
                full_name VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                phone VARCHAR(20) NULL,
                birthday DATE NULL,
                address VARCHAR(255) NULL,
                bank_account VARCHAR(100) NULL,
                e_wallet_account VARCHAR(100) NULL,
                role VARCHAR(20) NOT NULL DEFAULT 'customer',
                position VARCHAR(100) NULL,
                branch_name VARCHAR(150) NULL,
                hire_date DATE NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'active',
                avatar VARCHAR(255) NULL,
                oauth_provider VARCHAR(50) NULL,
                oauth_id VARCHAR(100) NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_users_role (role),
                INDEX idx_users_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }

        $this->addColumnIfMissing($this->legacyTable, 'phone', 'VARCHAR(20) NULL');
        $this->addColumnIfMissing($this->legacyTable, 'birthday', 'DATE NULL');
        $this->addColumnIfMissing($this->legacyTable, 'address', 'VARCHAR(255) NULL');
        $this->addColumnIfMissing($this->legacyTable, 'position', 'VARCHAR(100) NULL');
        $this->addColumnIfMissing($this->legacyTable, 'branch_name', 'VARCHAR(150) NULL');
        $this->addColumnIfMissing($this->legacyTable, 'hire_date', 'DATE NULL');
        $this->addColumnIfMissing($this->legacyTable, 'status', "VARCHAR(20) NOT NULL DEFAULT 'active'");
        $this->addColumnIfMissing($this->legacyTable, 'avatar', 'VARCHAR(255) NULL');
        $this->addColumnIfMissing($this->legacyTable, 'created_at', 'TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP');
        $this->addColumnIfMissing($this->legacyTable, 'updated_at', 'TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    }

    private function ensureSchema(): void {
        if (!$this->tableExists($this->table)) {
            $this->conn->exec("CREATE TABLE {$this->table} (
                employee_id INT AUTO_INCREMENT PRIMARY KEY,
                full_name VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                phone VARCHAR(20) NULL,
                birthday DATE NULL,
                address VARCHAR(255) NULL,
                avatar VARCHAR(255) NULL,
                position VARCHAR(100) NULL,
                branch_name VARCHAR(150) NULL,
                hire_date DATE NULL,
                role VARCHAR(20) NOT NULL DEFAULT 'staff',
                status VARCHAR(20) NOT NULL DEFAULT 'working',
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_employees_role (role),
                INDEX idx_employees_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }

        $this->addColumnIfMissing($this->table, 'phone', 'VARCHAR(20) NULL');
        $this->addColumnIfMissing($this->table, 'birthday', 'DATE NULL');
        $this->addColumnIfMissing($this->table, 'address', 'VARCHAR(255) NULL');
        $this->addColumnIfMissing($this->table, 'avatar', 'VARCHAR(255) NULL');
        $this->addColumnIfMissing($this->table, 'position', 'VARCHAR(100) NULL');
        $this->addColumnIfMissing($this->table, 'branch_name', 'VARCHAR(150) NULL');
        $this->addColumnIfMissing($this->table, 'hire_date', 'DATE NULL');
        $this->addColumnIfMissing($this->table, 'role', "VARCHAR(20) NOT NULL DEFAULT 'staff'");
        $this->addColumnIfMissing($this->table, 'status', "VARCHAR(20) NOT NULL DEFAULT 'working'");
        $this->addColumnIfMissing($this->table, 'created_at', 'TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP');
        $this->addColumnIfMissing($this->table, 'updated_at', 'TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

        $this->ensureLegacyTable();
        $this->bootstrapFromLegacyEmployees();
    }

    private function bootstrapFromLegacyEmployees(): void {
        if (!$this->tableExists($this->legacyTable)) {
            return;
        }

        try {
            $this->conn->exec("INSERT INTO {$this->table}
                (full_name, email, password, phone, birthday, address, avatar, position, branch_name, hire_date, role, status)
                SELECT u.full_name, u.email, u.password, u.phone, u.birthday, u.address, u.avatar,
                       COALESCE(NULLIF(u.position, ''), 'Nhân viên'),
                       COALESCE(NULLIF(u.branch_name, ''), 'Cinema Central'),
                       u.hire_date,
                       CASE WHEN u.role = 'admin' THEN 'admin' ELSE 'staff' END,
                       CASE WHEN u.status IN ('inactive', 'blocked', 'resigned', 'leave') THEN u.status ELSE 'working' END
                FROM {$this->legacyTable} u
                LEFT JOIN {$this->table} e ON e.email = u.email
                WHERE u.role IN ('admin', 'staff') AND e.employee_id IS NULL");
        } catch (Throwable $e) {
        }
    }

    private function mirrorToLegacyUser(array $employee): void {
        $this->ensureLegacyTable();
        $email = trim((string) ($employee['email'] ?? ''));
        if ($email === '') {
            return;
        }

        $stmt = $this->conn->prepare("SELECT user_id FROM {$this->legacyTable} WHERE email = :email AND role IN ('admin', 'staff') LIMIT 1");
        $stmt->execute([':email' => $email]);
        $legacyId = $stmt->fetchColumn();

        $params = [
            ':full_name' => $employee['full_name'] ?? 'Nhân sự hệ thống',
            ':email' => $email,
            ':password' => $employee['password'] ?? '',
            ':phone' => $employee['phone'] ?: null,
            ':birthday' => $employee['birthday'] ?: null,
            ':address' => $employee['address'] ?: null,
            ':position' => $employee['position'] ?: 'Nhân viên',
            ':branch_name' => $employee['branch_name'] ?: 'Cinema Central',
            ':hire_date' => $employee['hire_date'] ?: null,
            ':role' => in_array(($employee['role'] ?? 'staff'), ['admin', 'staff'], true) ? $employee['role'] : 'staff',
            ':status' => $employee['status'] ?? 'working',
            ':avatar' => $employee['avatar'] ?: 'assets/images/default-avatar.svg',
        ];

        if ($legacyId) {
            $sql = "UPDATE {$this->legacyTable}
                    SET full_name = :full_name,
                        email = :email,
                        password = :password,
                        phone = :phone,
                        birthday = :birthday,
                        address = :address,
                        position = :position,
                        branch_name = :branch_name,
                        hire_date = :hire_date,
                        role = :role,
                        status = :status,
                        avatar = :avatar
                    WHERE user_id = :user_id";
            $params[':user_id'] = $legacyId;
            $this->conn->prepare($sql)->execute($params);
            return;
        }

        $sql = "INSERT INTO {$this->legacyTable}
                (full_name, email, password, phone, birthday, address, position, branch_name, hire_date, role, status, avatar)
                VALUES (:full_name, :email, :password, :phone, :birthday, :address, :position, :branch_name, :hire_date, :role, :status, :avatar)";
        $this->conn->prepare($sql)->execute($params);
    }

    private function deleteLegacyUser(string $email): void {
        if (!$this->tableExists($this->legacyTable) || trim($email) === '') {
            return;
        }
        $stmt = $this->conn->prepare("DELETE FROM {$this->legacyTable} WHERE email = :email AND role IN ('admin', 'staff')");
        $stmt->execute([':email' => trim($email)]);
    }

    private function findLegacyEmployeeByLogin(string $identifier): ?array {
        if (!$this->tableExists($this->legacyTable)) {
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

        $employee = $this->findByEmail((string) ($legacy['email'] ?? ''));
        if ($employee) {
            $this->mirrorToLegacyUser($employee);
        }
        return $employee;
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
        $this->bootstrapFromLegacyEmployees();

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
        $this->bootstrapFromLegacyEmployees();

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
        $ok = $stmt->execute([
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
        if ($ok) {
            $employee = $this->findByEmail((string) $data['email']);
            if ($employee) {
                $this->mirrorToLegacyUser($employee);
            }
        }
        return $ok;
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
        $ok = $stmt->execute($params);
        if ($ok) {
            $employee = $this->getById($employeeId);
            if ($employee) {
                $employee['password'] = $data['password'] ?? ($existing['password'] ?? '');
                $this->mirrorToLegacyUser($employee);
            }
        }
        return $ok;
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
        $existing = $this->getById($employeeId);
        try {
            $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE employee_id = :id");
            $ok = $stmt->execute([':id' => $employeeId]);
            if ($ok && $existing) {
                $this->deleteLegacyUser((string) ($existing['email'] ?? ''));
            }
            return $ok;
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

        $ok = $stmt->execute([
            ':full_name' => $data['full_name'],
            ':email' => $data['email'],
            ':phone' => $data['phone'] ?: null,
            ':birthday' => $data['birthday'] ?: null,
            ':address' => $data['address'] ?: null,
            ':id' => $employeeId,
        ]);
        if ($ok) {
            $employee = $this->getById($employeeId);
            if ($employee) {
                $this->mirrorToLegacyUser($employee);
            }
        }
        return $ok;
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
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET password = :password WHERE employee_id = :id");
        $ok = $stmt->execute([
            ':password' => $hashed,
            ':id' => $employeeId,
        ]);
        if ($ok) {
            $employee['password'] = $hashed;
            $this->mirrorToLegacyUser($employee);
        }
        return $ok;
    }
}
