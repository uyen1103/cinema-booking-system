<div class="admin-page-heading d-flex flex-wrap justify-content-between gap-3">
    <div>
        <h2>CHỈNH SỬA SUẤT CHIẾU #<?= str_pad((string) $showtime['showtime_id'], 4, '0', STR_PAD_LEFT) ?></h2>
        <p>Cập nhật lịch chiếu, giá vé và trạng thái bán vé.</p>
    </div>
    <a class="admin-btn admin-btn--light" href="<?= h(admin_url('admin_showtimes')) ?>">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Quay lại</span>
    </a>
</div>

<div class="admin-card">
    <div class="admin-card__body">
        <form method="POST" action="<?= h(admin_url('admin_update_showtime')) ?>" class="admin-form-grid">
            <input type="hidden" name="showtime_id" value="<?= (int) $showtime['showtime_id'] ?>">

            <div class="admin-form-grid admin-form-grid--2">
                <div>
                    <label class="admin-form-label">Bộ phim</label>
                    <select class="admin-select" name="movie_id" required>
                        <?php foreach ($movies as $movie): ?>
                            <option value="<?= (int) $movie['movie_id'] ?>" <?= (int) $movie['movie_id'] === (int) $showtime['movie_id'] ? 'selected' : '' ?>>
                                <?= h($movie['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="admin-form-label">Phòng chiếu</label>
                    <select class="admin-select" name="room_id" required>
                        <?php foreach ($rooms as $room): ?>
                            <option value="<?= (int) $room['room_id'] ?>" <?= (int) $room['room_id'] === (int) $showtime['room_id'] ? 'selected' : '' ?> <?= ((int) $room['status'] === 0 && (int) $room['room_id'] !== (int) $showtime['room_id']) ? 'disabled' : '' ?>>
                                <?= h($room['name']) ?> <?= (int) $room['status'] === 0 ? '(Bảo trì)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="admin-form-label">Ngày chiếu</label>
                    <input class="admin-input" type="date" name="show_date" value="<?= h($showtime['show_date']) ?>" required>
                </div>
                <div>
                    <label class="admin-form-label">Giá vé (VNĐ)</label>
                    <input class="admin-input" type="number" name="price" min="1" value="<?= (int) $showtime['price'] ?>" required>
                </div>
                <div>
                    <label class="admin-form-label">Giờ bắt đầu</label>
                    <input class="admin-input" type="time" name="start_time" value="<?= h(substr($showtime['start_time'],0,5)) ?>" required>
                </div>
                <div>
                    <label class="admin-form-label">Giờ kết thúc</label>
                    <input class="admin-input" type="time" name="end_time" value="<?= h(substr($showtime['end_time'],0,5)) ?>" required>
                </div>
                <div>
                    <label class="admin-form-label">Trạng thái</label>
                    <select class="admin-select" name="status">
                        <option value="1" <?= (int) $showtime['status'] === 1 ? 'selected' : '' ?>>Mở bán vé</option>
                        <option value="0" <?= (int) $showtime['status'] === 0 ? 'selected' : '' ?>>Đã hủy / tạm dừng</option>
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a class="admin-btn admin-btn--light" href="<?= h(admin_url('admin_showtimes')) ?>">Hủy bỏ</a>
                <button class="admin-btn admin-btn--primary" type="submit">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Lưu thay đổi</span>
                </button>
            </div>
        </form>
    </div>
</div>
