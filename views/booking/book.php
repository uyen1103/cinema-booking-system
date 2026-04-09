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
                                    <?php 
                                    $seatType = $seat['seat_type'] ?? 'standard';
                                    $seatTypeClass = 'seat-' . $seatType;
                                    
                                    // Get seat price based on type
                                    $seatPrice = floatval($showtime['base_price']);
                                    if (!empty($seatPricesInfo)) {
                                        foreach ($seatPricesInfo as $sp) {
                                            if ($sp['seat_type'] === $seatType) {
                                                $seatPrice = floatval($showtime['base_price']) * floatval($sp['price_multiplier']);
                                                break;
                                            }
                                        }
                                    }
                                    ?>
                                    <label class="seat-item <?php echo $seat['reserved'] ? 'seat-reserved' : 'seat-available'; ?> <?php echo htmlspecialchars($seatTypeClass); ?>" 
                                           data-seat-id="<?php echo $seat['seat_id']; ?>"
                                           data-seat-type="<?php echo htmlspecialchars($seatType); ?>"
                                           data-seat-price="<?php echo $seatPrice; ?>"
                                           title="<?php echo htmlspecialchars($seat['seat_row'] . $seat['seat_number']); ?> - <?php echo number_format($seatPrice, 0, ',', '.'); ?>₫">
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

                <div style="margin-top: 20px; padding: 15px; background: #f9f9f9; border-radius: 8px;">
                    <?php if (!empty($seatPricesInfo)): ?>
                        <div style="font-size: 0.9rem;">
                            <?php foreach ($seatPricesInfo as $sp): ?>
                                <?php $priceValue = floatval($showtime['base_price']) * floatval($sp['price_multiplier']); ?>
                                <?php 
                                    $seatTypeClass = 'seat-' . $sp['seat_type'];
                                    $iconColors = [
                                        'standard' => '#f2f4fb',
                                        'vip' => '#ffd700',
                                        'couple' => '#e8f5ff'
                                    ];
                                    $iconColor = $iconColors[$sp['seat_type']] ?? '#f2f4fb';
                                ?>
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #e0e0e0;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span style="display: inline-block; width: 16px; height: 16px; background-color: <?php echo $iconColor; ?>; border: 1px solid #999; border-radius: 3px;"></span>
                                        <span><?php echo htmlspecialchars($sp['description']); ?></span>
                                    </div>
                                    <strong><?php echo number_format($priceValue, 0, ',', '.'); ?>₫</strong>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div style="font-size: 0.9rem;">Giá vé: <?php echo number_format($showtime['base_price'], 0, ',', '.'); ?>₫</div>
                    <?php endif; ?>
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

                    <div class="booking-selected-seats">
                        <strong>Ghế đã chọn:</strong>
                        <div id="selected-seat-list" class="selected-seat-list" style="display: flex; flex-direction: column; gap: 8px;">
                            <span style="color: #999; font-style: italic;">Chưa chọn ghế</span>
                        </div>
                        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e0e0e0; display: flex; justify-content: space-between; align-items: center;">
                            <strong>Tổng tiền:</strong>
                            <span id="total-price" style="font-size: 1.3rem; color: #e71930; font-weight: bold;">0₫</span>
                        </div>
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
        const totalPriceEl = document.getElementById('total-price');
        const seatInputs = document.querySelectorAll('.seat-item input[type="checkbox"]');

        function updateSelectedSeats() {
            const selected = [];
            let totalPrice = 0;
            
            seatInputs.forEach(function (input) {
                if (input.checked) {
                    const label = input.parentElement;
                    const seatSpan = label.querySelector('span');
                    const seatType = label.getAttribute('data-seat-type') || 'standard';
                    const price = parseFloat(label.getAttribute('data-seat-price')) || 0;
                    
                    if (seatSpan) {
                        const seatName = seatSpan.textContent.trim();
                        // Format seat type name
                        const seatTypeLabel = {
                            'standard': 'Standard',
                            'vip': 'VIP',
                            'couple': 'Couple'
                        }[seatType] || seatType;
                        
                        selected.push({
                            name: seatName,
                            type: seatTypeLabel,
                            price: price
                        });
                    }
                    totalPrice += price;
                }
            });
            
            // Render selected seats
            if (selected.length === 0) {
                selectedSeatList.innerHTML = '<span style="color: #999; font-style: italic;">Chưa chọn ghế</span>';
            } else {
                selectedSeatList.innerHTML = selected.map(seat => 
                    `<div style="background: #f5f5f5; padding: 8px 12px; border-radius: 6px; font-size: 0.95rem;">
                        <strong>${seat.name}</strong> (${seat.type}) : <span style="color: #e71930; font-weight: bold;">${Math.round(seat.price).toLocaleString('vi-VN')}₫</span>
                    </div>`
                ).join('');
            }
            
            // Format and display total price
            if (totalPrice > 0) {
                totalPriceEl.textContent = Math.round(totalPrice).toLocaleString('vi-VN') + '₫';
            } else {
                totalPriceEl.textContent = '0₫';
            }
        }

        seatInputs.forEach(function (input) {
            input.addEventListener('change', updateSelectedSeats);
        });

        updateSelectedSeats();
    });
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
