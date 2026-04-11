<?php
function promotion_status_badge(array $promotion): string {
    $isRunning = (int) $promotion['status'] === 1;
    return $isRunning
        ? '<span class="admin-badge admin-badge--success">Đang kích hoạt</span>'
        : '<span class="admin-badge admin-badge--muted">Tạm ngưng</span>';
}

function promotion_type_label(string $type): string {
    return $type === 'fixed' ? 'Coupon' : 'Trực tiếp';
}
?>

<div class="admin-page-heading d-flex flex-wrap justify-content-between gap-3">
    <div>
        <h2>QUẢN LÝ KHUYẾN MÃI</h2>
        <p>Theo dõi mã giảm giá, ngân sách ưu đãi và hiệu quả sử dụng chiến dịch.</p>
    </div>
    <a class="admin-btn admin-btn--primary" href="<?= h(admin_url('admin_create_promotion')) ?>">
        <i class="fa-solid fa-plus"></i>
        <span>Thêm khuyến mãi mới</span>
    </a>
</div>

<div class="row g-3 admin-section">
    <div class="col-md-3">
        <div class="admin-stat-card">
            <div class="admin-stat-card__head"><div class="admin-stat-card__icon admin-icon--red"><i class="fa-solid fa-ticket-percent"></i></div></div>
            <div class="admin-stat-card__label">Tổng mã hoạt động</div>
            <div class="admin-stat-card__value"><?= (int) ($stats['active_promotions'] ?? 0) ?></div>
            <div class="admin-stat-card__meta">Khuyến mãi đang có hiệu lực</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="admin-stat-card">
            <div class="admin-stat-card__head"><div class="admin-stat-card__icon admin-icon--yellow"><i class="fa-solid fa-cart-shopping"></i></div></div>
            <div class="admin-stat-card__label">Tổng lượt đã dùng</div>
            <div class="admin-stat-card__value"><?= number_format((int) ($stats['total_used'] ?? 0)) ?></div>
            <div class="admin-stat-card__meta">Dựa trên dữ liệu hóa đơn</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="admin-stat-card">
            <div class="admin-stat-card__head"><div class="admin-stat-card__icon admin-icon--blue"><i class="fa-solid fa-wallet"></i></div></div>
            <div class="admin-stat-card__label">Ngân sách ưu đãi</div>
            <div class="admin-stat-card__value"><?= number_format((float) ($stats['total_budget'] ?? 0) / 1000000, 0) ?>M</div>
            <div class="admin-stat-card__meta">Tổng ngân sách đã cấu hình</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="admin-stat-card">
            <div class="admin-stat-card__head"><div class="admin-stat-card__icon admin-icon--green"><i class="fa-solid fa-tags"></i></div></div>
            <div class="admin-stat-card__label">Tổng chương trình</div>
            <div class="admin-stat-card__value"><?= (int) ($stats['total_promotions'] ?? 0) ?></div>
            <div class="admin-stat-card__meta">Bao gồm cả đã kết thúc</div>
        </div>
    </div>
</div>

<div class="admin-card admin-section">
    <div class="admin-card__body">
        <form method="GET" class="admin-filter-bar mb-4">
            <input type="hidden" name="action" value="admin_promotions">
            <div class="admin-toolbar mb-0">
                <div class="admin-toolbar__group flex-grow-1">
                    <div class="flex-grow-1">
                        <input class="admin-search" type="text" name="keyword" value="<?= h($filters['keyword'] ?? '') ?>" placeholder="Tìm theo mã hoặc tên chương trình khuyến mãi...">
                    </div>
                    <select class="admin-select admin-control--md" name="status">
                        <option value="">Mọi trạng thái</option>
                        <option value="1" <?= ($filters['status'] ?? '') === '1' ? 'selected' : '' ?>>Đang kích hoạt</option>
                        <option value="0" <?= ($filters['status'] ?? '') === '0' ? 'selected' : '' ?>>Tạm ngưng</option>
                    </select>
                </div>
                <button class="admin-btn admin-btn--light" type="submit"><i class="fa-solid fa-magnifying-glass"></i><span>Tìm kiếm</span></button>
            </div>
        </form>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Mã khuyến mãi</th>
                        <th>Tên chương trình</th>
                        <th>Mức giảm</th>
                        <th>Loại</th>
                        <th>Thời gian</th>
                        <th>Trạng thái</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($promotions)): ?>
                    <tr><td colspan="7"><div class="admin-empty"><i class="fa-regular fa-folder-open"></i><div>Chưa có chương trình khuyến mãi nào.</div></div></td></tr>
                <?php else: ?>
                    <?php foreach ($promotions as $promotion): ?>
                        <tr>
                            <td class="fw-bold text-danger"><?= h($promotion['code']) ?></td>
                            <td>
                                <div class="fw-bold"><?= h($promotion['title']) ?></div>
                                <div class="text-muted small"><?= h($promotion['description'] ?: 'Chưa có mô tả') ?></div>
                            </td>
                            <td class="fw-bold text-success">
                                <?= $promotion['discount_type'] === 'percent'
                                    ? h(rtrim(rtrim(number_format((float) $promotion['discount_value'], 2, '.', ''), '0'), '.')) . '%'
                                    : h(format_currency($promotion['discount_value'])) ?>
                            </td>
                            <td><span class="admin-chip"><?= h(promotion_type_label($promotion['discount_type'])) ?></span></td>
                            <td>
                                <div><?= h(format_date($promotion['start_date'])) ?> - <?= h(format_date($promotion['end_date'])) ?></div>
                                <div class="text-muted small">Đã dùng: <?= number_format((int) $promotion['used_count']) ?><?= $promotion['usage_limit'] ? ' / ' . number_format((int) $promotion['usage_limit']) : '' ?></div>
                            </td>
                            <td><?= promotion_status_badge($promotion) ?></td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    <a class="admin-btn admin-btn--light admin-btn--icon" href="<?= h(admin_url('admin_edit_promotion', ['id' => (int) $promotion['promotion_id']])) ?>">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <button class="admin-btn admin-btn--danger admin-btn--icon" type="button" data-bs-toggle="modal" data-bs-target="#deletePromotionModal<?= (int) $promotion['promotion_id'] ?>">
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
