<?php require_once __DIR__ . '/../layouts/header_auth.php'; ?>

<link rel="stylesheet" href="assets/css/auth.css">

<div class="auth-container">
    <div class="auth-box register-box">

        <header class="auth-header">
            <h2>Đăng Ký Tài Khoản</h2>
            <p class="auth-subtitle">Tạo tài khoản để bắt đầu đặt vé xem phim</p>
        </header>

        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p>❌ <?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            <div class="form-group">
                <label>Họ và Tên</label>
                <input type="text" name="full_name" placeholder="Nguyễn Văn A" required class="form-control" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="email@example.com" required class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input type="tel" name="phone" placeholder="0901234567" required class="form-control" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Ngày sinh</label>
                    <input type="date" name="birthday" class="form-control" value="<?= htmlspecialchars($_POST['birthday'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Địa chỉ</label>
                    <input type="text" name="address" placeholder="Quận 1, TP. HCM" class="form-control" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Mật khẩu</label>
                <div class="password-input-container">
                    <input type="password" id="password" name="password" placeholder="••••••••" required class="form-control">
                    <button type="button" class="password-toggle" onclick="togglePass('password')">👁️</button>
                </div>
                <div class="password-tips">
                    <small>• 8-50 ký tự, gồm chữ hoa, chữ thường, số và ký tự đặc biệt</small>
                </div>
            </div>

            <div class="form-group">
                <label>Xác nhận mật khẩu</label>
                <div class="password-input-container">
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="••••••••" required class="form-control">
                    <button type="button" class="password-toggle" onclick="togglePass('confirm_password')">👁️</button>
                </div>
            </div>

            <div class="form-group terms">
                <label class="checkbox-label">
                    <input type="checkbox" required> 
                    <span>Tôi đồng ý với <a href="#">Điều khoản</a> & <a href="#">Bảo mật</a></span>
                </label>
            </div>

            <button type="submit" class="btn-primary">Đăng Ký Ngay</button>
        </form>

        <div class="auth-links">
            <p>Đã có tài khoản? <a href="<?= h(app_url('login')) ?>">Đăng nhập</a></p>
        </div>

        <div class="social-divider"><span>Hoặc đăng ký với</span></div>

        <div class="social-login">
            <a href="<?= h(app_url('login')) ?>-google" class="social-btn">
                <svg viewBox="0 0 24 24" width="20"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                <span>Google</span>
            </a>
        </div>
    </div>
</div>

<script>
function togglePass(id) {
    const input = document.getElementById(id);
    const btn = input.nextElementSibling;
    input.type = input.type === 'password' ? 'text' : 'password';
    btn.textContent = input.type === 'password' ? '👁️' : '🙈';
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>