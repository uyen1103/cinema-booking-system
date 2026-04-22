<?php
require_once __DIR__ . '/Order.php';
require_once __DIR__ . '/Movie.php';
require_once __DIR__ . '/Customer.php';
require_once __DIR__ . '/Promotion.php';
require_once __DIR__ . '/Showtime.php';
require_once __DIR__ . '/../config/database.php';

class Report {
    private PDO $conn;
    private Order $orderModel;
    private Movie $movieModel;
    private Customer $customerModel;
    private Promotion $promotionModel;
    private Showtime $showtimeModel;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->orderModel = new Order();
        $this->movieModel = new Movie();
        $this->customerModel = new Customer();
        $this->promotionModel = new Promotion();
        $this->showtimeModel = new Showtime();
    }

    public function getOverview(): array {
        return [
            'orders' => $this->orderModel->getStats(),
            'movies' => $this->movieModel->getStats(),
            'customers' => $this->getCustomerStats(),
            'promotions' => $this->promotionModel->getStats(),
            'showtimes' => $this->showtimeModel->getStats(),
        ];
    }

    private function getCustomerStats(): array {
        if (method_exists($this->customerModel, 'getStats')) {
            return $this->customerModel->getStats();
        }

        $stmt = $this->conn->query("SELECT COUNT(*) AS total,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_count,
            SUM(CASE WHEN status IN ('inactive', 'blocked') THEN 1 ELSE 0 END) AS inactive_count
            FROM customers");
        $row = $stmt->fetch() ?: [];

        return [
            'total' => (int) ($row['total'] ?? 0),
            'active_count' => (int) ($row['active_count'] ?? 0),
            'inactive_count' => (int) ($row['inactive_count'] ?? 0),
            'leave_count' => 0,
        ];
    }

    public function getRevenueBars(int $year): array {
        $rows = $this->orderModel->getMonthlyRevenue($year);
        $map = array_fill(1, 12, 0.0);
        foreach ($rows as $row) {
            $map[(int) $row['month_num']] = (float) $row['revenue'];
        }

        $result = [];
        foreach ($map as $month => $revenue) {
            $result[] = [
                'label' => 'T' . $month,
                'value' => $revenue,
            ];
        }
        return $result;
    }

    public function getTopMovies(int $limit = 5): array {
        $sql = "SELECT m.title, COUNT(t.ticket_id) AS ticket_count, SUM(t.price) AS revenue
                FROM tickets t
                INNER JOIN showtimes s ON s.showtime_id = t.showtime_id
                INNER JOIN movies m ON m.movie_id = s.movie_id
                WHERE t.ticket_status = 'paid'
                GROUP BY m.movie_id, m.title
                ORDER BY ticket_count DESC, revenue DESC
                LIMIT {$limit}";
        return $this->conn->query($sql)->fetchAll();
    }

    public function getPromotionPerformance(int $limit = 5): array {
        $sql = "SELECT COALESCE(title, CONCAT('Khuyến mãi ', promotion_id)) AS title,
                       COALESCE(code, promo_code, '') AS code,
                       COALESCE(used_count, 0) AS used_count,
                       COALESCE(budget, 0) AS budget
                FROM promotions
                ORDER BY used_count DESC, budget DESC, promotion_id DESC
                LIMIT {$limit}";
        return $this->conn->query($sql)->fetchAll();
    }

    public function getRecentInvoices(int $limit = 5): array {
        $orders = $this->orderModel->getAll();
        if ($limit > 0) {
            $orders = array_slice($orders, 0, $limit);
        }
        return array_map(function (array $order): array {
            return [
                'order_code' => $order['order_code'] ?? '',
                'final_amount' => (float) ($order['final_amount'] ?? 0),
                'order_status' => ($order['order_status'] ?? '') === 'paid' ? 'completed' : ($order['order_status'] ?? 'pending'),
                'payment_status' => ($order['payment_status'] ?? '') === 'success' ? 'paid' : ($order['payment_status'] ?? 'pending'),
                'order_date' => $order['order_date'] ?? null,
                'full_name' => $order['full_name'] ?? 'Khách hàng',
            ];
        }, $orders);
    }
}
