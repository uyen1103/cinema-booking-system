<?php
require_once __DIR__ . '/../models/Customer.php';
require_once __DIR__ . '/../helpers/OAuthHelper.php';

// Controller xử lý OAuth login (Google)
class OAuthController {
    private $oauthHelper;

    public function __construct() {
        $this->oauthHelper = new OAuthHelper();
    }

    // Google Login
    public function googleLogin() {
        $authUrl = $this->oauthHelper->getGoogleAuthUrl();
        header("Location: " . $authUrl);
        exit;
    }

    // Google Callback
    public function googleCallback() {
        $code = $_GET['code'] ?? null;

        if (!$code) {
            header("Location: " . customer_url('login', ['error' => 'Google login failed']));
            exit;
        }

        try {
            $tokenData = $this->oauthHelper->getGoogleAccessToken($code);
            $userInfo = $this->oauthHelper->getGoogleUserInfo($tokenData['access_token']);
            
            // Tìm hoặc tạo user từ Google info
            $user = $this->findOrCreateOAuthUser('google', $userInfo);
            
            if ($user) {
                // Lưu session
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
                header("Location: " . customer_url('login', ['error' => 'Failed to create user account']));
                exit;
            }
        } catch (Exception $e) {
            header("Location: " . customer_url('login', ['error' => $e->getMessage()]));
            exit;
        }
    }



    // Tìm hoặc tạo user từ OAuth data
    private function findOrCreateOAuthUser($provider, $userInfo) {
        $userModel = new Customer();
        
        // Tìm user theo oauth_id
        $existingUser = $userModel->findByOAuth($provider, $userInfo['id']);
        
        if ($existingUser) {
            return $existingUser;
        }

        // Nếu có email, tìm theo email
        if (!empty($userInfo['email'])) {
            $userByEmail = $userModel->findByEmail($userInfo['email']);
            if ($userByEmail) {
                // Cập nhật oauth info cho user hiện tại
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
