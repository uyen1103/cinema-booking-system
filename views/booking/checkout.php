<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="page-section booking-page">
    <div class="container">
        <div class="booking-header">
            <a href="web.php?action=book&showtime_id=<?php echo intval($tickets[0]['showtime_id'] ?? 0); ?>" class="btn-back">&larr; Quay lại</a>
        </div>

        <div class="booking-steps">
            <div class="booking-step">Chọn ghế</div>
            <div class="booking-step booking-step-active">Thanh toán</div>
            <div class="booking-step">Hoàn tất</div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p>❌ <?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="success-message" style="margin-bottom: 20px;">
                ✅ <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="checkout-grid">
            <div class="checkout-left card-panel">
                <div class="section-block">
                    <h2>Chọn phương thức thanh toán</h2>
                </div>

                <div class="payment-options">
                    <label class="radio-card <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] === 'card') ? 'selected' : ''; ?>">
                        <input type="radio" name="payment_method" value="card" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] === 'card') ? 'checked' : ''; ?> />
                        <div>
                            <strong>Thẻ ngân hàng</strong>
                            <span>Visa, Mastercard, JCB</span>
                        </div>
                    </label>

                    <label class="radio-card <?php echo (!isset($_POST['payment_method']) || $_POST['payment_method'] === 'wallet') ? 'selected' : ''; ?>">
                        <input type="radio" name="payment_method" value="wallet" <?php echo (!isset($_POST['payment_method']) || $_POST['payment_method'] === 'wallet') ? 'checked' : ''; ?> />
                        <div>
                            <strong>Ví điện tử</strong>
                            <span>MoMo, ZaloPay, VNPAY</span>
                        </div>
                    </label>

                    <label class="radio-card <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] === 'qr') ? 'selected' : ''; ?>">
                        <input type="radio" name="payment_method" value="qr" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] === 'qr') ? 'checked' : ''; ?> />
                        <div>
                            <strong>Quét mã QR</strong>
                            <span>Thanh toán nhanh qua QR Code</span>
                        </div>
                    </label>
                </div>

<<<<<<< HEAD
                <div class="promo-block" style="margin-top: 20px;">
                    <div class="promo-header" style="margin-bottom: 12px;">
=======
                <div class="promo-block">
                    <div class="promo-header">
>>>>>>> 79d8d1d56f94b32a57937290034834493747c163
                        <h2>Mã giảm giá</h2>
                    </div>
                    <div class="promo-input-row">
                        <input type="text" name="promo_code" value="<?php echo htmlspecialchars($promoCode); ?>" placeholder="Nhập mã ưu đãi" class="form-control" />
                        <button type="submit" name="apply_promo" class="btn promo-btn">Áp dụng</button>
                    </div>
                    <div class="promo-suggestions">
                        <?php foreach ($activePromotions as $promo): ?>
                            <?php $isActive = strcasecmp($promoCode, $promo['promo_code']) === 0; ?>
                            <?php $promoValidation = $promoApplicability[$promo['promo_code']] ?? ['applicable' => true, 'reason' => '']; ?>
                            <?php $isDisabled = !$promoValidation['applicable'] && !$isActive; ?>
                            <div class="promo-card <?php echo $isActive ? 'promo-card-active' : ''; ?>">
                                <div>
                                    <strong><?php echo htmlspecialchars($promo['discount_type'] === 'percent' ? 'Giảm ' . $promo['discount_value'] . '%' : 'Giảm ' . number_format($promo['discount_value'], 0, ',', '.') . '₫'); ?></strong>
                                    <span><?php echo htmlspecialchars($promo['description'] ?? $promo['promo_code']); ?></span>
                                    <?php if ($isDisabled): ?>
                                        <p class="promo-warning" style="color:#d9534f; margin:8px 0 0; font-size:13px;"><?php echo htmlspecialchars($promoValidation['reason']); ?></p>
                                    <?php endif; ?>
                                </div>
<<<<<<< HEAD
                                <button type="button" name="apply_promo_code" value="<?php echo htmlspecialchars($promo['promo_code']); ?>" class="btn btn-sm <?php echo $isActive ? 'btn-outline' : ''; ?>" <?php echo $isDisabled ? 'disabled' : ''; ?>>
=======
                                <button type="submit" name="apply_promo_code" value="<?php echo htmlspecialchars($promo['promo_code']); ?>" class="btn btn-sm <?php echo $isActive ? 'btn-outline' : ''; ?>" <?php echo $isDisabled ? 'disabled' : ''; ?>>
