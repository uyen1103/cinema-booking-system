<?php require_once __DIR__ . '/../layouts/header_auth.php'; ?>

<link rel="stylesheet" href="assets/css/auth.css">

<div class="auth-container">
    <div class="auth-box">
        
        <header class="auth-header">
            <h2>Khôi phục mật khẩu</h2>
            <p class="auth-subtitle">Vui lòng nhập email đã đăng ký để nhận mã xác thực</p>
        </header>

        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="success-message">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            <div class="form-group">
                <label for="email">Địa chỉ Email</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="example@email.com" required maxlength="100" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <button type="submit" class="btn-primary">Gửi mã xác nhận</button>
        </form>

        <div class="auth-links">
            <a href="web.php?action=login">Quay lại đăng nhập</a>
        </div>
        
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>