<?php
require_once __DIR__ . '/../models/Showtime.php';
require_once __DIR__ . '/../models/Movie.php';
require_once __DIR__ . '/../models/Room.php';

class ShowtimeController {
    private Showtime $showtimeModel;
    private Movie $movieModel;
    private Room $roomModel;

    public function __construct() {
        $this->showtimeModel = new Showtime();
        $this->movieModel = new Movie();
        $this->roomModel = new Room();
    }

    private function renderAdmin(string $viewPath, array $data = []): void {
        extract($data);
        ob_start();
        include __DIR__ . "/../views/admin/showtimes/{$viewPath}.php";
        $content = ob_get_clean();
        include __DIR__ . '/../views/layouts/admin_layout.php';
    }

    private function redirect(string $url): void {
        header("Location: {$url}");
        exit;
    }

    private function validatePayload(array $data, int $ignoreId = 0): ?string {
        if ($data['movie_id'] <= 0 || !$this->movieModel->getById($data['movie_id'])) {
            return 'Phim được chọn không tồn tại.';
        }
        $room = $this->roomModel->getById($data['room_id']);
        if ($data['room_id'] <= 0 || !$room) {
            return 'Phòng chiếu được chọn không tồn tại.';
        }
        if ((int)($room['status'] ?? 1) === 0) {
            return 'Phòng chiếu đang bảo trì và không thể tạo suất chiếu.';
        }
        if ($data['price'] <= 0) {
            return 'Giá vé phải lớn hơn 0.';
        }
        if (strtotime($data['show_date'] . ' ' . $data['start_time']) >= strtotime($data['show_date'] . ' ' . $data['end_time'])) {
            return 'Giờ bắt đầu phải nhỏ hơn giờ kết thúc.';
        }
        if ($this->showtimeModel->hasConflict($data['room_id'], $data['show_date'], $data['start_time'], $data['end_time'], $ignoreId ?: null)) {
            return 'Phòng chiếu đã có suất khác trùng khung giờ.';
        }
        return null;
    }

    public function index(): void {
        $filters = [
            'keyword' => trim($_GET['keyword'] ?? ''),
            'show_date' => $_GET['show_date'] ?? '',
            'status' => $_GET['status'] ?? '',
        ];

        $this->renderAdmin('index', [
            'showtimes' => $this->showtimeModel->getAll($filters),
            'stats' => $this->showtimeModel->getStats(),
            'filters' => $filters,
            'activeMenu' => 'showtimes',
            'breadcrumb' => 'Quản lý suất chiếu',
            'pageTitle' => 'Quản lý suất chiếu'
        ]);
    }

    public function create(): void {
        $this->renderAdmin('create', [
            'movies' => $this->movieModel->getAll([]),
            'rooms' => $this->roomModel->getAll([]),
            'activeMenu' => 'showtimes',
            'breadcrumb' => 'Thêm suất chiếu mới',
            'pageTitle' => 'Thêm suất chiếu mới'
        ]);
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('admin_showtimes'));
        }

        $data = [
            'movie_id' => (int) ($_POST['movie_id'] ?? 0),
            'room_id' => (int) ($_POST['room_id'] ?? 0),
            'show_date' => $_POST['show_date'] ?? '',
            'start_time' => $_POST['start_time'] ?? '',
            'end_time' => $_POST['end_time'] ?? '',
            'price' => (float) ($_POST['price'] ?? 0),
            'status' => (int) ($_POST['status'] ?? 1),
        ];

        if ($error = $this->validatePayload($data)) {
            set_flash('danger', $error);
            $this->redirect(admin_url('admin_create_showtime'));
        }

        if ($this->showtimeModel->create($data)) {
            set_flash('success', 'Thêm suất chiếu thành công.');
        } else {
            set_flash('danger', 'Không thể thêm suất chiếu.');
        }

        $this->redirect(admin_url('admin_showtimes'));
    }

    public function edit(int $id): void {
        $showtime = $this->showtimeModel->getById($id);
        if (!$showtime) {
            set_flash('danger', 'Không tìm thấy suất chiếu.');
            $this->redirect(admin_url('admin_showtimes'));
        }

        $this->renderAdmin('edit', [
            'showtime' => $showtime,
            'movies' => $this->movieModel->getAll([]),
            'rooms' => $this->roomModel->getAll([]),
            'activeMenu' => 'showtimes',
            'breadcrumb' => 'Chỉnh sửa suất chiếu',
            'pageTitle' => 'Chỉnh sửa suất chiếu'
        ]);
    }

    public function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('admin_showtimes'));
        }

        $id = (int) ($_POST['showtime_id'] ?? 0);
        $showtime = $this->showtimeModel->getById($id);
        if (!$showtime) {
            set_flash('danger', 'Suất chiếu không tồn tại.');
            $this->redirect(admin_url('admin_showtimes'));
        }

        $data = [
            'movie_id' => (int) ($_POST['movie_id'] ?? 0),
            'room_id' => (int) ($_POST['room_id'] ?? 0),
            'show_date' => $_POST['show_date'] ?? '',
            'start_time' => $_POST['start_time'] ?? '',
            'end_time' => $_POST['end_time'] ?? '',
            'price' => (float) ($_POST['price'] ?? 0),
            'status' => (int) ($_POST['status'] ?? 1),
        ];

        if ($error = $this->validatePayload($data, $id)) {
            set_flash('danger', $error);
            $this->redirect(admin_url('admin_edit_showtime', ['id' => $id]));
        }

        if ($this->showtimeModel->update($id, $data)) {
            set_flash('success', 'Cập nhật suất chiếu thành công.');
        } else {
            set_flash('danger', 'Không thể cập nhật suất chiếu.');
        }

        $this->redirect(admin_url('admin_showtimes'));
    }

    public function delete(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('admin_showtimes'));
        }

        $id = (int) ($_POST['showtime_id'] ?? 0);

        if (!$this->showtimeModel->canDelete($id)) {
            set_flash('danger', 'Không thể xóa suất chiếu đã phát sinh vé đặt.');
        } elseif ($this->showtimeModel->delete($id)) {
            set_flash('success', 'Đã xóa suất chiếu.');
        } else {
            set_flash('danger', 'Không thể xóa suất chiếu.');
        }

        $this->redirect(admin_url('admin_showtimes'));
    }
}
