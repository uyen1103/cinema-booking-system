<?php
require_once __DIR__ . '/../models/Customer.php';
require_once __DIR__ . '/../models/Employee.php';

class UserController {
    private Customer $customerModel;
    private Employee $employeeModel;
    private string $defaultAvatar = 'assets/images/default-avatar.svg';

    public function __construct() {
        $this->customerModel = new Customer();
        $this->employeeModel = new Employee();
    }

    private function renderAdmin(string $viewPath, array $data = []): void {
        extract($data);
        ob_start();
        include __DIR__ . "/../views/admin/users/{$viewPath}.php";
        $content = ob_get_clean();
        include __DIR__ . '/../views/layouts/admin_layout.php';
    }

    private function redirect(string $url): void {
        header("Location: {$url}");
        exit;
    }

    private function resolveScope(?string $roleOrScope): string {
        return $roleOrScope === 'customer' ? 'customer' : 'employee';
    }

    private function emailExistsGlobally(string $email, ?int $ignoreId = null, string $scope = 'customer'): bool {
        if ($scope === 'customer') {
            return $this->customerModel->emailExists($email, $ignoreId) || $this->employeeModel->emailExists($email, null);
        }
        return $this->employeeModel->emailExists($email, $ignoreId) || $this->customerModel->emailExists($email, null);
    }

    public function indexEmployee(): void {
        $filters = [
            'keyword' => trim($_GET['keyword'] ?? ''),
            'status' => $_GET['status'] ?? '',
            'position' => $_GET['position'] ?? '',
        ];

        $this->renderAdmin('index', [
            'users' => $this->employeeModel->getAll('staff', $filters),
            'stats' => $this->employeeModel->getStats('staff'),
            'filters' => $filters,
            'userRole' => 'staff',
            'positions' => $this->employeeModel->getPositionOptions(),
            'branches' => $this->employeeModel->getBranchOptions(),
            'activeMenu' => 'employee',
            'breadcrumb' => 'Quản lý nhân viên',
            'pageTitle' => 'Quản lý nhân viên'
        ]);
    }

    public function indexCustomer(): void {
        $filters = [
            'keyword' => trim($_GET['keyword'] ?? ''),
            'status' => $_GET['status'] ?? '',
            'position' => '',
        ];

        $this->renderAdmin('index', [
            'users' => $this->customerModel->getAll($filters),
            'stats' => $this->customerModel->getStats(),
            'filters' => $filters,
            'userRole' => 'customer',
            'positions' => [],
            'branches' => [],
            'activeMenu' => 'customer',
            'breadcrumb' => 'Quản lý khách hàng',
            'pageTitle' => 'Quản lý khách hàng'
        ]);
    }

