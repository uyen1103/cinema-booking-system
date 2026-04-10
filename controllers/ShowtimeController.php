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
            $this->redirect('?action=showtimes');
        }

        $data = [
            'movie_id' => (int) ($_POST['movie_id'] ?? 0),
            'room_id' => (int) ($_POST['room_id'] ?? 0),
            'show_date' => $_POST['show_date'] ?? '',
            'start_time' => $_POST['start_time'] ?? '',
            'end_time' => $_POST['end_time'] ?? '',
            'price' => (int) ($_POST['price'] ?? 0),
            'status' => (int) ($_POST['status'] ?? 1),
        ];

        if ($data['movie_id'] <= 0 || $data['room_id'] <= 0 || $data['price'] <= 0) {
            set_flash('danger', 'Vui lòng nhập đủ phim, phòng và giá vé.');
            $this->redirect('?action=create_showtime');
        }

        if ($this->showtimeModel->hasConflict($data['room_id'], $data['show_date'], $data['start_time'], $data['end_time'])) {
            set_flash('danger', 'Phòng chiếu đã có suất khác trùng khung giờ.');
            $this->redirect('?action=create_showtime');
        }

        if ($this->showtimeModel->create($data)) {
            set_flash('success', 'Thêm suất chiếu thành công.');
        } else {
            set_flash('danger', 'Không thể thêm suất chiếu.');
        }

        $this->redirect('?action=showtimes');
    }

    public function edit(int $id): void {
        $showtime = $this->showtimeModel->getById($id);
        if (!$showtime) {
            set_flash('danger', 'Không tìm thấy suất chiếu.');
            $this->redirect('?action=showtimes');
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
            $this->redirect('?action=showtimes');
        }

        $id = (int) ($_POST['showtime_id'] ?? 0);
        $showtime = $this->showtimeModel->getById($id);
        if (!$showtime) {
            set_flash('danger', 'Suất chiếu không tồn tại.');
            $this->redirect('?action=showtimes');
        }

        $data = [
            'movie_id' => (int) ($_POST['movie_id'] ?? 0),
            'room_id' => (int) ($_POST['room_id'] ?? 0),
            'show_date' => $_POST['show_date'] ?? '',
            'start_time' => $_POST['start_time'] ?? '',
            'end_time' => $_POST['end_time'] ?? '',
            'price' => (int) ($_POST['price'] ?? 0),
            'status' => (int) ($_POST['status'] ?? 1),
        ];

        if ($this->showtimeModel->hasConflict($data['room_id'], $data['show_date'], $data['start_time'], $data['end_time'], $id)) {
            set_flash('danger', 'Phòng chiếu đã có suất khác trùng khung giờ.');
            $this->redirect('?action=edit_showtime&id=' . $id);
        }

        if ($this->showtimeModel->update($id, $data)) {
            set_flash('success', 'Cập nhật suất chiếu thành công.');
        } else {
            set_flash('danger', 'Không thể cập nhật suất chiếu.');
        }

        $this->redirect('?action=showtimes');
    }

    public function delete(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?action=showtimes');
        }

        $id = (int) ($_POST['showtime_id'] ?? 0);

        if ($this->showtimeModel->delete($id)) {
            set_flash('success', 'Đã xóa suất chiếu.');
        } else {
            set_flash('danger', 'Không thể xóa suất chiếu.');
        }

        $this->redirect('?action=showtimes');
    }
}
