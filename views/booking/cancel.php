<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="page-section cancel-page">
    <div class="container">
        <div class="section-header">
            <h1>Yêu cầu hủy vé</h1>
        </div>

        <?php if (!empty($success)): ?>
            <div class="success-message">
                <p>✅ <?php echo htmlspecialchars($success); ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p>❌ <?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($existingRequest)): ?>
            <div class="card card-panel cancel-card">
                <div class="cancel-status-card">
                    <p class="cancel-status-label">Yêu cầu đã gửi và đang chờ xử lý</p>
                    <p>Trạng thái: <strong><?php echo htmlspecialchars(ucfirst($existingRequest['status'])); ?></strong></p>
                    <p>Lý do: <?php echo nl2br(htmlspecialchars($existingRequest['reason'])); ?></p>
                </div>
            </div>
        <?php else: ?>
            <div class="card card-panel cancel-card">
                <div class="cancel-summary">
                    <div>
                        <h2>Đơn <?php echo htmlspecialchars($order['order_code']); ?></h2>
                        <p class="cancel-meta">Ngày tạo: <?php echo htmlspecialchars($order['order_date']); ?> • Trạng thái: <strong><?php echo htmlspecialchars($order['order_status']); ?></strong></p>
                    </div>
                    <div class="cancel-tip">
                        <p>Vui lòng mô tả lý do hủy vé rõ ràng để nhân viên xử lý nhanh.</p>
                    </div>
                </div>

                <form method="POST" class="booking-form cancel-form">
                    <div class="form-group">
                        <label for="reason">Lý do hủy vé</label>
                        <textarea id="reason" name="reason" class="form-control" rows="6" placeholder="Nhập lý do..." required></textarea>
                    </div>
                    <p class="form-help">Ghi rõ lý do, ví dụ: thay đổi lịch, ốm, hoặc trùng giờ.</p>
                    <button type="submit" class="btn btn-primary btn-block">Gửi yêu cầu</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
