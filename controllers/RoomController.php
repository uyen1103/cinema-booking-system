<?php
require_once __DIR__ . '/../models/Room.php';

class RoomController {
    private Room $roomModel;

    public function __construct() {
        $this->roomModel = new Room();
    }

    private function renderAdmin(string $viewPath, array $data = []): void {
        extract($data);
        ob_start();
        include __DIR__ . "/../views/admin/rooms/{$viewPath}.php";
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
            'status' => $_GET['status'] ?? '',
        ];

        $this->renderAdmin('index', [
            'rooms' => $this->roomModel->getAll($filters),
            'stats' => $this->roomModel->getStats(),
            'filters' => $filters,
            'activeMenu' => 'rooms',
            'breadcrumb' => 'Quản lý phòng chiếu và ghế',
            'pageTitle' => 'Quản lý phòng chiếu và ghế'
        ]);
    }

    public function showSeats(int $roomId): void {
        $room = $this->roomModel->getById($roomId);
        if (!$room) {
            set_flash('danger', 'Không tìm thấy phòng chiếu.');
            $this->redirect('?action=rooms');
        }

        $this->renderAdmin('seats', [
            'room' => $room,
            'seats' => $this->roomModel->getSeatsByRoomId($roomId),
            'activeMenu' => 'rooms',
            'breadcrumb' => 'Sơ đồ ghế ' . $room['name'],
            'pageTitle' => 'Sơ đồ ghế ' . $room['name']
        ]);
    }

    public function create(): void {
        $this->renderAdmin('create', [
            'activeMenu' => 'rooms',
            'breadcrumb' => 'Thêm phòng chiếu mới',
            'pageTitle' => 'Thêm phòng chiếu mới'
        ]);
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?action=rooms');
        }

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'capacity' => (int) ($_POST['capacity'] ?? 0),
            'opening_time' => $_POST['opening_time'] ?? '08:00',
            'closing_time' => $_POST['closing_time'] ?? '23:30',
            'status' => (int) ($_POST['status'] ?? 1),
            'maintenance_reason' => trim($_POST['maintenance_reason'] ?? '')
        ];

        if ($data['name'] === '' || $data['capacity'] <= 0) {
            set_flash('danger', 'Vui lòng nhập tên phòng và sức chứa hợp lệ.');
            $this->redirect('?action=create_room');
        }

        if ($this->roomModel->create($data)) {
            set_flash('success', 'Đã tạo phòng chiếu mới và sinh sơ đồ ghế mặc định.');
        } else {
            set_flash('danger', 'Không thể tạo phòng chiếu.');
        }

        $this->redirect('?action=rooms');
    }

    public function edit(int $id): void {
        $room = $this->roomModel->getById($id);
        if (!$room) {
            set_flash('danger', 'Không tìm thấy phòng chiếu.');
            $this->redirect('?action=rooms');
        }

        $this->renderAdmin('edit', [
            'room' => $room,
            'activeMenu' => 'rooms',
            'breadcrumb' => 'Chỉnh sửa phòng chiếu',
            'pageTitle' => 'Chỉnh sửa phòng chiếu'
        ]);
    }

    public function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?action=rooms');
        }

        $id = (int) ($_POST['room_id'] ?? 0);
        $room = $this->roomModel->getById($id);
        if (!$room) {
            set_flash('danger', 'Không tìm thấy phòng chiếu.');
            $this->redirect('?action=rooms');
        }

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'capacity' => (int) ($_POST['capacity'] ?? $room['capacity']),
            'opening_time' => $_POST['opening_time'] ?? $room['opening_time'],
            'closing_time' => $_POST['closing_time'] ?? $room['closing_time'],
            'status' => (int) ($_POST['status'] ?? $room['status']),
            'maintenance_reason' => trim($_POST['maintenance_reason'] ?? '')
        ];

        if ($this->roomModel->update($id, $data)) {
            set_flash('success', 'Đã cập nhật phòng chiếu.');
        } else {
            set_flash('danger', 'Không thể cập nhật phòng chiếu.');
        }

        $this->redirect('?action=rooms');
    }

    public function delete(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?action=rooms');
        }

        $id = (int) ($_POST['room_id'] ?? 0);

        if ($this->roomModel->delete($id)) {
            set_flash('success', 'Đã xóa phòng chiếu.');
        } else {
            set_flash('danger', 'Không thể xóa phòng chiếu.');
        }

        $this->redirect('?action=rooms');
    }

    public function generateSeats(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?action=rooms');
        }

        $roomId = (int) ($_POST['room_id'] ?? 0);
        if ($this->roomModel->generateStandardSeats($roomId)) {
            set_flash('success', 'Đã sinh sơ đồ ghế mặc định.');
            $this->redirect('?action=room_seats&id=' . $roomId);
        }

        set_flash('danger', 'Không thể sinh sơ đồ ghế.');
        $this->redirect('?action=rooms');
    }

    public function toggleSeat(): void {
        $seatId = (int) ($_GET['seat_id'] ?? 0);
        $roomId = (int) ($_GET['room_id'] ?? 0);

        if ($seatId > 0 && $this->roomModel->toggleSeatStatus($seatId)) {
            set_flash('success', 'Đã cập nhật trạng thái ghế.');
        } else {
            set_flash('danger', 'Không thể cập nhật trạng thái ghế.');
        }

        $this->redirect('?action=room_seats&id=' . $roomId);
    }
}
