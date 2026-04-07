<?php
require_once 'config/database.php';

// Model User quản lý thông tin và thao tác với bảng Users
class User {
    private $conn;
    private $table_name = "Users";

    // Các thuộc tính của user
    public $user_id;
    public $full_name;
    public $email;
    public $password;
    public $phone;
    public $birthday;
    public $address;
    public $role;
    public $status;

    // kết nối CSDL
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Đăng ký tài khoản mới
    public function register() {
        $sql = "INSERT INTO $this->table_name 
        SET full_name=:full_name, email=:email, password=:password, phone=:phone, birthday=:birthday, address=:address, role=:role, status=:status";
        $stmt = $this->conn->prepare($sql);

        // xử lý dữ liệu
        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        $this->phone = htmlspecialchars(strip_tags($this->phone ?? ''));
        $this->birthday = htmlspecialchars(strip_tags($this->birthday ?? ''));
        $this->address = htmlspecialchars(strip_tags($this->address ?? ''));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->status = htmlspecialchars(strip_tags($this->status));

        // Gán giá trị vào câu lệnh SQL
        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":birthday", $this->birthday);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":status", $this->status);

        return $stmt->execute();
    }

    // Đăng nhập
    public function login() {
        $sql = "SELECT user_id, full_name, email, password, phone, birthday, address, role, status 
        FROM $this->table_name 
        WHERE email = :email LIMIT 0,1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row && password_verify($this->password, $row['password'])) {
            // Gán thông tin user vào object
            $this->user_id = $row['user_id'];
            $this->full_name = $row['full_name'];
            $this->email = $row['email'];
            $this->phone = $row['phone'];
            $this->birthday = $row['birthday'];
            $this->address = $row['address'];
            $this->role = $row['role'];
            $this->status = $row['status'];
            return true;
        }
        return false;
    }

    // Cập nhật thông tin 
    public function update() {
        $sql = "UPDATE $this->table_name 
        SET full_name=:full_name, email=:email, phone=:phone, birthday=:birthday, address=:address 
        WHERE user_id=:user_id";
        $stmt = $this->conn->prepare($sql);

        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone ?? ''));
        $this->birthday = htmlspecialchars(strip_tags($this->birthday ?? ''));
        $this->address = htmlspecialchars(strip_tags($this->address ?? ''));

        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":birthday", $this->birthday);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":user_id", $this->user_id);

        return $stmt->execute();
    }

    // Lấy thông tin user theo ID
    public function getUserById($user_id) {
        $sql = "SELECT user_id, full_name, email, phone, birthday, address, role, status 
        FROM $this->table_name 
        WHERE user_id = :user_id LIMIT 0,1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Kiểm tra email đã tồn tại chưa
    public function emailExists($email) {
        $sql = "SELECT user_id 
        FROM $this->table_name 
        WHERE email = :email LIMIT 0,1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Đổi mật khẩu
    public function changePassword($user_id, $old_password, $new_password) {
        $user = $this->getUserById($user_id);
        if (!$user) return false;

        // Kiểm tra mật khẩu cũ
        $sql = "SELECT password FROM $this->table_name WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!password_verify($old_password, $row['password'])) {
            return false;
        }

        // Kiểm tra mật khẩu mới mạnh
        $passwordErrors = $this->validatePassword($new_password);
        if (!empty($passwordErrors)) {
            return $passwordErrors; // Trả về mảng lỗi thay vì false
        }

        // Cập nhật mật khẩu mới
        $sql = "UPDATE $this->table_name SET password = :password WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":user_id", $user_id);
        return $stmt->execute();
    }

    // Kiểm tra mật khẩu mạnh
    public function validatePassword($password) {
        $errors = [];

        if (strlen($password) < 8 || strlen($password) > 50) {
            $errors[] = 'Mật khẩu ít nhất 8 ký tự';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Mật khẩu phải chứa ít nhất 1 chữ cái viết hoa';
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Mật khẩu phải chứa ít nhất 1 chữ cái viết thường';
        }
        if (!preg_match('/\d/', $password)) {
            $errors[] = 'Mật khẩu phải chứa ít nhất 1 số';
        }
        if (!preg_match('/[^a-zA-Z\d]/', $password)) {
            $errors[] = 'Mật khẩu phải chứa ít nhất 1 ký tự đặc biệt';
        }

        return $errors;
    }

    // === OAUTH METHODS ===

    // Tìm user theo OAuth provider và ID
    public function findByOAuth($provider, $oauth_id) {
        $sql = "SELECT user_id, full_name, email, phone, role, status 
        FROM $this->table_name 
        WHERE oauth_provider = :oauth_provider AND oauth_id = :oauth_id LIMIT 0,1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":oauth_provider", $provider);
        $stmt->bindParam(":oauth_id", $oauth_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Tìm user theo email
    public function findByEmail($email) {
        $sql = "SELECT user_id, full_name, email, phone, role, status 
        FROM $this->table_name 
        WHERE email = :email LIMIT 0,1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Đăng ký user mới qua OAuth
    public function registerOAuth($provider, $oauth_id) {
        $sql = "INSERT INTO $this->table_name 
        SET full_name=:full_name, email=:email, password=:password, role=:role, status=:status, oauth_provider=:oauth_provider, oauth_id=:oauth_id, phone=:phone, birthday=:birthday, address=:address";
        $stmt = $this->conn->prepare($sql);

        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->phone = htmlspecialchars(strip_tags($this->phone ?? ''));
        $this->birthday = htmlspecialchars(strip_tags($this->birthday ?? ''));
        $this->address = htmlspecialchars(strip_tags($this->address ?? ''));

        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":oauth_provider", $provider);
        $stmt->bindParam(":oauth_id", $oauth_id);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":birthday", $this->birthday);
        $stmt->bindParam(":address", $this->address);

        return $stmt->execute();
    }
}
?>
