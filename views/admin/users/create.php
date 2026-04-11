<?php $isStaff = $userRole === 'staff'; ?>
<div class="admin-page-heading d-flex flex-wrap justify-content-between gap-3">
    <div>
        <h2><?= $isStaff ? 'THÊM NHÂN VIÊN MỚI' : 'THÊM KHÁCH HÀNG MỚI' ?></h2>
        <p>Hoàn thiện thông tin dưới đây để tạo hồ sơ <?= $isStaff ? 'nhân sự' : 'khách hàng' ?> mới.</p>
    </div>
    <a class="admin-btn admin-btn--light" href="?action=<?= $isStaff ? 'employees' : 'customers' ?>">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Quay lại</span>
    </a>
</div>

<div class="admin-card">
    <div class="admin-card__body">
        <form method="POST" action="?action=store_user" enctype="multipart/form-data" class="admin-form-grid">
            <input type="hidden" name="role" value="<?= h($userRole) ?>">

            <div class="admin-form-grid admin-form-grid--2">
                <div>
                    <label class="admin-form-label">Ảnh đại diện</label>
                    <div class="admin-avatar-preview mb-3">
                        <img src="assets/images/default-avatar.svg" alt="Avatar">
                    </div>
                    <input class="admin-file" type="file" name="avatar" accept=".jpg,.jpeg,.png,.webp,.svg">
                </div>

                <div class="admin-form-grid">
                    <div class="admin-form-grid admin-form-grid--2">
                        <div>
                            <label class="admin-form-label">Họ và tên</label>
                            <input class="admin-input" type="text" name="full_name" required>
                        </div>
                        <div>
                            <label class="admin-form-label">Email</label>
                            <input class="admin-input" type="email" name="email" required>
                        </div>
                        <div>
                            <label class="admin-form-label">Số điện thoại</label>
                            <input class="admin-input" type="text" name="phone">
                        </div>
                        <div>
                            <label class="admin-form-label">Ngày sinh</label>
                            <input class="admin-input" type="date" name="birthday">
                        </div>
                    </div>

                    <?php if ($isStaff): ?>
                        <div class="admin-form-grid admin-form-grid--2">
                            <div>
                                <label class="admin-form-label">Chức vụ</label>
                                <select class="admin-select" name="position">
                                    <option value="">Chọn chức vụ</option>
                                    <?php foreach ($positions as $position): ?>
                                        <option value="<?= h($position) ?>"><?= h($position) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="admin-form-label">Cơ sở làm việc</label>
                                <select class="admin-select" name="branch_name">
                                    <option value="">Chọn cơ sở</option>
                                    <?php foreach ($branches as $branch): ?>
                                        <option value="<?= h($branch) ?>"><?= h($branch) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="admin-form-label">Ngày bắt đầu</label>
                                <input class="admin-input" type="date" name="hire_date">
                            </div>
                            <div>
                                <label class="admin-form-label">Trạng thái</label>
                                <select class="admin-select" name="status">
                                    <option value="working">Đang làm việc</option>
                                    <option value="leave">Nghỉ phép</option>
                                    <option value="inactive">Tạm ngưng</option>
                                </select>
                            </div>
                        </div>
                    <?php else: ?>
                        <div>
                            <label class="admin-form-label">Trạng thái</label>
                            <select class="admin-select" name="status">
                                <option value="active">Đang hoạt động</option>
                                <option value="inactive">Tạm ngưng</option>
                                <option value="blocked">Bị khóa</option>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div>
                        <label class="admin-form-label">Địa chỉ</label>
                        <input class="admin-input" type="text" name="address">
                    </div>

                    <div class="admin-form-grid admin-form-grid--2">
                        <div>
                            <label class="admin-form-label">Mật khẩu</label>
                            <input class="admin-input" type="password" name="password" required>
                        </div>
                        <div>
                            <label class="admin-form-label">Gợi ý</label>
                            <div class="admin-note pt-2">Mật khẩu nên có chữ hoa, chữ thường và số để bảo mật tốt hơn.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a class="admin-btn admin-btn--light" href="?action=<?= $isStaff ? 'employees' : 'customers' ?>">Hủy bỏ</a>
                <button class="admin-btn admin-btn--primary" type="submit">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Lưu hồ sơ</span>
                </button>
            </div>
        </form>
    </div>
</div>
