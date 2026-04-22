<div class="admin-page-heading d-flex flex-wrap justify-content-between gap-3">
    <div>
        <h2>THÊM PHÒNG CHIẾU MỚI</h2>
        <p>Tạo phòng chiếu mới và sinh sơ đồ ghế mặc định theo sức chứa.</p>
    </div>
    <a class="admin-btn admin-btn--light" href="<?= h(admin_url('admin_rooms')) ?>">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Quay lại</span>
    </a>
</div>

<div class="admin-card">
    <div class="admin-card__body">
        <form method="POST" action="<?= h(admin_url('admin_store_room')) ?>" class="admin-form-grid">
            <div class="admin-form-grid admin-form-grid--2">
                <div>
                    <label class="admin-form-label">Tên phòng chiếu</label>
                    <input class="admin-input" type="text" name="name" required>
                </div>
                <div>
                    <label class="admin-form-label">Sức chứa mặc định</label>
                    <input class="admin-input" type="number" name="capacity" min="1" required>
                </div>
                <div>
                    <label class="admin-form-label">Giờ mở cửa</label>
                    <input class="admin-input" type="time" name="opening_time" value="08:00" required>
                </div>
                <div>
                    <label class="admin-form-label">Giờ đóng cửa</label>
                    <input class="admin-input" type="time" name="closing_time" value="23:30" required>
                </div>
                <div>
                    <label class="admin-form-label">Trạng thái</label>
                    <select class="admin-select" name="status">
                        <option value="1">Đang hoạt động</option>
                        <option value="0">Bảo trì</option>
                    </select>
                </div>
                <div>
                    <label class="admin-form-label">Lý do bảo trì</label>
                    <input class="admin-input" type="text" name="maintenance_reason" placeholder="Chỉ nhập khi phòng bảo trì">
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a class="admin-btn admin-btn--light" href="<?= h(admin_url('admin_rooms')) ?>">Hủy bỏ</a>
                <button class="admin-btn admin-btn--primary" type="submit">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Lưu phòng chiếu</span>
                </button>
            </div>
        </form>
    </div>
</div>
