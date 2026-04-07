<?php

// quản lý kết nối database
class Database {
    private $host = 'localhost';
    private $db_name = 'movie_booking';
    private $username = 'root';
    private $password = '';

    public function getConnection() {
        try {
            // tạo kết nối PDO
            $conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name};charset=utf8",
                $this->username,
                $this->password
            );

            // bật báo lỗi
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $conn;

        } catch (PDOException $e) {
            echo "Lỗi kết nối: " . $e->getMessage();
            return null;
        }
    }
}