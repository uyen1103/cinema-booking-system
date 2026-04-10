<?php
$groupedSeats = [];
foreach ($seats as $seat) {
    $groupedSeats[$seat['row_name']][] = $seat;
}

function seat_class(array $seat): string {
    if ((int) $seat['status'] === 0) {
        return 'admin-seat admin-seat--disabled';
    }

    return match ((int) $seat['type']) {
        2 => 'admin-seat admin-seat--vip',
        3 => 'admin-seat admin-seat--couple',
        default => 'admin-seat admin-seat--standard',
    };
}
?>

<div class="admin-page-heading d-flex flex-wrap justify-content-between gap-3">
    <div>
        <h2>SƠ ĐỒ GHẾ: <?= h($room['name']) ?></h2>
        <p>Nhấn vào từng ghế để đổi trạng thái hoạt động hoặc bảo trì.</p>
    </div>
    <div class="d-flex gap-2">
        <a class="admin-btn admin-btn--light" href="?action=rooms">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Danh sách phòng</span>
        </a>
        <form method="POST" action="?action=generate_seats">
            <input type="hidden" name="room_id" value="<?= (int) $room['room_id'] ?>">
            <button class="admin-btn admin-btn--ghost" type="submit">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                <span>Sinh ghế mặc định</span>
            </button>
        </form>
    </div>
</div>

<div class="row g-3 admin-section">
    <div class="col-lg-8">
        <div class="admin-card">
            <div class="admin-card__body">
                <div class="text-center mb-4">
                    <div class="admin-badge admin-badge--info">Màn hình chiếu</div>
                </div>

                <div class="admin-seat-grid">
                    <?php foreach ($groupedSeats as $rowName => $seatRow): ?>
                        <div class="admin-seat-row">
                            <div class="admin-seat-label"><?= h($rowName) ?></div>
                            <?php foreach ($seatRow as $seat): ?>
                                <a class="<?= seat_class($seat) ?>" href="?action=toggle_seat&seat_id=<?= (int) $seat['seat_id'] ?>&room_id=<?= (int) $room['room_id'] ?>">
                                    <?= h($seat['row_name']) . (int) $seat['seat_number'] ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="admin-card">
            <div class="admin-card__body">
                <h4 class="fw-bold mb-3">Chú thích</h4>
                <div class="admin-kpi-list">
                    <div class="admin-kpi-item"><span class="admin-seat admin-seat--standard"></span> <span class="ms-2">Ghế thường</span></div>
                    <div class="admin-kpi-item"><span class="admin-seat admin-seat--vip"></span> <span class="ms-2">Ghế VIP</span></div>
                    <div class="admin-kpi-item"><span class="admin-seat admin-seat--couple"></span> <span class="ms-2">Ghế đôi</span></div>
                    <div class="admin-kpi-item"><span class="admin-seat admin-seat--disabled"></span> <span class="ms-2">Bảo trì / ngưng sử dụng</span></div>
                </div>
                <hr>
                <div class="admin-note">
                    Tổng ghế: <?= count($seats) ?><br>
                    Ghế hoạt động: <?= count(array_filter($seats, fn($seat) => (int) $seat['status'] === 1)) ?><br>
                    Ghế bảo trì: <?= count(array_filter($seats, fn($seat) => (int) $seat['status'] === 0)) ?>
                </div>
            </div>
        </div>
    </div>
</div>
