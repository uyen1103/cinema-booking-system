<?php
require_once __DIR__ . '/../models/Report.php';
require_once __DIR__ . '/../models/CancellationRequest.php';

class ReportController {
    private Report $reportModel;
    private CancellationRequest $cancellationModel;

    public function __construct() {
        $this->reportModel = new Report();
        $this->cancellationModel = new CancellationRequest();
    }

    private function render(string $viewFile, array $data): void {
        extract($data);
        ob_start();
        include $viewFile;
        $content = ob_get_clean();
        include __DIR__ . '/../views/layouts/admin_layout.php';
    }

    public function dashboard(): void {
        $overview = $this->reportModel->getOverview();
        $recentInvoices = $this->reportModel->getRecentInvoices(8);
        $pendingRequests = $this->cancellationModel->getPendingRequests();

        $this->render(__DIR__ . '/../views/admin/dashboard/index.php', [
            'overview' => $overview,
            'recentInvoices' => $recentInvoices,
            'pendingRequests' => $pendingRequests,
            'activeMenu' => 'dashboard',
            'breadcrumb' => 'Bảng điều khiển',
            'pageTitle' => 'Bảng điều khiển',
        ]);
    }

    public function reports(): void {
        $year = (int) ($_GET['year'] ?? date('Y'));
        $overview = $this->reportModel->getOverview();
        $revenueBars = $this->reportModel->getRevenueBars($year);
        $topMovies = $this->reportModel->getTopMovies();
        $promotionPerformance = $this->reportModel->getPromotionPerformance();
        $recentInvoices = $this->reportModel->getRecentInvoices();

        $this->render(__DIR__ . '/../views/admin/reports/index.php', [
            'overview' => $overview,
            'revenueBars' => $revenueBars,
            'topMovies' => $topMovies,
            'promotionPerformance' => $promotionPerformance,
            'recentInvoices' => $recentInvoices,
            'activeMenu' => 'reports',
            'breadcrumb' => 'Tạo báo cáo thống kê',
            'pageTitle' => 'Báo cáo thống kê',
        ]);
    }
}
