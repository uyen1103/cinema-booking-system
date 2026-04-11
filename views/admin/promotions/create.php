<div class="admin-page-heading d-flex flex-wrap justify-content-between gap-3">
    <div>
        <h2>THÊM KHUYẾN MÃI MỚI</h2>
        <p>Tạo mã ưu đãi mới và thiết lập điều kiện áp dụng trực tiếp trên database.</p>
    </div>
    <a class="admin-btn admin-btn--light" href="<?= h(admin_url('admin_promotions')) ?>">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Quay lại</span>
    </a>
</div>

<div class="admin-card">
    <div class="admin-card__body">
        <form method="POST" action="<?= h(admin_url('admin_store_promotion')) ?>" enctype="multipart/form-data" class="admin-form-grid">
            <div class="admin-form-grid admin-form-grid--2">
                <div>
                    <label class="admin-form-label">Tên chương trình</label>
                    <input class="admin-input" type="text" name="title" required>
                </div>
                <div>
                    <label class="admin-form-label">Mã khuyến mãi</label>
                    <input class="admin-input" type="text" name="code" required>
                </div>
                <div>
                    <label class="admin-form-label">Loại chiết khấu</label>
                    <select class="admin-select" name="discount_type">
                        <option value="percent">Phần trăm (%)</option>
                        <option value="fixed">Số tiền cố định</option>
                    </select>
                </div>
                <div>
                    <label class="admin-form-label">Giá trị giảm</label>
                    <input class="admin-input" type="number" step="0.01" name="discount_value" required>
                </div>
                <div>
                    <label class="admin-form-label">Đơn tối thiểu</label>
                    <input class="admin-input" type="number" step="0.01" name="min_order_amount">
                </div>
                <div>
                    <label class="admin-form-label">Giảm tối đa</label>
                    <input class="admin-input" type="number" step="0.01" name="max_discount">
                </div>
                <div>
                    <label class="admin-form-label">Giới hạn lượt dùng</label>
                    <input class="admin-input" type="number" name="usage_limit">
                </div>
                <div>
                    <label class="admin-form-label">Ngân sách ưu đãi</label>
                    <input class="admin-input" type="number" step="0.01" name="budget">
                </div>
                <div>
                    <label class="admin-form-label">Từ ngày</label>
                    <input class="admin-input" type="date" name="start_date" required>
                </div>
                <div>
                    <label class="admin-form-label">Đến ngày</label>
                    <input class="admin-input" type="date" name="end_date" required>
                </div>
                <div>
                    <label class="admin-form-label">Trạng thái</label>
                    <select class="admin-select" name="status">
                        <option value="1">Đang kích hoạt</option>
                        <option value="0">Tạm ngưng</option>
                    </select>
                </div>
                <div>
                    <label class="admin-form-label">Ảnh chiến dịch</label>
                    <input class="admin-file" type="file" name="image_path" accept=".jpg,.jpeg,.png,.webp,.svg">
                </div>
            </div>

            <div>
                <label class="admin-form-label">Mô tả chi tiết</label>
                <textarea class="admin-textarea" name="description"></textarea>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a class="admin-btn admin-btn--light" href="<?= h(admin_url('admin_promotions')) ?>">Hủy bỏ</a>
                <button class="admin-btn admin-btn--primary" type="submit">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Lưu khuyến mãi</span>
                </button>
            </div>
        </form>
    </div>
</div>
