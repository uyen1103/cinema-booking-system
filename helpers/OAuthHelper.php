<?php

// xử lý đăng nhập Google
class OAuthHelper {
    private $google_client_id;
    private $google_client_secret;
    private $google_redirect_uri;

    public function __construct() {
        $this->loadEnv();
        $this->loadConfig();
    }

    // đọc file .env
    private function loadEnv() {
        $env_file = __DIR__ . '/../.env';

        if (!file_exists($env_file)) return;

        $lines = file($env_file);
        foreach ($lines as $line) {
            if (strpos($line, '#') === 0 || strpos($line, '=') === false) continue;

            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }

    // lấy config
    private function loadConfig() {
        $this->google_client_id = getenv('GOOGLE_CLIENT_ID');
        $this->google_client_secret = getenv('GOOGLE_CLIENT_SECRET');
        $this->google_redirect_uri = getenv('GOOGLE_REDIRECT_URI');
    }

    // tạo link login Google
    public function getGoogleAuthUrl() {
        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth_state'] = $state;

        $params = [
            'client_id' => $this->google_client_id,
            'redirect_uri' => $this->google_redirect_uri,
            'response_type' => 'code',
            'scope' => 'openid profile email',
            'state' => $state
        ];

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }

    // lấy access token
    public function getGoogleAccessToken($code) {
        $url = 'https://oauth2.googleapis.com/token';

        $data = [
            'client_id' => $this->google_client_id,
            'client_secret' => $this->google_client_secret,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->google_redirect_uri
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    // lấy info user
    public function getGoogleUserInfo($accessToken) {
        $url = 'https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $accessToken;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}