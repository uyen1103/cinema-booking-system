<?php
require_once __DIR__ . '/Order.php';
require_once __DIR__ . '/Movie.php';
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/Promotion.php';
require_once __DIR__ . '/Showtime.php';
require_once __DIR__ . '/../config/database.php';

class Report {
    private PDO $conn;
    private Order $orderModel;
    private Movie $movieModel;
    private User $userModel;
    private Promotion $promotionModel;
    private Showtime $showtimeModel;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->orderModel = new Order();
        $this->movieModel = new Movie();
        $this->userModel = new User();
        $this->promotionModel = new Promotion();
        $this->showtimeModel = new Showtime();
    }

    public function getOverview(): array {
        return [
            'orders' => $this->orderModel->getStats(),
            'movies' => $this->movieModel->getStats(),
            'customers' => $this->userModel->getStatsByRole('customer'),
            'promotions' => $this->promotionModel->getStats(),
            'showtimes' => $this->showtimeModel->getStats(),
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
                       COALESCE(code, '') AS code,
                       COALESCE(used_count, 0) AS used_count,
                       COALESCE(budget, 0) AS budget
                FROM promotions
                ORDER BY used_count DESC, budget DESC, promotion_id DESC
                LIMIT {$limit}";
        return $this->conn->query($sql)->fetchAll();
    }

    public function getRecentInvoices(int $limit = 5): array {
        $sql = "SELECT o.order_code,
                       o.final_amount,
                       CASE WHEN o.order_status = 'paid' THEN 'completed' ELSE o.order_status END AS order_status,
                       CASE WHEN o.payment_status = 'success' THEN 'paid' ELSE o.payment_status END AS payment_status,
                       o.order_date,
                       u.full_name
                FROM orders o
                INNER JOIN users u ON u.user_id = o.user_id
                ORDER BY o.order_date DESC
                LIMIT {$limit}";
        return $this->conn->query($sql)->fetchAll();
    }
}
