<?php

// xử lý đăng nhập Google
class OAuthHelper {
    // Các biến lưu trữ cấu hình Google OAuth
    private $google_client_id;
    private $google_client_secret;
    private $google_redirect_uri;

    // Constructor: Gọi loadEnv() và loadConfig() khi khởi tạo
    public function __construct() {
        $this->loadEnv();      // Đọc file .env
        $this->loadConfig();   // Lấy config từ environment
    }

    // Hàm loadEnv(): Đọc file .env để lấy các biến môi trường
    // File .env chứa: GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, GOOGLE_REDIRECT_URI
    private function loadEnv() {
        $env_file = __DIR__ . '/../.env'; // Đường dẫn đến file .env

        // Nếu file không tồn tại thì bỏ qua
        if (!file_exists($env_file)) return;

        // Đọc từng dòng trong file .env
        $lines = file($env_file);
        foreach ($lines as $line) {
            // Bỏ qua dòng comment (bắt đầu bằng #) và dòng không có =
            if (strpos($line, '#') === 0 || strpos($line, '=') === false) continue;

            // Tách key và value theo dấu =
            list($key, $value) = explode('=', $line, 2);
            // Lưu vào environment variable bằng putenv()
            putenv(trim($key) . '=' . trim($value));
        }
    }

    // Hàm loadConfig(): Lấy các giá trị từ environment variable
    private function loadConfig() {
        $this->google_client_id = getenv('GOOGLE_CLIENT_ID');
        $this->google_client_secret = getenv('GOOGLE_CLIENT_SECRET');
        $this->google_redirect_uri = getenv('GOOGLE_REDIRECT_URI');
    }

    // Hàm getGoogleAuthUrl(): Tạo URL để chuyển hướng sang Google đăng nhập
    // Trả về URL hoàn chỉnh để redirect
    public function getGoogleAuthUrl() {
        // Tạo random state để bảo mật chống CSRF
        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth_state'] = $state; // Lưu vào session để verify sau

        // Các tham số cho Google OAuth
        $params = [
            'client_id' => $this->google_client_id,      // Client ID từ Google Developer Console
            'redirect_uri' => $this->google_redirect_uri, // URL callback sau khi đăng nhập
            'response_type' => 'code',                    // Yêu cầu trả về authorization code
            'scope' => 'openid profile email',           // Quyền truy cập: identity, profile, email
            'state' => $state                             // State token để verify
        ];

        // Build URL với query string
        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }

    // Hàm getGoogleAccessToken(): Đổi authorization code lấy access token
    // $code: Authorization code nhận được từ Google callback
    public function getGoogleAccessToken($code) {
        $url = 'https://oauth2.googleapis.com/token'; // Endpoint lấy token

        // Dữ liệu gửi đi dạng POST
        $data = [
            'client_id' => $this->google_client_id,
            'client_secret' => $this->google_client_secret,
            'code' => $code,                                    // Authorization code
            'grant_type' => 'authorization_code',               // Loại grant
            'redirect_uri' => $this->google_redirect_uri        // Phải khớp với lúc gọi auth
        ];

        // Sử dụng cURL để gọi API
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);                  // Method POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); // Body request
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);        // Trả về string thay vì in ra

        $response = curl_exec($ch);
        curl_close($ch);

        // Parse JSON response thành mảng
        return json_decode($response, true);
    }

    // Hàm getGoogleUserInfo(): Lấy thông tin user từ Google
    // $accessToken: Token đã lấy được ở bước trước
    public function getGoogleUserInfo($accessToken) {
        // Gọi Google UserInfo API
        $url = 'https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $accessToken;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}