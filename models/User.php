<?php
// Model User - Lớp facade để tương thích với code cũ
// Bên trong ủy thác cho 2 model riêng biệt: Customer và Employee

// Include 2 model con
require_once __DIR__ . '/Customer.php';
require_once __DIR__ . '/Employee.php';

/**
 * Legacy compatibility facade.
 * Kept only so old code paths can continue to work while the application
 * internally uses separated Customer and Employee models.
 */
class User {
    // Khởi tạo 2 model con để ủy thác
    private Customer $customerModel;
    private Employee $employeeModel;

    // Các thuộc tính (properties) của user - ánh xạ từ database
    public ?int $user_id = null;
    public string $full_name = '';
    public string $email = '';
    public string $password = '';
    public ?string $phone = null;
    public ?string $birthday = null;
    public ?string $address = null;
    public ?string $bank_account = null;
    public ?string $e_wallet_account = null;
    public string $role = 'customer';        // 'customer', 'admin', 'staff'
    public string $status = 'active';         // 'active', 'inactive'
    public ?string $avatar = null;
    public ?string $position = null;          // Chức vụ (cho employee)
    public ?string $branch_name = null;       // Chi nhánh (cho employee)
    public ?string $hire_date = null;         // Ngày vào làm (cho employee)

    // Constructor: Khởi tạo 2 model để sử dụng
    public function __construct() {
        $this->customerModel = new Customer();
        $this->employeeModel = new Employee();
    }

    // Hàm xác định scope hiện tại: 'customer' hoặc 'employee'
    // Dựa vào role để quyết định dùng model nào
    private function scope(?string $scope = null): string {
        // Nếu truyền scope cụ thể thì dùng trực tiếp
        if ($scope === 'customer') {
            return 'customer';
        }
        if ($scope === 'employee' || in_array($scope, ['admin', 'staff'], true)) {
            return 'employee';
        }
        // Ngược lại dựa vào role hiện tại
        return in_array($this->role, ['admin', 'staff'], true) ? 'employee' : 'customer';
    }

    // Hàm hydrate: Điền dữ liệu từ mảng database vào các thuộc tính
    // Xử lý cả 3 trường hợp: user_id, customer_id, employee_id
    private function hydrate(array $row): void {
        $this->user_id = (int) ($row['user_id'] ?? ($row['customer_id'] ?? $row['employee_id'] ?? 0));
        $this->full_name = (string) ($row['full_name'] ?? '');
        $this->email = (string) ($row['email'] ?? '');
        $this->phone = $row['phone'] ?? null;
        $this->birthday = $row['birthday'] ?? null;
        $this->address = $row['address'] ?? null;
        $this->bank_account = $row['bank_account'] ?? null;
        $this->e_wallet_account = $row['e_wallet_account'] ?? null;
        $this->role = (string) ($row['role'] ?? 'customer');
        $this->status = (string) ($row['status'] ?? 'active');
        $this->avatar = $row['avatar'] ?? null;
        $this->position = $row['position'] ?? null;
        $this->branch_name = $row['branch_name'] ?? null;
        $this->hire_date = $row['hire_date'] ?? null;
    }

    public function register(): bool {
        return $this->customerModel->register([
            'full_name' => $this->full_name,
            'email' => $this->email,
            'password' => $this->password,
            'phone' => $this->phone,
            'birthday' => $this->birthday,
            'address' => $this->address,
            'bank_account' => $this->bank_account,
            'e_wallet_account' => $this->e_wallet_account,
            'status' => $this->status,
            'avatar' => $this->avatar,
        ]);
    }

    public function login(): bool {
        $customer = $this->customerModel->authenticate($this->email, $this->password);
        if ($customer) {
            $this->hydrate($customer);
            return true;
        }
        $employee = $this->employeeModel->authenticate($this->email, $this->password);
        if ($employee) {
            $this->hydrate($employee);
            return true;
        }
        return false;
    }

