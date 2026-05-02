<?php
require_once __DIR__ . '/../models/Customer.php'; // Model quản lý khách hàng dùng cho đăng ký, đăng nhập và profile
require_once __DIR__ . '/../models/Promotion.php'; // Model khuyến mãi dùng cho voucher và mã giảm giá
require_once __DIR__ . '/../models/Employee.php'; // Model nhân viên dùng để kiểm tra đăng nhập chung qua cùng form

// Controller xử lý đăng ký, đăng nhập, profile và các chức năng auth của khách hàng
// - Đăng ký khách hàng mới
// - Xác thực đăng nhập khách hàng và nhân viên
// - Quản lý thông tin hồ sơ và voucher khuyến mãi
class CustomerAuthController {
    // Lấy giá trị POST an toàn và xóa khoảng trắng 2 đầu
    private function input(string $key, string $default = ''): string {
        return trim($_POST[$key] ?? $default);
    }

    // Chuyển hướng người dùng tới URL khác
    private function redirect(string $url): void {
        header('Location: ' . $url);
        exit;
    }

    // Khởi tạo session cho khách hàng sau khi đăng nhập thành công
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

    // Kiểm tra dữ liệu đăng ký cơ bản, trả về danh sách lỗi nếu có
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

    // Chức năng 4.3.1: Đăng ký tài khoản khách hàng mới
    // - Hiển thị form đăng ký nếu truy cập bằng GET
    // - Xử lý dữ liệu POST để tạo khách hàng mới trong hệ thống
    // - Kiểm tra email trùng, validate mật khẩu và điều hướng khi thành công
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

        // Thu thập dữ liệu đăng ký từ form và chuẩn hóa input
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

        // Nếu có lỗi validate, hiện lại form với thông báo lỗi
        if (!empty($errors)) {
            include __DIR__ . '/../views/auth/register.php';
            return;
        }

        // Gọi model tạo tài khoản mới và chuyển hướng khi thành công
        if ($customerModel->register($payload + ['status' => 'active'])) {
            $this->redirect(app_url('login', ['message' => 'Đăng ký thành công']));
        }

        // Nếu lưu không thành công, báo lỗi chung và hiển thị lại form
        $errors[] = 'Đăng ký thất bại.';
        include __DIR__ . '/../views/auth/register.php';
    }

    // Chức năng 4.3.2: Đăng nhập hệ thống
    // - Kiểm tra thông tin email/số điện thoại và mật khẩu
    // - Phân biệt đăng nhập khách hàng hoặc nhân viên
    // - Điều hướng sang khu vực phù hợp sau khi xác thực thành công
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
            // Lấy dữ liệu đăng nhập từ form
            $loginIdentifier = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            // Thử xác thực nhân viên trước nếu thông tin trùng
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

            // Nếu không phải nhân viên thì xác thực khách hàng
            $customerModel = new Customer();
            $customer = $customerModel->authenticate($loginIdentifier, $password);
            if ($customer) {
                $this->seedSession($customer);
                $this->redirect(app_url('home'));
            }

            // Nếu cả hai đều không đúng thì show lỗi
            $errors = ['Thông tin đăng nhập không chính xác, hoặc tài khoản đã bị khóa.'];
            include __DIR__ . '/../views/auth/login.php';
            return;
        }

        include __DIR__ . '/../views/auth/login.php';
    }

    // Chức năng 4.3.3: Xem thông tin tài khoản cá nhân
    // - Hiển thị trang thông tin khách hàng hiện tại
    // - Lấy thông tin user hiện tại từ model theo session
    public function profile(): void {
        if (!isCustomerLoggedIn()) {
            $this->redirect(app_url('login'));
        }
        $customerModel = new Customer();
        $user = $customerModel->getById(currentCustomerId());
        include __DIR__ . '/../views/auth/profile.php';
    }

    // Chức năng 4.3.3: Cập nhật tài khoản cá nhân
    // - Cho phép khách hàng sửa thông tin cá nhân như tên, email, điện thoại, địa chỉ
    // - Kiểm tra dữ liệu nhập và lưu thay đổi qua model
    public function editProfile(): void {
        if (!isCustomerLoggedIn()) {
            $this->redirect(app_url('login'));
        }

        $customerModel = new Customer();
        $user = $customerModel->getById(currentCustomerId());

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Lấy dữ liệu profile mới từ form
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

            // Nếu dữ liệu hợp lệ, cập nhật profile trong model
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

    // Chức năng 4.3.9: Nhận khuyến mãi / xem voucher khuyến mãi
    // - Hiển thị danh sách khuyến mãi đang hoạt động cho khách hàng
    // - Lấy dữ liệu khuyến mãi từ model promotion và render view voucher
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
