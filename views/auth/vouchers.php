<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<link rel="stylesheet" href="assets/css/profile.css">
<link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

<div class="profile-page-container">
    <div class="profile-content-wrapper">

        <aside class="profile-sidebar">
            <div class="sidebar-user-card">
                <div class="avatar-box">
                    <?= strtoupper(mb_substr($_SESSION['full_name'] ?? 'U', 0, 2)) ?>
                </div>
                <div class="user-meta">
                    <h3 class="user-name-sidebar"><?= htmlspecialchars($_SESSION['full_name'] ?? 'Người dùng') ?></h3>
                    <?php if (!isAdmin()): ?>
                    <?php if (!isAdmin()): ?><span class="badge-membership">Thành viên bạc</span><?php else: ?><span class="badge-membership">Nhân sự hệ thống</span><?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <nav class="sidebar-nav">
                <a href="<?= h(account_profile_url()) ?>" class="profile-sidebar-item">
                    <i class="ri-user-smile-line"></i><span>Thông tin cá nhân</span>
                </a>
                <?php if (!isAdmin()): ?>
                <a href="<?= h(app_url('history')) ?>" class="profile-sidebar-item">
                    <i class="ri-history-line"></i><span>Lịch sử đặt vé</span>
                </a>
                <a href="<?= h(app_url('vouchers')) ?>" class="profile-sidebar-item active">
                    <i class="ri-coupon-2-line"></i><span>Voucher của tôi</span>
                </a>
                <?php endif; ?>
                <a href="<?= h(account_change_password_url()) ?>" class="profile-sidebar-item">
                    <i class="ri-lock-password-line"></i><span>Đổi mật khẩu</span>
                </a>
                <?php if (!isAdmin()): ?>
                <a href="<?= h(app_url('link-bank-account')) ?>" class="profile-sidebar-item">
                    <i class="ri-bank-card-line"></i><span>Liên kết tài khoản ngân hàng</span>
                </a>
                <?php endif; ?>
                <?php if (isAdmin()): ?>
                <a href="<?= h(admin_url('dashboard')) ?>" class="profile-sidebar-item">
                    <i class="ri-dashboard-line"></i><span>Quay về Admin</span>
                </a>
                <?php endif; ?>
                <div class="nav-divider"></div>
                <a href="<?= h(account_logout_url()) ?>" class="profile-sidebar-item">
                    <i class="ri-logout-box-r-line"></i><span>Đăng xuất</span>
                </a>
            </nav>
        </aside>

        <main class="profile-main-content" style="flex: 1;">
            <header style="margin-bottom: 30px;">
                <h1 class="page-title">Mã giảm giá khả dụng</h1>
                <p class="page-subtitle">Chọn mã voucher để xem chi tiết</p>
            </header>

            <div class="vouchers-container">
                <?php if (!empty($vouchers)): ?>
                    <div class="vouchers-left">
                        <div class="vouchers-tabs">
                            <button class="tab-btn active" onclick="filterVouchers('all')">Tất cả</button>
                            <button class="tab-btn" onclick="filterVouchers('percent')">Giảm %</button>
                            <button class="tab-btn" onclick="filterVouchers('fixed')">Giảm tiền</button>
                        </div>

                        <div class="vouchers-list" id="vouchersList">
                            <?php foreach ($vouchers as $index => $voucher): ?>
                                <div class="voucher-item" data-type="<?= ($voucher['discount_type'] ?? '') === 'percent' ? 'percent' : 'fixed' ?>" onclick="selectVoucher(<?= $index ?>)" style="cursor: pointer;">
                                    <div class="voucher-item-left">
                                        <div class="voucher-item-icon">
                                            <i class="ri-coupon-2-fill"></i>
                                        </div>
                                    </div>
                                    <div class="voucher-item-middle">
                                        <div class="voucher-item-code"><?= htmlspecialchars($voucher['promo_code'] ?? '') ?></div>
                                        <div class="voucher-item-desc"><?= htmlspecialchars(substr($voucher['description'] ?? '', 0, 50)) ?>...</div>
                                    </div>
                                    <div class="voucher-item-right">
                                        <div class="voucher-item-discount">
                                            <?php if (($voucher['discount_type'] ?? '') === 'percent'): ?>
                                                -<?= $voucher['discount_value'] ?? 0 ?>%
                                            <?php else: ?>
                                                -<?= number_format($voucher['discount_value'] ?? 0, 0, ',', '.') ?>đ
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="vouchers-right">
                        <?php if (!empty($vouchers)): ?>
                            <div class="voucher-detail" id="voucherDetail">
                                <div class="voucher-detail-content">
                                    <div class="voucher-detail-header">
                                        <div class="voucher-detail-icon">
                                            <i class="ri-coupon-2-fill"></i>
                                        </div>
                                    </div>

                                    <div class="voucher-detail-body">
                                        <h2 class="voucher-detail-code" id="detailCode"><?= htmlspecialchars($vouchers[0]['promo_code'] ?? '') ?></h2>
                                        <p class="voucher-detail-desc" id="detailDesc"><?= htmlspecialchars($vouchers[0]['description'] ?? '') ?></p>

                                        <div class="voucher-detail-info">
                                            <div class="info-row">
                                                <span class="info-label">Loại giảm giá:</span>
                                                <span class="info-value">
                                                    <?php if (($vouchers[0]['discount_type'] ?? '') === 'percent'): ?>
                                                        Giảm <?= $vouchers[0]['discount_value'] ?? 0 ?>%
                                                    <?php else: ?>
                                                        Giảm <?= number_format($vouchers[0]['discount_value'] ?? 0, 0, ',', '.') ?>đ
                                                    <?php endif; ?>
                                                </span>
                                            </div>

                                            <?php if (($vouchers[0]['min_amount'] ?? 0) > 0): ?>
                                                <div class="info-row">
                                                    <span class="info-label">Tối thiểu:</span>
                                                    <span class="info-value"><?= number_format($vouchers[0]['min_amount'], 0, ',', '.') ?>đ</span>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (($vouchers[0]['min_tickets'] ?? 0) > 0): ?>
                                                <div class="info-row">
                                                    <span class="info-label">Min vé:</span>
                                                    <span class="info-value"><?= $vouchers[0]['min_tickets'] ?> vé</span>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (!empty($vouchers[0]['end_date'])): ?>
                                                <div class="info-row">
                                                    <span class="info-label">Hạn sử dụng:</span>
                                                    <span class="info-value"><?= date('d/m/Y', strtotime($vouchers[0]['end_date'])) ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <button type="button" class="btn-copy-large" onclick="copyToClipboard('<?= htmlspecialchars($vouchers[0]['promo_code'] ?? '') ?>')">
                                            <i class="ri-file-copy-line"></i> Sao chép mã
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state" style="grid-column: 1/-1;">
                        <h2>Chưa có voucher nào</h2>
                        <p>Hiện tại không có mã giảm giá nào khả dụng. Vui lòng quay lại sau!</p>
                        <a href="<?= h(app_url('promotions')) ?>" class="btn btn-primary">Xem khuyến mãi</a>
                    </div>
                <?php endif; ?>
            </div>

            <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #e1e4e8;">
                <a href="<?= h(account_profile_url()) ?>" class="btn-back-link">
                    <i class="ri-arrow-go-back-line"></i><span>Quay lại</span>
                </a>
            </div>
        </main>
    </div>
