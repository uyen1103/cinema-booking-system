<?php
require_once __DIR__ . '/../config/database.php';

class Showtime {
    private PDO $conn;
    private string $table = 'showtimes';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    private function normalizeRow(array $row): array {
        $row['showtime_id'] = (int) ($row['showtime_id'] ?? 0);
        $row['movie_id'] = (int) ($row['movie_id'] ?? 0);
        $row['room_id'] = (int) ($row['room_id'] ?? 0);
        $row['movie_title'] = (string) ($row['movie_title'] ?? $row['title'] ?? 'Chưa cập nhật');
        $row['title'] = (string) ($row['title'] ?? $row['movie_title'] ?? 'Chưa cập nhật');
        $row['room_name'] = (string) ($row['room_name'] ?? $row['name'] ?? 'Chưa cập nhật');
        $row['name'] = (string) ($row['name'] ?? $row['room_name'] ?? 'Chưa cập nhật');
        $row['movie_poster'] = (string) ($row['movie_poster'] ?? $row['poster'] ?? $row['poster_url'] ?? '');
        $row['price'] = (float) ($row['price'] ?? $row['base_price'] ?? 0);
        $row['base_price'] = (float) ($row['base_price'] ?? $row['price'] ?? 0);
        $row['status'] = (int) ($row['status'] ?? 1);
        $row['sold_tickets'] = (int) ($row['sold_tickets'] ?? 0);
        return $row;
    }

    public function getAll(array $filters = []): array {
        $sql = "SELECT s.*,
                    m.title AS movie_title,
                    COALESCE(m.poster, m.poster_url) AS movie_poster,
                    COALESCE(NULLIF(r.room_name, ''), r.name) AS room_name,
                    (SELECT COUNT(*) FROM tickets t WHERE t.showtime_id = s.showtime_id AND t.ticket_status <> 'cancelled') AS sold_tickets
                FROM {$this->table} s
                INNER JOIN movies m ON m.movie_id = s.movie_id
                INNER JOIN rooms r ON r.room_id = s.room_id
                WHERE 1 = 1";
        $params = [];

        if (!empty($filters['keyword'])) {
            $sql .= " AND (m.title LIKE :keyword OR COALESCE(NULLIF(r.room_name, ''), r.name) LIKE :keyword)";
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
        return array_map(fn(array $row) => $this->normalizeRow($row), $stmt->fetchAll());
    }

    public function getById(int $id): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE showtime_id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->normalizeRow($row) : null;
    }

    public function create(array $data): bool {
        $sql = "INSERT INTO {$this->table}
                (movie_id, room_id, show_date, start_time, end_time, price, base_price, status)
                VALUES (:movie_id, :room_id, :show_date, :start_time, :end_time, :price, :base_price, :status)";
        $stmt = $this->conn->prepare($sql);
        $price = (float) $data['price'];
        return $stmt->execute([
            ':movie_id' => (int) $data['movie_id'],
            ':room_id' => (int) $data['room_id'],
            ':show_date' => $data['show_date'],
            ':start_time' => $data['start_time'],
            ':end_time' => $data['end_time'],
            ':price' => $price,
            ':base_price' => $price,
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
                    base_price = :base_price,
                    status = :status
                WHERE showtime_id = :showtime_id";
        $stmt = $this->conn->prepare($sql);
        $price = (float) $data['price'];
        return $stmt->execute([
            ':movie_id' => (int) $data['movie_id'],
            ':room_id' => (int) $data['room_id'],
            ':show_date' => $data['show_date'],
            ':start_time' => $data['start_time'],
            ':end_time' => $data['end_time'],
            ':price' => $price,
            ':base_price' => $price,
            ':status' => (int) $data['status'],
            ':showtime_id' => $id,
        ]);
    }

    public function countTickets(int $showtimeId): int {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM tickets WHERE showtime_id = :id AND ticket_status IN ('reserved', 'paid')");
        $stmt->execute([':id' => $showtimeId]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    public function canDelete(int $showtimeId): bool {
        return $this->countTickets($showtimeId) === 0;
    }

    public function delete(int $id): bool {
        if (!$this->canDelete($id)) {
            return false;
        }
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
