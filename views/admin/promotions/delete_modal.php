<div class="modal fade" id="deletePromotionModal<?= (int) $promotion['promotion_id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content admin-card admin-modal-danger">
            <div class="admin-card__body">
                <div class="text-center mb-3">
                    <div class="admin-stat-card__icon admin-icon--red mx-auto"><i class="fa-solid fa-triangle-exclamation"></i></div>
                </div>
                <h4 class="text-center fw-bold mb-3">Xác nhận xóa khuyến mãi</h4>
                <p class="text-center text-muted">Bạn muốn xóa mã <strong><?= h($promotion['code']) ?></strong> khỏi hệ thống?</p>
                <div class="alert alert-danger small">Nếu hóa đơn đã sử dụng mã này, chỉ nên xóa khi thật sự cần thiết.</div>
                <form method="POST" action="<?= h(admin_url('admin_delete_promotion')) ?>" class="d-flex justify-content-center gap-2">
                    <input type="hidden" name="promotion_id" value="<?= (int) $promotion['promotion_id'] ?>">
                    <button class="admin-btn admin-btn--light" type="button" data-bs-dismiss="modal">Hủy</button>
                    <button class="admin-btn admin-btn--danger" type="submit">Xác nhận xóa</button>
                </form>
            </div>
        </div>
    </div>
</div>
