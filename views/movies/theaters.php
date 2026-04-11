<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="page-section theaters-section">
    <div class="container">
        <div class="section-header section-spacer">
            <div class="section-title">
                <span class="eyebrow">RẠP</span>
                <h2>Hệ thống rạp và phòng chiếu</h2>
            </div>
        </div>

        <?php if (!empty($theaters)): ?>
            <div class="theater-grid">
                <?php foreach ($theaters as $cinema): ?>
                    <article class="theater-card">
                        <div class="theater-card-header">
                            <span class="cinema-icon">📍</span>
                            <h3><?php echo htmlspecialchars($cinema['name']); ?></h3>
                        </div>
                        <?php if (!empty($cinema['address'])): ?>
                            <p class="cinema-address"><?php echo htmlspecialchars($cinema['address']); ?></p>
                        <?php endif; ?>
                        <ul class="room-list">
                            <?php foreach ($cinema['halls'] as $room): ?>
                                <li><?php echo htmlspecialchars($room['hall']); ?> <span class="room-capacity">(Sức chứa <?php echo number_format($room['capacity'], 0, ',', '.'); ?>)</span></li>
                            <?php endforeach; ?>
                        </ul>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <p>Hiện chưa có thông tin rạp và phòng chiếu.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>