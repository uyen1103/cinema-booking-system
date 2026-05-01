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
                    <?php if (!isAdmin()): ?><span class="badge-membership">Thành viên bạc</span><?php else: ?><span class="badge-membership">Nhân sự hệ thống</span><?php endif; ?>
                </div>
            </div>

            <nav class="sidebar-nav">
                <a href="<?= h(account_profile_url()) ?>" class="profile-sidebar-item">
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
                <a href="<?= h(account_change_password_url()) ?>" class="profile-sidebar-item active">
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
                <a href="<?= h(account_logout_url()) ?>" class="profile-sidebar-item">
                    <i class="ri-logout-box-r-line"></i><span>Đăng xuất</span>
                </a>
            </nav>
        </aside>

        <main class="profile-main-content">
            <header>
                <h1 class="page-title">Đổi mật khẩu</h1>
                <p class="page-subtitle">Thay đổi mật khẩu để bảo vệ tài khoản của bạn</p>
            </header>

            <?php if (!empty($errors)): ?>
                <div class="profile-alert error-alert">
                    <?php foreach ($errors as $error): ?>
                        <p>❌ <?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="password-form-container" style="max-width: 700px; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                <form method="POST" class="auth-form">
                    <div class="form-group" style="margin-bottom: 25px;">
                        <label style="display: block; margin-bottom: 10px; font-weight: 500; color: #333; font-size: 14px;">Mật khẩu hiện tại</label>
                        <div style="position: relative;">
                            <input type="password" name="current_password" placeholder="Nhập mật khẩu hiện tại" class="form-control password-input" style="padding-right: 40px;" required>
                            <button type="button" class="password-toggle" onclick="togglePassword(this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #999; font-size: 18px; padding: 0; transition: color 0.3s;">
                                <i class="ri-eye-line"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 25px;">
                        <label style="display: block; margin-bottom: 10px; font-weight: 500; color: #333; font-size: 14px;">Mật khẩu mới</label>
                        <div style="position: relative;">
                            <input type="password" name="new_password" placeholder="Nhập mật khẩu mới" class="form-control password-input" style="padding-right: 40px;" required>
                            <button type="button" class="password-toggle" onclick="togglePassword(this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #999; font-size: 18px; padding: 0; transition: color 0.3s;">
                                <i class="ri-eye-line"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 30px;">
                        <label style="display: block; margin-bottom: 10px; font-weight: 500; color: #333; font-size: 14px;">Xác nhận mật khẩu mới</label>
                        <div style="position: relative;">
                            <input type="password" name="confirm_password" placeholder="Xác nhận mật khẩu mới" class="form-control password-input" style="padding-right: 40px;" required>
                            <button type="button" class="password-toggle" onclick="togglePassword(this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #999; font-size: 18px; padding: 0; transition: color 0.3s;">
                                <i class="ri-eye-line"></i>
                            </button>
                        </div>
                    </div>

                    <div style="background: #f8f8f8; padding: 20px; border-radius: 6px; margin-bottom: 30px; border-left: 4px solid #e84c3d;">
                        <p style="margin: 0 0 12px 0; font-weight: 600; color: #333; font-size: 13px;">Yêu cầu mật khẩu:</p>
                        <ul style="margin: 0; padding-left: 20px; font-size: 13px; color: #666; line-height: 1.8;">
                            <li>Ít nhất 8 ký tự</li>
                            <li>Chứa chữ hoa (A-Z)</li>
                            <li>Chứa chữ thường (a-z)</li>
                            <li>Chứa số (0-9)</li>
                            <li>Chứa ký tự đặc biệt (!@#$%^&*...)</li>
                        </ul>
                    </div>

                    <button type="submit" class="update-profile-btn" style="width: 100%; padding: 12px 20px; font-size: 15px; font-weight: 600;">
                        <i class="ri-save-line"></i>Cập nhật mật khẩu
                    </button>
                </form>

                <div style="margin-top: 18px; text-align: center;">
                    <a href="<?= h(account_profile_url()) ?>" style="color: #e84c3d; text-decoration: none; font-weight: 500; font-size: 14px;">
                        <i class="ri-arrow-left-line"></i>Quay lại trang thông tin
                    </a>
                </div>
            </div>
        </main>

    </div>
</div>

<script>
function togglePassword(button) {
    event.preventDefault();
    const input = button.parentElement.querySelector('.password-input');
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'ri-eye-off-line';
    } else {
        input.type = 'password';
        icon.className = 'ri-eye-line';
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
