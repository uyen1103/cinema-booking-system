<?php
function order_status_badge(string $status): string {
    return match ($status) {
        'completed', 'paid' => '<span class="admin-badge admin-badge--success">Hoàn tất</span>',
        'cancelled' => '<span class="admin-badge admin-badge--danger">Đã hủy</span>',
        default => '<span class="admin-badge admin-badge--warning">Chờ xử lý</span>',
    };
}

function payment_status_badge(string $status): string {
    return match ($status) {
        'paid', 'success' => '<span class="admin-badge admin-badge--success">Đã thanh toán</span>',
        'refunded' => '<span class="admin-badge admin-badge--info">Đã hoàn tiền</span>',
        'failed' => '<span class="admin-badge admin-badge--danger">Thanh toán lỗi</span>',
        default => '<span class="admin-badge admin-badge--warning">Chưa thanh toán</span>',
    };
}
?>

<div class="admin-page-heading d-flex flex-wrap justify-content-between gap-3 align-items-start">
    <div>
        <h2>QUẢN LÝ HÓA ĐƠN</h2>
        <p>Kiểm soát trạng thái thanh toán, tạo đơn vé thủ công và doanh thu phát sinh.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a class="admin-btn admin-btn--light" href="<?= h(admin_url('admin_cancellation_requests')) ?>">
            <i class="fa-regular fa-rectangle-list"></i>
            <span>Yêu cầu hủy<?= !empty($pendingCancellationCount) ? ' (' . (int) $pendingCancellationCount . ')' : '' ?></span>
        </a>
        <a class="admin-btn admin-btn--primary" href="<?= h(admin_url('admin_create_order')) ?>">
            <i class="fa-solid fa-plus"></i>
            <span>Tạo đơn vé</span>
        </a>
    </div>
</div>

<div class="row g-3 admin-section">
    <div class="col-md-3">
        <div class="admin-stat-card">
            <div class="admin-stat-card__head"><div class="admin-stat-card__icon admin-icon--blue"><i class="fa-solid fa-file-invoice"></i></div></div>
            <div class="admin-stat-card__label">Tổng hóa đơn</div>
            <div class="admin-stat-card__value"><?= (int) ($stats['total_orders'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="admin-stat-card">
            <div class="admin-stat-card__head"><div class="admin-stat-card__icon admin-icon--green"><i class="fa-solid fa-circle-check"></i></div></div>
            <div class="admin-stat-card__label">Hoàn tất</div>
            <div class="admin-stat-card__value"><?= (int) ($stats['completed_orders'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="admin-stat-card">
            <div class="admin-stat-card__head"><div class="admin-stat-card__icon admin-icon--yellow"><i class="fa-solid fa-hourglass-half"></i></div></div>
            <div class="admin-stat-card__label">Đang chờ</div>
            <div class="admin-stat-card__value"><?= (int) ($stats['pending_orders'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="admin-stat-card">
            <div class="admin-stat-card__head"><div class="admin-stat-card__icon admin-icon--red"><i class="fa-solid fa-wallet"></i></div></div>
            <div class="admin-stat-card__label">Doanh thu đã thu</div>
            <div class="admin-stat-card__value"><?= number_format((float) ($stats['paid_revenue'] ?? 0) / 1000, 0) ?>K</div>
            <div class="admin-stat-card__meta"><?= h(format_currency($stats['paid_revenue'] ?? 0)) ?></div>
        </div>
    </div>
</div>

<div class="admin-card admin-section">
    <div class="admin-card__body">
        <form method="GET" class="admin-filter-bar mb-4">
            <input type="hidden" name="action" value="admin_orders">
            <div class="admin-toolbar mb-0">
                <div class="admin-toolbar__group flex-grow-1">
                    <div class="flex-grow-1">
                        <input class="admin-search" type="text" name="keyword" value="<?= h($filters['keyword'] ?? '') ?>" placeholder="Tìm theo mã hóa đơn, khách hàng, email...">
                    </div>
                    <select class="admin-select admin-control--sm" name="order_status">
                        <option value="">Mọi trạng thái đơn</option>
                        <option value="pending" <?= ($filters['order_status'] ?? '') === 'pending' ? 'selected' : '' ?>>Chờ xử lý</option>
                        <option value="completed" <?= ($filters['order_status'] ?? '') === 'completed' ? 'selected' : '' ?>>Hoàn tất</option>
                        <option value="cancelled" <?= ($filters['order_status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                    </select>
                    <select class="admin-select admin-control--sm" name="payment_status">
                        <option value="">Mọi thanh toán</option>
                        <option value="pending" <?= ($filters['payment_status'] ?? '') === 'pending' ? 'selected' : '' ?>>Chưa thanh toán</option>
                        <option value="paid" <?= ($filters['payment_status'] ?? '') === 'paid' ? 'selected' : '' ?>>Đã thanh toán</option>
                        <option value="failed" <?= ($filters['payment_status'] ?? '') === 'failed' ? 'selected' : '' ?>>Thanh toán lỗi</option>
                    </select>
                    <input class="admin-input admin-control--sm" type="date" name="date" value="<?= h($filters['date'] ?? '') ?>">
                </div>
                <button class="admin-btn admin-btn--light" type="submit"><i class="fa-solid fa-filter"></i> <span>Lọc</span></button>
            </div>
        </form>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Mã hóa đơn</th>
                        <th>Khách hàng</th>
                        <th>Số vé</th>
                        <th>Khuyến mãi</th>
                        <th>Thành tiền</th>
                        <th>Thanh toán</th>
                        <th>Trạng thái</th>
                        <th class="text-end">Chi tiết</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="8"><div class="admin-empty"><i class="fa-regular fa-folder-open"></i><div>Chưa có hóa đơn nào.</div></div></td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-danger"><?= h($order['order_code'] ?? '') ?></div>
                                <div class="text-muted small"><?= h(format_datetime($order['order_date'] ?? null)) ?></div>
                            </td>
                            <td>
                                <div class="fw-bold"><?= h($order['full_name'] ?? 'Khách hàng') ?></div>
                                <div class="text-muted small"><?= h($order['email'] ?? '') ?></div>
                            </td>
                            <td><?= number_format((int) ($order['ticket_count'] ?? 0)) ?> vé</td>
                            <td><?= h(($order['promotion_code'] ?? $order['promo_code'] ?? '') ?: 'Không áp dụng') ?></td>
                            <td>
                                <div class="fw-bold"><?= h(format_currency($order['final_amount'] ?? 0)) ?></div>
                                <?php if ((float) ($order['discount_amount'] ?? 0) > 0): ?>
                                    <div class="text-muted small">Giảm: <?= h(format_currency($order['discount_amount'] ?? 0)) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= payment_status_badge((string) ($order['payment_status'] ?? 'pending')) ?></td>
                            <td><?= order_status_badge((string) ($order['order_status'] ?? 'pending')) ?></td>
                            <td class="text-end">
                                <a class="admin-btn admin-btn--light" href="<?= h(admin_url('admin_order_detail', ['id' => (int) ($order['order_id'] ?? 0)])) ?>">
                                    <i class="fa-regular fa-eye"></i>
                                    <span>Xem</span>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
