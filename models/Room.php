<?php
require_once __DIR__ . '/../config/database.php';

class Room {
    private PDO $conn;
    private string $table = 'rooms';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAll(array $filters = []): array {
        $sql = "SELECT r.*,
                    (SELECT COUNT(*) FROM seats s WHERE s.room_id = r.room_id) AS total_seats,
                    (SELECT COUNT(*) FROM seats s WHERE s.room_id = r.room_id AND s.status = 1) AS active_seats
                FROM {$this->table} r
                WHERE 1=1";
        $params = [];

        if (!empty($filters['keyword'])) {
            $sql .= " AND r.name LIKE :keyword";
            $params[':keyword'] = '%' . trim($filters['keyword']) . '%';
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $sql .= " AND r.status = :status";
            $params[':status'] = (int) $filters['status'];
        }

        $sql .= " ORDER BY r.room_id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE room_id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function getSeatsByRoomId(int $roomId): array {
        $stmt = $this->conn->prepare("SELECT * FROM seats WHERE room_id = :room_id ORDER BY row_name ASC, seat_number ASC");
        $stmt->execute([':room_id' => $roomId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int|false {
        $sql = "INSERT INTO {$this->table}
                (name, capacity, opening_time, closing_time, status, maintenance_reason)
                VALUES (:name, :capacity, :opening_time, :closing_time, :status, :maintenance_reason)";
        $stmt = $this->conn->prepare($sql);

        $ok = $stmt->execute([
            ':name' => trim($data['name']),
            ':capacity' => (int) $data['capacity'],
            ':opening_time' => $data['opening_time'],
            ':closing_time' => $data['closing_time'],
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
                    capacity = :capacity,
                    opening_time = :opening_time,
                    closing_time = :closing_time,
                    status = :status,
                    maintenance_reason = :maintenance_reason
                WHERE room_id = :room_id";
        $stmt = $this->conn->prepare($sql);

        $ok = $stmt->execute([
            ':name' => trim($data['name']),
            ':capacity' => (int) $data['capacity'],
            ':opening_time' => $data['opening_time'],
            ':closing_time' => $data['closing_time'],
            ':status' => (int) $data['status'],
            ':maintenance_reason' => $data['maintenance_reason'] ?: null,
            ':room_id' => $id,
        ]);

        return $ok;
    }

    public function delete(int $id): bool {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE room_id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function generateStandardSeats(int $roomId, ?int $capacity = null): bool {
        $room = $this->getById($roomId);
        if (!$room) {
            return false;
        }

        $capacity = $capacity ?? (int) $room['capacity'];

        $checkStmt = $this->conn->prepare("SELECT COUNT(*) FROM seats WHERE room_id = :room_id");
        $checkStmt->execute([':room_id' => $roomId]);
        if ((int) $checkStmt->fetchColumn() > 0) {
            return true;
        }

        $rows = (int) ceil($capacity / 10);
        $seatRows = [];
        $remaining = $capacity;

        for ($rowIndex = 0; $rowIndex < $rows; $rowIndex++) {
            $rowName = chr(65 + $rowIndex);
            $seatsThisRow = min(10, $remaining);

            for ($seatNumber = 1; $seatNumber <= $seatsThisRow; $seatNumber++) {
                $type = 1;
                if ($rowIndex >= $rows - 1) {
                    $type = 2;
                } elseif ($rowIndex === $rows - 2 && $capacity >= 30) {
                    $type = 2;
                }

                $seatRows[] = [
                    'room_id' => $roomId,
                    'row_name' => $rowName,
                    'seat_number' => $seatNumber,
                    'type' => $type,
                    'status' => 1,
                ];
            }

            $remaining -= $seatsThisRow;
        }

        $sql = "INSERT INTO seats (room_id, row_name, seat_number, type, status)
                VALUES (:room_id, :row_name, :seat_number, :type, :status)";
        $stmt = $this->conn->prepare($sql);

        foreach ($seatRows as $seat) {
            $stmt->execute([
                ':room_id' => $seat['room_id'],
                ':row_name' => $seat['row_name'],
                ':seat_number' => $seat['seat_number'],
                ':type' => $seat['type'],
                ':status' => $seat['status'],
            ]);
        }

        return true;
    }

    public function toggleSeatStatus(int $seatId): bool {
        $stmt = $this->conn->prepare("UPDATE seats SET status = IF(status = 1, 0, 1) WHERE seat_id = :seat_id");
        $ok = $stmt->execute([':seat_id' => $seatId]);

        return $ok;
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
        ];
    }
}
