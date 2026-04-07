<?php
require_once 'models/User.php';
require_once 'helpers/OAuthHelper.php';

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
            header("Location: web.php?action=login&error=Google login failed");
            exit;
        }

        try {
            $tokenData = $this->oauthHelper->getGoogleAccessToken($code);
            $userInfo = $this->oauthHelper->getGoogleUserInfo($tokenData['access_token']);
            
            // Tìm hoặc tạo user từ Google info
            $user = $this->findOrCreateOAuthUser('google', $userInfo);
            
            if ($user) {
                // Lưu session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['phone'] = $user['phone'] ?? '';
                $_SESSION['role'] = $user['role'];
                
                header("Location: web.php?action=home");

                exit;
            } else {
                header("Location: web.php?action=login&error=Failed to create user account");
                exit;
            }
        } catch (Exception $e) {
            header("Location: web.php?action=login&error=" . urlencode($e->getMessage()));
            exit;
        }
    }



    // Tìm hoặc tạo user từ OAuth data
    private function findOrCreateOAuthUser($provider, $userInfo) {
        $userModel = new User();
        
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
                $userModel->linkOAuthAccount($userByEmail['user_id'], $provider, $userInfo['id']);
                return $userByEmail;
            }
        }

        // Tạo user mới
        $userModel->full_name = $userInfo['name'] ?? 'User';
        $userModel->email = $userInfo['email'] ?? '';
        $userModel->phone = '';
        $userModel->birthday = '';
        $userModel->address = '';
        $userModel->password = bin2hex(random_bytes(16)); // Random password
        $userModel->role = 'customer';
        $userModel->status = 'active';
        
        if ($userModel->registerOAuth($provider, $userInfo['id'])) {
            // Lấy user vừa tạo
            return $userModel->findByEmail($userModel->email);
        }

        return null;
    }
}
?>
