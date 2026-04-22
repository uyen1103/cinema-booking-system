<!DOCTYPE html>
<html lang="vi">
<?php require_once __DIR__ . '/../../helpers/functions.php'; ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cinema Central - Hệ thống đặt vé xem phim</title>
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/movie.css">
    <link rel="stylesheet" href="assets/css/booking.css">
    <link rel="stylesheet" href="assets/css/checkout.css">
    <link rel="stylesheet" href="assets/css/pages.css">
</head>
<body>
<?php $currentAction = $_GET['action'] ?? ''; ?>
    <header class="header">
        <div class="container">
            <div class="logo">
                <a href="<?= h(isAdmin() ? admin_url('admin_dashboard') : app_url('home')) ?>" class="logo-link">
                    <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="32" height="32" rx="6" fill="#e71930"/>
                        <rect x="6" y="8" width="20" height="16" rx="2" fill="white"/>
                        <circle cx="10" cy="12" r="1" fill="#e71930"/>
                        <circle cx="16" cy="12" r="1" fill="#e71930"/>
                        <circle cx="22" cy="12" r="1" fill="#e71930"/>
                        <rect x="8" y="16" width="16" height="6" rx="1" fill="#e71930"/>
                    </svg>
                    <span><?= isAdmin() ? 'CINEMA CENTRAL ADMIN' : 'CINEMA CENTRAL' ?></span>
                </a>
            </div>
            <div class="header-toolbar">
                <?php if (isAdmin()): ?>
                    <nav class="nav nav-main">
                        <div class="nav-group nav-links">
                            <a href="<?= h(admin_url('admin_dashboard')) ?>" class="nav-link <?= in_array($currentAction, ['dashboard','admin_dashboard'], true) ? 'active' : '' ?>">DASHBOARD</a>
                            <a href="<?= h(admin_url('admin_orders')) ?>" class="nav-link <?= in_array($currentAction, ['orders','admin_orders'], true) ? 'active' : '' ?>">HÓA ĐƠN</a>
                            <a href="<?= h(admin_url('admin_reports')) ?>" class="nav-link <?= in_array($currentAction, ['reports','admin_reports'], true) ? 'active' : '' ?>">BÁO CÁO</a>
                        </div>
                    </nav>
                    <div class="header-right">
                        <div class="nav-group nav-actions">
                            <a href="<?= h(account_profile_url()) ?>" class="nav-link nav-account <?= in_array($currentAction, ['profile', 'admin_profile'], true) ? 'active' : '' ?>">TÀI KHOẢN</a>
                            <a href="<?= h(account_logout_url()) ?>" class="nav-link">ĐĂNG XUẤT</a>
                        </div>
                    </div>
                <?php else: ?>
                    <nav class="nav nav-main">
                        <div class="nav-group nav-links">
                            <a href="<?= h(app_url('home')) ?>" class="nav-link <?= in_array($currentAction, ['', 'home'], true) ? 'active' : '' ?>">PHIM</a>
                            <a href="<?= h(app_url('theaters')) ?>" class="nav-link <?= $currentAction === 'theaters' ? 'active' : '' ?>">RẠP</a>
                            <a href="<?= h(app_url('promotions')) ?>" class="nav-link <?= $currentAction === 'promotions' ? 'active' : '' ?>">KHUYẾN MÃI</a>
                        </div>
                    </nav>
                    <div class="header-right">
                        <form action="<?= h(app_url()) ?>" method="GET" class="search-form">
                            <button type="submit" class="search-button" aria-label="Tìm kiếm">🔍</button>
                            <input type="search" name="q" placeholder="Tìm kiếm phim, rạp..." aria-label="Tìm kiếm phim, rạp" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" />
                        </form>
                        <div class="nav-group nav-actions">
                            <?php if (isCustomerLoggedIn()): ?>
                                <a href="<?= h(account_profile_url()) ?>" class="nav-link nav-account <?= $currentAction === 'profile' ? 'active' : '' ?>">TÀI KHOẢN</a>
                                <a href="<?= h(account_logout_url()) ?>" class="nav-link">ĐĂNG XUẤT</a>
                            <?php else: ?>
                                <a href="<?= h(app_url('login')) ?>" class="nav-link">ĐĂNG NHẬP</a>
                                <a href="<?= h(app_url('register')) ?>" class="nav-link">ĐĂNG KÝ</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <main class="main-content">
