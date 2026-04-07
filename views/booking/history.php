<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="page-section history-page">
    <div class="container">
        <?php if (isset($_GET['message'])): ?>
            <div class="success-message">✅ <?php echo htmlspecialchars($_GET['message']); ?></div>
        <?php endif; ?>

        <?php if (!empty($orders)): ?>
            <div class="history-list">
                <?php foreach ($orders as $order): ?>
                    <div class="history-card">
                        <div class="history-card-header">
                            <div>
                                <h2>Đơn <?php echo htmlspecialchars($order['order_code']); ?></h2>
                                <p class="history-meta"><?php echo htmlspecialchars($order['order_date']); ?> • <span class="status-badge status-<?php echo htmlspecialchars($order['order_status']); ?>"><?php echo htmlspecialchars(ucfirst($order['order_status'])); ?></span></p>
                            </div>
                            <div class="history-card-price">
                                <p>Thanh toán</p>
                                <strong><?php echo number_format($order['final_amount'], 0, ',', '.'); ?>₫</strong>
                            </div>
                        </div>

                        <div class="history-card-body">
                            <div class="ticket-list">
                                <?php foreach ($ticketMap[$order['order_id']] as $ticket): ?>
                                    <div class="ticket-item">
                                        <span class="ticket-seat"><?php echo htmlspecialchars($ticket['seat_row'] . $ticket['seat_number']); ?></span>
                                        <div class="ticket-info">
                                            <strong><?php echo htmlspecialchars($ticket['title']); ?></strong>
                                            <div class="ticket-meta">
                                                <span>Rạp/phòng: <?php echo htmlspecialchars($ticket['room_name']); ?></span>
                                                <span>Ngày giờ: <?php echo htmlspecialchars($ticket['show_date']); ?> • <?php echo htmlspecialchars(substr($ticket['start_time'], 0, 5)); ?> - <?php echo htmlspecialchars(substr($ticket['end_time'], 0, 5)); ?></span>
                                                <span>Giá vé: <?php echo number_format($ticket['price'], 0, ',', '.'); ?>₫</span>
                                            </div>
                                        </div>
                                        <span class="ticket-status"><?php echo htmlspecialchars(ucfirst($ticket['ticket_status'])); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <?php if (!empty($cancellationMap[$order['order_id']])): ?>
                                <div class="cancellation-status">
                                    <p>Yêu cầu hủy vé: <strong><?php echo htmlspecialchars(ucfirst($cancellationMap[$order['order_id']]['status'])); ?></strong></p>
                                    <p>Lý do: <?php echo nl2br(htmlspecialchars($cancellationMap[$order['order_id']]['reason'])); ?></p>
                                </div>
                            <?php elseif ($order['order_status'] === 'paid'): ?>
                                <div class="history-actions">
                                    <a href="web.php?action=cancel-booking&order_id=<?php echo $order['order_id']; ?>" class="btn btn-secondary">Yêu cầu hủy vé</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h2>Chưa có vé đặt</h2>
                <p>Bạn hiện chưa có đơn đặt vé nào. Hãy đặt vé để xem phim yêu thích.</p>
                <a href="web.php?action=home" class="btn btn-primary">Xem phim ngay</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
