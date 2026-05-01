<?php 
require_once __DIR__ . '/../layouts/header_auth.php'; ?>
<link rel="stylesheet" href="assets/css/auth.css">

<!-- Container chính -->
<div class="auth-container">
    <div class="auth-box">

        <header class="auth-header">
            <h2>Khôi phục mật khẩu</h2>
            <p class="auth-subtitle">Vui lòng nhập email đã đăng ký để nhận mã xác thực</p>
        </header>

        <!-- Hiển thị lỗi validation (nếu có) -->
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): // Duyệt mảng lỗi ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Hiển thị thông báo thành công (nếu có) -->
        <?php if (!empty($success)): ?>
            <div class="success-message">
                <?= htmlspecialchars($success) // Hiển thị thông báo thành công ?>
            </div>
        <?php endif; ?>

        <!-- Form gửi email khôi phục -->
        <form method="POST" class="auth-form">
            <!-- Trường nhập email -->
            <div class="form-group">
                <label for="email">Địa chỉ Email</label>
                <!-- type="email" để browser validate định dạng email -->
                <input type="email" id="email" name="email" class="form-control" placeholder="example@email.com" required maxlength="100" value="<?= htmlspecialchars($_POST['email'] ?? '') // Giữ lại giá trị đã nhập ?>">
            </div>

            <!-- Nút gửi mã xác thực -->
            <button type="submit" class="btn-primary">Gửi mã xác nhận</button>
        </form>

        <!-- Link quay lại trang đăng nhập -->
        <div class="auth-links">
            <a href="<?= h(app_url('login')) ?>">Quay lại đăng nhập</a>
        </div>
        
    </div>
</div>

<?php 
// Include footer layout
require_once __DIR__ . '/../layouts/footer.php'; ?>