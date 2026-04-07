<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<?php
$roomName = $showtime['room_name'] ?? '';
$cinemaName = $roomName;
$hallName = '';
if (strpos($roomName, ' - ') !== false) {
    list($cinemaName, $hallName) = explode(' - ', $roomName, 2);
}
if ($hallName === '') {
    $hallName = $showtime['room_id'] ?? '';
}

$selectedSeatLabels = [];
if (!empty($_POST['seats']) && is_array($_POST['seats'])) {
    $selectedSeatIds = array_map('intval', $_POST['seats']);
    foreach ($seatMap as $seat) {
        if (in_array(intval($seat['seat_id']), $selectedSeatIds, true)) {
            $selectedSeatLabels[] = $seat['seat_row'] . $seat['seat_number'];
        }
    }
}
?>

<div class="page-section booking-page">
    <div class="container">
        <div class="booking-header">
            <a href="web.php?action=movie&id=<?php echo intval($movie['movie_id']); ?>" class="btn-back">&larr; Quay lại</a>
        </div>

        <div class="booking-steps">
            <div class="booking-step booking-step-active">Chọn ghế</div>
            <div class="booking-step">Thanh toán</div>
            <div class="booking-step">Hoàn tất</div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p>❌ <?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="booking-layout">
            <div class="booking-left card-panel">
                <div class="screen-bar">
                    <div class="screen-bar-line"></div>
                    <div class="screen-bar-text">MÀN HÌNH</div>
                    <div class="screen-bar-line"></div>
                </div>
                <div class="seat-map">
                    <?php
                    $rows = [];
                    foreach ($seatMap as $seat) {
                        $rows[$seat['seat_row']][] = $seat;
                    }
                    foreach ($rows as $rowName => $seats):
                    ?>

                        <div class="seat-row">
                            <span class="seat-row-label"><?php echo htmlspecialchars($rowName); ?></span>
                            <div class="seat-row-items">
                                <?php foreach ($seats as $seat): ?>
                                    <?php $seatTypeClass = 'seat-' . ($seat['seat_type'] ?? 'standard'); ?>
                                    <label class="seat-item <?php echo $seat['reserved'] ? 'seat-reserved' : 'seat-available'; ?> <?php echo htmlspecialchars($seatTypeClass); ?>">
                                        <input type="checkbox" name="seats[]" value="<?php echo $seat['seat_id']; ?>" <?php echo $seat['reserved'] ? 'disabled' : ''; ?> />
                                        <span><?php echo htmlspecialchars($seat['seat_row'] . $seat['seat_number']); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="booking-legend">
                    <span class="seat-legend seat-available"></span> Ghế trống
                    <span class="seat-legend seat-reserved"></span> Ghế đã đặt
                    <span class="seat-legend seat-standard"></span> Standard
                    <span class="seat-legend seat-vip"></span> VIP
                    <span class="seat-legend seat-couple"></span> Couple
                </div>
            </div>

            <div class="booking-right card-panel">
                <div class="summary-card">
                    <div class="summary-movie-card">
                        <div class="summary-poster" style="background-image:url('<?php echo htmlspecialchars(getPosterUrl($movie['poster_url'] ?? '')); ?>')"></div>
                        <div class="summary-movie-info">
                            <strong class="summary-movie-title"><?php echo htmlspecialchars($movie['title']); ?></strong>
                            <?php if (!empty($movie['status']) && $movie['status'] === 'coming_soon'): ?>
                                <span class="movie-badge">Trước phát hành</span>
                            <?php else: ?>
                                <span class="movie-badge">T18 - 18+</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="summary-row"><span>Ngày & giờ</span><strong><?php echo date('D, d/m/Y', strtotime($showtime['show_date'])); ?> • <?php echo htmlspecialchars(substr($showtime['start_time'], 0, 5)); ?></strong></div>
                    <div class="summary-row"><span>Rạp chiếu</span><strong><?php echo htmlspecialchars($cinemaName); ?></strong></div>
                    <div class="summary-row"><span>Phòng chiếu</span><strong><?php echo htmlspecialchars($hallName); ?></strong></div>
                    <div class="summary-row"><span>Giá vé</span><strong><?php echo number_format($showtime['base_price'], 0, ',', '.'); ?>₫</strong></div>

                    <div class="booking-selected-seats">
                        <strong>Ghế đã chọn:</strong>
                        <div id="selected-seat-list" class="selected-seat-list">Chưa chọn ghế</div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-full">Tiếp tục thanh toán →</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectedSeatList = document.getElementById('selected-seat-list');
        const seatInputs = document.querySelectorAll('.seat-item input[type="checkbox"]');

        function updateSelectedSeats() {
            const selected = [];
            seatInputs.forEach(function (input) {
                if (input.checked) {
                    const label = input.parentElement.querySelector('span');
                    if (label) {
                        selected.push(label.textContent.trim());
                    }
                }
            });
            selectedSeatList.textContent = selected.length ? selected.join(', ') : 'Chưa chọn ghế';
        }

        seatInputs.forEach(function (input) {
            input.addEventListener('change', updateSelectedSeats);
        });

        updateSelectedSeats();
    });
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
