<?php $isStaff = $userRole === 'staff'; ?>
<div class="admin-page-heading d-flex flex-wrap justify-content-between gap-3">
    <div>
        <h2><?= $isStaff ? 'CHỈNH SỬA NHÂN VIÊN' : 'CHỈNH SỬA KHÁCH HÀNG' ?></h2>
        <p>Cập nhật thông tin chi tiết và trạng thái hồ sơ trong hệ thống.</p>
    </div>
    <a class="admin-btn admin-btn--light" href="<?= h(admin_url($isStaff ? 'admin_employees' : 'admin_customers')) ?>">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Quay lại</span>
    </a>
</div>

<div class="admin-card">
    <div class="admin-card__body">
        <form method="POST" action="<?= h(admin_url('admin_update_user')) ?>" enctype="multipart/form-data" class="admin-form-grid">
            <input type="hidden" name="user_id" value="<?= (int) $user['user_id'] ?>">
            <input type="hidden" name="role" value="<?= h($user['role']) ?>">
            <div class="admin-form-grid admin-form-grid--2">
                <div>
                    <label class="admin-form-label">Ảnh đại diện</label>
                    <div class="admin-avatar-preview mb-3">
                        <img src="<?= h($user['avatar'] ?: 'assets/images/default-avatar.svg') ?>" alt="Avatar">
                    </div>
                    <input class="admin-file" type="file" name="avatar" accept=".jpg,.jpeg,.png,.webp,.svg">
                </div>

                <div class="admin-form-grid">
                    <div class="admin-form-grid admin-form-grid--2">
                        <div>
                            <label class="admin-form-label">Họ và tên</label>
                            <input class="admin-input" type="text" name="full_name" value="<?= h($user['full_name']) ?>" required>
                        </div>
                        <div>
                            <label class="admin-form-label">Email</label>
                            <input class="admin-input" type="email" name="email" value="<?= h($user['email']) ?>" required>
                        </div>
                        <div>
                            <label class="admin-form-label">Số điện thoại</label>
                            <input class="admin-input" type="text" name="phone" value="<?= h($user['phone']) ?>">
                        </div>
                        <div>
                            <label class="admin-form-label">Ngày sinh</label>
                            <input class="admin-input" type="date" name="birthday" value="<?= h($user['birthday']) ?>">
                        </div>
                    </div>

                    <?php if ($isStaff): ?>
                        <div class="admin-form-grid admin-form-grid--2">
                            <div>
                                <label class="admin-form-label">Chức vụ</label>
                                <select class="admin-select" name="position">
                                    <option value="">Chọn chức vụ</option>
                                    <?php foreach ($positions as $position): ?>
                                        <option value="<?= h($position) ?>" <?= ($user['position'] ?? '') === $position ? 'selected' : '' ?>><?= h($position) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="admin-form-label">Cơ sở làm việc</label>
                                <select class="admin-select" name="branch_name">
                                    <option value="">Chọn cơ sở</option>
                                    <?php foreach ($branches as $branch): ?>
                                        <option value="<?= h($branch) ?>" <?= ($user['branch_name'] ?? '') === $branch ? 'selected' : '' ?>><?= h($branch) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="admin-form-label">Ngày bắt đầu</label>
                                <input class="admin-input" type="date" name="hire_date" value="<?= h($user['hire_date']) ?>">
                            </div>
                            <div>
                                <label class="admin-form-label">Trạng thái</label>
                                <select class="admin-select" name="status">
                                    <option value="working" <?= ($user['status'] ?? '') === 'working' ? 'selected' : '' ?>>Đang làm việc</option>
                                    <option value="leave" <?= ($user['status'] ?? '') === 'leave' ? 'selected' : '' ?>>Nghỉ phép</option>
                                    <option value="inactive" <?= ($user['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Tạm ngưng</option>
                                </select>
                            </div>
                        </div>
                    <?php else: ?>
                        <div>
                            <label class="admin-form-label">Trạng thái</label>
                            <select class="admin-select" name="status">
                                <option value="active" <?= ($user['status'] ?? '') === 'active' ? 'selected' : '' ?>>Đang hoạt động</option>
                                <option value="inactive" <?= ($user['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Tạm ngưng</option>
                                <option value="blocked" <?= ($user['status'] ?? '') === 'blocked' ? 'selected' : '' ?>>Bị khóa</option>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div>
                        <label class="admin-form-label">Địa chỉ</label>
                        <input class="admin-input" type="text" name="address" value="<?= h($user['address']) ?>">
                    </div>

                    <div class="admin-form-grid admin-form-grid--2">
                        <div>
                            <label class="admin-form-label">Mật khẩu mới</label>
                            <input class="admin-input" type="password" name="password" placeholder="Bỏ trống nếu không đổi">
                        </div>
                        <div>
                            <label class="admin-form-label">Mã hồ sơ</label>
                            <div class="admin-note pt-2">#<?= $isStaff ? 'NV-' : 'KH-' ?><?= str_pad((string) $user['user_id'], 4, '0', STR_PAD_LEFT) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a class="admin-btn admin-btn--light" href="<?= h(admin_url($isStaff ? 'admin_employees' : 'admin_customers')) ?>">Hủy bỏ</a>
                <button class="admin-btn admin-btn--primary" type="submit">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Lưu thay đổi</span>
                </button>
            </div>
        </form>
    </div>
</div>
