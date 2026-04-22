<?php
require_once __DIR__ . '/../config/database.php';

class Room {
    private PDO $conn;
    private string $table = 'rooms';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    private function displayNameExpr(): string {
        return "COALESCE(NULLIF(name, ''), room_name)";
    }

    private function normalizeRoom(array $room): array {
        $room['room_id'] = (int) ($room['room_id'] ?? 0);
        $room['name'] = trim((string)($room['name'] ?? '')) !== '' ? $room['name'] : ($room['room_name'] ?? '');
        $room['room_name'] = trim((string)($room['room_name'] ?? '')) !== '' ? $room['room_name'] : ($room['name'] ?? '');
        $room['capacity'] = (int) ($room['capacity'] ?? 0);
        $room['opening_time'] = (string) ($room['opening_time'] ?? '08:00:00');
        $room['closing_time'] = (string) ($room['closing_time'] ?? '23:30:00');
        $room['status'] = (int) ($room['status'] ?? 1);
        $room['maintenance_reason'] = (string) ($room['maintenance_reason'] ?? '');
        $room['total_seats'] = (int) ($room['total_seats'] ?? 0);
        $room['active_seats'] = (int) ($room['active_seats'] ?? 0);
        $room['total_showtimes'] = (int) ($room['total_showtimes'] ?? 0);
        return $room;
    }

    public function roomNameExists(string $name, ?int $ignoreId = null): bool {
        $sql = "SELECT room_id FROM {$this->table} WHERE {$this->displayNameExpr()} = :name";
        $params = [':name' => trim($name)];
        if ($ignoreId !== null) {
            $sql .= ' AND room_id <> :ignore_id';
            $params[':ignore_id'] = $ignoreId;
        }
        $sql .= ' LIMIT 1';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetch();
    }

    public function getAll(array $filters = []): array {
        $sql = "SELECT r.*,
                    {$this->displayNameExpr()} AS display_name,
                    (SELECT COUNT(*) FROM seats s WHERE s.room_id = r.room_id) AS total_seats,
                    (SELECT COUNT(*) FROM seats s WHERE s.room_id = r.room_id AND s.status = 1) AS active_seats,
                    (SELECT COUNT(*) FROM showtimes st WHERE st.room_id = r.room_id) AS total_showtimes
                FROM {$this->table} r
                WHERE 1=1";
        $params = [];

        if (!empty($filters['keyword'])) {
            $sql .= " AND {$this->displayNameExpr()} LIKE :keyword";
            $params[':keyword'] = '%' . trim($filters['keyword']) . '%';
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $sql .= " AND r.status = :status";
            $params[':status'] = (int) $filters['status'];
        }

        $sql .= " ORDER BY r.room_id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return array_map(fn(array $row) => $this->normalizeRoom($row), $stmt->fetchAll());
    }

