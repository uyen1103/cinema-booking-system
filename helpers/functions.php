<?php
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function isAdmin(): bool {
    return isLoggedIn() && isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'staff'], true);
}

function h($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function format_currency($amount): string {
    return number_format((float) $amount, 0, ',', '.') . ' đ';
}

function format_date(?string $date, string $format = 'd/m/Y'): string {
    if (empty($date) || $date === '0000-00-00') {
        return '--';
    }

    $timestamp = strtotime($date);
    return $timestamp ? date($format, $timestamp) : '--';
}

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
        if ($value !== null && $value != '') {
            $query[$key] = $value;
        }
    }
    return $entry . (!empty($query) ? '?' . http_build_query($query) : '');
}

function admin_url(string $action = 'admin_dashboard', array $params = []): string {
    return app_url($action, $params, 'index.php');
}

function current_admin_name(): string {
    return $_SESSION['full_name'] ?? 'Admin User';
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