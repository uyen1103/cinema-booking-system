<?php
// kiểm tra đã đăng nhập chưa
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// kiểm tra có phải admin không
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Chuẩn hóa URL poster phim
function getPosterUrl($posterUrl) {
    $poster = trim((string) ($posterUrl ?? ''));
    if ($poster === '') {
        return 'assets/images/movies/default-movie.jpg';
    }

    $posterLower = strtolower($poster);
    if (preg_match('#^(?:https?://)#', $posterLower)) {
        return $poster;
    }

    if (strpos($posterLower, 'assets/images/') === 0) {
        return $poster;
    }

    if (strpos($posterLower, '/assets/images/') === 0) {
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