    public function update(): bool {
        if ($this->scope() === 'customer') {
            return $this->customerModel->updateProfile($this->user_id ?? 0, [
                'full_name' => $this->full_name,
                'email' => $this->email,
                'phone' => $this->phone,
                'birthday' => $this->birthday,
                'address' => $this->address,
                'bank_account' => $this->bank_account,
                'e_wallet_account' => $this->e_wallet_account,
            ]);
        }
        return $this->employeeModel->updateProfile($this->user_id ?? 0, [
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'birthday' => $this->birthday,
            'address' => $this->address,
        ]);
    }

    public function getUserById(int $userId, ?string $scope = null): ?array {
        return $this->getById($userId, $scope);
    }

    public function getById(int $userId, ?string $scope = null): ?array {
        $scope = $this->scope($scope);
        return $scope === 'customer'
            ? $this->customerModel->getById($userId)
            : $this->employeeModel->getById($userId);
    }

    public function emailExists(string $email, ?int $ignoreId = null, ?string $scope = null): bool {
        $scope = $this->scope($scope);
        if ($scope === 'customer') {
            return $this->customerModel->emailExists($email, $ignoreId) || $this->employeeModel->emailExists($email, null);
        }
        return $this->employeeModel->emailExists($email, $ignoreId) || $this->customerModel->emailExists($email, null);
    }

    public function getAllUsers(string $role = 'customer', array $filters = []): array {
        return $this->scope($role) === 'customer'
            ? $this->customerModel->getAll($filters)
            : $this->employeeModel->getAll($role === 'admin' ? 'admin' : 'staff', $filters);
    }

    public function getStats(string $role = 'customer'): array {
        return $this->scope($role) === 'customer'
            ? $this->customerModel->getStats()
            : $this->employeeModel->getStats($role === 'admin' ? 'admin' : 'staff');
    }

    public function createByAdmin(array $data): bool {
        return $this->scope($data['role'] ?? 'customer') === 'customer'
            ? $this->customerModel->createByAdmin($data)
            : $this->employeeModel->createByAdmin($data);
    }

    public function adminUpdate(int $userId, array $data, ?string $scope = null): bool {
        return $this->scope($scope ?? ($data['role'] ?? 'customer')) === 'customer'
            ? $this->customerModel->adminUpdate($userId, $data)
            : $this->employeeModel->adminUpdate($userId, $data);
    }

    public function delete(int $userId, ?string $scope = null): bool {
        return $this->scope($scope) === 'customer'
            ? $this->customerModel->delete($userId)
            : $this->employeeModel->delete($userId);
    }

    public function toggleStatus(int $userId, ?string $scope = null): bool {
        return $this->scope($scope) === 'customer'
            ? $this->customerModel->toggleStatus($userId)
            : $this->employeeModel->toggleStatus($userId);
    }

    public function changePassword(int $userId, string $oldPassword, string $newPassword, ?string $scope = null): bool|array {
        return $this->scope($scope) === 'customer'
            ? $this->customerModel->changePassword($userId, $oldPassword, $newPassword)
            : $this->employeeModel->changePassword($userId, $oldPassword, $newPassword);
    }

    public function findByEmail(string $email, ?string $scope = null): ?array {
        return $this->scope($scope) === 'customer'
            ? $this->customerModel->findByEmail($email)
            : $this->employeeModel->findByEmail($email);
    }

    public function findByOAuth(string $provider, string $oauthId): ?array {
        return $this->customerModel->findByOAuth($provider, $oauthId);
    }

    public function registerOAuth(array $data, string $provider, string $oauthId): bool {
        return $this->customerModel->registerOAuth($data, $provider, $oauthId);
    }

    public function linkOAuthAccount(int $userId, string $provider, string $oauthId): bool {
        return $this->customerModel->linkOAuthAccount($userId, $provider, $oauthId);
    }
}
