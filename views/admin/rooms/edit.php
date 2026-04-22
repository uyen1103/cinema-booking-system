<div class="admin-page-heading d-flex flex-wrap justify-content-between gap-3">
    <div>
        <h2>CHỈNH SỬA PHÒNG: <?= h($room['name']) ?></h2>
        <p>Cập nhật thông số hoạt động và trạng thái của phòng chiếu.</p>
    </div>
    <a class="admin-btn admin-btn--light" href="<?= h(admin_url('admin_rooms')) ?>">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Quay lại</span>
    </a>
</div>

<div class="admin-card">
    <div class="admin-card__body">
        <form method="POST" action="<?= h(admin_url('admin_update_room')) ?>" class="admin-form-grid">
            <input type="hidden" name="room_id" value="<?= (int) $room['room_id'] ?>">

            <div class="admin-form-grid admin-form-grid--2">
                <div>
                    <label class="admin-form-label">Tên phòng chiếu</label>
                    <input class="admin-input" type="text" name="name" value="<?= h($room['name']) ?>" required>
                </div>
                <div>
                    <label class="admin-form-label">Sức chứa hoạt động</label>
                    <input class="admin-input" type="number" name="capacity" min="1" value="<?= (int) $room['capacity'] ?>" required>
                </div>
                <div>
                    <label class="admin-form-label">Giờ mở cửa</label>
                    <input class="admin-input" type="time" name="opening_time" value="<?= h(substr($room['opening_time'],0,5)) ?>" required>
                </div>
                <div>
                    <label class="admin-form-label">Giờ đóng cửa</label>
                    <input class="admin-input" type="time" name="closing_time" value="<?= h(substr($room['closing_time'],0,5)) ?>" required>
                </div>
                <div>
                    <label class="admin-form-label">Trạng thái</label>
                    <select class="admin-select" name="status">
                        <option value="1" <?= (int) $room['status'] === 1 ? 'selected' : '' ?>>Đang hoạt động</option>
                        <option value="0" <?= (int) $room['status'] === 0 ? 'selected' : '' ?>>Bảo trì</option>
                    </select>
                </div>
                <div>
                    <label class="admin-form-label">Lý do bảo trì</label>
                    <input class="admin-input" type="text" name="maintenance_reason" value="<?= h($room['maintenance_reason']) ?>">
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a class="admin-btn admin-btn--ghost" href="<?= h(admin_url('admin_room_seats', ['id' => (int) $room['room_id']])) ?>">
                    <i class="fa-solid fa-couch"></i>
                    <span>Xem sơ đồ ghế</span>
                </a>
                <button class="admin-btn admin-btn--primary" type="submit">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Lưu thay đổi</span>
                </button>
            </div>
        </form>
    </div>
</div>
