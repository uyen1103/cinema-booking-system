<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="page-section promotions-section">
    <div class="container">
        <div class="section-header section-spacer">
            <div class="section-title">
                <span class="eyebrow">KHUYẾN MÃI</span>
                <h2>Khuyến mãi đang áp dụng</h2>
            </div>
        </div>

        <?php if (!empty($activePromotions)): ?>
            <div class="promo-grid">
                <?php foreach ($activePromotions as $promo): ?>
                    <?php
                        $seatTypes = [];
                        if (!empty($promo['applicable_seat_types'])) {
                            $types = json_decode($promo['applicable_seat_types'], true);
                            if (is_array($types)) $seatTypes = $types;
                        }
                        $rules = [];
                        if (!empty($promo['min_tickets'])) $rules[] = 'Tối thiểu ' . intval($promo['min_tickets']) . ' vé';
                        if (!empty($promo['min_amount'])) $rules[] = 'Đơn từ ' . number_format($promo['min_amount'], 0, ',', '.') . '₫';
                        if (!empty($seatTypes)) $rules[] = 'Áp dụng ghế: ' . implode(', ', $seatTypes);
                        $discountLabel = $promo['discount_type'] === 'percent' ? 'Giảm ' . intval($promo['discount_value']) . '%' : 'Giảm ' . number_format($promo['discount_value'], 0, ',', '.') . '₫';
                    ?>
                    <article class="promo-card">
                        <div class="promo-card-header">
                            <span class="promo-code"><?php echo htmlspecialchars($promo['promo_code']); ?></span>
                            <strong><?php echo htmlspecialchars($discountLabel); ?></strong>
                        </div>
                        <p class="promo-description"><?php echo htmlspecialchars($promo['description']); ?></p>
                        <?php if (!empty($rules)): ?>
                            <ul class="promo-rules">
                                <?php foreach ($rules as $rule): ?>
                                    <li><?php echo htmlspecialchars($rule); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <div class="promo-apply">
                            <p><strong>Cách áp dụng:</strong> Nhập mã <code><?php echo htmlspecialchars($promo['promo_code']); ?></code> vào ô "Nhập mã ưu đãi" khi đặt vé, sau đó nhấn "Áp dụng".</p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <p>Hiện chưa có chương trình khuyến mãi nào.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>