<div class="admin-page-heading d-flex flex-wrap justify-content-between gap-3">
    <div>
        <h2>THÊM PHIM MỚI</h2>
        <p>Nhập đầy đủ các thông tin cần thiết trước khi lưu vào cơ sở dữ liệu.</p>
    </div>
    <a class="admin-btn admin-btn--light" href="<?= h(admin_url('admin_movies')) ?>">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Quay lại</span>
    </a>
</div>

<div class="admin-card">
    <div class="admin-card__body">
        <form method="POST" action="<?= h(admin_url('admin_store_movie')) ?>" enctype="multipart/form-data" class="admin-form-grid">
            <div class="admin-form-grid admin-form-grid--2">
                <div class="admin-form-grid">
                    <div>
                        <label class="admin-form-label">Poster phim</label>
                        <div class="admin-image-preview admin-image-preview--poster mb-3">
                            <img src="assets/images/default-poster.svg" alt="Poster mặc định">
                        </div>
                        <input class="admin-file" type="file" name="poster" accept=".jpg,.jpeg,.png,.webp,.svg">
                    </div>
                    <div>
                        <label class="admin-form-label">Banner phim</label>
                        <div class="admin-image-preview admin-image-preview--banner mb-3">
                            <img src="assets/images/default-banner.svg" alt="Banner mặc định">
                        </div>
                        <input class="admin-file" type="file" name="banner" accept=".jpg,.jpeg,.png,.webp,.svg">
                    </div>
                </div>

                <div class="admin-form-grid">
                    <div>
                        <label class="admin-form-label">Tên phim</label>
                        <input class="admin-input" type="text" name="title" required>
                    </div>

                    <div class="admin-form-grid admin-form-grid--2">
                        <div>
                            <label class="admin-form-label">Thời lượng (phút)</label>
                            <input class="admin-input" type="number" name="duration" min="1" required>
                        </div>
                        <div>
                            <label class="admin-form-label">Ngày khởi chiếu</label>
                            <input class="admin-input" type="date" name="release_date" required>
                        </div>
                    </div>

                    <div>
                        <label class="admin-form-label">Thể loại</label>
                        <input class="admin-input" type="text" name="genre" list="movie-genres" placeholder="Ví dụ: Hành động, Tình cảm">
                        <datalist id="movie-genres">
                            <?php foreach ($genres as $genre): ?>
                                <option value="<?= h($genre) ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>

                    <div class="admin-form-grid admin-form-grid--2">
                        <div>
                            <label class="admin-form-label">Đạo diễn</label>
                            <input class="admin-input" type="text" name="director">
                        </div>
                        <div>
                            <label class="admin-form-label">Diễn viên</label>
                            <input class="admin-input" type="text" name="cast">
                        </div>
                    </div>

                    <div class="admin-form-grid admin-form-grid--2">
                        <div>
                            <label class="admin-form-label">Trailer URL</label>
                            <input class="admin-input" type="text" name="trailer_url">
                        </div>
                        <div>
                            <label class="admin-form-label">Trạng thái</label>
                            <select class="admin-select" name="status">
                                <option value="1">Đang chiếu</option>
                                <option value="2">Sắp chiếu</option>
                                <option value="0">Ngừng chiếu</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="admin-form-label">Nội dung tóm tắt</label>
                        <textarea class="admin-textarea" name="description"></textarea>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a class="admin-btn admin-btn--light" href="<?= h(admin_url('admin_movies')) ?>">Hủy bỏ</a>
                <button class="admin-btn admin-btn--primary" type="submit">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Lưu phim</span>
                </button>
            </div>
        </form>
    </div>
</div>
