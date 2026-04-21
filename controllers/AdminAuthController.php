<?php
require_once __DIR__ . '/../models/Employee.php';

class AdminAuthController {
    private function redirect(string $url): void {
        header('Location: ' . $url);
        exit;
    }

    private function seedSession(array $employee): void {
        $_SESSION['employee_id'] = (int) $employee['employee_id'];
        $_SESSION['full_name'] = $employee['full_name'];
        $_SESSION['email'] = $employee['email'];
        $_SESSION['phone'] = $employee['phone'] ?? '';
        $_SESSION['role'] = $employee['role'] ?? 'staff';
        $_SESSION['avatar'] = $employee['avatar'] ?? 'assets/images/default-avatar.svg';
        $_SESSION['position'] = $employee['position'] ?? null;
        $_SESSION['branch_name'] = $employee['branch_name'] ?? null;
        $_SESSION['auth_scope'] = 'employee';
        unset($_SESSION['customer_id']);
    }

    public function login(): void {
        if (isEmployeeLoggedIn()) {
            $this->redirect(admin_url('admin_dashboard'));
        }
        if (isCustomerLoggedIn()) {
            $this->redirect(customer_url('home'));
        }

        $loginTitle = 'Đăng nhập hệ thống';
        $loginSubtitle = 'Sử dụng email hoặc số điện thoại để truy cập tài khoản khách hàng, nhân viên hoặc quản trị viên';
        $loginButtonLabel = 'Đăng nhập';
        $loginAction = app_url('login');
        $showRegisterLink = true;
        $showGoogleLogin = true;
        $showForgotPasswordLink = true;
        $registerUrl = app_url('register');
        $googleLoginUrl = app_url('login-google');
        $loginHelpText = 'Hệ thống sẽ tự động điều hướng đến đúng khu vực theo loại tài khoản sau khi xác thực thành công.';
        $errors = [];

        include __DIR__ . '/../views/auth/login.php';
    }

    public function profile(): void {
        if (!isEmployeeLoggedIn()) {
            $this->redirect(app_url('admin_login'));
        }
        $employeeModel = new Employee();
        $user = $employeeModel->getById(currentEmployeeId());
        include __DIR__ . '/../views/auth/profile.php';
    }

    public function editProfile(): void {
        if (!isEmployeeLoggedIn()) {
            $this->redirect(app_url('admin_login'));
        }

        $employeeModel = new Employee();
        $user = $employeeModel->getById(currentEmployeeId());

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $payload = [
                'full_name' => trim($_POST['full_name'] ?? ($user['full_name'] ?? '')),
                'email' => trim($_POST['email'] ?? ($user['email'] ?? '')),
                'phone' => trim($_POST['phone'] ?? ($user['phone'] ?? '')),
                'birthday' => trim($_POST['birthday'] ?? ($user['birthday'] ?? '')),
                'address' => trim($_POST['address'] ?? ($user['address'] ?? '')),
            ];

            $errors = [];
            if (empty($payload['full_name'])) $errors[] = 'Vui lòng nhập họ tên.';
            if (empty($payload['email'])) {
                $errors[] = 'Vui lòng nhập email.';
            } elseif (!filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email không hợp lệ.';
            }
            if (empty($payload['phone'])) $errors[] = 'Vui lòng nhập số điện thoại.';
            if ($payload['email'] !== ($user['email'] ?? '') && $employeeModel->emailExists($payload['email'], currentEmployeeId())) {
                $errors[] = 'Email đã được đăng ký.';
            }

            if (empty($errors) && $employeeModel->updateProfile(currentEmployeeId(), $payload)) {
                $_SESSION['full_name'] = $payload['full_name'];
                $_SESSION['email'] = $payload['email'];
                $_SESSION['phone'] = $payload['phone'];
                $this->redirect(admin_url('admin_profile', ['message' => 'Cập nhật hồ sơ thành công']));
            }

            if (empty($errors)) {
                $errors[] = 'Cập nhật thất bại.';
            }
            $user = $employeeModel->getById(currentEmployeeId());
        }

        include __DIR__ . '/../views/auth/edit-profile.php';
    }

    public function changePassword(): void {
        if (!isEmployeeLoggedIn()) {
            $this->redirect(app_url('admin_login'));
        }

        $employeeModel = new Employee();
        $user = $employeeModel->getById(currentEmployeeId());
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $currentPassword = trim($_POST['current_password'] ?? '');
            $newPassword = trim($_POST['new_password'] ?? '');
            $confirmPassword = trim($_POST['confirm_password'] ?? '');

            if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
                $errors[] = 'Vui lòng nhập đầy đủ thông tin mật khẩu.';
            } elseif ($newPassword !== $confirmPassword) {
                $errors[] = 'Mật khẩu xác nhận không khớp.';
            } else {
                $result = $employeeModel->changePassword(currentEmployeeId(), $currentPassword, $newPassword);
                if ($result === true) {
                    $this->redirect(admin_url('admin_change_password', ['message' => 'Đổi mật khẩu thành công']));
                } elseif ($result === false) {
                    $errors[] = 'Mật khẩu hiện tại không chính xác.';
                } else {
                    $errors = array_merge($errors, $result);
                }
            }
        }

        include __DIR__ . '/../views/auth/change-password.php';
    }

    public function logout(): void {
        session_unset();
        session_destroy();
        $this->redirect(app_url('admin_login'));
    }
}
