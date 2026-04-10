<?php
function showtime_status_badge(int $status): string {
    return $status === 1
        ? '<span class="admin-badge admin-badge--success">Mở bán vé</span>'
        : '<span class="admin-badge admin-badge--danger">Đã hủy</span>';
}
?>

<div class="admin-page-heading d-flex flex-wrap justify-content-between gap-3">
    <div>
        <h2>QUẢN LÝ SUẤT CHIẾU</h2>
        <p>Kiểm soát lịch chiếu, phòng chiếu, giá vé và trạng thái bán vé.</p>
    </div>
    <a class="admin-btn admin-btn--primary" href="?action=create_showtime">
        <i class="fa-solid fa-plus"></i>
        <span>Thêm suất chiếu mới</span>
    </a>
</div>

<div class="row g-3 admin-section">
    <div class="col-md-3">
        <div class="admin-stat-card">
            <div class="admin-stat-card__head"><div class="admin-stat-card__icon admin-icon--blue"><i class="fa-solid fa-calendar-days"></i></div></div>
            <div class="admin-stat-card__label">Tổng suất chiếu</div>
            <div class="admin-stat-card__value"><?= (int) ($stats['total_showtimes'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="admin-stat-card">
            <div class="admin-stat-card__head"><div class="admin-stat-card__icon admin-icon--green"><i class="fa-solid fa-clock"></i></div></div>
            <div class="admin-stat-card__label">Hôm nay</div>
            <div class="admin-stat-card__value"><?= (int) ($stats['today_showtimes'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="admin-stat-card">
            <div class="admin-stat-card__head"><div class="admin-stat-card__icon admin-icon--green"><i class="fa-solid fa-ticket"></i></div></div>
            <div class="admin-stat-card__label">Đang mở bán</div>
            <div class="admin-stat-card__value"><?= (int) ($stats['active_showtimes'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="admin-stat-card">
            <div class="admin-stat-card__head"><div class="admin-stat-card__icon admin-icon--red"><i class="fa-solid fa-ban"></i></div></div>
            <div class="admin-stat-card__label">Đã hủy</div>
            <div class="admin-stat-card__value"><?= (int) ($stats['cancelled_showtimes'] ?? 0) ?></div>
        </div>
    </div>
</div>

<div class="admin-card admin-section">
    <div class="admin-card__body">
        <form method="GET" class="admin-filter-bar mb-4">
            <input type="hidden" name="action" value="showtimes">
            <div class="admin-toolbar mb-0">
                <div class="admin-toolbar__group flex-grow-1">
                    <div class="flex-grow-1">
                        <input class="admin-search" type="text" name="keyword" value="<?= h($filters['keyword'] ?? '') ?>" placeholder="Tìm theo phim hoặc phòng chiếu...">
                    </div>
                    <input class="admin-input admin-control--md" type="date" name="show_date" value="<?= h($filters['show_date'] ?? '') ?>">
                    <select class="admin-select admin-control--md" name="status">
                        <option value="">Mọi trạng thái</option>
                        <option value="1" <?= ($filters['status'] ?? '') === '1' ? 'selected' : '' ?>>Mở bán vé</option>
                        <option value="0" <?= ($filters['status'] ?? '') === '0' ? 'selected' : '' ?>>Đã hủy</option>
                    </select>
                </div>
                <button class="admin-btn admin-btn--light" type="submit"><i class="fa-solid fa-filter"></i> <span>Lọc</span></button>
            </div>
        </form>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Phim</th>
                        <th>Phòng</th>
                        <th>Lịch chiếu</th>
                        <th>Giá vé</th>
                        <th>Vé đã bán</th>
                        <th>Trạng thái</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($showtimes)): ?>
                    <tr><td colspan="7"><div class="admin-empty"><i class="fa-regular fa-calendar-xmark"></i><div>Chưa có suất chiếu phù hợp.</div></div></td></tr>
                <?php else: ?>
                    <?php foreach ($showtimes as $showtime): ?>
                        <tr>
                            <td>
                                <div class="admin-movie-mini">
                                    <img src="<?= h($showtime['movie_poster'] ?: 'assets/images/default-poster.svg') ?>" alt="Poster">
                                    <div>
                                        <div class="fw-bold"><?= h($showtime['movie_title']) ?></div>
                                        <div class="text-muted small">Mã suất: #SC<?= str_pad((string) $showtime['showtime_id'], 4, '0', STR_PAD_LEFT) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?= h($showtime['room_name']) ?></td>
                            <td>
                                <div><?= h(format_date($showtime['show_date'])) ?></div>
                                <div class="text-muted small"><?= h(substr($showtime['start_time'],0,5)) ?> - <?= h(substr($showtime['end_time'],0,5)) ?></div>
                            </td>
                            <td><?= h(format_currency($showtime['price'])) ?></td>
                            <td><?= number_format((int) $showtime['sold_tickets']) ?></td>
                            <td><?= showtime_status_badge((int) $showtime['status']) ?></td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    <a class="admin-btn admin-btn--light admin-btn--icon" href="?action=edit_showtime&id=<?= (int) $showtime['showtime_id'] ?>">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <button class="admin-btn admin-btn--danger admin-btn--icon" type="button" data-bs-toggle="modal" data-bs-target="#deleteShowtimeModal<?= (int) $showtime['showtime_id'] ?>">
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