</div>

<style>
.vouchers-container {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 24px;
}

.vouchers-tabs {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
}

.tab-btn {
    padding: 10px 18px;
    border: 2px solid #e71930;
    background: white;
    color: #e71930;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.2s;
}

.tab-btn.active {
    background: #e71930;
    color: white;
}

.tab-btn:hover {
    background: #fee2e2;
}

.vouchers-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.voucher-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    background: white;
    border: 2px solid #f1f1f1;
    border-radius: 12px;
    transition: all 0.3s;
}

.voucher-item:hover {
    border-color: #e71930;
    box-shadow: 0 4px 12px rgba(231, 25, 48, 0.1);
}

.voucher-item.active {
    border-color: #e71930;
    background: #fef2f2;
}

.voucher-item-left {
    flex-shrink: 0;
}

.voucher-item-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    background: #fee2e2;
    border-radius: 10px;
    font-size: 1.8rem;
    color: #e71930;
}

.voucher-item-middle {
    flex: 1;
    min-width: 0;
}

.voucher-item-code {
    font-weight: 700;
    font-size: 1rem;
    color: #333;
    margin-bottom: 4px;
    font-family: monospace;
}

.voucher-item-desc {
    font-size: 0.85rem;
    color: #999;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.voucher-item-right {
    flex-shrink: 0;
}

