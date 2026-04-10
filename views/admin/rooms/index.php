<?php
function room_status_badge(int $status): string {
    return $status === 1
        ? '<span class="admin-badge admin-badge--success">Đang hoạt động</span>'
        : '<span class="admin-badge admin-badge--warning">Bảo trì</span>';
}
?>

<div class="admin-page-heading d-flex flex-wrap justify-content-between align-items-start gap-3">
    <div>
        <h2>QUẢN LÝ PHÒNG CHIẾU VÀ GHẾ</h2>
        <p>Theo dõi trạng thái vận hành phòng chiếu và cấu trúc ghế hiện có.</p>
    </div>
    <a class="admin-btn admin-btn--primary" href="?action=create_room">
        <i class="fa-solid fa-plus"></i>
        <span>Thêm phòng chiếu</span>
    </a>
</div>

<div class="row g-3 admin-section">
    <div class="col-md-3">
        <div class="admin-stat-card">
            <div class="admin-stat-card__head"><div class="admin-stat-card__icon admin-icon--blue"><i class="fa-solid fa-door-open"></i></div></div>
            <div class="admin-stat-card__label">Tổng phòng</div>
            <div class="admin-stat-card__value"><?= (int) ($stats['total_rooms'] ?? 0) ?></div>
            <div class="admin-stat-card__meta">Toàn bộ phòng đang quản lý</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="admin-stat-card">
            <div class="admin-stat-card__head"><div class="admin-stat-card__icon admin-icon--green"><i class="fa-solid fa-circle-check"></i></div></div>
            <div class="admin-stat-card__label">Hoạt động</div>
            <div class="admin-stat-card__value"><?= (int) ($stats['active_rooms'] ?? 0) ?></div>
            <div class="admin-stat-card__meta">Phòng có thể xếp lịch chiếu</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="admin-stat-card">
            <div class="admin-stat-card__head"><div class="admin-stat-card__icon admin-icon--yellow"><i class="fa-solid fa-screwdriver-wrench"></i></div></div>
            <div class="admin-stat-card__label">Bảo trì</div>
            <div class="admin-stat-card__value"><?= (int) ($stats['maintenance_rooms'] ?? 0) ?></div>
            <div class="admin-stat-card__meta">Phòng tạm dừng khai thác</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="admin-stat-card">
            <div class="admin-stat-card__head"><div class="admin-stat-card__icon admin-icon--red"><i class="fa-solid fa-couch"></i></div></div>
            <div class="admin-stat-card__label">Tổng ghế hoạt động</div>
            <div class="admin-stat-card__value"><?= number_format((int) ($stats['total_capacity'] ?? 0)) ?></div>
            <div class="admin-stat-card__meta">Ghế sẵn sàng phục vụ khách</div>
        </div>
    </div>
</div>

<div class="admin-card admin-section">
    <div class="admin-card__body">
        <form method="GET" class="admin-filter-bar mb-4">
            <input type="hidden" name="action" value="rooms">
            <div class="admin-toolbar mb-0">
                <div class="admin-toolbar__group flex-grow-1">
                    <div class="flex-grow-1">
                        <input class="admin-search" type="text" name="keyword" value="<?= h($filters['keyword'] ?? '') ?>" placeholder="Tìm theo tên phòng chiếu...">
                    </div>
                    <select class="admin-select admin-control--md" name="status">
                        <option value="">Mọi trạng thái</option>
                        <option value="1" <?= ($filters['status'] ?? '') === '1' ? 'selected' : '' ?>>Đang hoạt động</option>
                        <option value="0" <?= ($filters['status'] ?? '') === '0' ? 'selected' : '' ?>>Bảo trì</option>
                    </select>
                </div>
                <button class="admin-btn admin-btn--light" type="submit"><i class="fa-solid fa-filter"></i> <span>Lọc</span></button>
            </div>
        </form>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Tên phòng</th>
                        <th>Sức chứa</th>
                        <th>Giờ hoạt động</th>
                        <th>Trạng thái</th>
                        <th>Lý do bảo trì</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($rooms)): ?>
                    <tr><td colspan="6"><div class="admin-empty"><i class="fa-regular fa-folder-open"></i><div>Chưa có dữ liệu phòng chiếu.</div></div></td></tr>
                <?php else: ?>
                    <?php foreach ($rooms as $room): ?>
                        <tr>
                            <td>
                                <div class="fw-bold"><?= h($room['name']) ?></div>
                                <div class="text-muted small"><?= (int) ($room['total_seats'] ?? 0) ?> ghế / <?= (int) ($room['active_seats'] ?? 0) ?> ghế hoạt động</div>
                            </td>
                            <td><?= number_format((int) $room['capacity']) ?> ghế</td>
                            <td><?= h(substr($room['opening_time'], 0, 5)) ?> - <?= h(substr($room['closing_time'], 0, 5)) ?></td>
                            <td><?= room_status_badge((int) $room['status']) ?></td>
                            <td><?= h($room['maintenance_reason'] ?: '—') ?></td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    <a class="admin-btn admin-btn--ghost admin-btn--icon" href="?action=room_seats&id=<?= (int) $room['room_id'] ?>" title="Sơ đồ ghế">
                                        <i class="fa-solid fa-couch"></i>
                                    </a>
                                    <a class="admin-btn admin-btn--light admin-btn--icon" href="?action=edit_room&id=<?= (int) $room['room_id'] ?>" title="Chỉnh sửa">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <button class="admin-btn admin-btn--danger admin-btn--icon" type="button" data-bs-toggle="modal" data-bs-target="#deleteRoomModal<?= (int) $room['room_id'] ?>" title="Xóa">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </div>
                                <?php include __DIR__ . '/delete_room.php'; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
