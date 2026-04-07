<?php
require_once 'config/database.php';

class Ticket {
    private $conn;
    private $table_name = 'Tickets';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function reserveTickets($order_id, $showtime_id, $seat_ids, $price) {
        try {
            $this->conn->beginTransaction();

            $sql = "INSERT INTO $this->table_name (order_id, showtime_id, seat_id, price) VALUES (:order_id, :showtime_id, :seat_id, :price)";
            $stmt = $this->conn->prepare($sql);

            foreach ($seat_ids as $seat_id) {
                $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
                $stmt->bindParam(':showtime_id', $showtime_id, PDO::PARAM_INT);
                $stmt->bindParam(':seat_id', $seat_id, PDO::PARAM_INT);
                $stmt->bindParam(':price', $price);
                $stmt->execute();
            }

            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function getTicketsByOrder($order_id) {
        $sql = "SELECT t.ticket_id, t.order_id, t.showtime_id, t.seat_id, t.price, t.ticket_status, s.seat_row, s.seat_number, s.seat_type, st.show_date, st.start_time, st.end_time, st.room_id, r.room_name, m.title, m.poster_url FROM $this->table_name t JOIN Seats s ON t.seat_id = s.seat_id JOIN Showtimes st ON t.showtime_id = st.showtime_id JOIN Movies m ON st.movie_id = m.movie_id JOIN Rooms r ON st.room_id = r.room_id WHERE t.order_id = :order_id ORDER BY s.seat_row, s.seat_number";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markPaid($order_id) {
        $sql = "UPDATE $this->table_name SET ticket_status = 'paid' WHERE order_id = :order_id AND ticket_status = 'reserved'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function markCancelled($order_id) {
        $sql = "UPDATE $this->table_name SET ticket_status = 'cancelled' WHERE order_id = :order_id AND ticket_status IN ('reserved','paid')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
