<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="page-section movie-detail-page">
    <div class="container">
        <?php
            $posterUrl = htmlspecialchars(getPosterUrl($movie['poster_url'] ?? ''));
        ?>

        <div class="movie-detail-layout">
            <!-- NỘI DUNG PHIM -->
            <div class="movie-detail-content">
                <div class="content-section">
                    <h2 class="section-label">NỘI DUNG PHIM</h2>
                    <div class="movie-detail-main">
                        <div class="movie-poster-large" style="background-image: url('<?php echo $posterUrl; ?>');"></div>
                        <div class="movie-detail-text">
                            <h1><?php echo htmlspecialchars($movie['title']); ?></h1>
                            <div class="movie-tags">
                                <span><?php echo htmlspecialchars($movie['genre']); ?></span>
                                <span><?php echo intval($movie['duration']); ?> phút</span>
                                <span><?php echo htmlspecialchars(date('d/m/Y', strtotime($movie['release_date']))); ?></span>
                            </div>
                            <p class="movie-description"><?php echo nl2br(htmlspecialchars($movie['description'])); ?></p>
                        </div>
                    </div>
                    <div class="trailer-section">
                        <h3>TRAILER</h3>
                        <?php if (!empty($movie['trailer_url'])): ?>
                            <div class="trailer-video">
                                <iframe src="<?php echo htmlspecialchars($movie['trailer_url']); ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                            </div>
                        <?php else: ?>
                            <div class="trailer-placeholder">
                                <div class="trailer-icon">▶</div>
                                <p>Không có trailer</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- LỊCH CHIẾU -->
            <div class="movie-showtime-sidebar">
                <div class="sidebar-section">
                    <h2 class="section-label">LỊCH CHIẾU</h2>

                    <?php
                    $weekdayMap = [
                        'Mon' => 'T2',
                        'Tue' => 'T3',
                        'Wed' => 'T4',
                        'Thu' => 'T5',
                        'Fri' => 'T6',
                        'Sat' => 'T7',
                        'Sun' => 'CN',
                    ];

                    if (!empty($showtimes)) {
                        $showTimesByDate = [];
                        foreach ($showtimes as $showtime) {
                            $date = $showtime['show_date'];
                            if (!isset($showTimesByDate[$date])) {
                                $showTimesByDate[$date] = [];
                            }
                            $showTimesByDate[$date][] = $showtime;
                        }
                        $dates = array_keys($showTimesByDate);
                    } else {
                        $dates = [];
                        for ($i = 0; $i < 4; $i++) {
                            $dates[] = date('Y-m-d', strtotime("+$i days"));
                        }
                    }
                    ?>

                    <!-- Date Selector -->
                    <div class="date-selector-inline">
                        <?php foreach ($dates as $index => $date): ?>
                            <?php $isToday = $date === date('Y-m-d'); ?>
                            <button type="button" class="date-btn-inline" data-date="<?php echo $date; ?>" <?php echo $index === 0 ? 'data-active="true"' : ''; ?>>
                                <span class="date-label">
                                    <?php if ($isToday): ?>
                                        HÔM NAY
                                    <?php else: ?>
                                        <?php echo $weekdayMap[date('D', strtotime($date))] ?? date('d', strtotime($date)); ?>
                                    <?php endif; ?>
                                </span>
                                <span class="date-num"><?php echo date('d', strtotime($date)); ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <!-- Cinemas Display -->
                    <div class="cinemas-display">
                        <?php if (!empty($showtimes)): ?>
                            <?php foreach ($showTimesByDate as $date => $dateShowtimes): ?>
                                <div class="date-cinemas" data-date="<?php echo $date; ?>" style="display: none;">
                                    <?php
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
                                        <div class="cinema-item">
                                            <div class="cinema-name">
                                                <span class="cinema-icon">📍</span>
                                                <span><?php echo htmlspecialchars($roomGroup['room_name'] ?? ''); ?></span>
                                            </div>
                                            <div class="cinema-times">
                                                <?php foreach ($roomGroup['showtimes'] as $showtime): ?>
                                                    <button type="button" class="time-slot" data-showtime-id="<?php echo intval($showtime['showtime_id']); ?>" data-date="<?php echo $date; ?>">
                                                        <span class="time-text"><?php echo htmlspecialchars(substr($showtime['start_time'], 0, 5)); ?></span>
                                                    </button>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="date-cinemas" data-date="<?php echo $dates[0]; ?>">
                                <div class="no-showtime">
                                    <p>❌ Không có lịch chiếu khả dụng</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($showtimes)): ?>
                        <a id="book-now-btn" href="#" class="btn btn-primary btn-block btn-disabled" aria-disabled="true">ĐẶT VÉ</a>
                    <?php else: ?>
                        <button class="btn btn-primary btn-block" disabled>ĐẶT VÉ</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateButtons = document.querySelectorAll('.date-btn-inline');
    const dateCinemas = document.querySelectorAll('.date-cinemas');
    const timeSlots = document.querySelectorAll('.time-slot');
    const bookNowBtn = document.getElementById('book-now-btn');

    function activateDate(date) {
        dateButtons.forEach(btn => {
            btn.removeAttribute('data-active');
        });
        dateCinemas.forEach(group => {
            group.style.display = 'none';
        });

        const activeButton = document.querySelector(`.date-btn-inline[data-date="${date}"]`);
        const activeGroup = document.querySelector(`.date-cinemas[data-date="${date}"]`);
        if (activeButton) {
            activeButton.setAttribute('data-active', 'true');
        }
        if (activeGroup) {
            activeGroup.style.display = 'block';
        }
    }

    function activateShowtime(showtimeId) {
        timeSlots.forEach(slot => {
            slot.classList.toggle('active', slot.getAttribute('data-showtime-id') === showtimeId);
        });

        if (bookNowBtn) {
            if (showtimeId) {
                bookNowBtn.href = `<?= customer_url('book') ?>&showtime_id=${showtimeId}`;
                bookNowBtn.classList.remove('btn-disabled');
                bookNowBtn.removeAttribute('aria-disabled');
            } else {
                bookNowBtn.href = '#';
                bookNowBtn.classList.add('btn-disabled');
                bookNowBtn.setAttribute('aria-disabled', 'true');
            }
        }
    }

    if (dateButtons.length > 0 && dateCinemas.length > 0) {
        activateDate(dateButtons[0].getAttribute('data-date'));

        dateButtons.forEach((button) => {
            button.addEventListener('click', function() {
                const selectedDate = this.getAttribute('data-date');
                activateDate(selectedDate);

                const firstSlot = document.querySelector(`.time-slot[data-date="${selectedDate}"]`);
                if (firstSlot) {
                    activateShowtime(firstSlot.getAttribute('data-showtime-id'));
                }
            });
        });
    }

    if (timeSlots.length > 0) {
        timeSlots.forEach((slot) => {
            slot.addEventListener('click', function() {
                const showtimeId = this.getAttribute('data-showtime-id');
                activateShowtime(showtimeId);
            });
        });

        const firstSlot = timeSlots[0];
        if (firstSlot) {
            activateShowtime(firstSlot.getAttribute('data-showtime-id'));
        }
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

