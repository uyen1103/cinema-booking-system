<?php
require_once __DIR__ . '/../models/Customer.php'; // Model Customer dùng để tìm, tạo và liên kết tài khoản khi đăng nhập OAuth
require_once __DIR__ . '/../helpers/OAuthHelper.php'; // Helper xử lý luồng OAuth Google: tạo URL, lấy token và thông tin user

// Controller xử lý OAuth login (Google)
// - Chuyển hướng khách hàng tới Google để xác thực
// - Nhận callback từ Google, lấy thông tin profile
// - Tự động đăng nhập hoặc tạo/tích hợp tài khoản khách hàng
class OAuthController {
    private $oauthHelper;

    public function __construct() {
        $this->oauthHelper = new OAuthHelper();
    }

    // Bước 1: Chuyển người dùng đến trang đăng nhập Google
    public function googleLogin() {
        $authUrl = $this->oauthHelper->getGoogleAuthUrl();
        header("Location: " . $authUrl);
        exit;
    }

    // Bước 2: Xử lý callback từ Google sau khi người dùng đồng ý xác thực
    public function googleCallback() {
        $code = $_GET['code'] ?? null;

        // Nếu không có code trả về, coi như đăng nhập thất bại
        if (!$code) {
            header("Location: " . customer_url('login', ['error' => 'Google login failed']));
            exit;
        }

        try {
            // Đổi code lấy access token từ Google
            $tokenData = $this->oauthHelper->getGoogleAccessToken($code);
            // Lấy thông tin user từ Google bằng access token
            $userInfo = $this->oauthHelper->getGoogleUserInfo($tokenData['access_token']);
            
            // Tìm hoặc tạo tài khoản khách hàng tương ứng với Google profile
            $user = $this->findOrCreateOAuthUser('google', $userInfo);
            
            if ($user) {
                // Ghi session khách hàng sau khi đăng nhập thành công
                $_SESSION['customer_id'] = (int) $user['customer_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['phone'] = $user['phone'] ?? '';
                $_SESSION['role'] = 'customer';
                $_SESSION['avatar'] = $user['avatar'] ?? 'assets/images/default-avatar.svg';
                $_SESSION['auth_scope'] = 'customer';
                
                header("Location: " . customer_url('home'));
                exit;
            } else {
                // Nếu không tạo/thực thi được user, chuyển về trang login với lỗi
                header("Location: " . customer_url('login', ['error' => 'Failed to create user account']));
                exit;
            }
        } catch (Exception $e) {
            // Nếu có lỗi trong quá trình OAuth, trả về trang login với thông báo lỗi
            header("Location: " . customer_url('login', ['error' => $e->getMessage()]));
            exit;
        }
    }



    // Tìm hoặc tạo user từ OAuth data
    // - Nếu user đã liên kết với oauth_id thì sử dụng luôn
    // - Nếu email đã tồn tại thì liên kết Google với tài khoản hiện có
    // - Nếu chưa có tài khoản, tạo mới và lưu thông tin Google
    private function findOrCreateOAuthUser($provider, $userInfo) {
        $userModel = new Customer();
        
        // Tìm user đã liên kết với tài khoản OAuth trước đó
        $existingUser = $userModel->findByOAuth($provider, $userInfo['id']);
        if ($existingUser) {
            return $existingUser;
        }

        // Nếu Google profile có email, kiểm tra xem đã có user với email đó chưa
        if (!empty($userInfo['email'])) {
            $userByEmail = $userModel->findByEmail($userInfo['email']);
            if ($userByEmail) {
                // Nếu user email tồn tại, liên kết tài khoản Google với user đó
                $userModel->linkOAuthAccount((int) $userByEmail['customer_id'], $provider, $userInfo['id']);
                return $userByEmail;
            }
        }

        // Không tìm thấy user nào, tạo user mới từ thông tin Google
        $data = [
            'full_name' => $userInfo['name'] ?? 'User',
            'email' => $userInfo['email'] ?? '',
            'phone' => '',
            'birthday' => '',
            'address' => '',
            'password' => bin2hex(random_bytes(16)),
            'avatar' => $userInfo['picture'] ?? 'assets/images/default-avatar.svg',
        ];

        // Gọi model tạo user OAuth và trả về user mới nếu thành công
        if ($userModel->registerOAuth($data, $provider, $userInfo['id'])) {
            return $userModel->findByEmail($data['email']);
        }

        // Nếu thất bại thì trả về null để xử lý ở callback
        return null;
    }
}
?>