    public function create(string $role): void {
        $this->renderAdmin('create', [
            'userRole' => $role,
            'positions' => $this->employeeModel->getPositionOptions(),
            'branches' => $this->employeeModel->getBranchOptions(),
            'activeMenu' => $role === 'staff' ? 'employee' : 'customer',
            'breadcrumb' => $role === 'staff' ? 'Thêm nhân viên mới' : 'Thêm khách hàng mới',
            'pageTitle' => $role === 'staff' ? 'Thêm nhân viên mới' : 'Thêm khách hàng mới',
        ]);
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('admin_employees'));
        }

        $role = $_POST['role'] ?? 'customer';
        if (!in_array($role, ['customer', 'staff', 'admin'], true)) {
            $role = 'customer';
        }
        if ($role === 'admin') {
            $role = 'staff';
        }
        $scope = $this->resolveScope($role);
        $redirect = $role === 'staff' ? admin_url('admin_employees') : admin_url('admin_customers');

        $email = trim($_POST['email'] ?? '');
        if ($this->emailExistsGlobally($email, null, $scope)) {
            set_flash('danger', 'Email đã tồn tại trong hệ thống.');
            $this->redirect($redirect);
        }

        $password = $_POST['password'] ?? '';
        $validator = $scope === 'customer' ? $this->customerModel : $this->employeeModel;
        $passwordErrors = $validator->validatePassword($password);
        if ($passwordErrors) {
            set_flash('danger', implode(' ', $passwordErrors));
            $this->redirect($role === 'staff' ? admin_url('admin_create_employee') : admin_url('admin_create_customer'));
        }

        $avatar = upload_file($_FILES['avatar'] ?? [], 'assets/uploads/avatars', ['jpg', 'jpeg', 'png', 'webp', 'svg'], 'avatar') ?: $this->defaultAvatar;

        $data = [
            'full_name' => trim($_POST['full_name'] ?? ''),
            'email' => $email,
            'phone' => trim($_POST['phone'] ?? ''),
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
            'position' => trim($_POST['position'] ?? ''),
            'birthday' => $_POST['birthday'] ?? null,
            'address' => trim($_POST['address'] ?? ''),
            'branch_name' => trim($_POST['branch_name'] ?? ''),
            'hire_date' => $_POST['hire_date'] ?? null,
            'status' => $_POST['status'] ?? ($role === 'staff' ? 'working' : 'active'),
            'avatar' => $avatar,
        ];

        $saved = $scope === 'customer'
            ? $this->customerModel->createByAdmin($data)
            : $this->employeeModel->createByAdmin($data);

        if ($saved) {
            set_flash('success', $role === 'staff' ? 'Thêm nhân viên thành công.' : 'Thêm khách hàng thành công.');
        } else {
            set_flash('danger', 'Không thể lưu người dùng mới.');
        }

        $this->redirect($redirect);
    }

    public function edit(int $id): void {
        $scope = isset($_GET['action']) && str_contains((string) $_GET['action'], 'customer') ? 'customer' : 'employee';
        $user = $scope === 'customer' ? $this->customerModel->getById($id) : $this->employeeModel->getById($id);
        if (!$user) {
            set_flash('danger', 'Không tìm thấy người dùng cần chỉnh sửa.');
            $this->redirect($scope === 'customer' ? admin_url('admin_customers') : admin_url('admin_employees'));
        }

        $this->renderAdmin('edit', [
            'user' => $user,
            'userRole' => $user['role'],
            'positions' => $this->employeeModel->getPositionOptions(),
            'branches' => $this->employeeModel->getBranchOptions(),
            'activeMenu' => $user['role'] === 'staff' ? 'employee' : 'customer',
            'breadcrumb' => $user['role'] === 'staff' ? 'Chỉnh sửa nhân viên' : 'Chỉnh sửa khách hàng',
            'pageTitle' => $user['role'] === 'staff' ? 'Chỉnh sửa nhân viên' : 'Chỉnh sửa khách hàng',
        ]);
    }

    public function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('admin_employees'));
        }

        $id = (int) ($_POST['user_id'] ?? 0);
        $scope = $this->resolveScope($_POST['role'] ?? ($_GET['role'] ?? 'customer'));
        $existingUser = $scope === 'customer' ? $this->customerModel->getById($id) : $this->employeeModel->getById($id);

        if (!$existingUser) {
            set_flash('danger', 'Người dùng không tồn tại.');
            $this->redirect(admin_url('admin_employees'));
        }

        $role = $existingUser['role'];
        $redirect = $role === 'staff' ? admin_url('admin_employees') : admin_url('admin_customers');
        $email = trim($_POST['email'] ?? '');

        if ($this->emailExistsGlobally($email, $id, $scope)) {
            set_flash('danger', 'Email đã được sử dụng bởi tài khoản khác.');
            $this->redirect($redirect);
        }

        $data = [
            'full_name' => trim($_POST['full_name'] ?? ''),
            'email' => $email,
            'phone' => trim($_POST['phone'] ?? ''),
            'birthday' => $_POST['birthday'] ?: null,
            'address' => trim($_POST['address'] ?? ''),
            'position' => trim($_POST['position'] ?? ''),
            'branch_name' => trim($_POST['branch_name'] ?? ''),
            'hire_date' => $_POST['hire_date'] ?: null,
            'status' => $_POST['status'] ?? $existingUser['status'],
        ];

        if (!empty($_POST['password'])) {
            $validator = $scope === 'customer' ? $this->customerModel : $this->employeeModel;
            $passwordErrors = $validator->validatePassword($_POST['password']);
            if ($passwordErrors) {
                set_flash('danger', implode(' ', $passwordErrors));
                $this->redirect($redirect);
            }
            $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        $newAvatar = upload_file($_FILES['avatar'] ?? [], 'assets/uploads/avatars', ['jpg', 'jpeg', 'png', 'webp', 'svg'], 'avatar');
        if ($newAvatar) {
            $data['avatar'] = $newAvatar;
        }

        $updated = $scope === 'customer'
            ? $this->customerModel->adminUpdate($id, $data)
            : $this->employeeModel->adminUpdate($id, $data);

        if ($updated) {
            if ($newAvatar) {
                delete_local_file($existingUser['avatar'] ?? null, [$this->defaultAvatar]);
            }
            set_flash('success', 'Cập nhật thông tin thành công.');
        } else {
            if ($newAvatar) {
                delete_local_file($newAvatar);
            }
            set_flash('danger', 'Không thể cập nhật người dùng.');
        }

        $this->redirect($redirect);
    }

    public function delete(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('admin_employees'));
        }

        $id = (int) ($_POST['user_id'] ?? 0);
        $scope = $this->resolveScope($_POST['role'] ?? 'customer');
        if ($scope === 'employee' && currentEmployeeId() === $id) {
            set_flash('danger', 'Không thể xóa tài khoản quản trị đang đăng nhập.');
            $this->redirect(admin_url('admin_employees'));
        }
        $user = $scope === 'customer' ? $this->customerModel->getById($id) : $this->employeeModel->getById($id);
        if (!$user) {
            set_flash('danger', 'Không tìm thấy người dùng để xóa.');
            $this->redirect(admin_url('admin_employees'));
        }

        $canDelete = $scope === 'customer' ? $this->customerModel->canDelete($id) : $this->employeeModel->canDelete($id);
        if (!$canDelete) {
            set_flash('danger', $scope === 'customer'
                ? 'Không thể xóa khách hàng đã phát sinh đơn vé hoặc yêu cầu hủy.'
                : 'Không thể xóa nhân sự đã tham gia xử lý nghiệp vụ trong hệ thống.');
            $this->redirect($user['role'] === 'staff' ? admin_url('admin_employees') : admin_url('admin_customers'));
        }

        $deleted = $scope === 'customer' ? $this->customerModel->delete($id) : $this->employeeModel->delete($id);

        if ($deleted) {
            delete_local_file($user['avatar'] ?? null, [$this->defaultAvatar]);
            set_flash('success', 'Đã xóa người dùng khỏi hệ thống.');
        } else {
            set_flash('danger', 'Không thể xóa người dùng.');
        }

        $this->redirect($user['role'] === 'staff' ? admin_url('admin_employees') : admin_url('admin_customers'));
    }

    public function toggleStatus(): void {
        $id = (int) ($_GET['id'] ?? 0);
        $role = $_GET['role'] ?? 'customer';
        $scope = $this->resolveScope($role);

        if ($scope === 'employee' && currentEmployeeId() === $id) {
            set_flash('danger', 'Không thể tự khóa tài khoản quản trị đang đăng nhập.');
            $this->redirect(admin_url('admin_employees'));
        }

        $success = $scope === 'customer'
            ? $this->customerModel->toggleStatus($id)
            : $this->employeeModel->toggleStatus($id);

        if ($id > 0 && $success) {
            set_flash('success', 'Đã cập nhật trạng thái tài khoản.');
        } else {
            set_flash('danger', 'Không thể cập nhật trạng thái.');
        }

        $this->redirect($role === 'staff' ? admin_url('admin_employees') : admin_url('admin_customers'));
    }
}
