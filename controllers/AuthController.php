<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Promotion.php';

class AuthController {
    private function input(string $key, string $default = ''): string {
        return trim($_POST[$key] ?? $default);
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

        $userModel = new User();
        $errors = $this->validateRegisterData($payload);
        if ($userModel->emailExists($payload['email'])) {
            $errors[] = 'Email đã được đăng ký.';
        }
        $errors = array_merge($errors, $userModel->validatePassword($payload['password']));

        if (!empty($errors)) {
            include __DIR__ . '/../views/auth/register.php';
            return;
        }

        $user = new User();
        $user->full_name = $payload['full_name'];
        $user->email = $payload['email'];
        $user->phone = $payload['phone'];
        $user->birthday = $payload['birthday'];
        $user->address = $payload['address'];
        $user->password = $payload['password'];
        $user->role = 'customer';
        $user->status = 'active';

        if ($user->register()) {
            header('Location: index.php?action=login&message=Đăng ký thành công');
            exit;
        }

        $errors[] = 'Đăng ký thất bại.';
        include __DIR__ . '/../views/auth/register.php';
    }

    public function login(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = new User();
            $user->email = trim($_POST['email'] ?? '');
            $user->password = $_POST['password'] ?? '';

            if ($user->login()) {
                $_SESSION['user_id'] = $user->user_id;
                $_SESSION['full_name'] = $user->full_name;
                $_SESSION['email'] = $user->email;
                $_SESSION['phone'] = $user->phone;
                $_SESSION['role'] = $user->role;
                $_SESSION['avatar'] = $user->avatar;

                if (in_array($user->role, ['admin', 'staff'], true)) {
                    header('Location: index.php?action=admin_dashboard');
                } else {
                    header('Location: index.php');
                }
                exit;
            }

            $errors = ['Email hoặc mật khẩu không chính xác, hoặc tài khoản đã bị khóa.'];
            include __DIR__ . '/../views/auth/login.php';
            return;
        }

