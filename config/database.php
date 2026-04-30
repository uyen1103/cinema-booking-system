<?php
// Database.php - Cấu hình kết nối cơ sở dữ liệu
// Sử dụng PDO (PHP Data Object) để kết nối MySQL

class Database {
    // Các thuộc tính cấu hình kết nối
    private string $host = 'localhost';      // Địa chỉ server MySQL (XAMPP mặc định localhost)
    private string $db_name = 'movie_booking'; // Tên database
    private string $username = 'root';       // Username mặc định của XAMPP
    private string $password = '';           // Password mặc định của XAMPP (để trống)

    // getConnection(): Hàm tạo kết nối PDO
    // Trả về: Đối tượng PDO nếu thành công, null nếu lỗi
    public function getConnection(): ?PDO {
        try {
            // Tạo đối tượng PDO với chuỗi kết nối
            $conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4", // DSN
                $this->username,   // Username
                $this->password,   // Password
                [
                    // Các options cho PDO
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Ném exception khi có lỗi
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Mặc định trả về mảng associative
                ]
            );

            // Thiết lập charset utf8mb4 để hỗ trợ tiếng Việt
            $conn->exec("SET NAMES utf8mb4");
            return $conn;
        } catch (PDOException $e) {
            // Nếu kết nối thất bại -> hiển thị lỗi và dừng
            die('Lỗi kết nối CSDL: ' . $e->getMessage());
        }
    }
}
