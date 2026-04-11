<?php
class Database {
    private string $host = 'localhost';
    private string $db_name = 'movie_booking';
    private string $username = 'root';
    private string $password = '';

    public function getConnection(): ?PDO {
        try {
            $conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );

            $conn->exec("SET NAMES utf8mb4");
            return $conn;
        } catch (PDOException $e) {
            die('Lỗi kết nối CSDL: ' . $e->getMessage());
        }
    }
}
