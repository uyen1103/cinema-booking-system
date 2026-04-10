<div class="admin-page-heading d-flex flex-wrap justify-content-between gap-3">
    <div>
        <h2>CHỈNH SỬA KHUYẾN MÃI</h2>
        <p>Cập nhật thông tin, trạng thái và điều kiện áp dụng của chương trình.</p>
    </div>
    <a class="admin-btn admin-btn--light" href="<?= h(admin_url('admin_promotions')) ?>">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Quay lại</span>
    </a>
</div>

<div class="admin-card">
    <div class="admin-card__body">
        <form method="POST" action="<?= h(admin_url('admin_update_promotion')) ?>" enctype="multipart/form-data" class="admin-form-grid">
            <input type="hidden" name="promotion_id" value="<?= (int) $promotion['promotion_id'] ?>">

            <div class="admin-form-grid admin-form-grid--2">
                <div>
                    <label class="admin-form-label">Tên chương trình</label>
                    <input class="admin-input" type="text" name="title" value="<?= h($promotion['title']) ?>" required>
                </div>
                <div>
                    <label class="admin-form-label">Mã khuyến mãi</label>
                    <input class="admin-input" type="text" name="code" value="<?= h($promotion['code']) ?>" required>
                </div>
                <div>
                    <label class="admin-form-label">Loại chiết khấu</label>
                    <select class="admin-select" name="discount_type">
                        <option value="percent" <?= $promotion['discount_type'] === 'percent' ? 'selected' : '' ?>>Phần trăm (%)</option>
                        <option value="fixed" <?= $promotion['discount_type'] === 'fixed' ? 'selected' : '' ?>>Số tiền cố định</option>
                    </select>
                </div>
                <div>
                    <label class="admin-form-label">Giá trị giảm</label>
                    <input class="admin-input" type="number" step="0.01" name="discount_value" value="<?= h($promotion['discount_value']) ?>" required>
                </div>
                <div>
                    <label class="admin-form-label">Đơn tối thiểu</label>
                    <input class="admin-input" type="number" step="0.01" name="min_order_amount" value="<?= h($promotion['min_order_amount']) ?>">
                </div>
                <div>
                    <label class="admin-form-label">Giảm tối đa</label>
                    <input class="admin-input" type="number" step="0.01" name="max_discount" value="<?= h($promotion['max_discount']) ?>">
                </div>
                <div>
                    <label class="admin-form-label">Giới hạn lượt dùng</label>
                    <input class="admin-input" type="number" name="usage_limit" value="<?= h($promotion['usage_limit']) ?>">
                </div>
                <div>
                    <label class="admin-form-label">Đã dùng</label>
                    <input class="admin-input" type="number" name="used_count" value="<?= h($promotion['used_count']) ?>">
                </div>
                <div>
                    <label class="admin-form-label">Ngân sách ưu đãi</label>
                    <input class="admin-input" type="number" step="0.01" name="budget" value="<?= h($promotion['budget']) ?>">
                </div>
                <div>
                    <label class="admin-form-label">Từ ngày</label>
                    <input class="admin-input" type="date" name="start_date" value="<?= h($promotion['start_date']) ?>" required>
                </div>
                <div>
                    <label class="admin-form-label">Đến ngày</label>
                    <input class="admin-input" type="date" name="end_date" value="<?= h($promotion['end_date']) ?>" required>
                </div>
                <div>
                    <label class="admin-form-label">Trạng thái</label>
                    <select class="admin-select" name="status">
                        <option value="1" <?= (int) $promotion['status'] === 1 ? 'selected' : '' ?>>Đang kích hoạt</option>
                        <option value="0" <?= (int) $promotion['status'] === 0 ? 'selected' : '' ?>>Tạm ngưng</option>
                    </select>
                </div>
                <div>
                    <label class="admin-form-label">Ảnh chiến dịch mới</label>
                    <input class="admin-file" type="file" name="image_path" accept=".jpg,.jpeg,.png,.webp,.svg">
                </div>
            </div>

            <div>
                <label class="admin-form-label">Mô tả chi tiết</label>
                <textarea class="admin-textarea" name="description"><?= h($promotion['description']) ?></textarea>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a class="admin-btn admin-btn--light" href="<?= h(admin_url('admin_promotions')) ?>">Hủy bỏ</a>
                <button class="admin-btn admin-btn--primary" type="submit">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Lưu thay đổi</span>
                </button>
            </div>
        </form>
    </div>
</div>
