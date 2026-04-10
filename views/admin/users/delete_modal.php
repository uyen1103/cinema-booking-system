<div class="modal fade" id="deleteUserModal<?= (int) $user['user_id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content admin-card admin-modal-danger">
            <div class="admin-card__body">
                <div class="text-center mb-3">
                    <div class="admin-stat-card__icon admin-icon--red mx-auto"><i class="fa-solid fa-triangle-exclamation"></i></div>
                </div>
                <h4 class="text-center fw-bold mb-3">Xác nhận xóa <?= $userRole === 'staff' ? 'nhân viên' : 'khách hàng' ?></h4>
                <p class="text-center text-muted mb-3">
                    Bạn có chắc chắn muốn xóa <strong><?= h($user['full_name']) ?></strong> khỏi hệ thống không?
                </p>
                <div class="alert alert-danger small">
                    Thao tác này không thể hoàn tác. Toàn bộ dữ liệu liên quan tới tài khoản sẽ bị xóa.
                </div>

                <form method="POST" action="?action=delete_user" class="d-flex gap-2 justify-content-center">
                    <input type="hidden" name="user_id" value="<?= (int) $user['user_id'] ?>">
                    <button class="admin-btn admin-btn--light" type="button" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button class="admin-btn admin-btn--danger" type="submit">Xác nhận xóa</button>
                </form>
            </div>
        </div>
    </div>
</div>
