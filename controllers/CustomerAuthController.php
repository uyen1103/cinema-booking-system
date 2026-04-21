<?php
require_once __DIR__ . '/../models/Customer.php';
require_once __DIR__ . '/../models/Promotion.php';
require_once __DIR__ . '/../models/Employee.php';

class CustomerAuthController {
    private function input(string $key, string $default = ''): string {
        return trim($_POST[$key] ?? $default);
    }

    private function redirect(string $url): void {
        header('Location: ' . $url);
        exit;
    }

    private function seedSession(array $customer): void {
        $_SESSION['customer_id'] = (int) $customer['customer_id'];
        $_SESSION['full_name'] = $customer['full_name'];
        $_SESSION['email'] = $customer['email'];
        $_SESSION['phone'] = $customer['phone'] ?? '';
        $_SESSION['role'] = 'customer';
        $_SESSION['avatar'] = $customer['avatar'] ?? 'assets/images/default-avatar.svg';
        $_SESSION['auth_scope'] = 'customer';
        unset($_SESSION['employee_id'], $_SESSION['position'], $_SESSION['branch_name']);
    }

    private function validateRegisterData(array $data): array {
        $errors = [];
        if (empty($data['full_name'])) $errors[] = 'Vui lòng nhập họ tên.';
        if (empty($data['email'])) {
            $errors[] = 'Vui lòng nhập email.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email không hợp lệ.';
        }
        if (empty($data['phone'])) $errors[] = 'Vui lòng nhập số điện thoại.';
        if (empty($data['password'])) $errors[] = 'Vui lòng nhập mật khẩu.';
        if ($data['password'] !== $data['confirm_password']) $errors[] = 'Mật khẩu không khớp.';
        return $errors;
    }

    public function register(): void {
        if (isEmployeeLoggedIn()) {
            $this->redirect(admin_url('admin_dashboard'));
        }
        if (isCustomerLoggedIn()) {
            $this->redirect(app_url('home'));
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            include __DIR__ . '/../views/auth/register.php';
            return;
        }

        $payload = [
            'full_name' => $this->input('full_name'),
            'email' => $this->input('email'),
            'phone' => $this->input('phone'),
            'birthday' => $this->input('birthday'),
            'address' => $this->input('address'),
            'password' => $this->input('password'),
            'confirm_password' => $this->input('confirm_password'),
        ];

        $customerModel = new Customer();
        $errors = $this->validateRegisterData($payload);
        if ($customerModel->emailExists($payload['email'])) {
            $errors[] = 'Email đã được đăng ký.';
        }
        $errors = array_merge($errors, $customerModel->validatePassword($payload['password']));

        if (!empty($errors)) {
            include __DIR__ . '/../views/auth/register.php';
            return;
        }

        if ($customerModel->register($payload + ['status' => 'active'])) {
            $this->redirect(app_url('login', ['message' => 'Đăng ký thành công']));
        }

        $errors[] = 'Đăng ký thất bại.';
        include __DIR__ . '/../views/auth/register.php';
    }

    public function login(): void {
        if (isCustomerLoggedIn()) {
            $this->redirect(app_url('home'));
        }
        if (isEmployeeLoggedIn()) {
            $this->redirect(admin_url('admin_dashboard'));
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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $loginIdentifier = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            $employeeModel = new Employee();
            $employee = $employeeModel->authenticate($loginIdentifier, $password);
            if ($employee) {
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
                $this->redirect(admin_url('admin_dashboard'));
            }

            $customerModel = new Customer();
            $customer = $customerModel->authenticate($loginIdentifier, $password);
            if ($customer) {
                $this->seedSession($customer);
                $this->redirect(app_url('home'));
            }

            $errors = ['Thông tin đăng nhập không chính xác, hoặc tài khoản đã bị khóa.'];
            include __DIR__ . '/../views/auth/login.php';
            return;
        }

        include __DIR__ . '/../views/auth/login.php';
    }

    public function profile(): void {
        if (!isCustomerLoggedIn()) {
            $this->redirect(app_url('login'));
        }
        $customerModel = new Customer();
        $user = $customerModel->getById(currentCustomerId());
        include __DIR__ . '/../views/auth/profile.php';
    }

