<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="page-section booking-showtime-page">
    <div class="container">
        <div class="booking-header">
            <a href="web.php?action=movie&id=<?php echo intval($movie['movie_id']); ?>" class="btn-back">&larr; Quay lại</a>
            <div>
                <h1>Chọn lịch chiếu</h1>
            </div>
        </div>

        <div class="showtime-wrapper">
            <!-- PHẦN THÔNG TIN PHIM -->
            <div class="movie-info-section card-panel">
                <h2 class="section-title">NỘI DUNG PHIM</h2>
                <div class="movie-info-content">
                    <div class="movie-poster-small">
                        <img src="<?php echo htmlspecialchars(getPosterUrl($movie['poster_url'] ?? '')); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>">
                    </div>
                    <div class="movie-details">
                        <h3><?php echo htmlspecialchars($movie['title']); ?></h3>
                        <div class="movie-meta">
                            <span class="meta-item">
                                <strong>Thể loại:</strong> <?php echo htmlspecialchars($movie['genre']); ?>
                            </span>
                            <span class="meta-item">
                                <strong>Thời lượng:</strong> <?php echo intval($movie['duration']); ?> phút
                            </span>
                            <span class="meta-item">
                                <strong>Ngày phát hành:</strong> <?php echo date('d/m/Y', strtotime($movie['release_date'])); ?>
                            </span>
                        </div>
                        <p class="movie-description"><?php echo htmlspecialchars(substr($movie['description'], 0, 200)) . '...'; ?></p>
                    </div>
                </div>
            </div>

            <!-- PHẦN LỊCH CHIẾU -->
            <div class="showtime-section card-panel">
                <h2 class="section-title">LỊCH CHIẾU</h2>

                <?php if (empty($showtimes)): ?>
                    <div class="no-showtime">
                        <p>❌ Không có lịch chiếu khả dụng cho bộ phim này.</p>
                    </div>
                <?php else: ?>
                    <?php
                    // Nhóm showtimes theo ngày
                    $showTimesByDate = [];
                    foreach ($showtimes as $showtime) {
                        $date = $showtime['show_date'];
                        if (!isset($showTimesByDate[$date])) {
                            $showTimesByDate[$date] = [];
                        }
                        $showTimesByDate[$date][] = $showtime;
                    }
                    ?>

                    <!-- Date Selector -->
                    <div class="date-selector-wrapper">
                        <div class="date-selector">
                            <?php foreach ($showTimesByDate as $date => $dateShowtimes): ?>
                                <button type="button" class="date-btn" data-date="<?php echo $date; ?>">
                                    <span class="date-day"><?php echo date('d', strtotime($date)); ?></span>
                                    <span class="date-month"><?php echo 'Tháng ' . date('m', strtotime($date)); ?></span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Showtimes Display -->
                    <div class="showtimes-display">
                        <?php foreach ($showTimesByDate as $date => $dateShowtimes): ?>
                            <div class="date-group" data-date="<?php echo $date; ?>" style="display: none;">
                                <h3 class="selected-date-title">
                                    Ngày <?php echo date('d/m/Y - l', strtotime($date)); ?>
                                </h3>

                                <?php
                                // Nhóm showtimes theo rạp
                                $showTimesByRoom = [];
                                foreach ($dateShowtimes as $showtime) {
                                    $roomId = $showtime['room_id'];
                                    $roomName = $showtime['room_name'];
                                    $key = $roomId . '_' . $roomName;
                                    if (!isset($showTimesByRoom[$key])) {
                                        $showTimesByRoom[$key] = [
                                            'room_id' => $roomId,
                                            'room_name' => $roomName,
                                            'showtimes' => []
                                        ];
                                    }
                                    $showTimesByRoom[$key]['showtimes'][] = $showtime;
                                }
                                ?>

                                <?php foreach ($showTimesByRoom as $roomGroup): ?>
                                    <div class="cinema-group">
                                        <div class="cinema-name">
                                            <span class="cinema-icon">📍</span>
                                            <?php echo htmlspecialchars($roomGroup['room_name']); ?>
                                        </div>

                                        <div class="showtime-list">
                                            <?php foreach ($roomGroup['showtimes'] as $showtime): ?>
                                                <a href="web.php?action=book&showtime_id=<?php echo intval($showtime['showtime_id']); ?>" class="showtime-btn">
                                                    <span class="showtime-time">
                                                        <?php echo htmlspecialchars(substr($showtime['start_time'], 0, 5)); ?>
                                                    </span>
                                                    <span class="showtime-end">
                                                        - <?php echo htmlspecialchars(substr($showtime['end_time'], 0, 5)); ?>
                                                    </span>
                                                    <span class="showtime-price">
                                                        <?php echo number_format($showtime['base_price'], 0, '', '.') . '₫'; ?>
                                                    </span>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
