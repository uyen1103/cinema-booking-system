<?php
require_once 'models/User.php';
require_once 'models/Promotion.php';

// Controller xử lý các chức năng xác thực (đăng ký, đăng nhập, cập nhật profile, đổi mật khẩu, đăng xuất)
class AuthController {
    private function input($key, $default = '') {
        return trim($_POST[$key] ?? $default);
    }

    private function validateRegisterData($data) {
        $errors = [];

        if (empty($data['full_name'])) {
            $errors[] = 'Vui lòng nhập họ tên';
        }
        if (empty($data['email'])) {
            $errors[] = 'Vui lòng nhập email';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email không hợp lệ';
        }
        if (empty($data['phone'])) {
            $errors[] = 'Vui lòng nhập số điện thoại';
        }
        if (empty($data['password'])) {
            $errors[] = 'Vui lòng nhập mật khẩu';
        }
        if ($data['password'] !== $data['confirm_password']) {
            $errors[] = 'Mật khẩu không khớp';
        }

        return $errors;
    }


    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            include 'views/auth/register.php';
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

        $errors = $this->validateRegisterData($payload);

        $userModel = new User();
        if ($userModel->emailExists($payload['email'])) {
            $errors[] = 'Email đã được đăng ký';
        }

        $errors = array_merge($errors, $userModel->validatePassword($payload['password']));

        if (!empty($errors)) {
            include 'views/auth/register.php';
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
            header('Location: web.php?action=login&message=Đăng ký thành công');
            exit;
        }

        $errors[] = 'Đăng ký thất bại';
        include 'views/auth/register.php';
    }

    // Đăng nhập
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $user = new User();
            $user->email = $_POST['email'] ?? '';
            $user->password = $_POST['password'] ?? '';

            if ($user->login()) {
                // Lưu thông tin user vào session
                $_SESSION['user_id'] = $user->user_id;
                $_SESSION['full_name'] = $user->full_name;
                $_SESSION['email'] = $user->email;
                $_SESSION['phone'] = $user->phone;
                $_SESSION['role'] = $user->role;
                header("Location: index.php");
            } else {
                $errors = ["Email hoặc mật khẩu không chính xác"];
                include 'views/auth/login.php';
            }
        } else {
            // Hiển thị form đăng nhập
            include 'views/auth/login.php';
        }
    }

    // Hiển thị trang profile cá nhân
    public function profile() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: web.php?action=login");
            exit;
        }

        $userModel = new User();
        $user = $userModel->getUserById($_SESSION['user_id']);
        include 'views/auth/profile.php';
    }

    // Cập nhật thông tin cá nhân
    public function editProfile() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: web.php?action=login");
            exit;
        }

        $userModel = new User();
        $user = $userModel->getUserById($_SESSION['user_id']);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $userModel->user_id = $_SESSION['user_id'];
            $userModel->full_name = trim($_POST['full_name'] ?? $user['full_name']);
            $userModel->email = trim($_POST['email'] ?? $user['email']);
            $userModel->phone = trim($_POST['phone'] ?? $user['phone']);
            $userModel->birthday = trim($_POST['birthday'] ?? $user['birthday']);
            $userModel->address = trim($_POST['address'] ?? $user['address']);
<<<<<<< HEAD
            $userModel->bank_account = trim($_POST['bank_account'] ?? $user['bank_account']);
=======
>>>>>>> 79d8d1d56f94b32a57937290034834493747c163

            $errors = [];
            if (empty($userModel->full_name)) {
                $errors[] = 'Vui lòng nhập họ tên';
            }
            if (empty($userModel->email)) {
                $errors[] = 'Vui lòng nhập email';
            } elseif (!filter_var($userModel->email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email không hợp lệ';
            }
            if (empty($userModel->phone)) {
                $errors[] = 'Vui lòng nhập số điện thoại';
            }
            if ($userModel->email !== $user['email'] && $userModel->emailExists($userModel->email)) {
                $errors[] = 'Email đã được đăng ký';
            }

<<<<<<< HEAD
            // Xử lý cập nhật mật khẩu nếu có
            $new_password = trim($_POST['new_password'] ?? '');
            $confirm_password = trim($_POST['confirm_password'] ?? '');
            $current_password = trim($_POST['current_password'] ?? '');
            
            if (!empty($new_password)) {
                if (empty($current_password)) {
                    $errors[] = 'Vui lòng nhập mật khẩu hiện tại';
                } elseif ($new_password !== $confirm_password) {
                    $errors[] = 'Mật khẩu xác nhận không khớp';
                } else {
                    $passwordErrors = $userModel->validatePassword($new_password);
                    if (!empty($passwordErrors)) {
                        $errors = array_merge($errors, $passwordErrors);
                    }
                }
            }

            if (empty($errors)) {
                if ($userModel->update()) {
                    // Cập nhật mật khẩu nếu có
                    if (!empty($new_password)) {
                        $changePasswordResult = $userModel->changePassword($_SESSION['user_id'], $current_password, $new_password);
                        if ($changePasswordResult === false) {
                            $errors[] = 'Mật khẩu hiện tại không chính xác';
                        } elseif (is_array($changePasswordResult)) {
                            $errors = array_merge($errors, $changePasswordResult);
                        }
                    }
                    
                    if (empty($errors)) {
                        // Cập nhật session
                        $_SESSION['full_name'] = $userModel->full_name;
                        $_SESSION['email'] = $userModel->email;
                        $_SESSION['phone'] = $userModel->phone;
                        header("Location: web.php?action=profile&message=Cập nhật thành công");
                        exit;
                    }
                }
                if (empty($errors)) {
                    $errors = ["Cập nhật thất bại"];
                }
=======
            if (empty($errors)) {
                if ($userModel->update()) {
                    // Cập nhật session
                    $_SESSION['full_name'] = $userModel->full_name;
                    $_SESSION['email'] = $userModel->email;
                    $_SESSION['phone'] = $userModel->phone;
                    header("Location: web.php?action=profile&message=Cập nhật thành công");
                    exit;
                }
                $errors = ["Cập nhật thất bại"];
>>>>>>> 79d8d1d56f94b32a57937290034834493747c163
            }

            $user = [
                'full_name' => $userModel->full_name,
                'email' => $userModel->email,
                'phone' => $userModel->phone,
                'birthday' => $userModel->birthday,
                'address' => $userModel->address,
<<<<<<< HEAD
                'bank_account' => $userModel->bank_account,
=======
>>>>>>> 79d8d1d56f94b32a57937290034834493747c163
            ];
            include 'views/auth/edit-profile.php';
        } else {
            include 'views/auth/edit-profile.php';
        }
    }

    // Quên mật khẩu / xử lý gửi mã xác nhận
    public function forgotPassword() {
        $errors = [];
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');

            if (empty($email)) {
                $errors[] = 'Vui lòng nhập email';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email không hợp lệ';
            } else {
                $userModel = new User();
                if ($userModel->emailExists($email)) {
                    // Ở đây giả lập gửi mã xác nhận vì chưa có mail server
                    $success = 'Mã xác nhận đã được gửi tới email của bạn. Vui lòng kiểm tra hộp thư.';
                } else {
                    $errors[] = 'Email chưa được đăng ký';
                }
            }
        }

        include 'views/auth/forgot-password.php';
    }

    // Xem voucher của tôi
    public function vouchers() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: web.php?action=login");
            exit;
        }

        $promotionModel = new Promotion();
        $vouchers = $promotionModel->getActivePromotions();
        include 'views/auth/vouchers.php';
    }

    // Đăng xuất
    public function logout() {
        session_destroy();
        header("Location: index.php");
    }
}
