<?php
require_once __DIR__ . '/../config/database.php';

class Movie {
    private PDO $conn;
    private string $table = 'movies';
    private array $columns = [];

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->syncSchema();
        $this->columns = $this->fetchColumns($this->table);
    }

    private function fetchColumns(string $table): array {
        $columns = [];
        $rows = $this->conn->query("SHOW COLUMNS FROM {$table}")->fetchAll();
        foreach ($rows as $row) {
            $columns[strtolower($row['Field'])] = true;
        }
        return $columns;
    }

    private function hasColumn(string $column): bool {
        return isset($this->columns[strtolower($column)]);
    }

    private function addColumnIfMissing(string $table, string $column, string $definition): void {
        $existing = $this->fetchColumns($table);
        if (!isset($existing[strtolower($column)])) {
            $quoted = preg_match('/^[a-zA-Z0-9_]+$/', $column) ? $column : "`{$column}`";
            $this->conn->exec("ALTER TABLE {$table} ADD COLUMN {$quoted} {$definition}");
        }
    }

    private function syncSchema(): void {
        $this->addColumnIfMissing($this->table, 'description', 'TEXT NULL');
        $this->addColumnIfMissing($this->table, 'director', 'VARCHAR(150) NULL');
        $this->addColumnIfMissing($this->table, 'cast', 'TEXT NULL');
        $this->addColumnIfMissing($this->table, 'poster', 'VARCHAR(255) NULL');
        $this->addColumnIfMissing($this->table, 'poster_url', 'VARCHAR(255) NULL');
        $this->addColumnIfMissing($this->table, 'banner', 'VARCHAR(255) NULL');
        $this->addColumnIfMissing($this->table, 'trailer_url', 'VARCHAR(255) NULL');

        try {
            $cols = $this->fetchColumns($this->table);
            if (isset($cols['poster_url'])) {
                $this->conn->exec("UPDATE {$this->table} SET poster = COALESCE(NULLIF(poster, ''), poster_url)");
            }
            $this->conn->exec("UPDATE {$this->table} SET poster_url = COALESCE(NULLIF(poster_url, ''), poster)");
        } catch (Throwable $e) {
        }

        try {
            $showtimeCols = $this->fetchColumns('showtimes');
            if (!isset($showtimeCols['price']) && isset($showtimeCols['base_price'])) {
                $this->conn->exec("ALTER TABLE showtimes ADD COLUMN price DECIMAL(10,2) NULL AFTER end_time");
                $this->conn->exec("UPDATE showtimes SET price = base_price");
            }
            if (!isset($showtimeCols['base_price']) && isset($showtimeCols['price'])) {
                $this->conn->exec("ALTER TABLE showtimes ADD COLUMN base_price DECIMAL(10,2) NULL AFTER end_time");
                $this->conn->exec("UPDATE showtimes SET base_price = price");
            }
        } catch (Throwable $e) {
        }
    }

    private function publicStatusCondition(string $kind): string {
        return match ($kind) {
            'showing' => "(status IN (1, '1', 'showing') OR (status = 'coming_soon' AND release_date <= CURDATE()))",
            'coming_soon' => "(status IN (2, '2', 'coming_soon') OR (status IN (1, '1', 'showing') AND release_date > CURDATE()))",
            default => '1=1',
        };
    }

    private function normalizePublicMovieRow(array $row): array {
        $status = $row['status'] ?? 1;
        if (in_array((string) $status, ['1', 'showing'], true)) {
            $row['status'] = 'showing';
        } elseif (in_array((string) $status, ['2', 'coming_soon'], true)) {
            $row['status'] = 'coming_soon';
        } else {
            $row['status'] = 'stopped';
        }
        $poster = $row['poster'] ?? $row['poster_url'] ?? '';
        $row['poster_url'] = $poster;
        $row['poster'] = $poster;
        return $row;
    }

    public function homeList(array $filters = []): array {
        return $this->getAll($filters);
    }

    public function getAll(array $filters = []): array {
        $sql = "SELECT m.*,
                    (SELECT COUNT(*) FROM tickets t
                     INNER JOIN showtimes s ON s.showtime_id = t.showtime_id
                     WHERE s.movie_id = m.movie_id AND t.ticket_status <> 'cancelled') AS sold_tickets
                FROM {$this->table} m
                WHERE 1=1";
        $params = [];
        if (!empty($filters['keyword'])) {
            $sql .= " AND (m.title LIKE :keyword OR COALESCE(m.director,'') LIKE :keyword OR COALESCE(m.genre,'') LIKE :keyword)";
            $params[':keyword'] = '%' . trim($filters['keyword']) . '%';
        }
        if (isset($filters['status']) && $filters['status'] !== '') {
            $sql .= " AND m.status = :status";
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['genre'])) {
            $sql .= " AND m.genre LIKE :genre";
            $params[':genre'] = '%' . trim($filters['genre']) . '%';
        }
        $sql .= " ORDER BY m.release_date DESC, m.movie_id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE movie_id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function getMovieById($movieId): ?array {
        $movie = $this->getById((int) $movieId);
        return $movie ? $this->normalizePublicMovieRow($movie) : null;
    }

    public function getShowingMovies(): array {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->publicStatusCondition('showing')} ORDER BY release_date DESC";
        return array_map(fn($row) => $this->normalizePublicMovieRow($row), $this->conn->query($sql)->fetchAll());
    }

    public function getComingSoonMovies(): array {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->publicStatusCondition('coming_soon')} ORDER BY release_date ASC LIMIT 3";
        return array_map(fn($row) => $this->normalizePublicMovieRow($row), $this->conn->query($sql)->fetchAll());
    }

    public function searchMovies($searchQuery): array {
        $roomNameCol = 'name';
        try {
            $roomCols = $this->fetchColumns('rooms');
            if (isset($roomCols['room_name'])) {
                $roomNameCol = 'room_name';
            }
        } catch (Throwable $e) {
        }

        $wildcardQuery = '%' . mb_strtolower($searchQuery, 'UTF-8') . '%';
        $sql = "SELECT DISTINCT m.*
                FROM {$this->table} m
                LEFT JOIN showtimes s ON s.movie_id = m.movie_id
                LEFT JOIN rooms r ON r.room_id = s.room_id
                WHERE LOWER(m.title) LIKE :query
                   OR LOWER(COALESCE(m.genre,'')) LIKE :query
                   OR LOWER(COALESCE(m.description,'')) LIKE :query
                   OR LOWER(COALESCE(r.{$roomNameCol},'')) LIKE :query
                ORDER BY m.release_date DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':query' => $wildcardQuery]);
        return array_map(fn($row) => $this->normalizePublicMovieRow($row), $stmt->fetchAll());
    }

    public function getShowtimesByMovie($movieId): array {
        $roomNameCol = 'name';
        try {
            $roomCols = $this->fetchColumns('rooms');
            if (isset($roomCols['room_name'])) {
                $roomNameCol = 'room_name';
            }
        } catch (Throwable $e) {
        }

        $sql = "SELECT s.showtime_id, s.room_id, r.{$roomNameCol} AS room_name, s.show_date, s.start_time, s.end_time,
                       COALESCE(s.base_price, s.price) AS base_price, COALESCE(s.price, s.base_price) AS price
                FROM showtimes s
                JOIN rooms r ON s.room_id = r.room_id
                WHERE s.movie_id = :movie_id
                  AND s.show_date >= CURDATE()
                  AND (s.status = 1 OR s.status = 'active' OR s.status IS NULL)
                ORDER BY s.show_date, s.start_time";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':movie_id' => $movieId]);
        return $stmt->fetchAll();
    }

    public function getAllRooms(): array {
        $roomNameCol = 'name';
        try {
            $roomCols = $this->fetchColumns('rooms');
            if (isset($roomCols['room_name'])) {
                $roomNameCol = 'room_name';
            }
        } catch (Throwable $e) {
        }

        $stmt = $this->conn->prepare("SELECT room_id, {$roomNameCol} AS room_name, capacity FROM rooms ORDER BY {$roomNameCol}");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getShowtimeById($showtimeId): ?array {
        $roomNameCol = 'name';
        try {
            $roomCols = $this->fetchColumns('rooms');
            if (isset($roomCols['room_name'])) {
                $roomNameCol = 'room_name';
            }
        } catch (Throwable $e) {
        }

        $sql = "SELECT s.showtime_id, s.movie_id, s.room_id, r.{$roomNameCol} AS room_name,
                       s.show_date, s.start_time, s.end_time,
                       COALESCE(s.base_price, s.price) AS base_price,
                       COALESCE(s.price, s.base_price) AS price,
                       m.title, COALESCE(m.poster, m.poster_url) AS poster_url
                FROM showtimes s
                JOIN {$this->table} m ON s.movie_id = m.movie_id
                JOIN rooms r ON s.room_id = r.room_id
                WHERE s.showtime_id = :showtime_id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':showtime_id' => $showtimeId]);
        return $stmt->fetch() ?: null;
    }

    public function getSeatsForShowtime($showtimeId): array {
        $seatCols = $this->fetchColumns('seats');
        $rowCol = isset($seatCols['row_name']) ? 'row_name' : 'seat_row';
        $typeCol = isset($seatCols['type']) ? 'type' : 'seat_type';
        $sql = "SELECT s.seat_id,
                       s.{$rowCol} AS seat_row,
                       s.seat_number,
                       s.{$typeCol} AS raw_seat_type,
                       CASE
                            WHEN s.{$typeCol} IN (2, 'vip') THEN 'vip'
                            WHEN s.{$typeCol} IN (3, 'couple') THEN 'couple'
                            ELSE 'standard'
                       END AS seat_type,
                       CASE WHEN t.ticket_id IS NULL THEN 0 ELSE 1 END AS reserved
                FROM seats s
                JOIN showtimes st ON st.room_id = s.room_id
                LEFT JOIN tickets t
                    ON t.seat_id = s.seat_id
                   AND t.showtime_id = :showtime_id
                   AND t.ticket_status IN ('reserved','paid')
                WHERE st.showtime_id = :showtime_id
                  AND (s.status = 1 OR s.status = 'active' OR s.status IS NULL)
                ORDER BY s.{$rowCol}, s.seat_number";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':showtime_id' => $showtimeId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): bool {
        $sql = "INSERT INTO {$this->table}
                (title, description, director, `cast`, genre, duration, release_date, poster, banner, poster_url, trailer_url, status)
                VALUES (:title, :description, :director, :cast, :genre, :duration, :release_date, :poster, :banner, :poster_url, :trailer_url, :status)";
        $stmt = $this->conn->prepare($sql);
        $poster = $data['poster'] ?: 'assets/images/default-poster.svg';
        return $stmt->execute([
            ':title' => trim($data['title']),
            ':description' => $data['description'] ?: null,
            ':director' => $data['director'] ?: null,
            ':cast' => $data['cast'] ?: null,
            ':genre' => $data['genre'] ?: null,
            ':duration' => (int) $data['duration'],
            ':release_date' => $data['release_date'],
            ':poster' => $poster,
            ':banner' => $data['banner'] ?: 'assets/images/default-banner.svg',
            ':poster_url' => $poster,
            ':trailer_url' => $data['trailer_url'] ?: null,
            ':status' => $data['status'],
        ]);
    }

    public function update(int $id, array $data): bool {
        $fields = [];
        $params = [':movie_id' => $id];
        foreach ($data as $key => $value) {
            $column = $key === 'cast' ? '`cast`' : $key;
            $fields[] = "{$column} = :{$key}";
            $params[":{$key}"] = $value;
            if ($key === 'poster') {
                $fields[] = 'poster_url = :poster_url';
                $params[':poster_url'] = $value;
            }
        }
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET " . implode(', ', $fields) . ' WHERE movie_id = :movie_id');
        return $stmt->execute($params);
    }

    public function delete(int $id): bool {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE movie_id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function getStats(): array {
        $row = $this->conn->query("SELECT 
                    COUNT(*) AS total,
                    SUM(CASE WHEN status IN (1, '1', 'showing') THEN 1 ELSE 0 END) AS showing_count,
                    SUM(CASE WHEN status IN (2, '2', 'coming_soon') THEN 1 ELSE 0 END) AS coming_count,
                    SUM(CASE WHEN status IN (0, '0', 'stopped') THEN 1 ELSE 0 END) AS stopped_count
                FROM {$this->table}")->fetch() ?: [];
        $soldTickets = (int) ($this->conn->query("SELECT COUNT(*) FROM tickets WHERE ticket_status <> 'cancelled'")->fetchColumn() ?: 0);
        return [
            'total' => (int) ($row['total'] ?? 0),
            'showing_count' => (int) ($row['showing_count'] ?? 0),
            'coming_count' => (int) ($row['coming_count'] ?? 0),
            'stopped_count' => (int) ($row['stopped_count'] ?? 0),
            'sold_tickets' => $soldTickets,
        ];
    }

    public function getGenreOptions(): array {
        $rows = $this->conn->query("SELECT genre FROM {$this->table} WHERE genre IS NOT NULL AND genre <> ''")->fetchAll();
        $genres = [];
        foreach ($rows as $row) {
            foreach (explode(',', (string) $row['genre']) as $genre) {
                $genre = trim($genre);
                if ($genre !== '') {
                    $genres[$genre] = $genre;
                }
            }
        }
        ksort($genres);
        return array_values($genres);
    }
}
?>