CREATE DATABASE IF NOT EXISTS movie_booking CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE movie_booking;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS cancellation_requests;
DROP TABLE IF EXISTS tickets;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS promotions;
DROP TABLE IF EXISTS showtimes;
DROP TABLE IF EXISTS seat_prices;
DROP TABLE IF EXISTS seats;
DROP TABLE IF EXISTS rooms;
DROP TABLE IF EXISTS movies;
DROP TABLE IF EXISTS users;
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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE rooms (
    room_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    capacity INT NOT NULL,
    opening_time TIME NULL,
    closing_time TIME NULL,
    status TINYINT NOT NULL DEFAULT 1,
    maintenance_reason VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE seats (
    seat_id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    row_name VARCHAR(5) NOT NULL,
    seat_number INT NOT NULL,
    type INT NOT NULL DEFAULT 1,
    status TINYINT NOT NULL DEFAULT 1,
    UNIQUE KEY uniq_room_seat (room_id, row_name, seat_number),
    CONSTRAINT fk_seat_room FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE seat_prices (
    seat_price_id INT AUTO_INCREMENT PRIMARY KEY,
    seat_type VARCHAR(20) NOT NULL UNIQUE,
    price_multiplier DECIMAL(5,2) NOT NULL,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_showtime_movie FOREIGN KEY (movie_id) REFERENCES movies(movie_id) ON DELETE CASCADE,
    CONSTRAINT fk_showtime_room FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
    applicable_seat_types JSON NULL,
    image_path VARCHAR(255) NULL,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    status TINYINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_order_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_order_promotion FOREIGN KEY (promotion_id) REFERENCES promotions(promotion_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE tickets (
    ticket_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    showtime_id INT NOT NULL,
    seat_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    ticket_status VARCHAR(20) NOT NULL DEFAULT 'reserved',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_showtime_seat (showtime_id, seat_id),
    CONSTRAINT fk_ticket_order FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    CONSTRAINT fk_ticket_showtime FOREIGN KEY (showtime_id) REFERENCES showtimes(showtime_id) ON DELETE CASCADE,
    CONSTRAINT fk_ticket_seat FOREIGN KEY (seat_id) REFERENCES seats(seat_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL UNIQUE,
    payment_method VARCHAR(30) NOT NULL,
    amount_paid DECIMAL(12,2) NOT NULL,
    payment_status VARCHAR(20) NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_payment_order FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE cancellation_requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    user_id INT NOT NULL,
    reason TEXT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    request_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cancel_order FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    CONSTRAINT fk_cancel_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO users (full_name, email, password, phone, birthday, address, role, position, branch_name, hire_date, status, avatar) VALUES
('Nguyễn Hoàng Anh', 'admin@cinemacentral.vn', '$2y$12$3gMZX9o7DXUCDtw5bGZfUONrtl7iz7v0rI6HbRaq1QBmNDRLe2F4a', '0909123456', '1992-08-14', 'Quận 1, TP.HCM', 'admin', 'Quản trị hệ thống', 'Cinema Central HQ', '2021-01-10', 'active', 'assets/images/default-avatar.svg'),
('Phạm Minh Đức', 'duc.pm@cinemacentral.vn', '$2y$12$3gMZX9o7DXUCDtw5bGZfUONrtl7iz7v0rI6HbRaq1QBmNDRLe2F4a', '0909888777', '1995-02-15', 'Quận 3, TP.HCM', 'staff', 'Quản lý', 'Cinema Central Lê Lợi', '2022-03-20', 'working', 'assets/images/default-avatar.svg'),
('Lê Thị Bảo Vy', 'vy.lt@cinemacentral.vn', '$2y$12$3gMZX9o7DXUCDtw5bGZfUONrtl7iz7v0rI6HbRaq1QBmNDRLe2F4a', '0912345678', '1998-06-19', 'Quận 7, TP.HCM', 'staff', 'Nhân viên', 'Cinema Central Phú Mỹ Hưng', '2023-02-10', 'working', 'assets/images/default-avatar.svg'),
('Mai Phương', 'mai.phuong@gmail.com', '$2y$12$3gMZX9o7DXUCDtw5bGZfUONrtl7iz7v0rI6HbRaq1QBmNDRLe2F4a', '0989001122', '2000-04-12', 'Đà Nẵng', 'customer', NULL, NULL, NULL, 'active', 'assets/images/default-avatar.svg'),
('Trần Ngọc Tú', 'ngoctu@gmail.com', '$2y$12$3gMZX9o7DXUCDtw5bGZfUONrtl7iz7v0rI6HbRaq1QBmNDRLe2F4a', '0987333444', '1999-03-03', 'Hà Nội', 'customer', NULL, NULL, NULL, 'active', 'assets/images/default-avatar.svg');

INSERT INTO movies (title, description, director, `cast`, genre, duration, release_date, poster, poster_url, banner, trailer_url, status) VALUES
('Lật Mặt 7: Một Điều Ước', 'Bộ phim tình cảm gia đình cảm động của đạo diễn Lý Hải.', 'Lý Hải', 'Đinh Y Nhung, Thanh Hiền, Trương Minh Cường', 'Hành động, Tâm lý', 138, '2024-04-26', 'lat-mat-7.jpg', 'lat-mat-7.jpg', 'lat-mat-7.jpg', 'https://www.youtube.com/embed/d1ZHdosjNX8', '1'),
('Mai', 'Câu chuyện tình cảm nhiều biến cố của đạo diễn Trấn Thành.', 'Trấn Thành', 'Phương Anh Đào, Tuấn Trần', 'Tâm lý, Tình cảm', 131, '2024-05-10', 'mai.jpg', 'mai.jpg', 'mai.jpg', 'https://www.youtube.com/embed/EX6clvId19s', '2'),
('Godzilla x Kong: Đế Chế Mới', 'Cuộc chạm trán hoành tráng giữa các quái thú khổng lồ.', 'Adam Wingard', 'Rebecca Hall, Dan Stevens', 'Hành động, Viễn tưởng', 115, '2024-03-29', 'godzilla-x-kong-de-che-moi.jpg', 'godzilla-x-kong-de-che-moi.jpg', 'godzilla-x-kong-de-che-moi.jpg', 'https://www.youtube.com/embed/5XkgG_AAQs0', '1');

INSERT INTO rooms (name, capacity, opening_time, closing_time, status, maintenance_reason) VALUES
('Cinema Central Lê Lợi - Rạp 1', 40, '08:00:00', '23:30:00', 1, NULL),
('Cinema Central Lê Lợi - Rạp 2', 30, '08:30:00', '23:00:00', 1, NULL),
('Cinema Central Landmark - IMAX', 60, '09:00:00', '23:45:00', 1, NULL);

INSERT INTO seats (room_id, row_name, seat_number, type, status) VALUES
(1,'A',1,1,1),(1,'A',2,1,1),(1,'A',3,1,1),(1,'A',4,1,1),(1,'A',5,1,1),(1,'A',6,1,1),(1,'A',7,1,1),(1,'A',8,1,1),(1,'A',9,1,1),(1,'A',10,1,1),
(1,'B',1,1,1),(1,'B',2,1,1),(1,'B',3,1,1),(1,'B',4,1,1),(1,'B',5,1,1),(1,'B',6,1,1),(1,'B',7,1,1),(1,'B',8,1,1),(1,'B',9,1,1),(1,'B',10,1,1),
(1,'C',1,2,1),(1,'C',2,2,1),(1,'C',3,2,1),(1,'C',4,2,1),(1,'C',5,2,1),(1,'C',6,2,1),(1,'C',7,2,1),(1,'C',8,2,1),(1,'C',9,2,1),(1,'C',10,2,1),
(2,'A',1,1,1),(2,'A',2,1,1),(2,'A',3,1,1),(2,'A',4,1,1),(2,'A',5,1,1),(2,'A',6,1,1),(2,'A',7,1,1),(2,'A',8,1,1),(2,'A',9,1,1),(2,'A',10,1,1),
(2,'B',1,2,1),(2,'B',2,2,1),(2,'B',3,2,1),(2,'B',4,2,1),(2,'B',5,2,1),(2,'B',6,2,1),(2,'B',7,2,1),(2,'B',8,2,1),(2,'B',9,2,1),(2,'B',10,2,1),
(3,'A',1,1,1),(3,'A',2,1,1),(3,'A',3,1,1),(3,'A',4,1,1),(3,'A',5,1,1),(3,'A',6,1,1),(3,'A',7,1,1),(3,'A',8,1,1),(3,'A',9,1,1),(3,'A',10,1,1),
(3,'B',1,1,1),(3,'B',2,1,1),(3,'B',3,1,1),(3,'B',4,1,1),(3,'B',5,1,1),(3,'B',6,1,1),(3,'B',7,1,1),(3,'B',8,1,1),(3,'B',9,1,1),(3,'B',10,1,1),
(3,'C',1,2,1),(3,'C',2,2,1),(3,'C',3,2,1),(3,'C',4,2,1),(3,'C',5,2,1),(3,'C',6,2,1),(3,'C',7,2,1),(3,'C',8,2,1),(3,'C',9,2,1),(3,'C',10,2,1);

INSERT INTO seat_prices (seat_type, price_multiplier, description) VALUES
('standard', 1.00, 'Ghế thường'),
('vip', 1.30, 'Ghế VIP'),
('couple', 1.80, 'Ghế đôi');

INSERT INTO showtimes (movie_id, room_id, show_date, start_time, end_time, price, base_price, status) VALUES
(1,1,CURDATE() + INTERVAL 1 DAY,'18:00:00','20:18:00',90000,90000,1),
(1,2,CURDATE() + INTERVAL 2 DAY,'20:00:00','22:18:00',100000,100000,1),
(2,1,CURDATE() + INTERVAL 5 DAY,'19:00:00','21:11:00',95000,95000,1),
(3,3,CURDATE() + INTERVAL 1 DAY,'21:00:00','22:55:00',120000,120000,1);

INSERT INTO promotions (code, promo_code, title, discount_type, discount_value, min_order_amount, min_amount, min_tickets, max_discount, usage_limit, used_count, budget, description, start_date, end_date, status) VALUES
('WELCOME10','WELCOME10','Giảm 10% cho khách mới','percent',10,100000,100000,1,50000,500,0,5000000,'Áp dụng cho khách hàng mới và đơn hàng từ 100.000đ',NOW() - INTERVAL 7 DAY, NOW() + INTERVAL 30 DAY,1),
('GIAM50K','GIAM50K','Giảm 50.000đ cuối tuần','fixed',50000,200000,200000,2,50000,100,0,3000000,'Áp dụng cho đơn từ 200.000đ',NOW() - INTERVAL 7 DAY, NOW() + INTERVAL 15 DAY,1);

INSERT INTO orders (user_id, promotion_id, order_code, order_date, total_amount, discount_amount, final_amount, payment_method, payment_status, order_status, notes) VALUES
(4,1,'ORD1001',NOW() - INTERVAL 2 DAY,180000,18000,162000,'momo','paid','completed','Đơn mẫu đã duyệt'),
(5,NULL,'ORD1002',NOW() - INTERVAL 1 DAY,90000,0,90000,'cash','pending','pending','Đơn mẫu chờ duyệt');

INSERT INTO tickets (order_id, showtime_id, seat_id, price, ticket_status) VALUES
(1,1,1,90000,'paid'),
(1,1,2,90000,'paid'),
(2,2,31,90000,'reserved');

INSERT INTO payments (order_id, payment_method, amount_paid, payment_status) VALUES
(1,'momo',162000,'success'),
(2,'cash',90000,'pending');
