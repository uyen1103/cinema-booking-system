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
<<<<<<< HEAD
            <div class="form-row">
                <div class="form-group">
                    <label>Họ và Tên</label>
                    <input type="text" name="full_name" placeholder="Nguyễn Văn A" required class="form-control" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="email@example.com" required class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                </div>
            </div>

            <div class="form-row">
=======
            <div class="form-group">
                <label>Họ và Tên</label>
                <input type="text" name="full_name" placeholder="Nguyễn Văn A" required class="form-control" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="email@example.com" required class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                </div>
>>>>>>> 79d8d1d56f94b32a57937290034834493747c163
                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input type="tel" name="phone" placeholder="0901234567" required class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                </div>
<<<<<<< HEAD
=======
            </div>

            <div class="form-row">
>>>>>>> 79d8d1d56f94b32a57937290034834493747c163
                <div class="form-group">
                    <label>Ngày sinh</label>
                    <input type="date" name="birthday" class="form-control" value="<?= htmlspecialchars($user['birthday'] ?? '') ?>">
                </div>
<<<<<<< HEAD
            </div>

            <div class="form-row">
=======
>>>>>>> 79d8d1d56f94b32a57937290034834493747c163
                <div class="form-group">
                    <label>Địa chỉ</label>
                    <input type="text" name="address" placeholder="Quận 1, TP. HCM" class="form-control" value="<?= htmlspecialchars($user['address'] ?? '') ?>">
                </div>
<<<<<<< HEAD
                <div class="form-group">
                    <label>Số tài khoản ngân hàng</label>
                    <input type="text" name="bank_account" placeholder="Vd: 0123456789" class="form-control" value="<?= htmlspecialchars($user['bank_account'] ?? '') ?>">
                </div>
            </div>

            <hr style="margin: 30px 0; border: none; border-top: 1px solid #ddd;">
            <h3 style="margin-bottom: 20px; font-size: 16px;">Đổi mật khẩu (nếu muốn)</h3>

            <div class="form-row">
                <div class="form-group">
                    <label>Mật khẩu hiện tại</label>
                    <input type="password" name="current_password" placeholder="Nhập mật khẩu hiện tại" class="form-control">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Mật khẩu mới</label>
                    <input type="password" name="new_password" placeholder="Nhập mật khẩu mới" class="form-control">
                </div>
                <div class="form-group">
                    <label>Xác nhận mật khẩu mới</label>
                    <input type="password" name="confirm_password" placeholder="Xác nhận mật khẩu mới" class="form-control">
                </div>
            </div>

            <small style="display: block; margin: 10px 0; color: #666;">
                Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt.
            </small>

=======
            </div>

>>>>>>> 79d8d1d56f94b32a57937290034834493747c163
            <button type="submit" class="btn-primary">Lưu thay đổi</button>
        </form>

        <div class="auth-links">
            <p><a href="web.php?action=profile">Quay lại trang thông tin</a></p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>