    public function editProfile(): void {
        if (!isCustomerLoggedIn()) {
            $this->redirect(app_url('login'));
        }

        $customerModel = new Customer();
        $user = $customerModel->getById(currentCustomerId());

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $payload = [
                'full_name' => trim($_POST['full_name'] ?? ($user['full_name'] ?? '')),
                'email' => trim($_POST['email'] ?? ($user['email'] ?? '')),
                'phone' => trim($_POST['phone'] ?? ($user['phone'] ?? '')),
                'birthday' => trim($_POST['birthday'] ?? ($user['birthday'] ?? '')),
                'address' => trim($_POST['address'] ?? ($user['address'] ?? '')),
                'bank_account' => $user['bank_account'] ?? null,
                'e_wallet_account' => $user['e_wallet_account'] ?? null,
            ];

            $errors = [];
            if (empty($payload['full_name'])) $errors[] = 'Vui lòng nhập họ tên.';
            if (empty($payload['email'])) {
                $errors[] = 'Vui lòng nhập email.';
            } elseif (!filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email không hợp lệ.';
            }
            if (empty($payload['phone'])) $errors[] = 'Vui lòng nhập số điện thoại.';
            if ($payload['email'] !== ($user['email'] ?? '') && $customerModel->emailExists($payload['email'], currentCustomerId())) {
                $errors[] = 'Email đã được đăng ký.';
            }

            if (empty($errors) && $customerModel->updateProfile(currentCustomerId(), $payload)) {
                $_SESSION['full_name'] = $payload['full_name'];
                $_SESSION['email'] = $payload['email'];
                $_SESSION['phone'] = $payload['phone'];
                $this->redirect(app_url('profile', ['message' => 'Cập nhật thành công']));
            }

            if (empty($errors)) {
                $errors[] = 'Cập nhật thất bại.';
            }
            $user = $customerModel->getById(currentCustomerId());
        }

        include __DIR__ . '/../views/auth/edit-profile.php';
    }

    public function linkBankAccount(): void {
        if (!isCustomerLoggedIn()) {
            $this->redirect(app_url('login'));
        }

        $customerModel = new Customer();
        $user = $customerModel->getById(currentCustomerId());

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $payload = [
                'full_name' => $user['full_name'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'birthday' => $user['birthday'] ?? null,
                'address' => $user['address'] ?? null,
                'bank_account' => trim($_POST['bank_account'] ?? ($user['bank_account'] ?? '')),
                'e_wallet_account' => trim($_POST['e_wallet_account'] ?? ($user['e_wallet_account'] ?? '')),
            ];
            $errors = [];
            if (empty($errors) && $customerModel->updateProfile(currentCustomerId(), $payload)) {
                $this->redirect(app_url('link-bank-account', ['message' => 'Cập nhật tài khoản ngân hàng thành công']));
            }
            if (empty($errors)) {
                $errors[] = 'Cập nhật thất bại.';
            }
            $user = $customerModel->getById(currentCustomerId());
        }

        include __DIR__ . '/../views/auth/link-bank-account.php';
    }

    public function forgotPassword(): void {
        $errors = [];
        $success = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            if ($email === '') {
                $errors[] = 'Vui lòng nhập email';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email không hợp lệ';
            } else {
                $customerModel = new Customer();
                if ($customerModel->emailExists($email)) {
                    $success = 'Mã xác nhận đã được gửi tới email của bạn. Vui lòng kiểm tra hộp thư.';
                } else {
                    $errors[] = 'Email chưa được đăng ký';
                }
            }
        }

        include __DIR__ . '/../views/auth/forgot-password.php';
    }

    public function vouchers(): void {
        if (!isCustomerLoggedIn()) {
            $this->redirect(app_url('login'));
        }
        $promotionModel = new Promotion();
        $vouchers = $promotionModel->getActivePromotions();
        include __DIR__ . '/../views/auth/vouchers.php';
    }

    public function changePassword(): void {
        if (!isCustomerLoggedIn()) {
            $this->redirect(app_url('login'));
        }

        $customerModel = new Customer();
        $user = $customerModel->getById(currentCustomerId());
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
                $result = $customerModel->changePassword(currentCustomerId(), $currentPassword, $newPassword);
                if ($result === true) {
                    $this->redirect(app_url('change-password', ['message' => 'Đổi mật khẩu thành công']));
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
        $this->redirect(app_url('login'));
    }
}
