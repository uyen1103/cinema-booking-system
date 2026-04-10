CREATE DATABASE IF NOT EXISTS movie_booking CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE movie_booking;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS tickets;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS promotions;
DROP TABLE IF EXISTS showtimes;
DROP TABLE IF EXISTS seats;
DROP TABLE IF EXISTS rooms;
DROP TABLE IF EXISTS movies;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    birthday DATE DEFAULT NULL,
    address VARCHAR(255) DEFAULT NULL,
    role ENUM('admin','staff','customer') NOT NULL DEFAULT 'customer',
    position VARCHAR(100) DEFAULT NULL,
    branch_name VARCHAR(120) DEFAULT NULL,
    hire_date DATE DEFAULT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    avatar VARCHAR(255) DEFAULT 'assets/images/default-avatar.svg',
    oauth_provider VARCHAR(50) DEFAULT NULL,
    oauth_id VARCHAR(120) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE movies (
    movie_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    director VARCHAR(150) DEFAULT NULL,
    `cast` VARCHAR(255) DEFAULT NULL,
    genre VARCHAR(200) DEFAULT NULL,
    duration INT NOT NULL DEFAULT 90,
    release_date DATE NOT NULL,
    poster VARCHAR(255) DEFAULT 'assets/images/default-poster.svg',
    banner VARCHAR(255) DEFAULT 'assets/images/default-banner.svg',
    trailer_url VARCHAR(255) DEFAULT NULL,
    status TINYINT NOT NULL DEFAULT 1 COMMENT '1 showing, 2 coming soon, 0 stopped',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_movies_status (status),
    INDEX idx_movies_release_date (release_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE rooms (
    room_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    capacity INT NOT NULL DEFAULT 0,
    opening_time TIME NOT NULL DEFAULT '08:00:00',
    closing_time TIME NOT NULL DEFAULT '23:30:00',
    status TINYINT NOT NULL DEFAULT 1,
    maintenance_reason VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_rooms_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE seats (
    seat_id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    row_name VARCHAR(5) NOT NULL,
    seat_number INT NOT NULL,
    type TINYINT NOT NULL DEFAULT 1 COMMENT '1 standard, 2 vip, 3 couple',
    status TINYINT NOT NULL DEFAULT 1 COMMENT '1 active, 0 maintenance',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_room_seat (room_id, row_name, seat_number),
    CONSTRAINT fk_seats_room FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE CASCADE,
    INDEX idx_seats_room_status (room_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE showtimes (
    showtime_id INT AUTO_INCREMENT PRIMARY KEY,
    movie_id INT NOT NULL,
    room_id INT NOT NULL,
    show_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    price INT NOT NULL DEFAULT 80000,
    status TINYINT NOT NULL DEFAULT 1 COMMENT '1 active, 0 cancelled',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_showtimes_movie FOREIGN KEY (movie_id) REFERENCES movies(movie_id) ON DELETE CASCADE,
    CONSTRAINT fk_showtimes_room FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE CASCADE,
    INDEX idx_showtimes_lookup (show_date, room_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE promotions (
    promotion_id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(30) NOT NULL UNIQUE,
    title VARCHAR(160) NOT NULL,
    discount_type ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
    discount_value DECIMAL(10,2) NOT NULL DEFAULT 0,
    min_order_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    max_discount DECIMAL(12,2) DEFAULT NULL,
    usage_limit INT DEFAULT NULL,
    used_count INT NOT NULL DEFAULT 0,
    budget DECIMAL(12,2) NOT NULL DEFAULT 0,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    description TEXT DEFAULT NULL,
    image_path VARCHAR(255) DEFAULT NULL,
    status TINYINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_promotions_status_dates (status, start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    promotion_id INT DEFAULT NULL,
    order_code VARCHAR(30) NOT NULL UNIQUE,
    order_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    final_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    payment_method VARCHAR(30) NOT NULL DEFAULT 'cash',
    payment_status VARCHAR(20) NOT NULL DEFAULT 'pending',
    order_status VARCHAR(20) NOT NULL DEFAULT 'pending',
    notes VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_orders_promotion FOREIGN KEY (promotion_id) REFERENCES promotions(promotion_id) ON DELETE SET NULL,
    INDEX idx_orders_status (order_status, payment_status),
    INDEX idx_orders_date (order_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tickets (
    ticket_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    showtime_id INT NOT NULL,
    seat_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    ticket_status VARCHAR(20) NOT NULL DEFAULT 'paid',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tickets_order FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    CONSTRAINT fk_tickets_showtime FOREIGN KEY (showtime_id) REFERENCES showtimes(showtime_id) ON DELETE CASCADE,
    CONSTRAINT fk_tickets_seat FOREIGN KEY (seat_id) REFERENCES seats(seat_id) ON DELETE CASCADE,
    UNIQUE KEY uq_showtime_seat (showtime_id, seat_id),
    INDEX idx_tickets_order (order_id),
    INDEX idx_tickets_showtime (showtime_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (full_name, email, password, phone, birthday, address, role, position, branch_name, hire_date, status, avatar) VALUES
('Admin User', 'admin@cinemacentral.vn', '$2y$12$3gMZX9o7DXUCDtw5bGZfUONrtl7iz7v0rI6HbRaq1QBmNDRLe2F4a', '0901000000', '1995-10-15', 'TP.HCM', 'admin', 'Quản trị hệ thống', 'Cinema Central', '2021-01-03', 'active', 'assets/images/default-avatar.svg'),
('Nguyễn Văn An', 'an.nv@cinemacentral.vn', '$2y$12$3gMZX9o7DXUCDtw5bGZfUONrtl7iz7v0rI6HbRaq1QBmNDRLe2F4a', '0901234567', '1995-10-15', 'Quận 1, TP.HCM', 'staff', 'Quản lý', 'Cinema Central Lê Lợi', '2022-03-20', 'working', 'assets/images/default-avatar.svg'),
('Lê Thị Bảo Vy', 'vy.lt@cinemacentral.vn', '$2y$12$3gMZX9o7DXUCDtw5bGZfUONrtl7iz7v0rI6HbRaq1QBmNDRLe2F4a', '0912345678', '1998-06-19', 'Quận 7, TP.HCM', 'staff', 'Nhân viên', 'Cinema Central Phú Mỹ Hưng', '2023-02-10', 'working', 'assets/images/default-avatar.svg'),
('Trần Minh Quân', 'quan.tm@cinemacentral.vn', '$2y$12$3gMZX9o7DXUCDtw5bGZfUONrtl7iz7v0rI6HbRaq1QBmNDRLe2F4a', '0933445566', '1994-11-30', 'TP Thủ Đức, TP.HCM', 'staff', 'Kỹ thuật viên', 'Cinema Central Lê Lợi', '2022-07-01', 'leave', 'assets/images/default-avatar.svg'),
('Nguyễn Minh Khang', 'khang.nm@cinemacentral.vn', '$2y$12$3gMZX9o7DXUCDtw5bGZfUONrtl7iz7v0rI6HbRaq1QBmNDRLe2F4a', '0944455566', '1996-01-20', 'Bình Thạnh, TP.HCM', 'staff', 'Nhân viên bán vé', 'Cinema Central Landmark', '2024-01-15', 'inactive', 'assets/images/default-avatar.svg'),
('Mai Phương', 'mai.phuong@gmail.com', '$2y$12$3gMZX9o7DXUCDtw5bGZfUONrtl7iz7v0rI6HbRaq1QBmNDRLe2F4a', '0989001122', '2000-04-12', 'Đà Nẵng', 'customer', NULL, NULL, NULL, 'active', 'assets/images/default-avatar.svg'),
('Trần Ngọc Tú', 'ngoctu@gmail.com', '$2y$12$3gMZX9o7DXUCDtw5bGZfUONrtl7iz7v0rI6HbRaq1QBmNDRLe2F4a', '0987333444', '1999-03-03', 'Hà Nội', 'customer', NULL, NULL, NULL, 'active', 'assets/images/default-avatar.svg'),
('Võ Minh Thư', 'minhthu@gmail.com', '$2y$12$3gMZX9o7DXUCDtw5bGZfUONrtl7iz7v0rI6HbRaq1QBmNDRLe2F4a', '0977667788', '2001-07-21', 'Cần Thơ', 'customer', NULL, NULL, NULL, 'blocked', 'assets/images/default-avatar.svg'),
('Lê Gia Huy', 'giahuy@gmail.com', '$2y$12$3gMZX9o7DXUCDtw5bGZfUONrtl7iz7v0rI6HbRaq1QBmNDRLe2F4a', '0966998877', '2002-09-18', 'Biên Hòa', 'customer', NULL, NULL, NULL, 'inactive', 'assets/images/default-avatar.svg');

INSERT INTO movies (title, description, director, `cast`, genre, duration, release_date, poster, banner, trailer_url, status) VALUES
('Lật Mặt 7: Một Điều Ước', 'Bộ phim tình cảm gia đình cảm động của đạo diễn Lý Hải.', 'Lý Hải', 'Đinh Y Nhung, Thanh Hiền, Trương Minh Cường', 'Hành động, Tâm lý', 138, '2024-04-26', 'assets/images/default-poster.svg', 'assets/images/default-banner.svg', 'https://www.youtube.com/watch?v=1', 1),
('Mai', 'Câu chuyện tình cảm nhiều biến cố của đạo diễn Trấn Thành.', 'Trấn Thành', 'Phương Anh Đào, Tuấn Trần', 'Tâm lý, Tình cảm', 131, '2024-02-10', 'assets/images/default-poster.svg', 'assets/images/default-banner.svg', 'https://www.youtube.com/watch?v=2', 2),
('Nhà Bà Nữ', 'Bộ phim gia đình với nhiều câu chuyện gần gũi.', 'Trấn Thành', 'Uyển Ân, Song Luân', 'Gia đình, Hài', 102, '2024-01-22', 'assets/images/default-poster.svg', 'assets/images/default-banner.svg', 'https://www.youtube.com/watch?v=3', 0),
('Godzilla x Kong: Đế Chế Mới', 'Cuộc chạm trán hoành tráng giữa các quái thú khổng lồ.', 'Adam Wingard', 'Rebecca Hall, Dan Stevens', 'Hành động, Viễn tưởng', 115, '2024-03-29', 'assets/images/default-poster.svg', 'assets/images/default-banner.svg', 'https://www.youtube.com/watch?v=4', 1),
('Kung Fu Panda 4', 'Gấu Po trở lại với hành trình mới.', 'Mike Mitchell', 'Jack Black, Awkwafina', 'Hoạt hình, Hài', 94, '2024-03-08', 'assets/images/default-poster.svg', 'assets/images/default-banner.svg', 'https://www.youtube.com/watch?v=5', 1);

INSERT INTO rooms (name, capacity, opening_time, closing_time, status, maintenance_reason) VALUES
('Rạp 1 - Standard', 40, '08:00:00', '23:30:00', 1, NULL),
('Rạp 2 - Premium', 30, '08:30:00', '23:00:00', 1, NULL),
('Rạp 3 - IMAX', 60, '09:00:00', '23:45:00', 1, NULL),
('Rạp 4 - 3D', 24, '08:00:00', '22:00:00', 0, 'Bảo trì máy chiếu 3D định kỳ');

INSERT INTO seats (room_id, row_name, seat_number, type, status) VALUES
(1, 'A', 1, 1, 1),
(1, 'A', 2, 1, 1),
(1, 'A', 3, 1, 1),
(1, 'A', 4, 1, 1),
(1, 'A', 5, 1, 1),
(1, 'A', 6, 1, 1),
(1, 'A', 7, 1, 1),
(1, 'A', 8, 1, 1),
(1, 'A', 9, 1, 1),
(1, 'A', 10, 1, 1),
(1, 'B', 1, 1, 1),
(1, 'B', 2, 1, 1),
(1, 'B', 3, 1, 1),
(1, 'B', 4, 1, 1),
(1, 'B', 5, 1, 1),
(1, 'B', 6, 1, 1),
(1, 'B', 7, 1, 1),
(1, 'B', 8, 1, 1),
(1, 'B', 9, 1, 1),
(1, 'B', 10, 1, 1),
(1, 'C', 1, 2, 1),
(1, 'C', 2, 2, 1),
(1, 'C', 3, 2, 1),
(1, 'C', 4, 2, 1),
(1, 'C', 5, 2, 1),
(1, 'C', 6, 2, 1),
(1, 'C', 7, 2, 1),
(1, 'C', 8, 2, 1),
(1, 'C', 9, 2, 1),
(1, 'C', 10, 2, 1),
(1, 'D', 1, 2, 1),
(1, 'D', 2, 2, 1),
(1, 'D', 3, 2, 1),
(1, 'D', 4, 2, 1),
(1, 'D', 5, 2, 1),
(1, 'D', 6, 2, 1),
(1, 'D', 7, 2, 1),
(1, 'D', 8, 2, 1),
(1, 'D', 9, 2, 1),
(1, 'D', 10, 2, 1),
(2, 'A', 1, 1, 1),
(2, 'A', 2, 1, 1),
(2, 'A', 3, 1, 1),
(2, 'A', 4, 1, 1),
(2, 'A', 5, 1, 1),
(2, 'A', 6, 1, 1),
(2, 'A', 7, 1, 1),
(2, 'A', 8, 1, 1),
(2, 'A', 9, 1, 1),
(2, 'A', 10, 1, 1),
(2, 'B', 1, 2, 1),
(2, 'B', 2, 2, 1),
(2, 'B', 3, 2, 1),
(2, 'B', 4, 2, 1),
(2, 'B', 5, 2, 1),
(2, 'B', 6, 2, 1),
(2, 'B', 7, 2, 1),
(2, 'B', 8, 2, 1),
(2, 'B', 9, 2, 1),
(2, 'B', 10, 2, 1),
(2, 'C', 1, 2, 1),
(2, 'C', 2, 2, 1),
(2, 'C', 3, 2, 1),
(2, 'C', 4, 2, 1),
(2, 'C', 5, 2, 1),
(2, 'C', 6, 2, 1),
(2, 'C', 7, 2, 1),
(2, 'C', 8, 2, 1),
(2, 'C', 9, 2, 1),
(2, 'C', 10, 2, 1),
(3, 'A', 1, 1, 1),
(3, 'A', 2, 1, 1),
(3, 'A', 3, 1, 1),
(3, 'A', 4, 1, 1),
(3, 'A', 5, 1, 1),
(3, 'A', 6, 1, 1),
(3, 'A', 7, 1, 1),
(3, 'A', 8, 1, 1),
(3, 'A', 9, 1, 1),
(3, 'A', 10, 1, 1),
(3, 'B', 1, 1, 1),
(3, 'B', 2, 1, 1),
(3, 'B', 3, 1, 1),
(3, 'B', 4, 1, 1),
(3, 'B', 5, 1, 1),
(3, 'B', 6, 1, 1),
(3, 'B', 7, 1, 1),
(3, 'B', 8, 1, 1),
(3, 'B', 9, 1, 1),
(3, 'B', 10, 1, 1),
(3, 'C', 1, 1, 1),
(3, 'C', 2, 1, 1),
(3, 'C', 3, 1, 1),
(3, 'C', 4, 1, 1),
(3, 'C', 5, 1, 1),
(3, 'C', 6, 1, 1),
(3, 'C', 7, 1, 1),
(3, 'C', 8, 1, 1),
(3, 'C', 9, 1, 1),
(3, 'C', 10, 1, 1),
(3, 'D', 1, 1, 1),
(3, 'D', 2, 1, 1),
(3, 'D', 3, 1, 1),
(3, 'D', 4, 1, 1),
(3, 'D', 5, 1, 1),
(3, 'D', 6, 1, 1),
(3, 'D', 7, 1, 1),
(3, 'D', 8, 1, 1),
(3, 'D', 9, 1, 1),
(3, 'D', 10, 1, 1),
(3, 'E', 1, 2, 1),
(3, 'E', 2, 2, 1),
(3, 'E', 3, 2, 1),
(3, 'E', 4, 2, 1),
(3, 'E', 5, 2, 1),
(3, 'E', 6, 2, 1),
(3, 'E', 7, 2, 1),
(3, 'E', 8, 2, 1),
(3, 'E', 9, 2, 1),
(3, 'E', 10, 2, 1),
(3, 'F', 1, 2, 1),
(3, 'F', 2, 2, 1),
(3, 'F', 3, 2, 1),
(3, 'F', 4, 2, 1),
(3, 'F', 5, 2, 1),
(3, 'F', 6, 2, 1),
(3, 'F', 7, 2, 1),
(3, 'F', 8, 2, 1),
(3, 'F', 9, 2, 1),
(3, 'F', 10, 2, 1),
(4, 'A', 1, 1, 1),
(4, 'A', 2, 1, 1),
(4, 'A', 3, 1, 1),
(4, 'A', 4, 1, 1),
(4, 'A', 5, 1, 1),
(4, 'A', 6, 1, 1),
(4, 'A', 7, 1, 1),
(4, 'A', 8, 1, 1),
(4, 'A', 9, 1, 1),
(4, 'A', 10, 1, 1),
(4, 'B', 1, 1, 1),
(4, 'B', 2, 1, 1),
(4, 'B', 3, 1, 1),
(4, 'B', 4, 1, 1),
(4, 'B', 5, 1, 1),
(4, 'B', 6, 1, 1),
(4, 'B', 7, 1, 1),
(4, 'B', 8, 1, 1),
(4, 'B', 9, 1, 1),
(4, 'B', 10, 1, 1),
(4, 'C', 1, 2, 1),
(4, 'C', 2, 2, 1),
(4, 'C', 3, 2, 1),
(4, 'C', 4, 2, 1);

UPDATE seats SET type = 3 WHERE room_id = 2 AND row_name = 'C' AND seat_number IN (1,2,3,4,5,6,7,8,9,10);
UPDATE seats SET status = 0 WHERE room_id = 4 AND row_name IN ('A');

INSERT INTO showtimes (movie_id, room_id, show_date, start_time, end_time, price, status) VALUES
(1, 1, CURDATE(), '18:00:00', '20:18:00', 90000, 1),
(2, 2, CURDATE(), '20:30:00', '22:41:00', 95000, 1),
(4, 3, CURDATE(), '19:15:00', '21:10:00', 120000, 1),
(5, 1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '10:00:00', '11:34:00', 80000, 1),
(1, 2, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '17:00:00', '19:18:00', 100000, 1),
(3, 4, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '15:00:00', '16:42:00', 85000, 0);

INSERT INTO promotions (code, title, discount_type, discount_value, min_order_amount, max_discount, usage_limit, used_count, budget, start_date, end_date, description, image_path, status) VALUES
('SUMMER24', 'Ưu đãi chào hè rực rỡ', 'percent', 20, 120000, 50000, 5000, 1245, 58000000, DATE_SUB(CURDATE(), INTERVAL 10 DAY), DATE_ADD(CURDATE(), INTERVAL 40 DAY), 'Áp dụng cho các suất chiếu ngày thường và cuối tuần.', NULL, 1),
('HOTSUMMER', 'Mùa hè sôi động - giảm sâu', 'percent', 30, 150000, 70000, 3000, 870, 45000000, DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 25 DAY), 'Dành cho khách hàng đặt từ 2 vé trở lên.', NULL, 1),
('WELCOME2024', 'Quà tặng chào khách mới', 'fixed', 25000, 90000, 25000, 2000, 420, 15000000, DATE_SUB(CURDATE(), INTERVAL 20 DAY), DATE_ADD(CURDATE(), INTERVAL 10 DAY), 'Tặng cho khách hàng mới trên toàn hệ thống.', NULL, 0);

INSERT INTO orders (user_id, promotion_id, order_code, order_date, total_amount, discount_amount, final_amount, payment_method, payment_status, order_status, notes) VALUES
(6, 1, 'HD-202404-0001', DATE_SUB(NOW(), INTERVAL 2 DAY), 180000, 36000, 144000, 'momo', 'paid', 'completed', 'Đặt qua ứng dụng'),
(7, 2, 'HD-202404-0002', DATE_SUB(NOW(), INTERVAL 1 DAY), 240000, 72000, 168000, 'vnpay', 'paid', 'completed', 'Khách thanh toán online'),
(6, NULL, 'HD-202404-0003', DATE_SUB(NOW(), INTERVAL 5 HOUR), 95000, 0, 95000, 'cash', 'pending', 'pending', 'Chờ xác nhận tại quầy'),
(8, 3, 'HD-202404-0004', DATE_SUB(NOW(), INTERVAL 4 DAY), 160000, 25000, 135000, 'bank_transfer', 'failed', 'cancelled', 'Thanh toán lỗi');

INSERT INTO tickets (order_id, showtime_id, seat_id, price, ticket_status) VALUES
(1, 1, 1, 90000, 'paid'),
(1, 1, 2, 90000, 'paid'),
(2, 3, 71, 120000, 'paid'),
(2, 3, 72, 120000, 'paid'),
(3, 2, 41, 95000, 'reserved'),
(4, 5, 45, 80000, 'cancelled'),
(4, 5, 46, 80000, 'cancelled');

UPDATE promotions p
SET used_count = (
    SELECT COUNT(*) FROM orders o WHERE o.promotion_id = p.promotion_id AND o.order_status <> 'cancelled'
);

-- Tài khoản đăng nhập admin mẫu:
-- Email: admin@cinemacentral.vn
-- Password: Admin@123
