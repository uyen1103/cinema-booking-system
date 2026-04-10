<div class="admin-page-heading d-flex flex-wrap justify-content-between gap-3 align-items-start">
    <div>
        <h2>YÊU CẦU HỦY VÉ</h2>
        <p>Xử lý yêu cầu hủy vé từ khách hàng. Khi duyệt, đơn vé, trạng thái thanh toán và báo cáo doanh thu sẽ tự cập nhật.</p>
    </div>
    <a class="admin-btn admin-btn--light" href="<?= h(admin_url('admin_orders')) ?>">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Quay lại hóa đơn</span>
    </a>
</div>

<div class="admin-card admin-section">
    <div class="admin-card__body">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Mã hóa đơn</th>
                        <th>Khách hàng</th>
                        <th>Lý do</th>
                        <th>Số tiền</th>
                        <th>Trạng thái yêu cầu</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($requests)): ?>
                    <tr>
                        <td colspan="6">
                            <div class="admin-empty">
                                <i class="fa-regular fa-folder-open"></i>
                                <div>Không có yêu cầu hủy vé nào.</div>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($requests as $request): ?>
                        <?php $isPending = ($request['status'] ?? '') === 'pending'; ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-danger"><?= h($request['order_code']) ?></div>
                                <div class="text-muted small"><?= h(format_datetime($request['request_date'])) ?></div>
                            </td>
                            <td>
                                <div class="fw-bold"><?= h($request['full_name']) ?></div>
                                <div class="text-muted small"><?= h($request['email']) ?></div>
                            </td>
                            <td style="max-width: 280px; white-space: normal;"><?= nl2br(h($request['reason'])) ?></td>
                            <td>
                                <div class="fw-bold"><?= h(format_currency($request['final_amount'] ?? 0)) ?></div>
                                <div class="text-muted small">Đơn: <?= h($request['order_status'] ?? '--') ?> / TT: <?= h($request['payment_status'] ?? '--') ?></div>
                            </td>
                            <td>
                                <?php if ($request['status'] === 'approved'): ?>
                                    <span class="admin-badge admin-badge--success">Đã duyệt</span>
                                <?php elseif ($request['status'] === 'rejected'): ?>
                                    <span class="admin-badge admin-badge--danger">Đã từ chối</span>
                                <?php else: ?>
                                    <span class="admin-badge admin-badge--warning">Chờ xử lý</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2 flex-wrap">
                                    <a class="admin-btn admin-btn--light" href="<?= h(admin_url('admin_order_detail', ['id' => (int) $request['order_id']])) ?>">
                                        <i class="fa-regular fa-eye"></i>
                                        <span>Xem đơn</span>
                                    </a>
                                    <?php if ($isPending): ?>
                                        <form method="POST" action="<?= h(admin_url('admin_approve_cancel')) ?>">
                                            <input type="hidden" name="request_id" value="<?= (int) $request['request_id'] ?>">
                                            <button type="submit" name="decision" value="approved" class="admin-btn admin-btn--success">
                                                <i class="fa-solid fa-check"></i>
                                                <span>Duyệt hủy</span>
                                            </button>
                                        </form>
                                        <form method="POST" action="<?= h(admin_url('admin_approve_cancel')) ?>">
                                            <input type="hidden" name="request_id" value="<?= (int) $request['request_id'] ?>">
                                            <button type="submit" name="decision" value="rejected" class="admin-btn admin-btn--danger">
                                                <i class="fa-solid fa-xmark"></i>
                                                <span>Từ chối</span>
                                            </button>
                                        </form>
                                    <?php endif; ?>
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
