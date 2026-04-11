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
                    <span class="badge-membership">Thành viên bạc</span>
                </div>
            </div>

            <nav class="sidebar-nav">
                <a href="<?= h(app_url('profile')) ?>" class="profile-sidebar-item">
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
                <a href="<?= h(app_url('link-bank-account')) ?>" class="profile-sidebar-item active">
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
                <h1 class="page-title">Liên kết tài khoản ngân hàng</h1>
                <p class="page-subtitle">Cập nhật thông tin tài khoản ngân hàng và ví điện tử của bạn</p>
            </header>

            <?php if (!empty($errors)): ?>
                <div class="profile-alert error-alert">
                    <?php foreach ($errors as $error): ?>
                        <p>❌ <?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($_GET['message'])): ?>
                <div class="profile-alert success-alert"><?= htmlspecialchars($_GET['message']) ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= h(app_url('link-bank-account')) ?>" class="profile-form">
                <div class="form-section">
                    <h3>Thông tin tài khoản ngân hàng</h3>
                    
                    <div class="form-group">
                        <label for="bank_account">Số tài khoản ngân hàng</label>
                        <input type="text" id="bank_account" name="bank_account" placeholder="Vd: 0123456789" class="form-control" value="<?= htmlspecialchars($user['bank_account'] ?? '') ?>">
                        <small class="form-text text-muted">Nhập số tài khoản ngân hàng của bạn</small>
                    </div>

                    <div class="form-group">
                        <label for="e_wallet_account">Số tài khoản ví điện tử</label>
                        <input type="text" id="e_wallet_account" name="e_wallet_account" placeholder="Vd: 0123456789" class="form-control" value="<?= htmlspecialchars($user['e_wallet_account'] ?? '') ?>">
                        <small class="form-text text-muted">Nhập số ví điện tử của bạn</small>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Lưu thay đổi</button>
                    <a href="<?= h(app_url('profile')) ?>" class="btn-secondary btn-back">
                        <i class="ri-arrow-go-back-line"></i><span>Quay lại</span>
                    </a>
                </div>
            </form>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
