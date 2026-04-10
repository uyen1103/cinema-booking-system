<?php
require_once __DIR__ . '/../models/Promotion.php';

class PromotionController {
    private Promotion $promotionModel;

    public function __construct() {
        $this->promotionModel = new Promotion();
    }

    private function renderAdmin(string $viewPath, array $data = []): void {
        extract($data);
        ob_start();
        include __DIR__ . "/../views/admin/promotions/{$viewPath}.php";
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
            'status' => $_GET['status'] ?? ''
        ];

        $this->renderAdmin('index', [
            'promotions' => $this->promotionModel->getAll($filters),
            'stats' => $this->promotionModel->getStats(),
            'filters' => $filters,
            'activeMenu' => 'promotions',
            'breadcrumb' => 'Quản lý khuyến mãi',
            'pageTitle' => 'Quản lý khuyến mãi'
        ]);
    }

    public function create(): void {
        $this->renderAdmin('create', [
            'activeMenu' => 'promotions',
            'breadcrumb' => 'Thêm khuyến mãi mới',
            'pageTitle' => 'Thêm khuyến mãi mới'
        ]);
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('admin_promotions'));
        }

        $code = strtoupper(trim($_POST['code'] ?? ''));
        if ($this->promotionModel->codeExists($code)) {
            set_flash('danger', 'Mã khuyến mãi đã tồn tại.');
            $this->redirect(admin_url('admin_create_promotion'));
        }

        $imagePath = upload_file($_FILES['image_path'] ?? [], 'assets/uploads/promotions', ['jpg', 'jpeg', 'png', 'webp', 'svg'], 'promotion');

        $data = $_POST;
        $data['code'] = $code;
        $data['image_path'] = $imagePath;

        if ($this->promotionModel->create($data)) {
            set_flash('success', 'Đã tạo chương trình khuyến mãi mới.');
        } else {
            delete_local_file($imagePath);
            set_flash('danger', 'Không thể tạo khuyến mãi.');
        }

        $this->redirect(admin_url('admin_promotions'));
    }

    public function edit(int $id): void {
        $promotion = $this->promotionModel->getById($id);
        if (!$promotion) {
            set_flash('danger', 'Không tìm thấy khuyến mãi.');
            $this->redirect(admin_url('admin_promotions'));
        }

        $this->renderAdmin('edit', [
            'promotion' => $promotion,
            'activeMenu' => 'promotions',
            'breadcrumb' => 'Chỉnh sửa khuyến mãi',
            'pageTitle' => 'Chỉnh sửa khuyến mãi'
        ]);
    }

    public function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('admin_promotions'));
        }

        $id = (int) ($_POST['promotion_id'] ?? 0);
        $promotion = $this->promotionModel->getById($id);
        if (!$promotion) {
            set_flash('danger', 'Không tìm thấy khuyến mãi.');
            $this->redirect(admin_url('admin_promotions'));
        }

        $code = strtoupper(trim($_POST['code'] ?? ''));
        if ($this->promotionModel->codeExists($code, $id)) {
            set_flash('danger', 'Mã khuyến mãi đã tồn tại.');
            $this->redirect(admin_url('admin_edit_promotion', ['id' => $id]));
        }

        $newImage = upload_file($_FILES['image_path'] ?? [], 'assets/uploads/promotions', ['jpg', 'jpeg', 'png', 'webp', 'svg'], 'promotion');

        $data = $_POST;
        $data['code'] = $code;
        $data['image_path'] = $newImage ?: ($promotion['image_path'] ?? null);

        if ($this->promotionModel->update($id, $data)) {
            if ($newImage) {
                delete_local_file($promotion['image_path'] ?? null);
            }
            set_flash('success', 'Đã cập nhật khuyến mãi.');
        } else {
            if ($newImage) {
                delete_local_file($newImage);
            }
            set_flash('danger', 'Không thể cập nhật khuyến mãi.');
        }

        $this->redirect(admin_url('admin_promotions'));
    }

    public function delete(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('admin_promotions'));
        }

        $id = (int) ($_POST['promotion_id'] ?? 0);
        $promotion = $this->promotionModel->getById($id);

        if (!$promotion) {
            set_flash('danger', 'Không tìm thấy khuyến mãi để xóa.');
            $this->redirect(admin_url('admin_promotions'));
        }

        if ($this->promotionModel->delete($id)) {
            delete_local_file($promotion['image_path'] ?? null);
            set_flash('success', 'Đã xóa chương trình khuyến mãi.');
        } else {
            set_flash('danger', 'Không thể xóa khuyến mãi.');
        }

        $this->redirect(admin_url('admin_promotions'));
    }
}
