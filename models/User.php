<?php
require_once __DIR__ . '/Customer.php';
require_once __DIR__ . '/Employee.php';

/**
 * Lop trung gian de tuong thich voi code cu.
 * Giu lai de cac luong cu van hoat dong trong khi he thong tach Customer/Employee.
 */
class User {
    private Customer $customerModel;
    private Employee $employeeModel;

    public ?int $user_id = null;
    public string $full_name = '';
    public string $email = '';
    public string $password = '';
    public ?string $phone = null;
    public ?string $birthday = null;
    public ?string $address = null;
    public ?string $bank_account = null;
    public ?string $e_wallet_account = null;
    public string $role = 'customer';
    public string $status = 'active';
    public ?string $avatar = null;
    public ?string $position = null;
    public ?string $branch_name = null;
    public ?string $hire_date = null;

    // Khoi tao model khach hang va nhan vien.
    public function __construct() {
        $this->customerModel = new Customer();
        $this->employeeModel = new Employee();
    }

    // Xac dinh pham vi thao tac (customer/employee) theo role hoac tham so.
    private function scope(?string $scope = null): string {
        if ($scope === 'customer') {
            return 'customer';
        }
        if ($scope === 'employee' || in_array($scope, ['admin', 'staff'], true)) {
            return 'employee';
        }
        return in_array($this->role, ['admin', 'staff'], true) ? 'employee' : 'customer';
    }

    // Nap du lieu nguoi dung vao cac thuoc tinh cua doi tuong.
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

    // Dang ky tai khoan moi (khach hang).
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

    // Dang nhap, uu tien khach hang, fallback sang nhan vien.
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

    // Cap nhat thong tin ca nhan theo pham vi hien tai.
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

    // Alias giu tuong thich voi code cu.
    public function getUserById(int $userId, ?string $scope = null): ?array {
        return $this->getById($userId, $scope);
    }

    // Lay thong tin nguoi dung theo id va pham vi.
    public function getById(int $userId, ?string $scope = null): ?array {
        $scope = $this->scope($scope);
        return $scope === 'customer'
            ? $this->customerModel->getById($userId)
            : $this->employeeModel->getById($userId);
    }

    // Kiem tra email da ton tai trong ca hai bang (co bo qua id).
    public function emailExists(string $email, ?int $ignoreId = null, ?string $scope = null): bool {
        $scope = $this->scope($scope);
        if ($scope === 'customer') {
            return $this->customerModel->emailExists($email, $ignoreId) || $this->employeeModel->emailExists($email, null);
        }
        return $this->employeeModel->emailExists($email, $ignoreId) || $this->customerModel->emailExists($email, null);
    }

    // Lay danh sach nguoi dung theo vai tro va bo loc.
    public function getAllUsers(string $role = 'customer', array $filters = []): array {
        return $this->scope($role) === 'customer'
            ? $this->customerModel->getAll($filters)
            : $this->employeeModel->getAll($role === 'admin' ? 'admin' : 'staff', $filters);
    }

    // Lay thong ke nguoi dung theo vai tro.
    public function getStats(string $role = 'customer'): array {
        return $this->scope($role) === 'customer'
            ? $this->customerModel->getStats()
            : $this->employeeModel->getStats($role === 'admin' ? 'admin' : 'staff');
    }

    // Tao nguoi dung tu phia admin.
    public function createByAdmin(array $data): bool {
        return $this->scope($data['role'] ?? 'customer') === 'customer'
            ? $this->customerModel->createByAdmin($data)
            : $this->employeeModel->createByAdmin($data);
    }

    // Admin cap nhat thong tin nguoi dung.
    public function adminUpdate(int $userId, array $data, ?string $scope = null): bool {
        return $this->scope($scope ?? ($data['role'] ?? 'customer')) === 'customer'
            ? $this->customerModel->adminUpdate($userId, $data)
            : $this->employeeModel->adminUpdate($userId, $data);
    }

    // Xoa nguoi dung theo pham vi.
    public function delete(int $userId, ?string $scope = null): bool {
        return $this->scope($scope) === 'customer'
            ? $this->customerModel->delete($userId)
            : $this->employeeModel->delete($userId);
    }

    // Bat/tat trang thai tai khoan.
    public function toggleStatus(int $userId, ?string $scope = null): bool {
        return $this->scope($scope) === 'customer'
            ? $this->customerModel->toggleStatus($userId)
            : $this->employeeModel->toggleStatus($userId);
    }

    // Doi mat khau theo pham vi.
    public function changePassword(int $userId, string $oldPassword, string $newPassword, ?string $scope = null): bool|array {
        return $this->scope($scope) === 'customer'
            ? $this->customerModel->changePassword($userId, $oldPassword, $newPassword)
            : $this->employeeModel->changePassword($userId, $oldPassword, $newPassword);
    }

    // Tim nguoi dung theo email.
    public function findByEmail(string $email, ?string $scope = null): ?array {
        return $this->scope($scope) === 'customer'
            ? $this->customerModel->findByEmail($email)
            : $this->employeeModel->findByEmail($email);
    }

    // Tim khach hang bang tai khoan OAuth.
    public function findByOAuth(string $provider, string $oauthId): ?array {
        return $this->customerModel->findByOAuth($provider, $oauthId);
    }

    // Dang ky khach hang qua OAuth.
    public function registerOAuth(array $data, string $provider, string $oauthId): bool {
        return $this->customerModel->registerOAuth($data, $provider, $oauthId);
    }

    // Lien ket tai khoan OAuth voi khach hang.
    public function linkOAuthAccount(int $userId, string $provider, string $oauthId): bool {
        return $this->customerModel->linkOAuthAccount($userId, $provider, $oauthId);
    }
}
