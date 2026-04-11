<?php
$selectedShowtimeId = (int) ($selectedShowtime['showtime_id'] ?? 0);
?>

<div class="admin-page-heading d-flex flex-wrap justify-content-between gap-3">
    <div>
        <h2>TẠO ĐƠN VÉ</h2>
        <p>Tạo đơn vé thủ công cho khách hàng, chọn suất chiếu và ghế còn trống.</p>
    </div>
    <a class="admin-btn admin-btn--light" href="<?= h(admin_url('admin_orders')) ?>">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Quay lại</span>
    </a>
</div>

<div class="row g-3 admin-section">
    <div class="col-lg-4">
        <div class="admin-card admin-form-card">
            <div class="admin-card__body">
                <form method="GET" class="admin-form-grid">
                    <input type="hidden" name="action" value="create_order">
                    <div>
                        <label class="admin-form-label">Suất chiếu</label>
                        <select name="showtime_id" class="admin-select" onchange="this.form.submit()">
                            <option value="">Chọn suất chiếu</option>
                            <?php foreach ($showtimes as $showtime): ?>
                                <option value="<?= (int) $showtime['showtime_id'] ?>" <?= (int) $showtime['showtime_id'] === $selectedShowtimeId ? 'selected' : '' ?>>
                                    <?= h($showtime['movie_title']) ?> · <?= h(format_date($showtime['show_date'])) ?> · <?= h(substr($showtime['start_time'], 0, 5)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>

                <?php if ($selectedShowtime): ?>
                    <div class="admin-kpi-item mt-3">
                        <div class="text-muted small">Phim</div>
                        <div class="fw-bold"><?= h($selectedShowtime['title']) ?></div>
                        <div class="text-muted small"><?= h($selectedShowtime['room_name']) ?> · <?= h(format_date($selectedShowtime['show_date'])) ?> · <?= h(substr($selectedShowtime['start_time'], 0, 5)) ?></div>
                    </div>
                <?php else: ?>
                    <div class="admin-empty py-4">
                        <i class="fa-regular fa-calendar"></i>
                        <div>Chọn suất chiếu để tiếp tục.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="admin-card admin-form-card">
            <div class="admin-card__body">
                <form method="POST" action="<?= h(admin_url('admin_store_order')) ?>" class="admin-form-grid">
                    <input type="hidden" name="showtime_id" value="<?= $selectedShowtimeId ?>">

                    <div class="admin-form-grid admin-form-grid--2">
                        <div>
                            <label class="admin-form-label">Khách hàng</label>
                            <select class="admin-select" name="user_id" required>
                                <option value="">Chọn khách hàng</option>
                                <?php foreach ($customers as $customer): ?>
                                    <option value="<?= (int) $customer['user_id'] ?>"><?= h($customer['full_name']) ?> · <?= h($customer['email']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="admin-form-label">Khuyến mãi</label>
                            <select class="admin-select" name="promotion_id">
                                <option value="">Không áp dụng</option>
                                <?php foreach ($promotions as $promotion): ?>
                                    <option value="<?= (int) $promotion['promotion_id'] ?>">
                                        <?= h(($promotion['code'] ?? $promotion['promo_code']) . ' - ' . ($promotion['title'] ?? 'Khuyến mãi')) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="admin-form-label">Trạng thái thanh toán</label>
                            <select class="admin-select" name="payment_status">
                                <option value="pending">Chưa thanh toán</option>
                                <option value="paid">Đã thanh toán</option>
                            </select>
                        </div>
                        <div>
                            <label class="admin-form-label">Trạng thái đơn</label>
                            <select class="admin-select" name="order_status">
                                <option value="pending">Chờ xử lý</option>
                                <option value="completed">Hoàn tất</option>
                            </select>
                        </div>
                        <div>
                            <label class="admin-form-label">Phương thức thanh toán</label>
                            <select class="admin-select" name="payment_method">
                                <option value="cash">Tiền mặt</option>
                                <option value="bank_transfer">Chuyển khoản</option>
                                <option value="momo">MoMo</option>
                                <option value="vnpay">VNPay</option>
                            </select>
                        </div>
                        <div>
                            <label class="admin-form-label">Ghi chú</label>
                            <input class="admin-input" type="text" name="notes" placeholder="Ghi chú nội bộ nếu có">
                        </div>
                    </div>

                    <div>
                        <label class="admin-form-label">Chọn ghế</label>
                        <?php if (empty($seatMap)): ?>
                            <div class="admin-empty py-4">
                                <i class="fa-solid fa-couch"></i>
                                <div>Chưa có ghế khả dụng cho suất chiếu này.</div>
                            </div>
                        <?php else: ?>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($seatMap as $seat): ?>
                                    <?php
                                    $disabled = (int) ($seat['reserved'] ?? 0) === 1;
                                    $seatLabel = ($seat['seat_row'] ?? '') . ($seat['seat_number'] ?? '');
                                    ?>
                                    <label class="admin-chip <?= $disabled ? 'admin-badge admin-badge--muted' : '' ?>">
                                        <input type="checkbox" name="seat_ids[]" value="<?= (int) $seat['seat_id'] ?>" <?= $disabled ? 'disabled' : '' ?>>
                                        <?= h($seatLabel) ?> · <?= h(strtoupper($seat['seat_type'] ?? 'standard')) ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <button class="admin-btn admin-btn--primary" type="submit">
                            <i class="fa-solid fa-ticket"></i>
                            <span>Tạo đơn vé</span>
                        </button>
                        <a class="admin-btn admin-btn--light" href="<?= h(admin_url('admin_orders')) ?>">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
