-- Cinema Booking System | Clean XAMPP install
-- Import file này trực tiếp trong phpMyAdmin/XAMPP để tạo database sạch, tránh lỗi trùng bảng / trùng dữ liệu
SET NAMES utf8mb4;
SET time_zone = '+07:00';
CREATE DATABASE IF NOT EXISTS movie_booking CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE movie_booking;
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `CancellationRequests`;
DROP TABLE IF EXISTS `Payments`;
DROP TABLE IF EXISTS `Tickets`;
DROP TABLE IF EXISTS `Orders`;
DROP TABLE IF EXISTS `Promotions`;
DROP TABLE IF EXISTS `Showtimes`;
DROP TABLE IF EXISTS `SeatPrices`;
DROP TABLE IF EXISTS `Seats`;
DROP TABLE IF EXISTS `Rooms`;
DROP TABLE IF EXISTS `Movies`;
DROP TABLE IF EXISTS `Users`;
DROP TABLE IF EXISTS `cancellation_requests`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `tickets`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `promotions`;
DROP TABLE IF EXISTS `showtimes`;
DROP TABLE IF EXISTS `seat_prices`;
DROP TABLE IF EXISTS `seats`;
DROP TABLE IF EXISTS `rooms`;
DROP TABLE IF EXISTS `movies`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    birthday DATE NULL,
    address VARCHAR(255) NULL,
    bank_account VARCHAR(50) NULL,
    role ENUM('customer','staff','admin') NOT NULL DEFAULT 'customer',
    position VARCHAR(100) NULL,
    branch_name VARCHAR(150) NULL,
    hire_date DATE NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    avatar VARCHAR(255) NULL,
    oauth_provider VARCHAR(50) NULL,
    oauth_id VARCHAR(100) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE movies (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE rooms (
    room_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    capacity INT NOT NULL,
    opening_time TIME NULL,
    closing_time TIME NULL,
    status TINYINT NOT NULL DEFAULT 1,
    maintenance_reason VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE seats (
    seat_id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    row_name VARCHAR(5) NOT NULL,
    seat_number INT NOT NULL,
    type TINYINT NOT NULL DEFAULT 1 COMMENT '1-standard,2-vip,3-couple',
    status TINYINT NOT NULL DEFAULT 1,
    UNIQUE KEY uniq_room_seat (room_id, row_name, seat_number),
    CONSTRAINT fk_seat_room FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE seat_prices (
    seat_price_id INT AUTO_INCREMENT PRIMARY KEY,
    seat_type VARCHAR(20) NOT NULL UNIQUE,
    price_multiplier DECIMAL(5,2) NOT NULL,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE showtimes (
    showtime_id INT AUTO_INCREMENT PRIMARY KEY,
    movie_id INT NOT NULL,
    room_id INT NOT NULL,
    show_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    base_price DECIMAL(10,2) NOT NULL,
    status TINYINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_showtime_movie FOREIGN KEY (movie_id) REFERENCES movies(movie_id) ON DELETE CASCADE,
    CONSTRAINT fk_showtime_room FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE promotions (
    promotion_id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(30) NOT NULL UNIQUE,
    promo_code VARCHAR(30) NULL,
    title VARCHAR(160) NOT NULL,
    discount_type ENUM('percent','fixed') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    min_order_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    min_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    min_tickets INT NOT NULL DEFAULT 1,
    max_discount DECIMAL(12,2) NULL,
    usage_limit INT NULL,
    used_count INT NOT NULL DEFAULT 0,
    budget DECIMAL(12,2) NOT NULL DEFAULT 0,
    description TEXT NULL,
    applicable_seat_types TEXT NULL,
    image_path VARCHAR(255) NULL,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    status TINYINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    promotion_id INT NULL,
    order_code VARCHAR(30) NOT NULL UNIQUE,
    order_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(12,2) NOT NULL,
    discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    final_amount DECIMAL(12,2) NOT NULL,
    payment_method VARCHAR(30) NOT NULL DEFAULT 'cash',
    payment_status VARCHAR(20) NOT NULL DEFAULT 'pending',
    order_status VARCHAR(20) NOT NULL DEFAULT 'pending',
    notes VARCHAR(255) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_order_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_order_promotion FOREIGN KEY (promotion_id) REFERENCES promotions(promotion_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE tickets (
    ticket_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    showtime_id INT NOT NULL,
    seat_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    ticket_status VARCHAR(20) NOT NULL DEFAULT 'reserved',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_showtime_seat (showtime_id, seat_id),
    CONSTRAINT fk_ticket_order FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    CONSTRAINT fk_ticket_showtime FOREIGN KEY (showtime_id) REFERENCES showtimes(showtime_id) ON DELETE CASCADE,
    CONSTRAINT fk_ticket_seat FOREIGN KEY (seat_id) REFERENCES seats(seat_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL UNIQUE,
    payment_method VARCHAR(30) NOT NULL,
    amount_paid DECIMAL(12,2) NOT NULL,
    payment_status VARCHAR(20) NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_payment_order FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE cancellation_requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    user_id INT NOT NULL,
    reason TEXT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    admin_note VARCHAR(255) NULL,
    processed_by INT NULL,
    processed_at DATETIME NULL,
    request_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cancel_order FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    CONSTRAINT fk_cancel_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_cancel_processed_by FOREIGN KEY (processed_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO users (user_id, full_name, email, password, phone, birthday, address, bank_account, role, position, branch_name, hire_date, status, avatar) VALUES
(1, 'Nguyễn Hoàng Anh', 'admin@cinemacentral.vn', '$2y$12$3gMZX9o7DXUCDtw5bGZfUONrtl7iz7v0rI6HbRaq1QBmNDRLe2F4a', '0909123456', '1992-08-14', 'Quận 1, TP.HCM', '1234567890', 'admin', 'Quản trị hệ thống', 'Cinema Central HQ', '2021-01-10', 'active', 'assets/images/default-avatar.svg'),
(2, 'Phạm Minh Đức', 'duc.pm@cinemacentral.vn', '$2y$12$3gMZX9o7DXUCDtw5bGZfUONrtl7iz7v0rI6HbRaq1QBmNDRLe2F4a', '0909888777', '1995-02-15', 'Quận 3, TP.HCM', NULL, 'staff', 'Quản lý ca', 'Cinema Central Lê Lợi', '2022-03-20', 'working', 'assets/images/default-avatar.svg'),
(3, 'Lê Thị Bảo Vy', 'vy.lt@cinemacentral.vn', '$2y$12$3gMZX9o7DXUCDtw5bGZfUONrtl7iz7v0rI6HbRaq1QBmNDRLe2F4a', '0912345678', '1998-06-19', 'Quận 7, TP.HCM', NULL, 'staff', 'Nhân viên quầy vé', 'Cinema Central Landmark', '2023-02-10', 'working', 'assets/images/default-avatar.svg'),
(4, 'Mai Phương', 'mai.phuong@gmail.com', '$2y$12$3gMZX9o7DXUCDtw5bGZfUONrtl7iz7v0rI6HbRaq1QBmNDRLe2F4a', '0989001122', '2000-04-12', 'Đà Nẵng', '970400000001', 'customer', NULL, NULL, NULL, 'active', 'assets/images/default-avatar.svg'),
(5, 'Trần Ngọc Tú', 'ngoctu@gmail.com', '$2y$12$3gMZX9o7DXUCDtw5bGZfUONrtl7iz7v0rI6HbRaq1QBmNDRLe2F4a', '0987333444', '1999-03-03', 'Hà Nội', '970400000002', 'customer', NULL, NULL, NULL, 'active', 'assets/images/default-avatar.svg');


INSERT INTO movies (movie_id, title, description, director, `cast`, genre, duration, release_date, poster, poster_url, banner, trailer_url, status) VALUES
(1, 'Lật Mặt 7: Một Điều Ước', 'Bộ phim gia đình cảm động của đạo diễn Lý Hải.', 'Lý Hải', 'Đinh Y Nhung, Thanh Hiền, Trương Minh Cường', 'Tâm lý, Gia đình', 138, '2024-04-26', 'assets/images/default-poster.svg', 'assets/images/default-poster.svg', 'assets/images/default-banner.svg', 'https://www.youtube.com/watch?v=1', '1'),
(2, 'Mai', 'Câu chuyện tình cảm nhiều biến cố của đạo diễn Trấn Thành.', 'Trấn Thành', 'Phương Anh Đào, Tuấn Trần', 'Tâm lý, Tình cảm', 131, '2024-05-10', 'assets/images/default-poster.svg', 'assets/images/default-poster.svg', 'assets/images/default-banner.svg', 'https://www.youtube.com/watch?v=2', '2'),
(3, 'Godzilla x Kong: Đế Chế Mới', 'Cuộc chạm trán giữa các quái thú khổng lồ.', 'Adam Wingard', 'Rebecca Hall, Dan Stevens', 'Hành động, Viễn tưởng', 115, '2024-03-29', 'assets/images/default-poster.svg', 'assets/images/default-poster.svg', 'assets/images/default-banner.svg', 'https://www.youtube.com/watch?v=3', '1');


INSERT INTO rooms (room_id, name, capacity, opening_time, closing_time, status, maintenance_reason) VALUES
(1, 'Cinema Central Lê Lợi - Rạp 1', 32, '08:00:00', '23:30:00', 1, NULL),
(2, 'Cinema Central Lê Lợi - Rạp 2', 32, '08:00:00', '23:30:00', 1, NULL),
(3, 'Cinema Central Landmark - IMAX', 32, '09:00:00', '23:45:00', 1, NULL);

INSERT INTO seats (room_id, row_name, seat_number, type, status) VALUES
(1,'A',1,1,1),
(1,'A',2,1,1),
(1,'A',3,1,1),
(1,'A',4,1,1),
(1,'A',5,1,1),
(1,'A',6,1,1),
(1,'A',7,1,1),
(1,'A',8,1,1),
(1,'B',1,1,1),
(1,'B',2,1,1),
(1,'B',3,1,1),
(1,'B',4,1,1),
(1,'B',5,1,1),
(1,'B',6,1,1),
(1,'B',7,1,1),
(1,'B',8,1,1),
(1,'C',1,2,1),
(1,'C',2,2,1),
(1,'C',3,2,1),
(1,'C',4,2,1),
(1,'C',5,2,1),
(1,'C',6,2,1),
(1,'C',7,2,1),
(1,'C',8,2,1),
(1,'D',1,3,1),
(1,'D',2,3,1),
(1,'D',3,3,1),
(1,'D',4,3,1),
(1,'D',5,3,1),
(1,'D',6,3,1),
(1,'D',7,3,1),
(1,'D',8,3,1),
(2,'A',1,1,1),
(2,'A',2,1,1),
(2,'A',3,1,1),
(2,'A',4,1,1),
(2,'A',5,1,1),
(2,'A',6,1,1),
(2,'A',7,1,1),
(2,'A',8,1,1),
(2,'B',1,1,1),
(2,'B',2,1,1),
(2,'B',3,1,1),
(2,'B',4,1,1),
(2,'B',5,1,1),
(2,'B',6,1,1),
(2,'B',7,1,1),
(2,'B',8,1,1),
(2,'C',1,2,1),
(2,'C',2,2,1),
(2,'C',3,2,1),
(2,'C',4,2,1),
(2,'C',5,2,1),
(2,'C',6,2,1),
(2,'C',7,2,1),
(2,'C',8,2,1),
(2,'D',1,3,1),
(2,'D',2,3,1),
(2,'D',3,3,1),
(2,'D',4,3,1),
(2,'D',5,3,1),
(2,'D',6,3,1),
(2,'D',7,3,1),
(2,'D',8,3,1),
(3,'A',1,1,1),
(3,'A',2,1,1),
(3,'A',3,1,1),
(3,'A',4,1,1),
(3,'A',5,1,1),
(3,'A',6,1,1),
(3,'A',7,1,1),
(3,'A',8,1,1),
(3,'B',1,1,1),
(3,'B',2,1,1),
(3,'B',3,1,1),
(3,'B',4,1,1),
(3,'B',5,1,1),
(3,'B',6,1,1),
(3,'B',7,1,1),
(3,'B',8,1,1),
(3,'C',1,2,1),
(3,'C',2,2,1),
(3,'C',3,2,1),
(3,'C',4,2,1),
(3,'C',5,2,1),
(3,'C',6,2,1),
(3,'C',7,2,1),
(3,'C',8,2,1),
(3,'D',1,3,1),
(3,'D',2,3,1),
(3,'D',3,3,1),
(3,'D',4,3,1),
(3,'D',5,3,1),
(3,'D',6,3,1),
(3,'D',7,3,1),
(3,'D',8,3,1);

INSERT INTO seat_prices (seat_price_id, seat_type, price_multiplier, description) VALUES
(1, 'standard', 1.00, 'Ghế thường'),
(2, 'vip', 1.30, 'Ghế VIP'),
(3, 'couple', 1.80, 'Ghế đôi');


INSERT INTO showtimes (showtime_id, movie_id, room_id, show_date, start_time, end_time, price, base_price, status) VALUES
(1, 1, 1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '10:00:00', '12:18:00', 85000, 85000, 1),
(2, 1, 2, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '14:00:00', '16:18:00', 90000, 90000, 1),
(3, 2, 1, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '18:30:00', '20:41:00', 95000, 95000, 1),
(4, 2, 3, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '20:00:00', '22:11:00', 120000, 120000, 1),
(5, 3, 2, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '16:00:00', '17:55:00', 100000, 100000, 1),
(6, 3, 3, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '19:30:00', '21:25:00', 130000, 130000, 1);


INSERT INTO promotions (promotion_id, code, promo_code, title, discount_type, discount_value, min_order_amount, min_amount, min_tickets, max_discount, usage_limit, used_count, budget, description, applicable_seat_types, image_path, start_date, end_date, status) VALUES
(1, 'WELCOME10', 'WELCOME10', 'Khuyến mãi chào mừng 10%', 'percent', 10, 100000, 100000, 1, 50000, 100, 1, 5000000, 'Áp dụng cho mọi loại ghế.', 'standard,vip,couple', NULL, DATE_SUB(NOW(), INTERVAL 30 DAY), DATE_ADD(NOW(), INTERVAL 60 DAY), 1),
(2, 'VIP50000', 'VIP50000', 'Giảm 50.000đ cho vé VIP', 'fixed', 50000, 150000, 150000, 1, NULL, 50, 0, 2500000, 'Ưu đãi cho đơn từ 150.000đ.', 'vip,couple', NULL, DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_ADD(NOW(), INTERVAL 30 DAY), 1);


INSERT INTO orders (order_id, user_id, promotion_id, order_code, order_date, total_amount, discount_amount, final_amount, payment_method, payment_status, order_status, notes) VALUES
(1, 4, 1, 'ORD240001', DATE_SUB(NOW(), INTERVAL 2 DAY), 170000, 17000, 153000, 'momo', 'paid', 'completed', 'Đơn mẫu đã thanh toán'),
(2, 5, NULL, 'ORD240002', DATE_SUB(NOW(), INTERVAL 1 DAY), 120000, 0, 120000, 'cash', 'pending', 'pending', 'Đơn mẫu chờ xác nhận'),
(3, 4, NULL, 'ORD240003', DATE_SUB(NOW(), INTERVAL 5 DAY), 100000, 0, 100000, 'bank_transfer', 'refunded', 'cancelled', 'Đã hủy theo yêu cầu khách hàng'),
(4, 5, 2, 'ORD240004', DATE_SUB(NOW(), INTERVAL 3 DAY), 195000, 50000, 145000, 'vnpay', 'paid', 'completed', 'Khách hàng đã gửi yêu cầu hủy đang chờ duyệt'),
(5, 4, NULL, 'ORD240005', DATE_SUB(NOW(), INTERVAL 4 DAY), 90000, 0, 90000, 'cash', 'paid', 'completed', 'Yêu cầu hủy đã bị từ chối');


INSERT INTO tickets (ticket_id, order_id, showtime_id, seat_id, price, ticket_status) VALUES
(1, 1, 1, 1, 85000, 'paid'),
(2, 1, 1, 2, 85000, 'paid'),
(3, 2, 4, 65, 120000, 'reserved'),
(4, 3, 5, 33, 100000, 'cancelled'),
(5, 4, 6, 89, 130000, 'paid'),
(6, 4, 6, 90, 65000, 'paid'),
(7, 5, 2, 51, 90000, 'paid');


INSERT INTO payments (payment_id, order_id, payment_method, amount_paid, payment_status) VALUES
(1, 1, 'momo', 153000, 'success'),
(2, 2, 'cash', 0, 'pending'),
(3, 3, 'bank_transfer', 100000, 'refunded'),
(4, 4, 'vnpay', 145000, 'success'),
(5, 5, 'cash', 90000, 'success');


INSERT INTO cancellation_requests (request_id, order_id, user_id, reason, status, admin_note, processed_by, processed_at, request_date) VALUES
(1, 3, 4, 'Có việc đột xuất nên không thể xem phim.', 'approved', 'Đã xác nhận hoàn tiền về tài khoản khách hàng.', 2, DATE_SUB(NOW(), INTERVAL 4 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY)),
(2, 4, 5, 'Muốn đổi sang suất chiếu khác vào ngày mai.', 'pending', NULL, NULL, NULL, DATE_SUB(NOW(), INTERVAL 6 HOUR)),
(3, 5, 4, 'Bị trùng lịch nhưng vẫn có thể đi xem.', 'rejected', 'Suất chiếu đã quá thời hạn hỗ trợ hủy miễn phí.', 1, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY));

SET FOREIGN_KEY_CHECKS = 1;
