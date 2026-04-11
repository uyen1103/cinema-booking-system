<div class="modal fade" id="deleteMovieModal<?= (int) $movie['movie_id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content admin-card admin-modal-danger">
            <div class="admin-card__body">
                <div class="text-center mb-3">
                    <div class="admin-stat-card__icon admin-icon--red mx-auto"><i class="fa-solid fa-triangle-exclamation"></i></div>
                </div>
                <h4 class="text-center fw-bold mb-3">Xác nhận xóa phim</h4>
                <p class="text-center text-muted mb-3">
                    Bạn có chắc chắn muốn xóa phim <strong><?= h($movie['title']) ?></strong> khỏi hệ thống?
                </p>
                <div class="alert alert-danger small">
                    Phim, lịch chiếu và dữ liệu liên quan sẽ bị xóa nếu cơ sở dữ liệu đang bật khóa ngoại.
                </div>

                <form method="POST" action="?action=delete_movie" class="d-flex justify-content-center gap-2">
                    <input type="hidden" name="movie_id" value="<?= (int) $movie['movie_id'] ?>">
                    <button class="admin-btn admin-btn--light" type="button" data-bs-dismiss="modal">Hủy</button>
                    <button class="admin-btn admin-btn--danger" type="submit">Xác nhận xóa</button>
                </form>
            </div>
        </div>
    </div>
</div>
