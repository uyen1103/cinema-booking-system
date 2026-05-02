<?php
require_once __DIR__ . '/../config/database.php'; // Cấu hình cơ sở dữ liệu cho model vé

// Model quản lý vé của đơn hàng
// - Dùng để giữ chỗ ghế, truy vấn vé theo đơn, cập nhật trạng thái vé
class Ticket {
    private PDO $conn;
    private string $table = 'tickets';

    // Khởi tạo kết nối DB cho model vé

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function reserveTickets($orderId, $showtimeId, $seatIds, $price): bool {
        $seatPrices = [];
        foreach ($seatIds as $seatId) {
            $seatPrices[(int) $seatId] = $price;
        }
        return $this->reserveTicketsWithPrice($orderId, $showtimeId, $seatPrices);
    }

    // Đặt vé / giữ chỗ ghế cho đơn hàng
    // - Chèn các vé vào bảng tickets với trạng thái 'reserved'
    public function reserveTicketsWithPrice($orderId, $showtimeId, $seatPrices): bool {
        try {
            $this->conn->beginTransaction();
            $sql = "INSERT INTO {$this->table} (order_id, showtime_id, seat_id, price, ticket_status)
                    VALUES (:order_id, :showtime_id, :seat_id, :price, 'reserved')";
            $stmt = $this->conn->prepare($sql);
            foreach ($seatPrices as $seatId => $price) {
                $stmt->execute([
                    ':order_id' => $orderId,
                    ':showtime_id' => $showtimeId,
                    ':seat_id' => (int) $seatId,
                    ':price' => $price,
                ]);
            }
            $this->conn->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return false;
        }
    }

    // Lấy danh sách vé theo đơn hàng
    // - Dùng cho trang lịch sử, thông tin thanh toán và xác nhận vé
    public function getTicketsByOrder($orderId): array {
        $seatRowCol = 'row_name';
        $seatTypeCol = 'type';
        try {
            $seatCols = $this->conn->query('SHOW COLUMNS FROM seats')->fetchAll();
            $seatMap = [];
            foreach ($seatCols as $row) $seatMap[strtolower($row['Field'])] = true;
            $seatRowCol = isset($seatMap['row_name']) ? 'row_name' : 'seat_row';
            $seatTypeCol = isset($seatMap['type']) ? 'type' : 'seat_type';
        } catch (Throwable $e) {
        }

        $sql = "SELECT t.ticket_id, t.order_id, t.showtime_id, t.seat_id, t.price, t.ticket_status,
                       s.{$seatRowCol} AS seat_row,
                       s.seat_number,
                       CASE
                         WHEN s.{$seatTypeCol} IN (2, 'vip') THEN 'vip'
                         WHEN s.{$seatTypeCol} IN (3, 'couple') THEN 'couple'
                         ELSE 'standard'
                       END AS seat_type,
                       st.show_date, st.start_time, st.end_time, st.room_id,
                       COALESCE(NULLIF(r.room_name, ''), r.name) AS room_name,
                       m.title, COALESCE(m.poster, m.poster_url) AS poster_url
                FROM {$this->table} t
                JOIN seats s ON t.seat_id = s.seat_id
                JOIN showtimes st ON t.showtime_id = st.showtime_id
                JOIN movies m ON st.movie_id = m.movie_id
                JOIN rooms r ON st.room_id = r.room_id
                WHERE t.order_id = :order_id
                ORDER BY s.{$seatRowCol}, s.seat_number";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':order_id' => $orderId]);
        return $stmt->fetchAll();
    }

    // Cập nhật vé đã được thanh toán
    // - Khi đơn hàng thanh toán thành công, chuyển vé từ 'reserved' sang 'paid'
    public function markPaid($orderId): bool {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET ticket_status = 'paid' WHERE order_id = :order_id AND ticket_status = 'reserved'");
        return $stmt->execute([':order_id' => $orderId]);
    }

    // Cập nhật vé bị hủy
    // - Khi đơn hàng hủy, tất cả vé liên quan sẽ chuyển sang trạng thái 'cancelled'
    public function markCancelled($orderId): bool {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET ticket_status = 'cancelled' WHERE order_id = :order_id AND ticket_status IN ('reserved','paid')");
        return $stmt->execute([':order_id' => $orderId]);
    }
}
?>