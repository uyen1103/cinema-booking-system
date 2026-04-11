<div class="admin-page-heading d-flex flex-wrap justify-content-between gap-3">
    <div>
        <h2>THÊM SUẤT CHIẾU MỚI</h2>
        <p>Thiết lập lịch chiếu, phòng chiếu và giá bán vé.</p>
    </div>
    <a class="admin-btn admin-btn--light" href="?action=showtimes">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Quay lại</span>
    </a>
</div>

<div class="admin-card">
    <div class="admin-card__body">
        <form method="POST" action="?action=store_showtime" class="admin-form-grid">
            <div class="admin-form-grid admin-form-grid--2">
                <div>
                    <label class="admin-form-label">Bộ phim</label>
                    <select class="admin-select" name="movie_id" required>
                        <option value="">Chọn phim</option>
                        <?php foreach ($movies as $movie): ?>
                            <option value="<?= (int) $movie['movie_id'] ?>"><?= h($movie['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="admin-form-label">Phòng chiếu</label>
                    <select class="admin-select" name="room_id" required>
                        <option value="">Chọn phòng</option>
                        <?php foreach ($rooms as $room): ?>
                            <option value="<?= (int) $room['room_id'] ?>" <?= (int) $room['status'] === 0 ? 'disabled' : '' ?>>
                                <?= h($room['name']) ?> <?= (int) $room['status'] === 0 ? '(Bảo trì)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="admin-form-label">Ngày chiếu</label>
                    <input class="admin-input" type="date" name="show_date" required min="<?= date('Y-m-d') ?>">
                </div>
                <div>
                    <label class="admin-form-label">Giá vé (VNĐ)</label>
                    <input class="admin-input" type="number" name="price" min="1" required>
                </div>
                <div>
                    <label class="admin-form-label">Giờ bắt đầu</label>
                    <input class="admin-input" type="time" name="start_time" required>
                </div>
                <div>
                    <label class="admin-form-label">Giờ kết thúc</label>
                    <input class="admin-input" type="time" name="end_time" required>
                </div>
                <div>
                    <label class="admin-form-label">Trạng thái</label>
                    <select class="admin-select" name="status">
                        <option value="1">Mở bán vé</option>
                        <option value="0">Đã hủy / tạm dừng</option>
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a class="admin-btn admin-btn--light" href="?action=showtimes">Hủy bỏ</a>
                <button class="admin-btn admin-btn--primary" type="submit">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Lưu suất chiếu</span>
                </button>
            </div>
        </form>
    </div>
</div>
