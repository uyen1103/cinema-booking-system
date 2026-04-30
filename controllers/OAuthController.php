<?php

// Include model và helper
require_once __DIR__ . '/../models/Customer.php';
require_once __DIR__ . '/../helpers/OAuthHelper.php';

// Controller xử lý OAuth login (Google)
class OAuthController {
    // OAuthHelper để xử lý các tác vụ với Google
    private $oauthHelper;

    // Constructor: Khởi tạo OAuthHelper
    public function __construct() {
        $this->oauthHelper = new OAuthHelper();
    }

    // googleLogin(): Bước 1 - Chuyển hướng sang Google để đăng nhập
    // Tạo URL Google OAuth và redirect
    public function googleLogin() {
        $authUrl = $this->oauthHelper->getGoogleAuthUrl();
        header("Location: " . $authUrl);
        exit;
    }

    // googleCallback(): Bước 2 - Xử lý callback từ Google
    // Nhận authorization code, đổi lấy token, lấy user info, tạo session
    public function googleCallback() {
        // Lấy authorization code từ URL callback
        $code = $_GET['code'] ?? null;

        // Nếu không có code -> lỗi -> redirect về login
        if (!$code) {
            header("Location: " . customer_url('login', ['error' => 'Google login failed']));
            exit;
        }

        try {
            // Đổi authorization code lấy access token
            $tokenData = $this->oauthHelper->getGoogleAccessToken($code);
            // Lấy thông tin user từ Google
            $userInfo = $this->oauthHelper->getGoogleUserInfo($tokenData['access_token']);
            
            // Tìm hoặc tạo user trong hệ thống database
            $user = $this->findOrCreateOAuthUser('google', $userInfo);
            
            if ($user) {
                // Lưu thông tin vào session
                $_SESSION['customer_id'] = (int) $user['customer_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['phone'] = $user['phone'] ?? '';
                $_SESSION['role'] = 'customer';
                $_SESSION['avatar'] = $user['avatar'] ?? 'assets/images/default-avatar.svg';
                $_SESSION['auth_scope'] = 'customer';
                
                // Redirect về trang chủ
                header("Location: " . customer_url('home'));
                exit;
            } else {
                // Tạo user thất bại
                header("Location: " . customer_url('login', ['error' => 'Failed to create user account']));
                exit;
            }
        } catch (Exception $e) {
            // Bắt lỗi và redirect về login với thông báo lỗi
            header("Location: " . customer_url('login', ['error' => $e->getMessage()]));
            exit;
        }
    }



    // findOrCreateOAuthUser(): Tìm user theo OAuth provider hoặc tạo mới
    // $provider: 'google', $userInfo: mảng thông tin từ Google
    private function findOrCreateOAuthUser($provider, $userInfo) {
        $userModel = new Customer();
        
        // Bước 1: Tìm user theo oauth_id (google user id)
        $existingUser = $userModel->findByOAuth($provider, $userInfo['id']);
        
        if ($existingUser) {
            return $existingUser; // Đã tồn tại -> trả về
        }

        // Bước 2: Nếu không tìm theo oauth_id -> tìm theo email
        if (!empty($userInfo['email'])) {
            $userByEmail = $userModel->findByEmail($userInfo['email']);
            if ($userByEmail) {
                // Email đã tồn tại -> link oauth account với user hiện tại
                $userModel->linkOAuthAccount((int) $userByEmail['customer_id'], $provider, $userInfo['id']);
                return $userByEmail;
            }
        }

        // Tạo user mới
        $data = [
            'full_name' => $userInfo['name'] ?? 'User',
            'email' => $userInfo['email'] ?? '',
            'phone' => '',
            'birthday' => '',
            'address' => '',
            'password' => bin2hex(random_bytes(16)),
            'avatar' => $userInfo['picture'] ?? 'assets/images/default-avatar.svg',
        ];

        if ($userModel->registerOAuth($data, $provider, $userInfo['id'])) {
            return $userModel->findByEmail($data['email']);
        }

        return null;
    }
}
?>
