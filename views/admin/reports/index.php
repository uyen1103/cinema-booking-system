<?php
$overviewOrders = $overview['orders'];
$overviewMovies = $overview['movies'];
$overviewCustomers = $overview['customers'];
$overviewPromotions = $overview['promotions'];
$maxRevenue = max(array_column($revenueBars, 'value')) ?: 1;
$maxMovieTickets = max(array_map(fn($row) => (int) $row['ticket_count'], $topMovies ?: [['ticket_count' => 1]])) ?: 1;
?>

<div class="admin-page-heading">
    <h2>TẠO BÁO CÁO THỐNG KÊ</h2>
    <p>Tổng hợp nhanh doanh thu, lượng vé bán, khách hàng và hiệu quả khuyến mãi trong năm <?= (int) ($_GET['year'] ?? date('Y')) ?>.</p>
</div>

<div class="row g-3 admin-section">
    <div class="col-md-3">
        <div class="admin-stat-card">
            <div class="admin-stat-card__head"><div class="admin-stat-card__icon admin-icon--green"><i class="fa-solid fa-wallet"></i></div></div>
            <div class="admin-stat-card__label">Doanh thu đã thu</div>
            <div class="admin-stat-card__value"><?= number_format((($overviewOrders['paid_revenue'] ?? 0) / 1000000), 1) ?>M</div>
            <div class="admin-stat-card__meta"><?= h(format_currency($overviewOrders['paid_revenue'] ?? 0)) ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="admin-stat-card">
            <div class="admin-stat-card__head"><div class="admin-stat-card__icon admin-icon--blue"><i class="fa-solid fa-ticket"></i></div></div>
            <div class="admin-stat-card__label">Vé đã bán</div>
            <div class="admin-stat-card__value"><?= number_format((int) ($overviewMovies['sold_tickets'] ?? 0)) ?></div>
            <div class="admin-stat-card__meta">Từ hóa đơn không bị hủy</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="admin-stat-card">
            <div class="admin-stat-card__head"><div class="admin-stat-card__icon admin-icon--yellow"><i class="fa-solid fa-users"></i></div></div>
            <div class="admin-stat-card__label">Khách hàng hoạt động</div>
            <div class="admin-stat-card__value"><?= (int) ($overviewCustomers['active_count'] ?? 0) ?></div>
            <div class="admin-stat-card__meta">Khách có thể tiếp tục đặt vé</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="admin-stat-card">
            <div class="admin-stat-card__head"><div class="admin-stat-card__icon admin-icon--red"><i class="fa-solid fa-tags"></i></div></div>
            <div class="admin-stat-card__label">Khuyến mãi hoạt động</div>
            <div class="admin-stat-card__value"><?= (int) ($overviewPromotions['active_promotions'] ?? 0) ?></div>
            <div class="admin-stat-card__meta">Đang có hiệu lực trong hệ thống</div>
        </div>
    </div>
</div>

<div class="admin-report-grid admin-section">
    <div class="admin-card">
        <div class="admin-card__body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold mb-0">Doanh thu theo tháng</h4>
                <form method="GET" class="d-flex gap-2 align-items-center">
                    <input type="hidden" name="action" value="admin_reports">
                    <input class="admin-input admin-control--xs" type="number" name="year" value="<?= (int) ($_GET['year'] ?? date('Y')) ?>">
                    <button class="admin-btn admin-btn--light" type="submit">Xem</button>
                </form>
            </div>

            <div class="admin-chart-bars">
                <?php foreach (($revenueBars ?? []) as $bar): ?>
                    <div class="admin-chart-row">
                        <div class="fw-semibold text-muted"><?= h($bar['label']) ?></div>
                        <div class="admin-chart-bar">
                            <span class="admin-chart-fill" style="--bar-width: <?= max(8, (int) round(($bar['value'] / $maxRevenue) * 100)) ?>%"></span>
                        </div>
                        <div class="fw-semibold"><?= h(format_currency($bar['value'])) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card__body">
            <h4 class="fw-bold mb-3">Tổng quan vận hành</h4>
            <div class="admin-kpi-list">
                <div class="admin-kpi-item">
                    <div class="text-muted small">Hóa đơn hoàn tất</div>
                    <div class="fw-bold"><?= number_format((int) ($overviewOrders['completed_orders'] ?? 0)) ?></div>
                </div>
                <div class="admin-kpi-item">
                    <div class="text-muted small">Suất chiếu hôm nay</div>
                    <div class="fw-bold"><?= number_format((int) ($overview['showtimes']['today_showtimes'] ?? 0)) ?></div>
                </div>
                <div class="admin-kpi-item">
                    <div class="text-muted small">Phim đang chiếu</div>
                    <div class="fw-bold"><?= number_format((int) ($overviewMovies['showing_count'] ?? 0)) ?></div>
                </div>
                <div class="admin-kpi-item">
                    <div class="text-muted small">Lượt dùng khuyến mãi</div>
                    <div class="fw-bold"><?= number_format((int) ($overviewPromotions['total_used'] ?? 0)) ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="admin-report-grid admin-section">
    <div class="admin-card">
        <div class="admin-card__body">
            <h4 class="fw-bold mb-3">Top phim bán vé tốt</h4>
            <div class="admin-chart-bars">
                <?php foreach (($topMovies ?? []) as $movie): ?>
                    <div class="admin-chart-row">
                        <div class="fw-semibold text-muted"><?= h(($movie['title'] ?? 'Chưa cập nhật')) ?></div>
                        <div class="admin-chart-bar">
                            <span class="admin-chart-fill" style="--bar-width: <?= max(8, (int) round(((int) ($movie['ticket_count'] ?? 0) / $maxMovieTickets) * 100)) ?>%"></span>
                        </div>
                        <div class="fw-semibold"><?= number_format((int) ($movie['ticket_count'] ?? 0)) ?> vé</div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card__body">
            <h4 class="fw-bold mb-3">Hiệu quả khuyến mãi</h4>
            <div class="admin-kpi-list">
                <?php foreach (($promotionPerformance ?? []) as $promotion): ?>
                    <div class="admin-kpi-item">
                        <div class="d-flex justify-content-between gap-2">
                            <div>
                                <div class="fw-bold"><?= h(($promotion['title'] ?? 'Khuyến mãi')) ?></div>
                                <div class="text-muted small"><?= h(($promotion['code'] ?? '')) ?></div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold"><?= number_format((int) ($promotion['used_count'] ?? 0)) ?> lượt</div>
                                <div class="text-muted small"><?= h(format_currency($promotion['budget'] ?? 0)) ?></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="admin-card admin-section">
    <div class="admin-card__body">
        <h4 class="fw-bold mb-3">Hóa đơn gần đây</h4>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Mã hóa đơn</th>
                        <th>Khách hàng</th>
                        <th>Ngày đặt</th>
                        <th>Thành tiền</th>
                        <th>Trạng thái đơn</th>
                        <th>Thanh toán</th>
                    </tr>
                </thead>
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
