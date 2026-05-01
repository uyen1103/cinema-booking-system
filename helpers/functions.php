<?php
// Functions.php - Các hàm hỗ trợ (Helper Functions)
// Được include ở đầu các file view để sử dụng các hàm tiện ích

// NHÓM 1: HÀM KIỂM TRA ĐĂNG NHẬP VÀ QUYỀN

// currentAuthScope(): Lấy scope hiện tại từ session ('customer' hoặc 'employee')
function currentAuthScope(): string {
    return (string) ($_SESSION['auth_scope'] ?? '');
}

// isCustomerLoggedIn(): Kiểm tra khách hàng đã đăng nhập chưa
// Cần có session customer_id và auth_scope = 'customer'
function isCustomerLoggedIn(): bool {
    return isset($_SESSION['customer_id']) && currentAuthScope() === 'customer';
}

// isEmployeeLoggedIn(): Kiểm tra nhân viên đã đăng nhập chưa
function isEmployeeLoggedIn(): bool {
    return isset($_SESSION['employee_id']) && currentAuthScope() === 'employee';
}

// currentCustomerId(): Lấy ID khách hàng hiện tại
function currentCustomerId(): int {
    return (int) ($_SESSION['customer_id'] ?? 0);
}

// currentEmployeeId(): Lấy ID nhân viên hiện tại
function currentEmployeeId(): int {
    return (int) ($_SESSION['employee_id'] ?? 0);
}

// isLoggedIn(): Kiểm tra bất kỳ user nào đã đăng nhập chưa
function isLoggedIn(): bool {
    return isCustomerLoggedIn() || isEmployeeLoggedIn();
}

// isAdmin(): Kiểm tra user hiện tại có phải admin không
// Cần: là employee + có role là 'admin' hoặc 'staff'
function isAdmin(): bool {
    return isEmployeeLoggedIn() && isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'staff'], true);
}

// isCustomer(): Kiểm tra có phải khách hàng không
function isCustomer(): bool {
    return isCustomerLoggedIn();
}

// isEmployee(): Kiểm tra có phải nhân viên không
function isEmployee(): bool {
    return isEmployeeLoggedIn();
}


// NHÓM 2: HÀM FORMAT DỮ LIỆU


// h(): Hàm escape HTML - chống XSS (Cross-Site Scripting)
// Convert các ký tự đặc biệt thành HTML entities
function h($value): string {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

// format_currency(): Format số tiền theo kiểu Việt Nam
// VD: 100000 -> "100.000 đ"
function format_currency($amount): string {
    return number_format((float) $amount, 0, ',', '.') . ' đ';
}

// format_date(): Format ngày tháng
// $date: chuỗi ngày (YYYY-MM-DD), $format: định dạng output
function format_date(?string $date, string $format = 'd/m/Y'): string {
    // Trả về '--' nếu ngày rỗng hoặc là '0000-00-00'
    if (empty($date) || $date === '0000-00-00') {
        return '--';
    }

    $timestamp = strtotime($date);
    return $timestamp ? date($format, $timestamp) : '--';
}

// format_datetime(): Format ngày giờ
function format_datetime(?string $dateTime, string $format = 'd/m/Y H:i'): string {
    if (empty($dateTime)) {
        return '--';
    }

    $timestamp = strtotime($dateTime);
    return $timestamp ? date($format, $timestamp) : '--';
}

function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function get_flash(): ?array {
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function status_label(string $value, array $map, string $default = 'Không xác định'): string {
    return $map[$value] ?? $default;
}

function upload_file(array $file, string $directory, array $allowedExtensions, string $prefix = 'file'): ?string {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    $originalName = $file['name'] ?? '';
    $tmpName = $file['tmp_name'] ?? '';

    if ($originalName === '' || $tmpName === '') {
        return null;
    }

    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions, true)) {
        return null;
    }

    $safeBase = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
    $newName = date('YmdHis') . '_' . $prefix . '_' . $safeBase . '.' . $extension;
    $destination = rtrim($directory, '/') . '/' . $newName;

    if (move_uploaded_file($tmpName, $destination)) {
        return $destination;
    }

    return null;
}

function delete_local_file(?string $path, array $protectedFiles = []): void {
    if (empty($path) || in_array($path, $protectedFiles, true)) {
        return;
    }

    if (file_exists($path) && is_file($path)) {
        @unlink($path);
    }
}

function app_url(string $action = '', array $params = [], string $entry = 'index.php'): string {
    $query = [];
    if ($action !== '') {
        $query['action'] = $action;
    }
    foreach ($params as $key => $value) {
        if ($value !== null && $value !== '') {
            $query[$key] = $value;
        }
    }
    return $entry . (!empty($query) ? '?' . http_build_query($query) : '');
}

function customer_url(string $action = 'home', array $params = []): string {
    return app_url($action, $params, 'index.php');
}

function admin_url(string $action = 'admin_dashboard', array $params = []): string {
    return app_url($action, $params, 'index.php');
}

function current_admin_name(): string {
    return $_SESSION['full_name'] ?? 'Nhân sự hệ thống';
}

function current_admin_role(): string {
    $role = $_SESSION['role'] ?? 'admin';
    return match ($role) {
        'admin' => 'Quản trị viên',
        'staff' => 'Nhân viên',
        default => ucfirst($role),
    };
}

function current_admin_avatar(): string {
    return $_SESSION['avatar'] ?? 'assets/images/default-avatar.svg';
}

function account_profile_url(): string {
    return isAdmin() ? admin_url('admin_profile') : customer_url('profile');
}

function account_edit_profile_url(): string {
    return isAdmin() ? admin_url('admin_edit_profile') : customer_url('edit-profile');
}

function account_change_password_url(): string {
    return isAdmin() ? admin_url('admin_change_password') : customer_url('change-password');
}

function account_logout_url(): string {
    return isAdmin() ? admin_url('admin_logout') : customer_url('logout');
}

function account_login_url(): string {
    return customer_url('login');
}

function getPosterUrl($posterUrl): string {
    $poster = trim((string) ($posterUrl ?? ''));
    if ($poster === '') {
        return 'assets/images/default-poster.svg';
    }

    $posterLower = strtolower($poster);
    if (preg_match('#^(?:https?://)#', $posterLower)) {
        return $poster;
    }

    if (strpos($posterLower, 'assets/') === 0) {
        return $poster;
    }

    if (strpos($posterLower, '/assets/') === 0) {
        return ltrim($poster, '/');
    }

    if (strpos($posterLower, 'movies/') === 0) {
        return 'assets/images/' . $poster;
    }

    if (strpos($posterLower, '/movies/') === 0) {
        return 'assets/images/' . ltrim($poster, '/');
    }

    return 'assets/images/movies/' . ltrim($poster, '/');
}
?>