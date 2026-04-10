<?php
require_once __DIR__ . '/../models/User.php';

class UserController {
    private User $userModel;
    private string $defaultAvatar = 'assets/images/default-avatar.svg';

    public function __construct() {
        $this->userModel = new User();
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

    public function indexEmployee(): void {
        $filters = [
            'keyword' => trim($_GET['keyword'] ?? ''),
            'status' => $_GET['status'] ?? '',
            'position' => $_GET['position'] ?? '',
        ];

        $this->renderAdmin('index', [
            'users' => $this->userModel->getAllByRole('staff', $filters),
            'stats' => $this->userModel->getStatsByRole('staff'),
            'filters' => $filters,
            'userRole' => 'staff',
            'positions' => $this->userModel->getPositionOptions(),
            'branches' => $this->userModel->getBranchOptions(),
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
            'users' => $this->userModel->getAllByRole('customer', $filters),
            'stats' => $this->userModel->getStatsByRole('customer'),
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
            'positions' => $this->userModel->getPositionOptions(),
            'branches' => $this->userModel->getBranchOptions(),
            'activeMenu' => $role === 'staff' ? 'employee' : 'customer',
            'breadcrumb' => $role === 'staff' ? 'Thêm nhân viên mới' : 'Thêm khách hàng mới',
            'pageTitle' => $role === 'staff' ? 'Thêm nhân viên mới' : 'Thêm khách hàng mới',
        ]);
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?action=employees');
        }

        $role = $_POST['role'] ?? 'customer';
        $redirect = $role === 'staff' ? '?action=employees' : '?action=customers';

        $email = trim($_POST['email'] ?? '');
        if ($this->userModel->emailExists($email)) {
            set_flash('danger', 'Email đã tồn tại trong hệ thống.');
            $this->redirect($redirect);
        }

        $password = $_POST['password'] ?? '';
        $passwordErrors = $this->userModel->validatePassword($password);
        if ($passwordErrors) {
            set_flash('danger', implode(' ', $passwordErrors));
            $this->redirect($role === 'staff' ? '?action=create_employee' : '?action=create_customer');
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

        if ($this->userModel->create($data)) {
            set_flash('success', $role === 'staff' ? 'Thêm nhân viên thành công.' : 'Thêm khách hàng thành công.');
        } else {
            set_flash('danger', 'Không thể lưu người dùng mới.');
        }

        $this->redirect($redirect);
    }

    public function edit(int $id): void {
        $user = $this->userModel->getById($id);
        if (!$user) {
            set_flash('danger', 'Không tìm thấy người dùng cần chỉnh sửa.');
            $this->redirect('?action=employees');
        }

        $this->renderAdmin('edit', [
            'user' => $user,
            'userRole' => $user['role'],
            'positions' => $this->userModel->getPositionOptions(),
            'branches' => $this->userModel->getBranchOptions(),
            'activeMenu' => $user['role'] === 'staff' ? 'employee' : 'customer',
            'breadcrumb' => $user['role'] === 'staff' ? 'Chỉnh sửa nhân viên' : 'Chỉnh sửa khách hàng',
            'pageTitle' => $user['role'] === 'staff' ? 'Chỉnh sửa nhân viên' : 'Chỉnh sửa khách hàng',
        ]);
    }

    public function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?action=employees');
        }

        $id = (int) ($_POST['user_id'] ?? 0);
        $existingUser = $this->userModel->getById($id);

        if (!$existingUser) {
            set_flash('danger', 'Người dùng không tồn tại.');
            $this->redirect('?action=employees');
        }

        $role = $existingUser['role'];
        $redirect = $role === 'staff' ? '?action=employees' : '?action=customers';
        $email = trim($_POST['email'] ?? '');

        if ($this->userModel->emailExists($email, $id)) {
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
            $passwordErrors = $this->userModel->validatePassword($_POST['password']);
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

        if ($this->userModel->adminUpdate($id, $data)) {
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
            $this->redirect('?action=employees');
        }

        $id = (int) ($_POST['user_id'] ?? 0);
        $user = $this->userModel->getById($id);
        if (!$user) {
            set_flash('danger', 'Không tìm thấy người dùng để xóa.');
            $this->redirect('?action=employees');
        }

        if ($this->userModel->delete($id)) {
            delete_local_file($user['avatar'] ?? null, [$this->defaultAvatar]);
            set_flash('success', 'Đã xóa người dùng khỏi hệ thống.');
        } else {
            set_flash('danger', 'Không thể xóa người dùng.');
        }

        $this->redirect($user['role'] === 'staff' ? '?action=employees' : '?action=customers');
    }

    public function toggleStatus(): void {
        $id = (int) ($_GET['id'] ?? 0);
        $role = $_GET['role'] ?? 'customer';

        if ($id > 0 && $this->userModel->toggleStatus($id)) {
            set_flash('success', 'Đã cập nhật trạng thái tài khoản.');
        } else {
            set_flash('danger', 'Không thể cập nhật trạng thái.');
        }

        $this->redirect($role === 'staff' ? '?action=employees' : '?action=customers');
    }
}