>>>>>>> 79d8d1d56f94b32a57937290034834493747c163
                                    <?php echo $isActive ? 'Đã áp dụng' : ($isDisabled ? 'Không áp dụng được' : 'Áp dụng'); ?>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="checkout-right card-panel">
                <div class="summary-card summary-checkout-card">
                    <div class="summary-top-card">
                        <div>
                            <span class="summary-card-label">Tóm tắt đơn hàng</span>
                        </div>
                        <div class="summary-code">Mã: <?php echo htmlspecialchars($order['order_code']); ?></div>
                    </div>

                    <div class="summary-top-card">
                        <div class="summary-poster" style="background-image:url('<?php echo htmlspecialchars(getPosterUrl($tickets[0]['poster_url'] ?? '')); ?>')"></div>
                        <div class="summary-movie-info">
                            <strong class="summary-movie-title"><?php echo htmlspecialchars($tickets[0]['title'] ?? ''); ?></strong>
                            <span class="movie-badge"><?php echo htmlspecialchars($tickets[0]['room_name'] ?? ''); ?></span>
                            <p>📅 <?php echo htmlspecialchars($tickets[0]['show_date'] ?? ''); ?> • <?php echo htmlspecialchars(substr($tickets[0]['start_time'] ?? '', 0, 5)); ?></p>
                            <p>💺 Ghế: <?php echo htmlspecialchars(implode(', ', array_map(function($ticket) { return $ticket['seat_row'] . $ticket['seat_number']; }, $tickets))); ?></p>
                        </div>
                    </div>

                    <div class="summary-row"><span>Tạm tính</span><strong><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>₫</strong></div>
                    <div class="summary-row"><span>Giảm giá</span><strong>-<?php echo number_format($order['discount_amount'], 0, ',', '.'); ?>₫</strong></div>
                    <div class="summary-row total"><span>Tổng cộng</span><strong><?php echo number_format($order['final_amount'], 0, ',', '.'); ?>₫</strong></div>

<<<<<<< HEAD
                    <div class="form-group terms" style="margin-top: 18px; padding-top: 12px; border-top: 1px solid #f0f1f5;">
                        <label class="checkbox-label">
                            <input type="checkbox" name="terms_agree" required <?php echo isset($_POST['terms_agree']) ? 'checked' : ''; ?> />
=======
                    <div class="form-group terms">
                        <label class="checkbox-label">
                            <input type="checkbox" name="terms_agree" required />
>>>>>>> 79d8d1d56f94b32a57937290034834493747c163
                            Tôi đã đọc và đồng ý với các <a href="#">điều khoản</a> và <a href="#">chính sách</a> của hệ thống.
                        </label>
                    </div>

                    <button type="submit" name="confirm_payment" class="btn btn-primary btn-full">Xác nhận thanh toán → </button>
                </div>
            </div>
        </form>
    </div>
</div>

<<<<<<< HEAD
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Update selected class for payment methods
        const radioInputs = document.querySelectorAll('input[name="payment_method"]');
        const radioCards = document.querySelectorAll('.radio-card');
        
        function updateRadioSelection() {
            radioCards.forEach(card => {
                const input = card.querySelector('input[type="radio"]');
                if (input && input.checked) {
                    card.classList.add('selected');
                } else {
                    card.classList.remove('selected');
                }
            });
        }
        
        radioInputs.forEach(input => {
            input.addEventListener('change', updateRadioSelection);
        });
        
        // Initialize on page load
        updateRadioSelection();
        
        // Handle promo code suggestions - auto submit form with selected code
        const promoSuggestionButtons = document.querySelectorAll('button[name="apply_promo_code"]');
        promoSuggestionButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const promoCode = this.getAttribute('value');
                const form = this.closest('form');
                
                if (form) {
                    // Set the promo code input value
                    const promoInput = form.querySelector('input[name="promo_code"]');
                    if (promoInput) {
                        promoInput.value = promoCode;
                    }
                    
                    // Click the main apply button or submit form
                    const mainApplyBtn = form.querySelector('button[name="apply_promo"]');
                    if (mainApplyBtn) {
                        mainApplyBtn.click();
                    }
                }
            });
        });
    });
</script>

=======
>>>>>>> 79d8d1d56f94b32a57937290034834493747c163
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
