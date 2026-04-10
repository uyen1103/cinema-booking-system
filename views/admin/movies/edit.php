<div class="admin-page-heading d-flex flex-wrap justify-content-between gap-3">
    <div>
        <h2>CHỈNH SỬA PHIM: <?= h($movie['title']) ?></h2>
        <p>Cập nhật thông tin chi tiết, poster và trạng thái hiển thị.</p>
    </div>
    <a class="admin-btn admin-btn--light" href="?action=movies">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Quay lại</span>
    </a>
</div>

<div class="admin-card">
    <div class="admin-card__body">
        <form method="POST" action="?action=update_movie" enctype="multipart/form-data" class="admin-form-grid">
            <input type="hidden" name="movie_id" value="<?= (int) $movie['movie_id'] ?>">

            <div class="admin-form-grid admin-form-grid--2">
                <div class="admin-form-grid">
                    <div>
                        <label class="admin-form-label">Poster hiện tại</label>
                        <div class="admin-image-preview admin-image-preview--poster mb-3">
                            <img src="<?= h($movie['poster'] ?: 'assets/images/default-poster.svg') ?>" alt="Poster">
                        </div>
                        <input class="admin-file" type="file" name="poster" accept=".jpg,.jpeg,.png,.webp,.svg">
                    </div>
                    <div>
                        <label class="admin-form-label">Banner hiện tại</label>
                        <div class="admin-image-preview admin-image-preview--banner mb-3">
                            <img src="<?= h($movie['banner'] ?: 'assets/images/default-banner.svg') ?>" alt="Banner">
                        </div>
                        <input class="admin-file" type="file" name="banner" accept=".jpg,.jpeg,.png,.webp,.svg">
                    </div>
                </div>

                <div class="admin-form-grid">
                    <div>
                        <label class="admin-form-label">Tên phim</label>
                        <input class="admin-input" type="text" name="title" value="<?= h($movie['title']) ?>" required>
                    </div>

                    <div class="admin-form-grid admin-form-grid--2">
                        <div>
                            <label class="admin-form-label">Thời lượng</label>
                            <input class="admin-input" type="number" name="duration" min="1" value="<?= (int) $movie['duration'] ?>" required>
                        </div>
                        <div>
                            <label class="admin-form-label">Ngày khởi chiếu</label>
                            <input class="admin-input" type="date" name="release_date" value="<?= h($movie['release_date']) ?>" required>
                        </div>
                    </div>

                    <div>
                        <label class="admin-form-label">Thể loại</label>
                        <input class="admin-input" type="text" name="genre" value="<?= h($movie['genre']) ?>" list="movie-genres-edit">
                        <datalist id="movie-genres-edit">
                            <?php foreach ($genres as $genre): ?>
                                <option value="<?= h($genre) ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>

                    <div class="admin-form-grid admin-form-grid--2">
                        <div>
                            <label class="admin-form-label">Đạo diễn</label>
                            <input class="admin-input" type="text" name="director" value="<?= h($movie['director']) ?>">
                        </div>
                        <div>
                            <label class="admin-form-label">Diễn viên</label>
                            <input class="admin-input" type="text" name="cast" value="<?= h($movie['cast']) ?>">
                        </div>
                    </div>

                    <div class="admin-form-grid admin-form-grid--2">
                        <div>
                            <label class="admin-form-label">Trailer URL</label>
                            <input class="admin-input" type="text" name="trailer_url" value="<?= h($movie['trailer_url']) ?>">
                        </div>
                        <div>
                            <label class="admin-form-label">Trạng thái</label>
                            <select class="admin-select" name="status">
                                <option value="1" <?= (int) $movie['status'] === 1 ? 'selected' : '' ?>>Đang chiếu</option>
                                <option value="2" <?= (int) $movie['status'] === 2 ? 'selected' : '' ?>>Sắp chiếu</option>
                                <option value="0" <?= (int) $movie['status'] === 0 ? 'selected' : '' ?>>Ngừng chiếu</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="admin-form-label">Mô tả phim</label>
                        <textarea class="admin-textarea" name="description"><?= h($movie['description']) ?></textarea>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a class="admin-btn admin-btn--light" href="?action=movies">Hủy bỏ</a>
                <button class="admin-btn admin-btn--primary" type="submit">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Lưu thay đổi</span>
                </button>
            </div>
        </form>
    </div>
</div>
