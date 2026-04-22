<div class="modal fade" id="deleteRoomModal<?= (int) $room['room_id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content admin-card admin-modal-danger">
            <div class="admin-card__body">
                <div class="text-center mb-3">
                    <div class="admin-stat-card__icon admin-icon--red mx-auto"><i class="fa-solid fa-triangle-exclamation"></i></div>
                </div>
                <h4 class="text-center fw-bold mb-3">Xác nhận xóa phòng</h4>
                <p class="text-center text-muted">Bạn chắc chắn muốn xóa <strong><?= h($room['name']) ?></strong>?</p>
                <div class="alert alert-danger small">Dữ liệu ghế và suất chiếu liên quan có thể bị ảnh hưởng.</div>
                <form method="POST" action="<?= h(admin_url('admin_delete_room')) ?>" class="d-flex justify-content-center gap-2">
                    <input type="hidden" name="room_id" value="<?= (int) $room['room_id'] ?>">
                    <button class="admin-btn admin-btn--light" type="button" data-bs-dismiss="modal">Hủy</button>
                    <button class="admin-btn admin-btn--danger" type="submit">Xác nhận xóa</button>
                </form>
            </div>
        </div>
    </div>
</div>
