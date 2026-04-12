<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<link rel="stylesheet" href="assets/css/profile.css">
<link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

<div class="profile-page-container">
    <div class="profile-content-wrapper">

        <aside class="profile-sidebar">
            <div class="sidebar-user-card">
                <div class="avatar-box">
                    <?= strtoupper(mb_substr($user['full_name'] ?? 'U', 0, 2)) ?>
                </div>
                <div class="user-meta">
                    <h3 class="user-name-sidebar"><?= htmlspecialchars($user['full_name'] ?? 'Người dùng') ?></h3>
                    <?php if (!isAdmin()): ?>
                    <span class="badge-membership">Thành viên bạc</span>
                    <?php endif; ?>
                </div>
            </div>

            <nav class="sidebar-nav">
                <a href="<?= h(app_url('profile')) ?>" class="profile-sidebar-item active">
                    <i class="ri-user-smile-line"></i><span>Thông tin cá nhân</span>
                </a>
                <?php if (!isAdmin()): ?>
                <a href="<?= h(app_url('history')) ?>" class="profile-sidebar-item">
                    <i class="ri-history-line"></i><span>Lịch sử đặt vé</span>
                </a>
                <a href="<?= h(app_url('vouchers')) ?>" class="profile-sidebar-item">
                    <i class="ri-coupon-2-line"></i><span>Voucher của tôi</span>
                </a>
                <?php endif; ?>
                <a href="<?= h(app_url('change-password')) ?>" class="profile-sidebar-item">
                    <i class="ri-lock-password-line"></i><span>Đổi mật khẩu</span>
                </a>
                <?php if (!isAdmin()): ?>
                <a href="<?= h(app_url('link-bank-account')) ?>" class="profile-sidebar-item">
                    <i class="ri-bank-card-line"></i><span>Liên kết tài khoản ngân hàng</span>
                </a>
                <?php endif; ?>
                <?php if (isAdmin()): ?>
                <a href="<?= h(admin_url('dashboard')) ?>" class="profile-sidebar-item">
                    <i class="ri-dashboard-line"></i><span>Quay về Admin</span>
                </a>
                <?php endif; ?>
                <div class="nav-divider"></div>
                <a href="<?= h(app_url('logout')) ?>" class="profile-sidebar-item">
                    <i class="ri-logout-box-r-line"></i><span>Đăng xuất</span>
                </a>
            </nav>
        </aside>

        <main class="profile-main-content">
            <header>
                <h1 class="page-title">Thông tin cá nhân</h1>
                <p class="page-subtitle">Thông tin chi tiết tài khoản của bạn</p>
            </header>

            <?php if (!empty($_GET['message'])): ?>
                <div class="profile-alert success-alert"><?= htmlspecialchars($_GET['message']) ?></div>
            <?php endif; ?>

            <div class="profile-hero">
                <div class="avatar-main">
                    <?= strtoupper(mb_substr($user['full_name'] ?? 'U', 0, 1)) ?>
                </div>
                <div>
                    <h2 class="user-full-name"><?= htmlspecialchars($user['full_name'] ?? 'Họ và Tên') ?></h2>
                    <?php if (!isAdmin()): ?>
                    <span class="badge-membership">Thành viên bạc</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="info-grid">
                <div class="info-row">
                    <div class="info-group">
                        <span class="info-label">Họ và Tên</span>
                        <p class="info-value"><?= htmlspecialchars($user['full_name'] ?? '---') ?></p>
                    </div>
                    <div class="info-group">
                        <span class="info-label">Email</span>
                        <p class="info-value"><?= htmlspecialchars($user['email'] ?? '---') ?></p>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-group">
                        <span class="info-label">Số điện thoại</span>
                        <p class="info-value"><?= htmlspecialchars($user['phone'] ?? 'Chưa cập nhật') ?></p>
                    </div>
                    <div class="info-group">
                        <span class="info-label">Ngày sinh</span>
                        <p class="info-value"><?= !empty($user['birthday']) ? date('d/m/Y', strtotime($user['birthday'])) : 'Chưa cập nhật' ?></p>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-group">
                        <span class="info-label">Mại nhập hoà đơn</span>
                        <p class="info-value" style="width: 100%;"><?= htmlspecialchars($user['address'] ?? 'Chưa cập nhật') ?></p>
                    </div>
                </div>

                <?php if (!isAdmin()): ?>
                <div class="info-row">
                    <div class="info-group">
                        <span class="info-label">Số tài khoản ngân hàng</span>
                        <p class="info-value"><?= htmlspecialchars($user['bank_account'] ?? 'Chưa cập nhật') ?></p>
                    </div>
                    <div class="info-group">
                        <span class="info-label">Số tài khoản ví điện tử</span>
                        <p class="info-value"><?= htmlspecialchars($user['e_wallet_account'] ?? 'Chưa cập nhật') ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <a href="<?= h(app_url('edit-profile')) ?>" class="update-profile-btn">
                <i class="ri-edit-2-fill"></i>Cập nhật thông tin cá nhân
            </a>
        </main>

    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>