    public function getById(int $id): ?array {
        $stmt = $this->conn->prepare("SELECT *, {$this->displayNameExpr()} AS display_name FROM {$this->table} WHERE room_id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $room = $stmt->fetch();
        return $room ? $this->normalizeRoom($room) : null;
    }

    public function getSeatsByRoomId(int $roomId): array {
        $columns = [];
        try {
            foreach ($this->conn->query('SHOW COLUMNS FROM seats')->fetchAll() as $col) {
                $columns[strtolower($col['Field'])] = true;
            }
        } catch (Throwable $e) {
        }
        $rowCol = isset($columns['row_name']) ? 'row_name' : 'seat_row';
        $typeCol = isset($columns['type']) ? 'type' : 'seat_type';
        $stmt = $this->conn->prepare("SELECT *, {$rowCol} AS display_row, {$typeCol} AS display_type FROM seats WHERE room_id = :room_id ORDER BY {$rowCol} ASC, seat_number ASC");
        $stmt->execute([':room_id' => $roomId]);
        $rows = $stmt->fetchAll();
        return array_map(function (array $seat) {
            $seat['row_name'] = (string) ($seat['row_name'] ?? $seat['display_row'] ?? $seat['seat_row'] ?? '');
            $seat['seat_row'] = (string) ($seat['seat_row'] ?? $seat['row_name'] ?? '');
            $rawType = $seat['type'] ?? $seat['display_type'] ?? $seat['seat_type'] ?? 1;
            if (is_string($rawType)) {
                $normalizedType = match (strtolower($rawType)) {
                    'vip' => 2,
                    'couple' => 3,
                    default => 1,
                };
            } else {
                $normalizedType = (int) $rawType;
            }
            $seat['type'] = $normalizedType;
            $seat['seat_type'] = $seat['seat_type'] ?? match ($normalizedType) {
                2 => 'vip',
                3 => 'couple',
                default => 'standard',
            };
            $seat['status'] = (int) ($seat['status'] ?? 1);
            $seat['seat_number'] = (int) ($seat['seat_number'] ?? 0);
            $seat['seat_id'] = (int) ($seat['seat_id'] ?? 0);
            return $seat;
        }, $rows);
    }

    public function create(array $data): int|false {
        $sql = "INSERT INTO {$this->table}
                (name, room_name, capacity, opening_time, closing_time, status, maintenance_reason)
                VALUES (:name, :room_name, :capacity, :opening_time, :closing_time, :status, :maintenance_reason)";
        $stmt = $this->conn->prepare($sql);

        $name = trim((string) $data['name']);
        $ok = $stmt->execute([
            ':name' => $name,
            ':room_name' => $name,
            ':capacity' => (int) $data['capacity'],
            ':opening_time' => $data['opening_time'] ?: null,
            ':closing_time' => $data['closing_time'] ?: null,
            ':status' => (int) $data['status'],
            ':maintenance_reason' => $data['maintenance_reason'] ?: null,
        ]);

        if (!$ok) {
            return false;
        }

        $roomId = (int) $this->conn->lastInsertId();
        $this->generateStandardSeats($roomId, (int) $data['capacity']);
        return $roomId;
    }

    public function update(int $id, array $data): bool {
        $sql = "UPDATE {$this->table}
                SET name = :name,
                    room_name = :room_name,
                    capacity = :capacity,
                    opening_time = :opening_time,
                    closing_time = :closing_time,
                    status = :status,
                    maintenance_reason = :maintenance_reason
                WHERE room_id = :room_id";
        $stmt = $this->conn->prepare($sql);

        $name = trim((string) $data['name']);
        return $stmt->execute([
            ':name' => $name,
            ':room_name' => $name,
            ':capacity' => (int) $data['capacity'],
            ':opening_time' => $data['opening_time'] ?: null,
            ':closing_time' => $data['closing_time'] ?: null,
            ':status' => (int) $data['status'],
            ':maintenance_reason' => $data['maintenance_reason'] ?: null,
            ':room_id' => $id,
        ]);
    }

    public function countShowtimes(int $roomId): int {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM showtimes WHERE room_id = :room_id");
        $stmt->execute([':room_id' => $roomId]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    public function hasActiveTickets(int $roomId): bool {
        $stmt = $this->conn->prepare("SELECT COUNT(*)
            FROM tickets t
            INNER JOIN showtimes st ON st.showtime_id = t.showtime_id
            WHERE st.room_id = :room_id AND t.ticket_status IN ('reserved', 'paid')");
        $stmt->execute([':room_id' => $roomId]);
        return (int) ($stmt->fetchColumn() ?: 0) > 0;
    }

    public function canDelete(int $roomId): bool {
        return $this->countShowtimes($roomId) === 0 && !$this->hasActiveTickets($roomId);
    }

    public function delete(int $id): bool {
        if (!$this->canDelete($id)) {
            return false;
        }
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE room_id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function getSeatCount(int $roomId): int {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM seats WHERE room_id = :room_id");
        $stmt->execute([':room_id' => $roomId]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    public function generateStandardSeats(int $roomId, ?int $capacity = null): bool {
        $room = $this->getById($roomId);
        if (!$room) {
            return false;
        }

        $capacity = $capacity ?? (int) $room['capacity'];
        if ($capacity <= 0) {
            return false;
        }

        if ($this->getSeatCount($roomId) > 0) {
            return true;
        }

        $rows = (int) ceil($capacity / 10);
        $seatRows = [];
        $remaining = $capacity;

        for ($rowIndex = 0; $rowIndex < $rows; $rowIndex++) {
            $rowName = chr(65 + $rowIndex);
            $seatsThisRow = min(10, $remaining);

            for ($seatNumber = 1; $seatNumber <= $seatsThisRow; $seatNumber++) {
                $type = 'standard';
                if ($rowIndex >= $rows - 1) {
                    $type = 'vip';
                } elseif ($rowIndex === $rows - 2 && $capacity >= 30) {
                    $type = 'vip';
                }

                $seatRows[] = [
                    'room_id' => $roomId,
                    'row_name' => $rowName,
                    'seat_row' => $rowName,
                    'seat_number' => $seatNumber,
                    'type' => $type,
                    'seat_type' => $type,
                    'status' => 1,
                ];
            }

            $remaining -= $seatsThisRow;
        }

        $sql = "INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
                VALUES (:room_id, :row_name, :seat_row, :seat_number, :type, :seat_type, :status)";
        $stmt = $this->conn->prepare($sql);

        foreach ($seatRows as $seat) {
            $stmt->execute([
                ':room_id' => $seat['room_id'],
                ':row_name' => $seat['row_name'],
                ':seat_row' => $seat['seat_row'],
                ':seat_number' => $seat['seat_number'],
                ':type' => $seat['type'],
                ':seat_type' => $seat['seat_type'],
                ':status' => $seat['status'],
            ]);
        }

        return true;
    }

    public function seatHasBookedTickets(int $seatId): bool {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM tickets WHERE seat_id = :seat_id AND ticket_status IN ('reserved', 'paid')");
        $stmt->execute([':seat_id' => $seatId]);
        return (int) ($stmt->fetchColumn() ?: 0) > 0;
    }

    public function toggleSeatStatus(int $seatId): bool {
        if ($this->seatHasBookedTickets($seatId)) {
            return false;
        }
        $stmt = $this->conn->prepare("UPDATE seats SET status = IF(status = 1, 0, 1) WHERE seat_id = :seat_id");
        return $stmt->execute([':seat_id' => $seatId]);
    }

    public function getStats(): array {
        $sql = "SELECT
                    COUNT(*) AS total_rooms,
                    SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) AS active_rooms,
                    SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) AS maintenance_rooms,
                    (SELECT COUNT(*) FROM seats WHERE status = 1) AS total_capacity
                FROM {$this->table}";
        $row = $this->conn->query($sql)->fetch() ?: [];

        return [
            'total_rooms' => (int) ($row['total_rooms'] ?? 0),
            'active_rooms' => (int) ($row['active_rooms'] ?? 0),
            'maintenance_rooms' => (int) ($row['maintenance_rooms'] ?? 0),
            'total_capacity' => (int) ($row['total_capacity'] ?? 0),
            'total_seats' => (int) ($row['total_capacity'] ?? 0),
        ];
    }
}
