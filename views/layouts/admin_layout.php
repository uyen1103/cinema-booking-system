<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle ?? 'Admin') ?> - Cinema Central</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-body">
<?php $flash = get_flash(); ?>

<aside class="admin-sidebar">
    <div class="admin-sidebar__brand">
        <a href="<?= h(admin_url('admin_dashboard')) ?>" class="admin-brand">
            <div class="admin-brand__logo">
                <i class="fa-solid fa-clapperboard"></i>
            </div>
            <div>
                <div class="admin-brand__title">CINEMA.CENTRAL</div>
                <div class="admin-brand__subtitle">Management System</div>
            </div>
        </a>
    </div>

    <nav class="admin-sidebar__menu">
        <div class="admin-menu__label">Quản trị</div>
        <a href="<?= h(admin_url('admin_dashboard')) ?>" class="admin-nav-link <?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?>">
            <i class="fa-solid fa-chart-line"></i>
            <span>Bảng điều khiển</span>
        </a>
        <a href="<?= h(admin_url('admin_movies')) ?>" class="admin-nav-link <?= ($activeMenu ?? '') === 'movies' ? 'active' : '' ?>">
            <i class="fa-solid fa-film"></i>
            <span>Quản lý phim</span>
        </a>
        <a href="<?= h(admin_url('admin_showtimes')) ?>" class="admin-nav-link <?= ($activeMenu ?? '') === 'showtimes' ? 'active' : '' ?>">
            <i class="fa-regular fa-clock"></i>
            <span>Quản lý suất chiếu</span>
        </a>
        <a href="<?= h(admin_url('admin_rooms')) ?>" class="admin-nav-link <?= ($activeMenu ?? '') === 'rooms' ? 'active' : '' ?>">
            <i class="fa-solid fa-couch"></i>
            <span>Quản lý phòng chiếu và ghế</span>
        </a>

        <div class="admin-menu__label">Vận hành</div>
        <a href="<?= h(admin_url('admin_employees')) ?>" class="admin-nav-link <?= ($activeMenu ?? '') === 'employee' ? 'active' : '' ?>">
            <i class="fa-solid fa-user-tie"></i>
            <span>Quản lý nhân viên</span>
        </a>
        <a href="<?= h(admin_url('admin_customers')) ?>" class="admin-nav-link <?= ($activeMenu ?? '') === 'customer' ? 'active' : '' ?>">
            <i class="fa-solid fa-users"></i>
            <span>Quản lý khách hàng</span>
        </a>
        <a href="<?= h(admin_url('admin_promotions')) ?>" class="admin-nav-link <?= ($activeMenu ?? '') === 'promotions' ? 'active' : '' ?>">
            <i class="fa-solid fa-tags"></i>
            <span>Quản lý khuyến mãi</span>
        </a>
        <a href="<?= h(admin_url('admin_orders')) ?>" class="admin-nav-link <?= ($activeMenu ?? '') === 'orders' ? 'active' : '' ?>">
            <i class="fa-solid fa-file-invoice-dollar"></i>
            <span>Quản lý hóa đơn</span>
        </a>
        <a href="<?= h(admin_url('admin_reports')) ?>" class="admin-nav-link <?= ($activeMenu ?? '') === 'reports' ? 'active' : '' ?>">
            <i class="fa-solid fa-chart-pie"></i>
            <span>Tạo báo cáo thống kê</span>
        </a>
    </nav>

    <div class="admin-sidebar__user">
        <a href="<?= h(app_url('profile')) ?>" class="admin-user-card">
            <img src="<?= h(current_admin_avatar()) ?>" alt="Avatar">
            <div class="flex-grow-1 overflow-hidden">
                <div class="fw-bold text-truncate"><?= h(current_admin_name()) ?></div>
                <div class="text-muted small"><?= h(current_admin_role()) ?></div>
            </div>
            <i class="fa-solid fa-arrow-right-from-bracket text-muted"></i>
        </a>
    </div>
</aside>

<div class="admin-main">
    <header class="admin-topbar">
        <div class="admin-topbar__row">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item small text-muted">Trang quản trị</li>
                        <li class="breadcrumb-item active small fw-bold" aria-current="page"><?= h($breadcrumb ?? 'Dashboard') ?></li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted small d-none d-md-inline">Kết nối XAMPP / PHP</span>
                <div class="position-relative">
                    <button class="admin-btn admin-btn--light admin-btn--icon" type="button">
                        <i class="fa-regular fa-bell"></i>
                    </button>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">!</span>
                </div>
            </div>
        </div>
    </header>

    <main class="admin-content">
        <?php if ($flash): ?>
            <div class="alert alert-<?= h($flash['type']) ?> alert-dismissible fade show admin-card mb-4" role="alert">
                <?= h($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
            </div>
        <?php endif; ?>

        <?= $content ?? '' ?>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
