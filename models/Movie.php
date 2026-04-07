<?php
require_once 'config/database.php';

class Movie {
    private $conn;
    private $table_name = 'Movies';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getShowingMovies() {
        $sql = "SELECT movie_id, title, genre, duration, release_date, status, description, poster_url, trailer_url FROM $this->table_name WHERE status = 'showing' ORDER BY release_date DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMovieById($movie_id) {
        $sql = "SELECT movie_id, title, genre, duration, release_date, status, description, poster_url, trailer_url FROM $this->table_name WHERE movie_id = :movie_id LIMIT 0,1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':movie_id', $movie_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getComingSoonMovies() {
        $sql = "SELECT movie_id, title, genre, duration, release_date, status, description, poster_url, trailer_url FROM $this->table_name WHERE status = 'coming_soon' OR (status = 'showing' AND release_date > CURDATE()) ORDER BY release_date ASC LIMIT 3";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchMovies($searchQuery) {
        $wildcardQuery = '%' . $searchQuery . '%';
        $sql = "SELECT DISTINCT m.movie_id, m.title, m.genre, m.duration, m.release_date, m.status, m.description, m.poster_url, m.trailer_url
                FROM $this->table_name m
                LEFT JOIN Showtimes s ON s.movie_id = m.movie_id
                LEFT JOIN Rooms r ON r.room_id = s.room_id
                WHERE LOWER(m.title) LIKE :query
                   OR LOWER(m.genre) LIKE :query
                   OR LOWER(m.description) LIKE :query
                   OR LOWER(r.room_name) LIKE :query
                ORDER BY m.release_date DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':query', $wildcardQuery, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getShowtimesByMovie($movie_id) {
        $sql = "SELECT s.showtime_id, s.room_id, r.room_name, s.show_date, s.start_time, s.end_time, s.base_price FROM Showtimes s JOIN Rooms r ON s.room_id = r.room_id WHERE s.movie_id = :movie_id ORDER BY s.show_date, s.start_time";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':movie_id', $movie_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllRooms() {
        $sql = "SELECT room_id, room_name, capacity FROM Rooms ORDER BY room_name";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getShowtimeById($showtime_id) {
        $sql = "SELECT s.showtime_id, s.movie_id, s.room_id, r.room_name, s.show_date, s.start_time, s.end_time, s.base_price, m.title, m.poster_url FROM Showtimes s JOIN Movies m ON s.movie_id = m.movie_id JOIN Rooms r ON s.room_id = r.room_id WHERE s.showtime_id = :showtime_id LIMIT 0,1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':showtime_id', $showtime_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getSeatsForShowtime($showtime_id) {
        $sql = "SELECT s.seat_id, s.seat_row, s.seat_number, s.seat_type, CASE WHEN t.ticket_id IS NULL THEN 0 ELSE 1 END AS reserved FROM Seats s JOIN Showtimes st ON st.room_id = s.room_id LEFT JOIN Tickets t ON t.seat_id = s.seat_id AND t.showtime_id = :showtime_id AND t.ticket_status IN ('reserved','paid') WHERE st.showtime_id = :showtime_id ORDER BY s.seat_row, s.seat_number";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':showtime_id', $showtime_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
