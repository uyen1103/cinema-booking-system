<!DOCTYPE html>
<html lang="vi">
<?php 
// Header dành riêng cho trang Auth (đăng nhập/đăng ký/quên mật khẩu)
// Khác với header.php: không có menu điều hướng, không có tìm kiếm
// Chỉ hiển thị logo và 2 nút Đăng nhập / Đăng ký
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cinema Central - Đăng nhập</title>
    <!-- CSS cho trang auth -->
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/pages.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <!-- Header đơn giản cho trang auth -->
    <header class="header">
        <div class="container">
            <!-- Logo - luôn link về trang chủ -->
            <div class="logo">
                <a href="<?= h(app_url('home')) ?>" class="logo-link">
                    <!-- SVG Logo tương tự header.php -->
                    <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="32" height="32" rx="6" fill="#e71930"/>
                        <rect x="6" y="8" width="20" height="16" rx="2" fill="white"/>
                        <circle cx="10" cy="12" r="1" fill="#e71930"/>
                        <circle cx="16" cy="12" r="1" fill="#e71930"/>
                        <circle cx="22" cy="12" r="1" fill="#e71930"/>
                        <rect x="8" y="16" width="16" height="6" rx="1" fill="#e71930"/>
                    </svg>
                    <span>CINEMA CENTRAL</span>
                </a>
            </div>
            
            <!-- Thanh công cụ: chỉ có 2 nút Đăng nhập và Đăng ký -->
            <div class="header-toolbar">
                <div class="header-right">
                    <div class="nav-group nav-actions">
                        <a href="<?= h(app_url('login')) ?>" class="nav-link">ĐĂNG NHẬP</a>
                        <a href="<?= h(app_url('register')) ?>" class="nav-link">ĐĂNG KÝ</a>
                    </div>
                </div>
            </div>
        </div>
    </header>