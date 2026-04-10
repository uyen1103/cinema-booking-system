<?php
require_once __DIR__ . '/../config/database.php';

class Showtime {
    private PDO $conn;
    private string $table = 'showtimes';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAll(array $filters = []): array {
        $sql = "SELECT s.*,
                    m.title AS movie_title,
                    m.poster AS movie_poster,
                    r.name AS room_name,
                    (SELECT COUNT(*) FROM tickets t WHERE t.showtime_id = s.showtime_id AND t.ticket_status <> 'cancelled') AS sold_tickets
                FROM {$this->table} s
                INNER JOIN movies m ON m.movie_id = s.movie_id
                INNER JOIN rooms r ON r.room_id = s.room_id
                WHERE 1 = 1";
        $params = [];

        if (!empty($filters['keyword'])) {
            $sql .= " AND (m.title LIKE :keyword OR r.name LIKE :keyword)";
            $params[':keyword'] = '%' . trim($filters['keyword']) . '%';
        }

        if (!empty($filters['show_date'])) {
            $sql .= " AND s.show_date = :show_date";
            $params[':show_date'] = $filters['show_date'];
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $sql .= " AND s.status = :status";
            $params[':status'] = (int) $filters['status'];
        }

        $sql .= " ORDER BY s.show_date DESC, s.start_time DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE showtime_id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): bool {
        $sql = "INSERT INTO {$this->table}
                (movie_id, room_id, show_date, start_time, end_time, price, status)
                VALUES (:movie_id, :room_id, :show_date, :start_time, :end_time, :price, :status)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':movie_id' => (int) $data['movie_id'],
            ':room_id' => (int) $data['room_id'],
            ':show_date' => $data['show_date'],
            ':start_time' => $data['start_time'],
            ':end_time' => $data['end_time'],
            ':price' => (int) $data['price'],
            ':status' => (int) $data['status'],
        ]);
    }

    public function update(int $id, array $data): bool {
        $sql = "UPDATE {$this->table}
                SET movie_id = :movie_id,
                    room_id = :room_id,
                    show_date = :show_date,
                    start_time = :start_time,
                    end_time = :end_time,
                    price = :price,
                    status = :status
                WHERE showtime_id = :showtime_id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':movie_id' => (int) $data['movie_id'],
            ':room_id' => (int) $data['room_id'],
            ':show_date' => $data['show_date'],
            ':start_time' => $data['start_time'],
            ':end_time' => $data['end_time'],
            ':price' => (int) $data['price'],
            ':status' => (int) $data['status'],
            ':showtime_id' => $id,
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE showtime_id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function hasConflict(int $roomId, string $showDate, string $startTime, string $endTime, ?int $ignoreId = null): bool {
        $sql = "SELECT COUNT(*) FROM {$this->table}
                WHERE room_id = :room_id
                  AND show_date = :show_date
                  AND start_time < :end_time
                  AND end_time > :start_time";
        $params = [
            ':room_id' => $roomId,
            ':show_date' => $showDate,
            ':start_time' => $startTime,
            ':end_time' => $endTime,
        ];

        if ($ignoreId) {
            $sql .= " AND showtime_id <> :ignore_id";
            $params[':ignore_id'] = $ignoreId;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function getStats(): array {
        $sql = "SELECT
                    COUNT(*) AS total_showtimes,
                    SUM(CASE WHEN show_date = CURDATE() THEN 1 ELSE 0 END) AS today_showtimes,
                    SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) AS active_showtimes,
                    SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) AS cancelled_showtimes
                FROM {$this->table}";
        $row = $this->conn->query($sql)->fetch() ?: [];

        return [
            'total_showtimes' => (int) ($row['total_showtimes'] ?? 0),
            'today_showtimes' => (int) ($row['today_showtimes'] ?? 0),
            'active_showtimes' => (int) ($row['active_showtimes'] ?? 0),
            'cancelled_showtimes' => (int) ($row['cancelled_showtimes'] ?? 0),
        ];
    }
}
