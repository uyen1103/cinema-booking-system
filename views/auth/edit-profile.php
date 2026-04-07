<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<link rel="stylesheet" href="assets/css/auth.css">

<div class="auth-container">
    <div class="auth-box register-box">

        <header class="auth-header">
            <h2>Cập nhật thông tin</h2>
            <p class="auth-subtitle">Điền đầy đủ thông tin để cập nhật tài khoản</p>
        </header>

        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p>❌ <?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="auth-form" action="web.php?action=edit-profile">
            <div class="form-group">
                <label>Họ và Tên</label>
                <input type="text" name="full_name" placeholder="Nguyễn Văn A" required class="form-control" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="email@example.com" required class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input type="tel" name="phone" placeholder="0901234567" required class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Ngày sinh</label>
                    <input type="date" name="birthday" class="form-control" value="<?= htmlspecialchars($user['birthday'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Địa chỉ</label>
                    <input type="text" name="address" placeholder="Quận 1, TP. HCM" class="form-control" value="<?= htmlspecialchars($user['address'] ?? '') ?>">
                </div>
            </div>

            <button type="submit" class="btn-primary">Lưu thay đổi</button>
        </form>

        <div class="auth-links">
            <p><a href="web.php?action=profile">Quay lại trang thông tin</a></p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>