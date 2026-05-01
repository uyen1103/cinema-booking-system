<?php require_once __DIR__ . '/../layouts/header.php'; 

// Extract cinema and hall information from room_name
$roomName = $tickets[0]['room_name'] ?? '';
$cinemaName = $roomName;
$hallName = '';
if (strpos($roomName, ' - ') !== false) {
    list($cinemaName, $hallName) = explode(' - ', $roomName, 2);
} else {
    $hallName = $roomName;
}

$paymentMethodLabel = match (strtolower((string) ($order['payment_method'] ?? 'cash'))) {
    'momo' => 'Ví điện tử MoMo',
    'zalopay' => 'Ví điện tử ZaloPay',
    'vnpay' => 'Thanh toán QR / VNPAY',
    'bank_transfer' => 'Thẻ / Chuyển khoản ngân hàng',
    'cash' => 'Tiền mặt',
    default => ucfirst((string) ($order['payment_method'] ?? 'Thanh toán trực tuyến')),
};

?>

<div class="page-section success-page">
    <div class="container">
        <div class="success-panel card-panel">
            <!-- Success Header with Icon and Title -->
            <div class="success-header-content">
                <div class="success-header-icon">✓</div>
                <h1 class="success-header-title">Đặt vé thành công!</h1>
                <p class="success-header-text">Cảm ơn bạn đã lựa chọn Cinema Central. Chúc bạn có những giây phút xem phim tuyệt vời! Thông tin vé của bạn đã được gửi qua email.</p>
            </div>

            <!-- Main Content: 2 Column Layout -->
            <div class="success-details">
                <!-- Left Column: Movie Details Card -->
                <div class="success-left-column">
                    <div class="success-box success-detail-card">
                        <div class="success-card-header">
                            <h2 class="detail-title">Chi tiết vé xem phim</h2>
                            <div class="status-badge">Đã thanh toán</div>
                        </div>
                        <div class="success-card-body">
                            <div class="success-poster-block">
                                <img src="<?php echo htmlspecialchars(getPosterUrl($tickets[0]['poster_url'] ?? '')); ?>" alt="Poster" class="success-poster-img">
                            </div>
                            <div class="success-card-details">
                                <div class="summary-row"><span>Mã vé</span><strong><?php echo htmlspecialchars($order['order_code']); ?></strong></div>
                                <div class="summary-row"><span>Phim</span><strong><?php echo htmlspecialchars($tickets[0]['title'] ?? ''); ?></strong></div>
                                <div class="summary-row"><span>Suất chiếu</span><strong><?php echo date('H:i - d/m/Y', strtotime($tickets[0]['show_date'] . ' ' . $tickets[0]['start_time'])); ?></strong></div>
                                <div class="summary-row"><span>Rạp/Phòng </span><strong><?php echo htmlspecialchars($cinemaName); ?> / P.<?php echo htmlspecialchars($hallName); ?></strong></div>
                                <div class="summary-row"><span>Ghế</span><strong><?php echo htmlspecialchars(implode(', ', array_map(function($ticket) { return $ticket['seat_row'] . $ticket['seat_number']; }, $tickets))); ?></strong></div>
                                <div class="summary-row"><span>Hình thức thanh toán</span><strong><?php echo htmlspecialchars($paymentMethodLabel); ?></strong></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: QR Code -->
                <div class="success-right-column">
                    <div class="qr-section">
                        <h2 class="qr-title">Mã QR vào Rạp</h2>
                        
                        <div class="qr-card">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=ORD<?php echo htmlspecialchars($order['order_code']); ?>" alt="Mã QR vào rạp" class="success-qr-image">
                        </div>
                        
                        <p class="qr-note">Vui lòng xuất trình mã QR này tại quầy vé hoặc máy soát vé để vào rạp.</p>
                    </div>
                </div>
            </div>

            <!-- Bottom: Total Amount -->
            <div class="success-total">
                <div class="total-label">Tổng thanh toán</div>
                <div class="total-amount"><?php echo number_format($order['final_amount'], 0, ',', '.'); ?>₫</div>
            </div>

            <!-- Action Button -->
            <div class="success-actions">
                <a href="<?= h(app_url('history')) ?>" class="btn btn-primary btn-full">Xem lịch sử đặt vé</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>