        include __DIR__ . '/../views/auth/login.php';
    }

    public function profile(): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: web.php?action=login');
            exit;
        }
        $userModel = new User();
        $user = $userModel->getUserById((int) $_SESSION['user_id']);
        include __DIR__ . '/../views/auth/profile.php';
    }

    public function editProfile(): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: web.php?action=login');
            exit;
        }

        $userModel = new User();
        $user = $userModel->getUserById((int) $_SESSION['user_id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userModel->user_id = (int) $_SESSION['user_id'];
            $userModel->full_name = trim($_POST['full_name'] ?? $user['full_name']);
            $userModel->email = trim($_POST['email'] ?? $user['email']);
            $userModel->phone = trim($_POST['phone'] ?? $user['phone']);
            $userModel->birthday = trim($_POST['birthday'] ?? $user['birthday']);
            $userModel->address = trim($_POST['address'] ?? $user['address']);

            $errors = [];
            if (empty($userModel->full_name)) $errors[] = 'Vui lòng nhập họ tên.';
            if (empty($userModel->email)) {
                $errors[] = 'Vui lòng nhập email.';
            } elseif (!filter_var($userModel->email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email không hợp lệ.';
            }
            if (empty($userModel->phone)) $errors[] = 'Vui lòng nhập số điện thoại.';
            if ($userModel->email !== $user['email'] && $userModel->emailExists($userModel->email)) {
                $errors[] = 'Email đã được đăng ký.';
            }

            $newPassword = trim($_POST['new_password'] ?? '');
            $confirmPassword = trim($_POST['confirm_password'] ?? '');
            $currentPassword = trim($_POST['current_password'] ?? '');
            if ($newPassword !== '') {
                if ($currentPassword === '') {
                    $errors[] = 'Vui lòng nhập mật khẩu hiện tại.';
                } elseif ($newPassword !== $confirmPassword) {
                    $errors[] = 'Mật khẩu xác nhận không khớp.';
                } else {
                    $errors = array_merge($errors, $userModel->validatePassword($newPassword));
                }
            }

            if (empty($errors) && $userModel->update()) {
                if ($newPassword !== '') {
                    $changeResult = $userModel->changePassword((int) $_SESSION['user_id'], $currentPassword, $newPassword);
                    if ($changeResult === false) {
                        $errors[] = 'Mật khẩu hiện tại không chính xác.';
                    } elseif (is_array($changeResult)) {
                        $errors = array_merge($errors, $changeResult);
                    }
                }

                if (empty($errors)) {
                    $_SESSION['full_name'] = $userModel->full_name;
                    $_SESSION['email'] = $userModel->email;
                    $_SESSION['phone'] = $userModel->phone;
                    header('Location: web.php?action=profile&message=Cập nhật thành công');
                    exit;
                }
            }

            if (empty($errors)) {
                $errors[] = 'Cập nhật thất bại.';
            }
            $user = $userModel->getUserById((int) $_SESSION['user_id']);
        }

        include __DIR__ . '/../views/auth/edit-profile.php';
    }

    public function linkBankAccount(): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: web.php?action=login');
            exit;
        }

        // Prevent admin from accessing this page
        if (isAdmin()) {
            header('Location: web.php?action=profile');
            exit;
        }

        $userModel = new User();
        $user = $userModel->getUserById((int) $_SESSION['user_id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userModel->user_id = (int) $_SESSION['user_id'];
            $userModel->full_name = $user['full_name'];
            $userModel->email = $user['email'];
            $userModel->phone = $user['phone'];
            $userModel->birthday = $user['birthday'] ?? '';
            $userModel->address = $user['address'] ?? '';
            $userModel->bank_account = trim($_POST['bank_account'] ?? ($user['bank_account'] ?? ''));
            $userModel->e_wallet_account = trim($_POST['e_wallet_account'] ?? ($user['e_wallet_account'] ?? ''));

            $errors = [];
            // Optional validation - fields can be empty
            // if (empty($userModel->bank_account)) $errors[] = 'Vui lòng nhập số tài khoản ngân hàng.';
            // if (empty($userModel->e_wallet_account)) $errors[] = 'Vui lòng nhập số ví điện tử.';

            if (empty($errors) && $userModel->update()) {
                header('Location: web.php?action=link-bank-account&message=Cập nhật tài khoản ngân hàng thành công');
                exit;
            }

            if (empty($errors)) {
                $errors[] = 'Cập nhật thất bại.';
            }
            $user = $userModel->getUserById((int) $_SESSION['user_id']);
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
                $userModel = new User();
                if ($userModel->emailExists($email)) {
                    $success = 'Mã xác nhận đã được gửi tới email của bạn. Vui lòng kiểm tra hộp thư.';
                } else {
                    $errors[] = 'Email chưa được đăng ký';
                }
            }
        }

        include __DIR__ . '/../views/auth/forgot-password.php';
    }

    public function vouchers(): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: web.php?action=login');
            exit;
        }
        $promotionModel = new Promotion();
        $vouchers = $promotionModel->getActivePromotions();
        include __DIR__ . '/../views/auth/vouchers.php';
    }

    public function changePassword(): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: web.php?action=login');
            exit;
        }

        $userModel = new User();
        $user = $userModel->getUserById((int) $_SESSION['user_id']);
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $currentPassword = trim($_POST['current_password'] ?? '');
            $newPassword = trim($_POST['new_password'] ?? '');
            $confirmPassword = trim($_POST['confirm_password'] ?? '');

            if (empty($currentPassword)) {
                $errors[] = 'Vui lòng nhập mật khẩu hiện tại.';
            }
            if (empty($newPassword)) {
                $errors[] = 'Vui lòng nhập mật khẩu mới.';
            }
            if (empty($confirmPassword)) {
                $errors[] = 'Vui lòng xác nhận mật khẩu mới.';
            }

            if ($newPassword !== $confirmPassword) {
                $errors[] = 'Mật khẩu xác nhận không khớp.';
            }

            if (empty($errors)) {
                $errors = array_merge($errors, $userModel->validatePassword($newPassword));
            }

            if (empty($errors)) {
                $changeResult = $userModel->changePassword((int) $_SESSION['user_id'], $currentPassword, $newPassword);
                if ($changeResult === false) {
                    $errors[] = 'Mật khẩu hiện tại không chính xác.';
                } elseif (is_array($changeResult)) {
                    $errors = array_merge($errors, $changeResult);
                } else {
                    header('Location: web.php?action=profile&message=Đổi mật khẩu thành công');
                    exit;
                }
            }
        }

        include __DIR__ . '/../views/auth/change-password.php';
    }

    public function logout(): void {
        session_destroy();
        header('Location: index.php');
        exit;
    }
}
?>