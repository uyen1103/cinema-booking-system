<?php
$orders = $overview['orders'] ?? [];
$movies = $overview['movies'] ?? [];
$customers = $overview['customers'] ?? [];
$promotions = $overview['promotions'] ?? [];
?>
<div class="admin-page-heading d-flex flex-wrap justify-content-between gap-3 align-items-start">
    <div>
        <h2>BẢNG ĐIỀU KHIỂN</h2>
        <p>Theo dõi nhanh vận hành admin, yêu cầu hủy vé, doanh thu và các hóa đơn mới nhất.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a class="admin-btn admin-btn--light" href="<?= h(admin_url('admin_reports')) ?>">
            <i class="fa-solid fa-chart-pie"></i>
            <span>Xem báo cáo thống kê</span>
        </a>
        <a class="admin-btn admin-btn--primary" href="<?= h(admin_url('admin_cancellation_requests')) ?>">
            <i class="fa-solid fa-clipboard-check"></i>
            <span>Kiểm duyệt vé<?php if (!empty($pendingRequests)): ?> (<?= count($pendingRequests) ?>)<?php endif; ?></span>
        </a>
    </div>
</div>

<div class="row g-3 admin-section">
    <div class="col-md-3"><div class="admin-stat-card"><div class="admin-stat-card__label">Doanh thu đã thu</div><div class="admin-stat-card__value"><?= number_format((float) ($orders['paid_revenue'] ?? 0) / 1000000, 1) ?>M</div><div class="admin-stat-card__meta"><?= h(format_currency($orders['paid_revenue'] ?? 0)) ?></div></div></div>
    <div class="col-md-3"><div class="admin-stat-card"><div class="admin-stat-card__label">Hóa đơn hoàn tất</div><div class="admin-stat-card__value"><?= (int) ($orders['completed_orders'] ?? 0) ?></div><div class="admin-stat-card__meta">Đơn đã ghi nhận doanh thu</div></div></div>
    <div class="col-md-3"><div class="admin-stat-card"><div class="admin-stat-card__label">Khách hàng hoạt động</div><div class="admin-stat-card__value"><?= (int) ($customers['active_count'] ?? 0) ?></div><div class="admin-stat-card__meta">Khách có thể tiếp tục đặt vé</div></div></div>
    <div class="col-md-3"><div class="admin-stat-card"><div class="admin-stat-card__label">Khuyến mãi hoạt động</div><div class="admin-stat-card__value"><?= (int) ($promotions['active_promotions'] ?? 0) ?></div><div class="admin-stat-card__meta">Đang chạy trong hệ thống</div></div></div>
</div>

<div class="admin-report-grid admin-section">
    <div class="admin-card">
        <div class="admin-card__body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold mb-0">Kiểm duyệt yêu cầu hủy vé</h4>
                <a class="admin-btn admin-btn--light" href="<?= h(admin_url('admin_cancellation_requests')) ?>">Mở danh sách đầy đủ</a>
            </div>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead><tr><th>Mã đơn</th><th>Khách hàng</th><th>Lý do</th><th>Ngày yêu cầu</th><th class="text-end">Xử lý</th></tr></thead>
                    <tbody>
                    <?php if (empty($pendingRequests)): ?>
                        <tr><td colspan="5"><div class="admin-empty"><i class="fa-regular fa-circle-check"></i><div>Không có yêu cầu hủy vé đang chờ duyệt.</div></div></td></tr>
                    <?php else: ?>
                        <?php foreach (($pendingRequests ?? []) as $request): ?>
                            <tr>
                                <td class="fw-bold text-danger"><?= h(($request['order_code'] ?? '')) ?></td>
                                <td><div class="fw-bold"><?= h(($request['full_name'] ?? 'Khách hàng')) ?></div><div class="text-muted small"><?= h($request['email']) ?></div></td>
                                <td><?= nl2br(h(($request['reason'] ?? ''))) ?></td>
                                <td><?= h(format_datetime($request['request_date'] ?? null)) ?></td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2 flex-wrap">
                                        <form method="POST" action="<?= h(admin_url('admin_approve_cancel')) ?>">
                                            <input type="hidden" name="request_id" value="<?= (int) ($request['request_id'] ?? 0) ?>">
                                            <button type="submit" name="decision" value="approved" class="admin-btn admin-btn--success">Duyệt</button>
                                        </form>
                                        <form method="POST" action="<?= h(admin_url('admin_approve_cancel')) ?>">
                                            <input type="hidden" name="request_id" value="<?= (int) ($request['request_id'] ?? 0) ?>">
                                            <button type="submit" name="decision" value="rejected" class="admin-btn admin-btn--danger">Từ chối</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card__body">
            <h4 class="fw-bold mb-3">Tổng quan nhanh</h4>
            <div class="admin-kpi-list">
                <div class="admin-kpi-item"><div class="text-muted small">Tổng hóa đơn</div><div class="fw-bold"><?= (int) ($orders['total_orders'] ?? 0) ?></div></div>
                <div class="admin-kpi-item"><div class="text-muted small">Đang chờ xử lý</div><div class="fw-bold"><?= (int) ($orders['pending_orders'] ?? 0) ?></div></div>
                <div class="admin-kpi-item"><div class="text-muted small">Vé đã bán</div><div class="fw-bold"><?= (int) ($movies['sold_tickets'] ?? 0) ?></div></div>
                <div class="admin-kpi-item"><div class="text-muted small">Phim đang chiếu</div><div class="fw-bold"><?= (int) ($movies['showing_count'] ?? 0) ?></div></div>
            </div>
        </div>
    </div>
</div>

<div class="admin-card admin-section">
    <div class="admin-card__body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0">Hóa đơn gần đây</h4>
            <a class="admin-btn admin-btn--light" href="<?= h(admin_url('admin_orders')) ?>">Sang quản lý hóa đơn</a>
        </div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead><tr><th>Mã hóa đơn</th><th>Khách hàng</th><th>Ngày đặt</th><th>Thành tiền</th><th>Trạng thái</th><th>Thanh toán</th></tr></thead>
                <tbody>
                <?php foreach (($recentInvoices ?? []) as $invoice): ?>
                    <tr>
                        <td class="fw-bold text-danger"><?= h(($invoice['order_code'] ?? '')) ?></td>
                        <td><?= h(($invoice['full_name'] ?? 'Khách hàng')) ?></td>
                        <td><?= h(format_datetime($invoice['order_date'] ?? null)) ?></td>
                        <td><?= h(format_currency($invoice['final_amount'] ?? 0)) ?></td>
                        <td><?= h(ucfirst(($invoice['order_status'] ?? 'pending'))) ?></td>
                        <td><?= h(ucfirst(($invoice['payment_status'] ?? 'pending'))) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
