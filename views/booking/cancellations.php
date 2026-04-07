<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="page-section admin-page">
    <div class="container">
        <div class="section-header">
            <h1>Danh sách yêu cầu hủy vé</h1>
        </div>

        <?php if (!empty($requests)): ?>
            <div class="request-list">
                <?php foreach ($requests as $request): ?>
                    <div class="history-card">
                        <div class="history-card-header">
                            <div>
                                <h2>Đơn <?php echo htmlspecialchars($request['order_code']); ?></h2>
                                <p>Người gửi: <?php echo htmlspecialchars($request['full_name']); ?></p>
                                <p>Ngày yêu cầu: <?php echo htmlspecialchars($request['request_date']); ?></p>
                            </div>
                            <div>
                                <span class="badge badge-pending"><?php echo htmlspecialchars($request['status']); ?></span>
                            </div>
                        </div>

                        <div class="history-card-body">
                            <p><strong>Lý do:</strong></p>
                            <p><?php echo nl2br(htmlspecialchars($request['reason'])); ?></p>

                            <form method="POST" class="admin-actions">
                                <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($request['request_id']); ?>" />
                                <button type="submit" name="decision" value="approved" class="btn btn-primary">Duyệt hủy</button>
                                <button type="submit" name="decision" value="rejected" class="btn btn-secondary">Từ chối</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <p>Không có yêu cầu hủy vé chờ xử lý.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