/* ===== SHOWTIME BOOKING PAGE ===== */
.booking-showtime-page {
    padding: 40px 0;
    background: linear-gradient(135deg, #f5f5f5 0%, #ffffff 100%);
    min-height: calc(100vh - 200px);
}

.booking-header {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 40px;
    padding-bottom: 20px;
    border-bottom: 2px solid #ddd;
}

.booking-header h1 {
    font-size: 28px;
    font-weight: 700;
    color: var(--text-dark);
    margin: 0;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: var(--primary-color);
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 600;
    transition: all 0.3s;
    border: none;
    cursor: pointer;
}

.btn-back:hover {
    background: #c71425;
    transform: translateX(-4px);
}

.showtime-wrapper {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 30px;
}

@media (max-width: 1024px) {
    .showtime-wrapper {
        grid-template-columns: 1fr;
    }
}

/* ===== MOVIE INFO SECTION ===== */
.movie-info-section {
    position: sticky;
    top: 100px;
}

.section-title {
    font-size: 18px;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 2px solid var(--primary-color);
    text-transform: uppercase;
    letter-spacing: 1px;
}

.movie-info-content {
    display: flex;
    gap: 20px;
}

.movie-poster-small {
    flex-shrink: 0;
    width: 120px;
    height: 180px;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.movie-poster-small img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.movie-details {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.movie-details h3 {
    font-size: 20px;
    font-weight: 700;
    color: var(--text-dark);
    margin: 0;
}

.movie-meta {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.meta-item {
    display: flex;
    gap: 8px;
    font-size: 14px;
    color: var(--text-light);
}

.meta-item strong {
    color: var(--text-dark);
    min-width: 80px;
}

.movie-description {
    font-size: 13px;
    color: var(--text-light);
    line-height: 1.5;
    margin-top: 8px;
}

/* ===== SHOWTIME SECTION ===== */
.showtime-section {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.no-showtime {
    padding: 40px 20px;
    text-align: center;
    background: #f9f9f9;
    border-radius: 8px;
    border: 2px dashed #ddd;
}

.no-showtime p {
    margin: 0;
    font-size: 16px;
    color: var(--text-light);
}

/* ===== DATE SELECTOR ===== */
.date-selector-wrapper {
    overflow-x: auto;
    margin-bottom: 24px;
    padding-bottom: 12px;
}

.date-selector {
    display: flex;
    gap: 12px;
    flex-wrap: nowrap;
    min-width: min-content;
}

.date-btn {
    flex-shrink: 0;
    padding: 16px 20px;
    background: white;
    border: 2px solid #ddd;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    min-width: 90px;
}

.date-btn:hover {
    border-color: var(--primary-color);
    background: #fafafa;
}

.date-btn.active {
    background: var(--primary-color);
    border-color: var(--primary-color);
    color: white;
}

.date-day {
    font-size: 20px;
    font-weight: 700;
}

.date-month {
    font-size: 12px;
    opacity: 0.8;
}

/* ===== SHOWTIMES DISPLAY ===== */
.date-group {
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.selected-date-title {
    font-size: 16px;
    font-weight: 600;
    color: var(--text-dark);
    margin: 0 0 20px 0;
    padding-left: 12px;
    border-left: 4px solid var(--primary-color);
}

.cinema-group {
    margin-bottom: 24px;
}

.cinema-name {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 15px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid #eee;
}

.cinema-icon {
    font-size: 18px;
}

.showtime-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    gap: 12px;
    margin-bottom: 12px;
}

.showtime-btn {
    padding: 16px 12px;
    background: white;
    border: 2px solid #ddd;
    border-radius: 6px;
    text-decoration: none;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    transition: all 0.3s;
}

.showtime-btn:hover {
    border-color: var(--primary-color);
    background: #fff5f6;
    transform: translateY(-4px);
    box-shadow: 0 4px 12px rgba(231, 25, 48, 0.15);
}

.showtime-time {
    font-size: 20px;
    font-weight: 700;
    color: var(--primary-color);
}

.showtime-end {
    font-size: 12px;
    color: var(--text-light);
}

.showtime-price {
    font-size: 13px;
    font-weight: 600;
    color: var(--success-color);
    margin-top: 4px;
}

/* ===== CARD PANEL ===== */
.card-panel {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    border: 1px solid #f0f0f0;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateButtons = document.querySelectorAll('.date-btn');
    const dateGroups = document.querySelectorAll('.date-group');

    // Hiển thị ngày đầu tiên mặc định
    if (dateButtons.length > 0 && dateGroups.length > 0) {
        dateButtons[0].classList.add('active');
        dateGroups[0].style.display = 'block';
    }

    // Xử lý click chọn ngày
    dateButtons.forEach((button, index) => {
        button.addEventListener('click', function() {
            // Xóa active từ tất cả button
            dateButtons.forEach(btn => btn.classList.remove('active'));
            // Ẩn tất cả dateGroup
            dateGroups.forEach(group => group.style.display = 'none');

            // Thêm active cho button được click
            this.classList.add('active');
            // Hiển thị dateGroup tương ứng
            dateGroups[index].style.display = 'block';
        });
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
