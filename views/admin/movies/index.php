<?php
function movie_status_badge(int $status): string {
    return match ($status) {
        1 => '<span class="admin-badge admin-badge--success">Đang chiếu</span>',
        2 => '<span class="admin-badge admin-badge--warning">Sắp chiếu</span>',
        default => '<span class="admin-badge admin-badge--muted">Ngừng chiếu</span>',
    };
}
?>

<div class="admin-page-heading d-flex flex-wrap justify-content-between align-items-start gap-3">
    <div>
        <h2>QUẢN LÝ PHIM</h2>
        <p>Theo dõi và cập nhật thông tin kho phim của hệ thống.</p>
    </div>
    <a class="admin-btn admin-btn--primary" href="?action=create_movie">
        <i class="fa-solid fa-plus"></i>
        <span>Thêm phim mới</span>
    </a>
</div>

<div class="row g-3 admin-section">
    <div class="col-md-3">
        <div class="admin-stat-card">
            <div class="admin-stat-card__head">
                <div class="admin-stat-card__icon admin-icon--green"><i class="fa-solid fa-ticket"></i></div>
            </div>
            <div class="admin-stat-card__label">Đang công chiếu</div>
            <div class="admin-stat-card__value"><?= (int) ($stats['showing_count'] ?? 0) ?></div>
            <div class="admin-stat-card__meta">Phim đang mở bán vé</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="admin-stat-card">
            <div class="admin-stat-card__head">
                <div class="admin-stat-card__icon admin-icon--yellow"><i class="fa-solid fa-hourglass-start"></i></div>
            </div>
            <div class="admin-stat-card__label">Sắp khởi chiếu</div>
            <div class="admin-stat-card__value"><?= (int) ($stats['coming_count'] ?? 0) ?></div>
            <div class="admin-stat-card__meta">Phim sắp ra mắt</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="admin-stat-card">
            <div class="admin-stat-card__head">
                <div class="admin-stat-card__icon admin-icon--blue"><i class="fa-solid fa-film"></i></div>
            </div>
            <div class="admin-stat-card__label">Tổng số phim</div>
            <div class="admin-stat-card__value"><?= (int) ($stats['total'] ?? 0) ?></div>
            <div class="admin-stat-card__meta">Kho phim đang quản lý</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="admin-stat-card">
            <div class="admin-stat-card__head">
                <div class="admin-stat-card__icon admin-icon--red"><i class="fa-solid fa-receipt"></i></div>
            </div>
            <div class="admin-stat-card__label">Tổng vé đã bán</div>
            <div class="admin-stat-card__value"><?= number_format((int) ($stats['sold_tickets'] ?? 0)) ?></div>
            <div class="admin-stat-card__meta">Tính từ dữ liệu hóa đơn hiện có</div>
        </div>
    </div>
</div>

<div class="admin-card admin-section">
    <div class="admin-card__body">
        <form method="GET" class="admin-filter-bar mb-4">
            <input type="hidden" name="action" value="movies">
            <div class="admin-toolbar mb-0">
                <div class="admin-toolbar__group flex-grow-1">
                    <div class="flex-grow-1">
                        <input class="admin-search" type="text" name="keyword" value="<?= h($filters['keyword'] ?? '') ?>" placeholder="Tìm kiếm tên phim, đạo diễn, thể loại...">
                    </div>
                    <select class="admin-select admin-control--md" name="genre">
                        <option value="">Tất cả thể loại</option>
                        <?php foreach ($genres as $genre): ?>
                            <option value="<?= h($genre) ?>" <?= ($filters['genre'] ?? '') === $genre ? 'selected' : '' ?>><?= h($genre) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select class="admin-select admin-control--sm" name="status">
                        <option value="">Mọi trạng thái</option>
                        <option value="1" <?= ($filters['status'] ?? '') === '1' ? 'selected' : '' ?>>Đang chiếu</option>
                        <option value="2" <?= ($filters['status'] ?? '') === '2' ? 'selected' : '' ?>>Sắp chiếu</option>
                        <option value="0" <?= ($filters['status'] ?? '') === '0' ? 'selected' : '' ?>>Ngừng chiếu</option>
                    </select>
                </div>
                <button class="admin-btn admin-btn--light" type="submit">
                    <i class="fa-solid fa-sliders"></i>
                    <span>Lọc</span>
                </button>
            </div>
        </form>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Poster</th>
                        <th>Thông tin phim</th>
                        <th>Thể loại</th>
                        <th>Thời lượng</th>
                        <th>Khởi chiếu</th>
                        <th>Trạng thái</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($movies)): ?>
                    <tr>
                        <td colspan="7">
                            <div class="admin-empty">
                                <i class="fa-regular fa-folder-open"></i>
                                <div>Chưa có phim nào phù hợp với bộ lọc hiện tại.</div>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($movies as $movie): ?>
                        <tr>
                            <td><img class="admin-thumb admin-thumb--poster" src="<?= h($movie['poster'] ?: 'assets/images/default-poster.svg') ?>" alt="Poster"></td>
                            <td>
                                <div class="admin-movie-mini">
                                    <img src="<?= h($movie['poster'] ?: 'assets/images/default-poster.svg') ?>" alt="Poster">
                                    <div>
                                        <div class="fw-bold"><?= h($movie['title']) ?></div>
                                        <div class="text-muted small">Đạo diễn: <?= h($movie['director']) ?></div>
                                        <div class="text-muted small">Vé đã bán: <?= number_format((int) $movie['sold_tickets']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php foreach (explode(',', (string) ($movie['genre'] ?? '')) as $genre): ?>
                                    <?php $genre = trim($genre); if ($genre === '') continue; ?>
                                    <span class="admin-chip"><?= h($genre) ?></span>
                                <?php endforeach; ?>
                            </td>
                            <td><?= (int) $movie['duration'] ?> phút</td>
                            <td><?= h(format_date($movie['release_date'])) ?></td>
                            <td><?= movie_status_badge((int) $movie['status']) ?></td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    <a class="admin-btn admin-btn--light admin-btn--icon" href="?action=edit_movie&id=<?= (int) $movie['movie_id'] ?>">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <button class="admin-btn admin-btn--danger admin-btn--icon" type="button" data-bs-toggle="modal" data-bs-target="#deleteMovieModal<?= (int) $movie['movie_id'] ?>">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </div>
                                <?php include __DIR__ . '/delete_modal.php'; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