.voucher-item-discount {
    font-size: 1.3rem;
    font-weight: 700;
    color: #e71930;
    text-align: right;
}

.vouchers-right {
    position: sticky;
    top: 20px;
}

.voucher-detail {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid #f1f1f1;
}

.voucher-detail-header {
    height: 120px;
    background: linear-gradient(135deg, #e71930 0%, #c41629 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}

.voucher-detail-icon {
    font-size: 3rem;
    color: white;
}

.voucher-detail-body {
    padding: 24px;
}

.voucher-detail-code {
    font-size: 1.5rem;
    font-weight: 800;
    color: #333;
    margin: 0 0 8px 0;
    font-family: monospace;
    letter-spacing: 1px;
}

.voucher-detail-desc {
    font-size: 0.95rem;
    color: #666;
    margin: 0 0 20px 0;
    line-height: 1.5;
}

.voucher-detail-info {
    display: flex;
    flex-direction: column;
    gap: 14px;
    margin-bottom: 24px;
    padding-bottom: 24px;
    border-bottom: 1px solid #f1f1f1;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.info-label {
    font-size: 0.85rem;
    color: #999;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-value {
    font-size: 1rem;
    font-weight: 700;
    color: #e71930;
}

.btn-copy-large {
    width: 100%;
    padding: 14px;
    background: #e71930;
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: 700;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-copy-large:hover {
    background: #c41629;
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(231, 25, 48, 0.3);
}

@media (max-width: 1024px) {
    .vouchers-container {
        grid-template-columns: 1fr;
    }
    
    .vouchers-right {
        position: static;
    }
}

@media (max-width: 768px) {
    .vouchers-tabs {
        overflow-x: auto;
    }
    
    .tab-btn {
        font-size: 0.8rem;
        padding: 8px 14px;
    }
}
</style>

<script>
function selectVoucher(index) {
    const vouchers = <?= json_encode($vouchers) ?>;
    const voucher = vouchers[index];
    
    // Update active state
    document.querySelectorAll('.voucher-item').forEach((item, i) => {
        item.classList.toggle('active', i === index);
    });
    
    // Update detail panel
    document.getElementById('detailCode').textContent = voucher.promo_code;
    document.getElementById('detailDesc').textContent = voucher.description;
    
    // Update copy button
    document.querySelector('.btn-copy-large').onclick = function() {
        copyToClipboard(voucher.promo_code);
    };
}

function filterVouchers(type) {
    const items = document.querySelectorAll('.voucher-item');
    items.forEach(item => {
        if (type === 'all') {
            item.style.display = 'flex';
        } else {
            item.style.display = item.dataset.type === type ? 'flex' : 'none';
        }
    });
    
    // Update active tab
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        const btn = document.querySelector('.btn-copy-large');
        const oldText = btn.innerHTML;
        btn.innerHTML = '<i class="ri-check-line"></i> Đã sao chép!';
        setTimeout(() => {
            btn.innerHTML = oldText;
        }, 2000);
    }).catch(err => {
        console.error('Không thể sao chép:', err);
    });
}

// Select first voucher by default
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('.voucher-item')) {
        selectVoucher(0);
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>