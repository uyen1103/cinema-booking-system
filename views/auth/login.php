<?php require_once __DIR__ . '/../layouts/header_auth.php'; ?>

<?php
$loginTitle = $loginTitle ?? 'Đăng Nhập';
$loginSubtitle = $loginSubtitle ?? 'Chào mừng bạn đến với Cinema Central';
$loginButtonLabel = $loginButtonLabel ?? 'Đăng Nhập';
$loginAction = $loginAction ?? '';
$showRegisterLink = $showRegisterLink ?? true;
$showGoogleLogin = $showGoogleLogin ?? true;
$registerUrl = $registerUrl ?? app_url('register');
$googleLoginUrl = $googleLoginUrl ?? app_url('login-google');
$showForgotPasswordLink = $showForgotPasswordLink ?? true;
$loginHelpText = $loginHelpText ?? '';
?>

<link rel="stylesheet" href="assets/css/auth.css">

<div class="auth-container">
    <div class="auth-box">

        <header class="auth-header">
            <h2><?= htmlspecialchars($loginTitle) ?></h2>
            <p class="auth-subtitle"><?= htmlspecialchars($loginSubtitle) ?></p>
            <?php if ($loginHelpText !== ""): ?><p class="auth-subtitle" style="font-size:13px;"><?= htmlspecialchars($loginHelpText) ?></p><?php endif; ?>
        </header>

        <?php if (isset($_GET['message'])): ?>
            <div class="success-message">✅ <?= htmlspecialchars($_GET['message']) ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p>❌ <?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="auth-form" action="<?= h($loginAction) ?>">
            <div class="form-group">
                <label for="email">Email hoặc Số điện thoại</label>
                <input type="text" id="email" name="email" placeholder="Nhập email hoặc số điện thoại" required class="form-control">
            </div>

            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <div class="password-input-container">
                    <input type="password" id="password" name="password" placeholder="••••••••" required class="form-control" maxlength="50">
                    <button type="button" class="password-toggle" onclick="togglePass('password')">👁️</button>
                </div>
            </div>

            <button type="submit" class="btn-primary"><?= htmlspecialchars($loginButtonLabel) ?></button>
        </form>

        <div class="auth-links">
            <?php if ($showForgotPasswordLink): ?><a href="<?= h(app_url('forgot-password')) ?>">Quên mật khẩu?</a><?php endif; ?>
            <?php if ($showRegisterLink): ?>
                <span class="divider">•</span>
                <a href="<?= h($registerUrl) ?>">Đăng ký ngay</a>
            <?php endif; ?>
        </div>

        <?php if ($showGoogleLogin): ?>
        <div class="social-divider"><span>Hoặc đăng nhập với</span></div>

        <div class="social-login">
            <a href="<?= h($googleLoginUrl) ?>" class="social-btn google">
                <svg viewBox="0 0 24 24" width="20"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                Google
            </a>
        </div>
        <?php endif; ?>

    </div>
</div>

<script>
function togglePass(id) {
    const el = document.getElementById(id);
    const btn = el.nextElementSibling;
    el.type = el.type === 'password' ? 'text' : 'password';
    btn.textContent = el.type === 'password' ? '👁️' : '🙈';
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>