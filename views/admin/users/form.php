<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="admin-page-title"><?= $pageTitle ?></h3>
</div>

<div class="admin-card admin-form-card"><div class="admin-card__body">
    <form action="<?= h(admin_url(isset($user) ? 'admin_update_user' : 'admin_store_user')) ?>" method="POST">
        
        <?php if(isset($user)): ?>
            <input type="hidden" name="id" value="<?= $user['id'] ?>">
        <?php endif; ?>

        <input type="hidden" name="role" value="<?= isset($user) ? $user['role'] : (isset($_GET['action']) && $_GET['action'] == 'create_employee' ? 2 : 3) ?>">

        <div class="admin-form-grid admin-form-grid--2">
            <div>
                <label class="admin-form-label">Họ và tên</label>
                <input type="text" name="fullname" class="admin-input" value="<?= isset($user) ? htmlspecialchars($user['fullname']) : '' ?>" required>
            </div>
            
            <div>
                <label class="admin-form-label">Số điện thoại</label>
                <input type="text" name="phone" class="admin-input" value="<?= isset($user) ? htmlspecialchars($user['phone']) : '' ?>" required>
            </div>
        </div>

        <div>
            <label class="admin-form-label">Email đăng nhập</label>
            <input type="email" name="email" class="admin-input <?= isset($user) ? 'admin-readonly' : '' ?>" value="<?= isset($user) ? htmlspecialchars($user['email']) : '' ?>" <?= isset($user) ? 'readonly' : 'required' ?>>
            <?php if(isset($user)): ?>
                <small class="text-danger">* Không thể thay đổi email sau khi tạo để đảm bảo đăng nhập.</small>
            <?php endif; ?>
        </div>

        <?php if(!isset($user)): ?>
            <div>
                <label class="admin-form-label">Mật khẩu</label>
                <input type="password" name="password" class="admin-input" required>
            </div>
        <?php endif; ?>

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="admin-btn admin-btn--primary">
                <i class="fa-solid fa-floppy-disk me-2"></i> Lưu dữ liệu
            </button>
            <a href="javascript:history.back()" class="admin-btn admin-btn--light">Hủy bỏ</a>
        </div>
    </form>
</div></div>