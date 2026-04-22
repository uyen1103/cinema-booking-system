CREATE DATABASE IF NOT EXISTS movie_booking CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE movie_booking;

SET NAMES utf8mb4;
SET time_zone = '+07:00';
SET FOREIGN_KEY_CHECKS = 0;

/* ==========================================================
   STEP 1 - TÁCH TÀI KHOẢN KHÁCH HÀNG VÀ NHÂN SỰ/QUẢN TRỊ
   MỤC TIÊU:
   - customers là nguồn dữ liệu chính cho tài khoản khách hàng
   - employees là nguồn dữ liệu chính cho nhân sự/admin
   - users chỉ giữ lại ở mức tương thích cho giai đoạn chuyển tiếp
   - không DROP bảng/cột cũ để tránh làm mất dữ liệu hiện có
   ========================================================== */

/* ---------- 1. BẢNG NGUỒN DỮ LIỆU MỚI ---------- */
CREATE TABLE IF NOT EXISTS customers (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    birthday DATE NULL,
    address VARCHAR(255) NULL,
    avatar VARCHAR(255) NULL,
    bank_account VARCHAR(100) NULL,
    e_wallet_account VARCHAR(100) NULL,
    oauth_provider VARCHAR(50) NULL,
    oauth_id VARCHAR(100) NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_customers_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS employees (
    employee_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    birthday DATE NULL,
    address VARCHAR(255) NULL,
    avatar VARCHAR(255) NULL,
    position VARCHAR(100) NULL,
    branch_name VARCHAR(150) NULL,
    hire_date DATE NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'staff',
    status VARCHAR(20) NOT NULL DEFAULT 'working',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_employees_role (role),
    INDEX idx_employees_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* ---------- 2. BẢNG LEGACY GIỮ LẠI CHO TƯƠNG THÍCH CODE CŨ ---------- */
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    birthday DATE NULL,
    address VARCHAR(255) NULL,
    bank_account VARCHAR(100) NULL,
    e_wallet_account VARCHAR(100) NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'customer',
    position VARCHAR(100) NULL,
    branch_name VARCHAR(150) NULL,
    hire_date DATE NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    avatar VARCHAR(255) NULL,
    oauth_provider VARCHAR(50) NULL,
    oauth_id VARCHAR(100) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_role (role),
    INDEX idx_users_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* ---------- 3. CÁC BẢNG NGHIỆP VỤ ---------- */
CREATE TABLE IF NOT EXISTS movies (
    movie_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    director VARCHAR(150) NULL,
    `cast` TEXT NULL,
    genre VARCHAR(150) NULL,
    duration INT NOT NULL,
    release_date DATE NULL,
    poster VARCHAR(255) NULL,
    poster_url VARCHAR(255) NULL,
    banner VARCHAR(255) NULL,
    trailer_url VARCHAR(255) NULL,
    status VARCHAR(20) NOT NULL DEFAULT '1',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS rooms (
    room_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NULL,
    room_name VARCHAR(100) NULL,
    capacity INT NOT NULL DEFAULT 0,
    opening_time TIME NULL,
    closing_time TIME NULL,
    status TINYINT NOT NULL DEFAULT 1,
    maintenance_reason VARCHAR(255) NULL,
    UNIQUE KEY uniq_room_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS seats (
    seat_id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    row_name VARCHAR(5) NULL,
    seat_row VARCHAR(5) NULL,
    seat_number INT NOT NULL,
    type VARCHAR(20) NOT NULL DEFAULT 'standard',
    seat_type VARCHAR(20) NULL,
    status TINYINT NOT NULL DEFAULT 1,
    UNIQUE KEY uniq_room_seat (room_id, row_name, seat_number),
    INDEX idx_seat_room (room_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS seat_prices (
    seat_price_id INT AUTO_INCREMENT PRIMARY KEY,
    seat_type VARCHAR(20) NOT NULL UNIQUE,
    price_multiplier DECIMAL(5,2) NOT NULL,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS showtimes (
    showtime_id INT AUTO_INCREMENT PRIMARY KEY,
    movie_id INT NOT NULL,
    room_id INT NOT NULL,
    show_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    base_price DECIMAL(10,2) NOT NULL DEFAULT 0,
    status TINYINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_showtime_movie (movie_id),
    INDEX idx_showtime_room (room_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS promotions (
    promotion_id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(30) NULL,
    promo_code VARCHAR(30) NULL,
    title VARCHAR(160) NULL,
    discount_type ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
    discount_value DECIMAL(10,2) NOT NULL DEFAULT 0,
    min_order_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    min_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    min_tickets INT NOT NULL DEFAULT 1,
    max_discount DECIMAL(12,2) NULL,
    usage_limit INT NULL,
    used_count INT NOT NULL DEFAULT 0,
    budget DECIMAL(12,2) NOT NULL DEFAULT 0,
    description TEXT NULL,
    applicable_seat_types JSON NULL,
    image_path VARCHAR(255) NULL,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    status TINYINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_promotion_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NULL,
    user_id INT NULL,
    promotion_id INT NULL,
    order_code VARCHAR(30) NOT NULL,
    order_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(12,2) NOT NULL,
    discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    final_amount DECIMAL(12,2) NOT NULL,
    payment_method VARCHAR(30) NOT NULL DEFAULT 'cash',
    payment_status VARCHAR(20) NOT NULL DEFAULT 'pending',
    order_status VARCHAR(20) NOT NULL DEFAULT 'pending',
    notes VARCHAR(255) NULL,
    created_by_employee_id INT NULL,
    updated_by_employee_id INT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_order_code (order_code),
    INDEX idx_orders_customer (customer_id),
    INDEX idx_orders_user (user_id),
    INDEX idx_orders_status (order_status, payment_status),
    INDEX idx_orders_promotion (promotion_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tickets (
    ticket_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    showtime_id INT NOT NULL,
    seat_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    ticket_status VARCHAR(20) NOT NULL DEFAULT 'reserved',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_showtime_seat (showtime_id, seat_id),
    INDEX idx_ticket_order (order_id),
    INDEX idx_ticket_showtime (showtime_id),
    INDEX idx_ticket_seat (seat_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    payment_method VARCHAR(30) NOT NULL,
    amount_paid DECIMAL(12,2) NOT NULL,
    payment_status VARCHAR(20) NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_payment_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS cancellation_requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    customer_id INT NULL,
    user_id INT NULL,
    reason TEXT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    request_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    processed_by_employee_id INT NULL,
    processed_note VARCHAR(255) NULL,
    processed_at DATETIME NULL,
    INDEX idx_cancel_order (order_id),
    INDEX idx_cancel_customer (customer_id),
    INDEX idx_cancel_user (user_id),
    INDEX idx_cancel_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    report_type VARCHAR(20) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    employee_id INT NULL,
    INDEX idx_reports_employee (employee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* ---------- 4. BỔ SUNG CỘT THIẾU CHO CẤU TRÚC CŨ/NÂNG CẤP DẦN ---------- */
ALTER TABLE customers ADD COLUMN IF NOT EXISTS avatar VARCHAR(255) NULL AFTER address;
ALTER TABLE customers ADD COLUMN IF NOT EXISTS bank_account VARCHAR(100) NULL AFTER avatar;
ALTER TABLE customers ADD COLUMN IF NOT EXISTS e_wallet_account VARCHAR(100) NULL AFTER bank_account;
ALTER TABLE customers ADD COLUMN IF NOT EXISTS oauth_provider VARCHAR(50) NULL AFTER e_wallet_account;
ALTER TABLE customers ADD COLUMN IF NOT EXISTS oauth_id VARCHAR(100) NULL AFTER oauth_provider;
ALTER TABLE customers ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE customers ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE employees ADD COLUMN IF NOT EXISTS avatar VARCHAR(255) NULL AFTER address;
ALTER TABLE employees ADD COLUMN IF NOT EXISTS position VARCHAR(100) NULL AFTER avatar;
ALTER TABLE employees ADD COLUMN IF NOT EXISTS branch_name VARCHAR(150) NULL AFTER position;
ALTER TABLE employees ADD COLUMN IF NOT EXISTS hire_date DATE NULL AFTER branch_name;
ALTER TABLE employees ADD COLUMN IF NOT EXISTS role VARCHAR(20) NOT NULL DEFAULT 'staff' AFTER hire_date;
ALTER TABLE employees ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE employees ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE users ADD COLUMN IF NOT EXISTS bank_account VARCHAR(100) NULL AFTER address;
ALTER TABLE users ADD COLUMN IF NOT EXISTS e_wallet_account VARCHAR(100) NULL AFTER bank_account;
ALTER TABLE users ADD COLUMN IF NOT EXISTS position VARCHAR(100) NULL AFTER role;
ALTER TABLE users ADD COLUMN IF NOT EXISTS branch_name VARCHAR(150) NULL AFTER position;
ALTER TABLE users ADD COLUMN IF NOT EXISTS hire_date DATE NULL AFTER branch_name;
ALTER TABLE users ADD COLUMN IF NOT EXISTS avatar VARCHAR(255) NULL AFTER status;
ALTER TABLE users ADD COLUMN IF NOT EXISTS oauth_provider VARCHAR(50) NULL AFTER avatar;
ALTER TABLE users ADD COLUMN IF NOT EXISTS oauth_id VARCHAR(100) NULL AFTER oauth_provider;
ALTER TABLE users ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE users ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE rooms ADD COLUMN IF NOT EXISTS room_name VARCHAR(100) NULL AFTER name;
ALTER TABLE seats ADD COLUMN IF NOT EXISTS seat_row VARCHAR(5) NULL AFTER row_name;
ALTER TABLE seats ADD COLUMN IF NOT EXISTS seat_type VARCHAR(20) NULL AFTER type;
ALTER TABLE showtimes ADD COLUMN IF NOT EXISTS base_price DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER price;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS customer_id INT NULL AFTER order_id;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS user_id INT NULL AFTER customer_id;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS created_by_employee_id INT NULL AFTER notes;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS updated_by_employee_id INT NULL AFTER created_by_employee_id;
ALTER TABLE cancellation_requests ADD COLUMN IF NOT EXISTS customer_id INT NULL AFTER order_id;
ALTER TABLE cancellation_requests ADD COLUMN IF NOT EXISTS user_id INT NULL AFTER customer_id;
ALTER TABLE cancellation_requests ADD COLUMN IF NOT EXISTS processed_by_employee_id INT NULL AFTER request_date;
ALTER TABLE cancellation_requests ADD COLUMN IF NOT EXISTS processed_note VARCHAR(255) NULL AFTER processed_by_employee_id;
ALTER TABLE cancellation_requests ADD COLUMN IF NOT EXISTS processed_at DATETIME NULL AFTER processed_note;
ALTER TABLE reports ADD COLUMN IF NOT EXISTS employee_id INT NULL AFTER created_at;

/* ---------- 5. CHUẨN HÓA TÊN CỘT Ở BẢNG NGHIỆP VỤ ---------- */
UPDATE rooms SET name = COALESCE(NULLIF(name, ''), room_name);
UPDATE rooms SET room_name = COALESCE(NULLIF(room_name, ''), name);
UPDATE seats SET row_name = COALESCE(NULLIF(row_name, ''), seat_row);
UPDATE seats SET seat_row = COALESCE(NULLIF(seat_row, ''), row_name);
UPDATE seats SET type = COALESCE(NULLIF(type, ''), seat_type, 'standard');
UPDATE seats SET seat_type = COALESCE(NULLIF(seat_type, ''), type);
UPDATE promotions SET code = COALESCE(NULLIF(code, ''), promo_code);
UPDATE promotions SET promo_code = COALESCE(NULLIF(promo_code, ''), code);

/* ---------- 6. MIGRATE DỮ LIỆU TỪ users -> customers / employees ---------- */
INSERT INTO customers (
    customer_id, full_name, email, password, phone, birthday, address, avatar,
    bank_account, e_wallet_account, oauth_provider, oauth_id, status, created_at
)
SELECT 
    u.user_id, u.full_name, u.email, u.password, u.phone, u.birthday, u.address, u.avatar,
    u.bank_account, u.e_wallet_account, u.oauth_provider, u.oauth_id,
    CASE WHEN u.status IN ('active','inactive','blocked') THEN u.status ELSE 'active' END,
    COALESCE(u.created_at, CURRENT_TIMESTAMP)
FROM users u
WHERE u.role = 'customer'
  AND NOT EXISTS (
      SELECT 1 FROM customers c WHERE c.customer_id = u.user_id OR c.email = u.email
  );

INSERT INTO employees (
    employee_id, full_name, email, password, phone, birthday, address, avatar,
    position, branch_name, hire_date, role, status, created_at
)
SELECT
    u.user_id, u.full_name, u.email, u.password, u.phone, u.birthday, u.address, u.avatar,
    u.position, u.branch_name, u.hire_date,
    CASE WHEN u.role IN ('admin','staff') THEN u.role ELSE 'staff' END,
    CASE WHEN u.status IN ('working','leave','resigned','inactive','active') THEN u.status ELSE 'working' END,
    COALESCE(u.created_at, CURRENT_TIMESTAMP)
FROM users u
WHERE u.role IN ('admin','staff')
  AND NOT EXISTS (
      SELECT 1 FROM employees e WHERE e.employee_id = u.user_id OR e.email = u.email
  );

/* ---------- 7. ĐỒNG BỘ NGƯỢC customers / employees -> users ĐỂ GIỮ TƯƠNG THÍCH ---------- */
INSERT INTO users (
    user_id, full_name, email, password, phone, birthday, address, bank_account, e_wallet_account,
    role, position, branch_name, hire_date, status, avatar, oauth_provider, oauth_id, created_at
)
SELECT
    c.customer_id, c.full_name, c.email, c.password, c.phone, c.birthday, c.address,
    c.bank_account, c.e_wallet_account,
    'customer', NULL, NULL, NULL,
    CASE WHEN c.status IN ('active','inactive','blocked') THEN c.status ELSE 'active' END,
    c.avatar, c.oauth_provider, c.oauth_id, COALESCE(c.created_at, CURRENT_TIMESTAMP)
FROM customers c
WHERE NOT EXISTS (
    SELECT 1 FROM users u WHERE u.user_id = c.customer_id OR u.email = c.email
);

INSERT INTO users (
    user_id, full_name, email, password, phone, birthday, address, bank_account, e_wallet_account,
    role, position, branch_name, hire_date, status, avatar, oauth_provider, oauth_id, created_at
)
SELECT
    e.employee_id, e.full_name, e.email, e.password, e.phone, e.birthday, e.address,
    NULL, NULL,
    CASE WHEN e.role IN ('admin','staff') THEN e.role ELSE 'staff' END,
    e.position, e.branch_name, e.hire_date,
    CASE 
        WHEN e.status IN ('working','leave','resigned','inactive') THEN e.status
        ELSE 'working'
    END,
    e.avatar, NULL, NULL, COALESCE(e.created_at, CURRENT_TIMESTAMP)
FROM employees e
WHERE NOT EXISTS (
    SELECT 1 FROM users u WHERE u.user_id = e.employee_id OR u.email = e.email
);

/* ---------- 8. CHUẨN HÓA KHÓA NGOẠI LOGIC MỚI ---------- */
UPDATE orders o
LEFT JOIN customers c ON c.customer_id = o.customer_id
SET o.customer_id = o.user_id
WHERE o.customer_id IS NULL AND o.user_id IS NOT NULL;

UPDATE orders o
LEFT JOIN customers c ON c.customer_id = o.customer_id
LEFT JOIN users u ON u.user_id = o.user_id AND u.role = 'customer'
SET o.user_id = o.customer_id
WHERE o.customer_id IS NOT NULL AND o.user_id IS NULL;

UPDATE cancellation_requests cr
SET cr.customer_id = cr.user_id
WHERE cr.customer_id IS NULL AND cr.user_id IS NOT NULL;

UPDATE cancellation_requests cr
SET cr.user_id = cr.customer_id
WHERE cr.customer_id IS NOT NULL AND cr.user_id IS NULL;

/* ---------- 9. DỮ LIỆU NỀN TỐI THIỂU ---------- */
INSERT INTO employees (full_name, email, password, phone, position, branch_name, hire_date, role, status, avatar)
SELECT 'Admin User', 'admin@cinemacentral.vn',
       '$2y$12$MswZ/nWFVAE0uU6fqAS4Fuu9ovYIA.fIb7VDY9nzF3kr3HBHHJxkK',
       '0900000000', 'Quản trị hệ thống', 'Cinema Central Lê Lợi', CURDATE(), 'admin', 'working', 'assets/images/default-avatar.svg'
WHERE NOT EXISTS (SELECT 1 FROM employees WHERE email = 'admin@cinemacentral.vn');

INSERT INTO customers (full_name, email, password, phone, birthday, address, avatar, bank_account, e_wallet_account, status)
SELECT 'Test Customer', 'test@example.com',
       '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
       '0123456789', '1990-01-01', 'Ho Chi Minh City', 'assets/images/default-avatar.svg', NULL, NULL, 'active'
WHERE NOT EXISTS (SELECT 1 FROM customers WHERE email = 'test@example.com');

INSERT INTO users (full_name, email, password, phone, birthday, address, role, position, branch_name, hire_date, status, avatar)
SELECT 'Admin User', 'admin@cinemacentral.vn',
       '$2y$12$MswZ/nWFVAE0uU6fqAS4Fuu9ovYIA.fIb7VDY9nzF3kr3HBHHJxkK',
       '0900000000', NULL, 'Cinema Central Lê Lợi', 'admin', 'Quản trị hệ thống', 'Cinema Central Lê Lợi', CURDATE(), 'working', 'assets/images/default-avatar.svg'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'admin@cinemacentral.vn');

INSERT INTO users (full_name, email, password, phone, birthday, address, role, status, avatar)
SELECT 'Test Customer', 'test@example.com',
       '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
       '0123456789', '1990-01-01', 'Ho Chi Minh City', 'customer', 'active', 'assets/images/default-avatar.svg'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'test@example.com');

INSERT INTO seat_prices (seat_type, price_multiplier, description)
SELECT 'standard', 1.00, 'Ghế thường' WHERE NOT EXISTS (SELECT 1 FROM seat_prices WHERE seat_type = 'standard');
INSERT INTO seat_prices (seat_type, price_multiplier, description)
SELECT 'vip', 1.30, 'Ghế VIP' WHERE NOT EXISTS (SELECT 1 FROM seat_prices WHERE seat_type = 'vip');
INSERT INTO seat_prices (seat_type, price_multiplier, description)
SELECT 'couple', 1.60, 'Ghế đôi' WHERE NOT EXISTS (SELECT 1 FROM seat_prices WHERE seat_type = 'couple');

SET FOREIGN_KEY_CHECKS = 1;

/* ==========================================================
   GHI CHÚ
   - File final_xampp_setup.sql chỉ chứa cấu trúc chính, migrate và dữ liệu nền tối thiểu.
   - Dữ liệu minh họa / dữ liệu mẫu để test giao diện nằm trong file sample_data.sql.
   - Nếu muốn chạy thử đầy đủ giao diện trên XAMPP, hãy import theo thứ tự:
       1) final_xampp_setup.sql
       2) sample_data.sql
   ========================================================== */
