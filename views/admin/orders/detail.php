<div class="admin-page-heading d-flex flex-wrap justify-content-between gap-3">
    <div>
        <h2>CHI TIẾT HÓA ĐƠN: <?= h($order['order_code']) ?></h2>
        <p>Kiểm tra danh sách vé, duyệt vé, hủy vé và cập nhật trạng thái thanh toán.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a class="admin-btn admin-btn--light" href="<?= h(admin_url('admin_orders')) ?>">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Quay lại</span>
        </a>
        <?php if (!in_array(($order['order_status'] ?? ''), ['completed', 'cancelled'], true)): ?>
            <form method="POST" action="<?= h(admin_url('admin_approve_order')) ?>">
                <input type="hidden" name="order_id" value="<?= (int) $order['order_id'] ?>">
                <button class="admin-btn admin-btn--success" type="submit">
                    <i class="fa-solid fa-check"></i>
                    <span>Duyệt vé</span>
                </button>
            </form>
        <?php endif; ?>
        <?php if (($order['order_status'] ?? '') !== 'cancelled'): ?>
            <form method="POST" action="<?= h(admin_url('admin_cancel_order')) ?>" class="d-flex gap-2">
                <input type="hidden" name="order_id" value="<?= (int) $order['order_id'] ?>">
                <input class="admin-input admin-control--sm" type="text" name="cancel_note" placeholder="Lý do hủy">
                <button class="admin-btn admin-btn--danger" type="submit">
                    <i class="fa-solid fa-ban"></i>
                    <span>Hủy vé</span>
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="row g-3 admin-section">
    <div class="col-lg-7">
        <div class="admin-card">
            <div class="admin-card__body">
                <h4 class="fw-bold mb-3">Thông tin hóa đơn</h4>
                <div class="admin-form-grid admin-form-grid--2">
                    <div class="admin-kpi-item">
                        <div class="text-muted small">Khách hàng</div>
                        <div class="fw-bold"><?= h($order['full_name']) ?></div>
                        <div class="text-muted small"><?= h($order['email']) ?> · <?= h($order['phone']) ?></div>
                    </div>
                    <div class="admin-kpi-item">
                        <div class="text-muted small">Ngày đặt</div>
                        <div class="fw-bold"><?= h(format_datetime($order['order_date'])) ?></div>
                        <div class="text-muted small">Mã khuyến mãi: <?= h($order['promotion_code'] ?: 'Không áp dụng') ?></div>
                    </div>
                    <div class="admin-kpi-item">
                        <div class="text-muted small">Tổng tiền gốc</div>
                        <div class="fw-bold"><?= h(format_currency($order['total_amount'])) ?></div>
                    </div>
                    <div class="admin-kpi-item">
                        <div class="text-muted small">Thành tiền</div>
                        <div class="fw-bold text-success"><?= h(format_currency($order['final_amount'])) ?></div>
                        <div class="text-muted small">Giảm: <?= h(format_currency($order['discount_amount'])) ?></div>
                    </div>
                </div>


                <?php if (!empty($cancellationRequest)): ?>
                    <div class="admin-kpi-item mt-4">
                        <div class="text-muted small">Yêu cầu hủy vé từ khách hàng</div>
                        <div class="fw-bold">Trạng thái: <?= h(ucfirst($cancellationRequest['status'])) ?></div>
                        <div class="text-muted small"><?= h(format_datetime($cancellationRequest['request_date'])) ?></div>
                        <div class="mt-2"><?= nl2br(h($cancellationRequest['reason'])) ?></div>
                    </div>
                <?php endif; ?>

                <h5 class="fw-bold mt-4 mb-3">Danh sách vé</h5>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Phim</th>
                                <th>Lịch chiếu</th>
                                <th>Ghế</th>
                                <th>Giá vé</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($order['tickets'] as $ticket): ?>
                            <tr>
                                <td><?= h($ticket['movie_title']) ?></td>
                                <td><?= h(format_date($ticket['show_date'])) ?> · <?= h(substr($ticket['start_time'],0,5)) ?></td>
                                <td><?= h($ticket['room_name']) ?> / <?= h($ticket['seat_label']) ?></td>
                                <td><?= h(format_currency($ticket['price'])) ?></td>
                                <td><?= h(ucfirst($ticket['ticket_status'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="admin-card">
            <div class="admin-card__body">
                <h4 class="fw-bold mb-3">Cập nhật trạng thái</h4>
                <form method="POST" action="<?= h(admin_url('admin_update_order_status')) ?>" class="admin-form-grid">
                    <input type="hidden" name="order_id" value="<?= (int) $order['order_id'] ?>">

                    <div>
                        <label class="admin-form-label">Trạng thái đơn hàng</label>
                        <select class="admin-select" name="order_status">
                            <option value="pending" <?= $order['order_status'] === 'pending' ? 'selected' : '' ?>>Chờ xử lý</option>
                            <option value="completed" <?= $order['order_status'] === 'completed' ? 'selected' : '' ?>>Hoàn tất</option>
                            <option value="cancelled" <?= $order['order_status'] === 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                        </select>
                    </div>

                    <div>
                        <label class="admin-form-label">Trạng thái thanh toán</label>
                        <select class="admin-select" name="payment_status">
                            <option value="pending" <?= $order['payment_status'] === 'pending' ? 'selected' : '' ?>>Chưa thanh toán</option>
                            <option value="paid" <?= $order['payment_status'] === 'paid' ? 'selected' : '' ?>>Đã thanh toán</option>
                            <option value="failed" <?= $order['payment_status'] === 'failed' ? 'selected' : '' ?>>Thanh toán lỗi</option>
                            <option value="refunded" <?= $order['payment_status'] === 'refunded' ? 'selected' : '' ?>>Đã hoàn tiền</option>
                        </select>
                    </div>

                    <div>
                        <label class="admin-form-label">Phương thức thanh toán</label>
                        <select class="admin-select" name="payment_method">
                            <?php foreach (['cash' => 'Tiền mặt', 'bank_transfer' => 'Chuyển khoản', 'momo' => 'MoMo', 'vnpay' => 'VNPay'] as $value => $label): ?>
                                <option value="<?= h($value) ?>" <?= $order['payment_method'] === $value ? 'selected' : '' ?>><?= h($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="admin-form-label">Ghi chú</label>
                        <textarea class="admin-textarea" name="notes"><?= h($order['notes']) ?></textarea>
                    </div>

                    <button class="admin-btn admin-btn--primary" type="submit">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Lưu cập nhật</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
