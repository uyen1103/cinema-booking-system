CREATE DATABASE IF NOT EXISTS movie_booking CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE movie_booking;

SET NAMES utf8mb4;
SET time_zone = '+07:00';

CREATE TABLE IF NOT EXISTS users (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
    user_id INT NOT NULL,
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
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_order_code (order_code),
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
    user_id INT NOT NULL,
    reason TEXT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    request_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_cancel_order (order_id),
    INDEX idx_cancel_user (user_id),
    INDEX idx_cancel_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* =========================
   2) BỔ SUNG CỘT NẾU CÒN THIẾU
   ========================= */

ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(20) NULL AFTER email;
ALTER TABLE users ADD COLUMN IF NOT EXISTS birthday DATE NULL AFTER phone;
ALTER TABLE users ADD COLUMN IF NOT EXISTS address VARCHAR(255) NULL AFTER birthday;
ALTER TABLE users ADD COLUMN IF NOT EXISTS bank_account VARCHAR(50) NULL AFTER address;
ALTER TABLE users ADD COLUMN IF NOT EXISTS position VARCHAR(100) NULL AFTER role;
ALTER TABLE users ADD COLUMN IF NOT EXISTS branch_name VARCHAR(150) NULL AFTER position;
ALTER TABLE users ADD COLUMN IF NOT EXISTS hire_date DATE NULL AFTER branch_name;
ALTER TABLE users ADD COLUMN IF NOT EXISTS avatar VARCHAR(255) NULL AFTER status;
ALTER TABLE users ADD COLUMN IF NOT EXISTS oauth_provider VARCHAR(50) NULL AFTER avatar;
ALTER TABLE users ADD COLUMN IF NOT EXISTS oauth_id VARCHAR(100) NULL AFTER oauth_provider;
ALTER TABLE users ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE movies ADD COLUMN IF NOT EXISTS description TEXT NULL AFTER title;
ALTER TABLE movies ADD COLUMN IF NOT EXISTS director VARCHAR(150) NULL AFTER description;
ALTER TABLE movies ADD COLUMN IF NOT EXISTS `cast` TEXT NULL AFTER director;
ALTER TABLE movies ADD COLUMN IF NOT EXISTS genre VARCHAR(150) NULL AFTER `cast`;
ALTER TABLE movies ADD COLUMN IF NOT EXISTS duration INT NOT NULL DEFAULT 90 AFTER genre;
ALTER TABLE movies ADD COLUMN IF NOT EXISTS release_date DATE NULL AFTER duration;
ALTER TABLE movies ADD COLUMN IF NOT EXISTS poster VARCHAR(255) NULL AFTER release_date;
ALTER TABLE movies ADD COLUMN IF NOT EXISTS poster_url VARCHAR(255) NULL AFTER poster;
ALTER TABLE movies ADD COLUMN IF NOT EXISTS banner VARCHAR(255) NULL AFTER poster_url;
ALTER TABLE movies ADD COLUMN IF NOT EXISTS trailer_url VARCHAR(255) NULL AFTER banner;
ALTER TABLE movies ADD COLUMN IF NOT EXISTS status VARCHAR(20) NOT NULL DEFAULT '1' AFTER trailer_url;
ALTER TABLE movies ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE rooms ADD COLUMN IF NOT EXISTS room_name VARCHAR(100) NULL AFTER name;
ALTER TABLE rooms ADD COLUMN IF NOT EXISTS opening_time TIME NULL AFTER capacity;
ALTER TABLE rooms ADD COLUMN IF NOT EXISTS closing_time TIME NULL AFTER opening_time;
ALTER TABLE rooms ADD COLUMN IF NOT EXISTS status TINYINT NOT NULL DEFAULT 1 AFTER closing_time;
ALTER TABLE rooms ADD COLUMN IF NOT EXISTS maintenance_reason VARCHAR(255) NULL AFTER status;

ALTER TABLE seats ADD COLUMN IF NOT EXISTS row_name VARCHAR(5) NULL AFTER room_id;
ALTER TABLE seats ADD COLUMN IF NOT EXISTS seat_row VARCHAR(5) NULL AFTER row_name;
ALTER TABLE seats ADD COLUMN IF NOT EXISTS type VARCHAR(20) NOT NULL DEFAULT 'standard' AFTER seat_number;
ALTER TABLE seats ADD COLUMN IF NOT EXISTS seat_type VARCHAR(20) NULL AFTER type;
ALTER TABLE seats ADD COLUMN IF NOT EXISTS status TINYINT NOT NULL DEFAULT 1 AFTER seat_type;

ALTER TABLE seat_prices ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE showtimes ADD COLUMN IF NOT EXISTS base_price DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER price;
ALTER TABLE showtimes ADD COLUMN IF NOT EXISTS status TINYINT NOT NULL DEFAULT 1 AFTER base_price;
ALTER TABLE showtimes ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER status;

ALTER TABLE promotions ADD COLUMN IF NOT EXISTS code VARCHAR(30) NULL FIRST;
ALTER TABLE promotions ADD COLUMN IF NOT EXISTS promo_code VARCHAR(30) NULL AFTER code;
ALTER TABLE promotions ADD COLUMN IF NOT EXISTS title VARCHAR(160) NULL AFTER promo_code;
ALTER TABLE promotions ADD COLUMN IF NOT EXISTS min_order_amount DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER discount_value;
ALTER TABLE promotions ADD COLUMN IF NOT EXISTS min_amount DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER min_order_amount;
ALTER TABLE promotions ADD COLUMN IF NOT EXISTS min_tickets INT NOT NULL DEFAULT 1 AFTER min_amount;
ALTER TABLE promotions ADD COLUMN IF NOT EXISTS max_discount DECIMAL(12,2) NULL AFTER min_tickets;
ALTER TABLE promotions ADD COLUMN IF NOT EXISTS usage_limit INT NULL AFTER max_discount;
ALTER TABLE promotions ADD COLUMN IF NOT EXISTS used_count INT NOT NULL DEFAULT 0 AFTER usage_limit;
ALTER TABLE promotions ADD COLUMN IF NOT EXISTS budget DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER used_count;
ALTER TABLE promotions ADD COLUMN IF NOT EXISTS applicable_seat_types JSON NULL AFTER description;
ALTER TABLE promotions ADD COLUMN IF NOT EXISTS image_path VARCHAR(255) NULL AFTER applicable_seat_types;
ALTER TABLE promotions ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER status;
ALTER TABLE promotions ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_method VARCHAR(30) NOT NULL DEFAULT 'cash' AFTER final_amount;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_status VARCHAR(20) NOT NULL DEFAULT 'pending' AFTER payment_method;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS order_status VARCHAR(20) NOT NULL DEFAULT 'pending' AFTER payment_status;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS notes VARCHAR(255) NULL AFTER order_status;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER notes;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

ALTER TABLE tickets ADD COLUMN IF NOT EXISTS ticket_status VARCHAR(20) NOT NULL DEFAULT 'reserved' AFTER price;
ALTER TABLE tickets ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER ticket_status;

ALTER TABLE payments ADD COLUMN IF NOT EXISTS payment_status VARCHAR(20) NOT NULL DEFAULT 'pending' AFTER amount_paid;
ALTER TABLE payments ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER payment_status;

ALTER TABLE cancellation_requests ADD COLUMN IF NOT EXISTS status VARCHAR(20) NOT NULL DEFAULT 'pending' AFTER reason;
ALTER TABLE cancellation_requests ADD COLUMN IF NOT EXISTS request_date DATETIME DEFAULT CURRENT_TIMESTAMP AFTER status;

/* =========================
   3) ĐỒNG BỘ TÊN CỘT CŨ/MỚI
   ========================= */

UPDATE rooms SET name = COALESCE(NULLIF(name, ''), room_name);
UPDATE rooms SET room_name = COALESCE(NULLIF(room_name, ''), name);

UPDATE seats SET row_name = COALESCE(NULLIF(row_name, ''), seat_row);
UPDATE seats SET seat_row = COALESCE(NULLIF(seat_row, ''), row_name);
UPDATE seats
SET seat_type = CASE
    WHEN seat_type IS NOT NULL AND seat_type <> '' THEN seat_type
    WHEN LOWER(CAST(type AS CHAR)) IN ('2', 'vip') THEN 'vip'
    WHEN LOWER(CAST(type AS CHAR)) IN ('3', 'couple') THEN 'couple'
    ELSE 'standard'
END;
UPDATE seats
SET type = CASE
    WHEN LOWER(CAST(type AS CHAR)) IN ('standard','1') THEN 'standard'
    WHEN LOWER(CAST(type AS CHAR)) IN ('vip','2') THEN 'vip'
    WHEN LOWER(CAST(type AS CHAR)) IN ('couple','3') THEN 'couple'
    WHEN seat_type IN ('vip','couple','standard') THEN seat_type
    ELSE 'standard'
END;

UPDATE movies SET poster = COALESCE(NULLIF(poster, ''), poster_url, 'assets/images/default-poster.svg');
UPDATE movies SET poster_url = COALESCE(NULLIF(poster_url, ''), poster, 'assets/images/default-poster.svg');
UPDATE movies SET banner = COALESCE(NULLIF(banner, ''), poster, poster_url, 'assets/images/default-banner.svg');

UPDATE promotions SET code = COALESCE(NULLIF(code, ''), promo_code);
UPDATE promotions SET promo_code = COALESCE(NULLIF(promo_code, ''), code);
UPDATE promotions SET title = COALESCE(NULLIF(title, ''), code, promo_code, 'Khuyến mãi');
UPDATE promotions SET min_order_amount = COALESCE(min_order_amount, min_amount, 0);
UPDATE promotions SET min_amount = COALESCE(min_amount, min_order_amount, 0);

UPDATE showtimes SET base_price = CASE WHEN base_price IS NULL OR base_price = 0 THEN price ELSE base_price END;
UPDATE orders SET payment_status = CASE WHEN payment_status = 'success' THEN 'paid' ELSE payment_status END;
UPDATE orders SET order_status = CASE WHEN order_status = 'paid' THEN 'completed' ELSE order_status END;
UPDATE payments SET payment_status = CASE WHEN payment_status = 'paid' THEN 'success' ELSE payment_status END;

/* =========================
   4) TRIGGER TƯƠNG THÍCH
   ========================= */

DROP TRIGGER IF EXISTS trg_rooms_bi_sync;
DELIMITER $$
CREATE TRIGGER trg_rooms_bi_sync
BEFORE INSERT ON rooms
FOR EACH ROW
BEGIN
    IF (NEW.name IS NULL OR NEW.name = '') AND NEW.room_name IS NOT NULL AND NEW.room_name <> '' THEN
        SET NEW.name = NEW.room_name;
    END IF;
    IF (NEW.room_name IS NULL OR NEW.room_name = '') AND NEW.name IS NOT NULL AND NEW.name <> '' THEN
        SET NEW.room_name = NEW.name;
    END IF;
END$$
DELIMITER ;

DROP TRIGGER IF EXISTS trg_rooms_bu_sync;
DELIMITER $$
CREATE TRIGGER trg_rooms_bu_sync
BEFORE UPDATE ON rooms
FOR EACH ROW
BEGIN
    IF (NEW.name IS NULL OR NEW.name = '') AND NEW.room_name IS NOT NULL AND NEW.room_name <> '' THEN
        SET NEW.name = NEW.room_name;
    END IF;
    IF (NEW.room_name IS NULL OR NEW.room_name = '') AND NEW.name IS NOT NULL AND NEW.name <> '' THEN
        SET NEW.room_name = NEW.name;
    END IF;
END$$
DELIMITER ;

DROP TRIGGER IF EXISTS trg_seats_bi_sync;
DELIMITER $$
CREATE TRIGGER trg_seats_bi_sync
BEFORE INSERT ON seats
FOR EACH ROW
BEGIN
    IF (NEW.row_name IS NULL OR NEW.row_name = '') AND NEW.seat_row IS NOT NULL AND NEW.seat_row <> '' THEN
        SET NEW.row_name = NEW.seat_row;
    END IF;
    IF (NEW.seat_row IS NULL OR NEW.seat_row = '') AND NEW.row_name IS NOT NULL AND NEW.row_name <> '' THEN
        SET NEW.seat_row = NEW.row_name;
    END IF;

    IF (NEW.type IS NULL OR NEW.type = '') AND NEW.seat_type IS NOT NULL AND NEW.seat_type <> '' THEN
        SET NEW.type = LOWER(NEW.seat_type);
    END IF;
    IF (NEW.seat_type IS NULL OR NEW.seat_type = '') AND NEW.type IS NOT NULL AND NEW.type <> '' THEN
        SET NEW.seat_type = LOWER(NEW.type);
    END IF;

    IF NEW.type IN ('1', 'standard') THEN SET NEW.type = 'standard'; END IF;
    IF NEW.type IN ('2', 'vip') THEN SET NEW.type = 'vip'; END IF;
    IF NEW.type IN ('3', 'couple') THEN SET NEW.type = 'couple'; END IF;

    IF NEW.seat_type IN ('1', 'standard') THEN SET NEW.seat_type = 'standard'; END IF;
    IF NEW.seat_type IN ('2', 'vip') THEN SET NEW.seat_type = 'vip'; END IF;
    IF NEW.seat_type IN ('3', 'couple') THEN SET NEW.seat_type = 'couple'; END IF;
END$$
DELIMITER ;

DROP TRIGGER IF EXISTS trg_seats_bu_sync;
DELIMITER $$
CREATE TRIGGER trg_seats_bu_sync
BEFORE UPDATE ON seats
FOR EACH ROW
BEGIN
    IF (NEW.row_name IS NULL OR NEW.row_name = '') AND NEW.seat_row IS NOT NULL AND NEW.seat_row <> '' THEN
        SET NEW.row_name = NEW.seat_row;
    END IF;
    IF (NEW.seat_row IS NULL OR NEW.seat_row = '') AND NEW.row_name IS NOT NULL AND NEW.row_name <> '' THEN
        SET NEW.seat_row = NEW.row_name;
    END IF;

    IF (NEW.type IS NULL OR NEW.type = '') AND NEW.seat_type IS NOT NULL AND NEW.seat_type <> '' THEN
        SET NEW.type = LOWER(NEW.seat_type);
    END IF;
    IF (NEW.seat_type IS NULL OR NEW.seat_type = '') AND NEW.type IS NOT NULL AND NEW.type <> '' THEN
        SET NEW.seat_type = LOWER(NEW.type);
    END IF;

    IF NEW.type IN ('1', 'standard') THEN SET NEW.type = 'standard'; END IF;
    IF NEW.type IN ('2', 'vip') THEN SET NEW.type = 'vip'; END IF;
    IF NEW.type IN ('3', 'couple') THEN SET NEW.type = 'couple'; END IF;

    IF NEW.seat_type IN ('1', 'standard') THEN SET NEW.seat_type = 'standard'; END IF;
    IF NEW.seat_type IN ('2', 'vip') THEN SET NEW.seat_type = 'vip'; END IF;
    IF NEW.seat_type IN ('3', 'couple') THEN SET NEW.seat_type = 'couple'; END IF;
END$$
DELIMITER ;

DROP TRIGGER IF EXISTS trg_promotions_bi_sync;
DELIMITER $$
CREATE TRIGGER trg_promotions_bi_sync
BEFORE INSERT ON promotions
FOR EACH ROW
BEGIN
    IF (NEW.code IS NULL OR NEW.code = '') AND NEW.promo_code IS NOT NULL AND NEW.promo_code <> '' THEN
        SET NEW.code = NEW.promo_code;
    END IF;
    IF (NEW.promo_code IS NULL OR NEW.promo_code = '') AND NEW.code IS NOT NULL AND NEW.code <> '' THEN
        SET NEW.promo_code = NEW.code;
    END IF;
    IF NEW.title IS NULL OR NEW.title = '' THEN
        SET NEW.title = COALESCE(NEW.code, NEW.promo_code, 'Khuyến mãi');
    END IF;
    IF NEW.min_order_amount IS NULL THEN
        SET NEW.min_order_amount = COALESCE(NEW.min_amount, 0);
    END IF;
    IF NEW.min_amount IS NULL THEN
        SET NEW.min_amount = COALESCE(NEW.min_order_amount, 0);
    END IF;
END$$
DELIMITER ;

DROP TRIGGER IF EXISTS trg_promotions_bu_sync;
DELIMITER $$
CREATE TRIGGER trg_promotions_bu_sync
BEFORE UPDATE ON promotions
FOR EACH ROW
BEGIN
    IF (NEW.code IS NULL OR NEW.code = '') AND NEW.promo_code IS NOT NULL AND NEW.promo_code <> '' THEN
        SET NEW.code = NEW.promo_code;
    END IF;
    IF (NEW.promo_code IS NULL OR NEW.promo_code = '') AND NEW.code IS NOT NULL AND NEW.code <> '' THEN
        SET NEW.promo_code = NEW.code;
    END IF;
    IF NEW.title IS NULL OR NEW.title = '' THEN
        SET NEW.title = COALESCE(NEW.code, NEW.promo_code, 'Khuyến mãi');
    END IF;
    IF NEW.min_order_amount IS NULL THEN
        SET NEW.min_order_amount = COALESCE(NEW.min_amount, 0);
    END IF;
    IF NEW.min_amount IS NULL THEN
        SET NEW.min_amount = COALESCE(NEW.min_order_amount, 0);
    END IF;
END$$
DELIMITER ;

DROP TRIGGER IF EXISTS trg_showtimes_bi_sync;
DELIMITER $$
CREATE TRIGGER trg_showtimes_bi_sync
BEFORE INSERT ON showtimes
FOR EACH ROW
BEGIN
    IF NEW.base_price IS NULL OR NEW.base_price = 0 THEN
        SET NEW.base_price = NEW.price;
    END IF;
END$$
DELIMITER ;

DROP TRIGGER IF EXISTS trg_showtimes_bu_sync;
DELIMITER $$
CREATE TRIGGER trg_showtimes_bu_sync
BEFORE UPDATE ON showtimes
FOR EACH ROW
BEGIN
    IF NEW.base_price IS NULL OR NEW.base_price = 0 THEN
        SET NEW.base_price = NEW.price;
    END IF;
END$$
DELIMITER ;

INSERT INTO users (full_name, email, password, phone, birthday, address, role, position, branch_name, hire_date, status, avatar)
SELECT 'Nguyễn Hoàng Anh', 'admin@cinemacentral.vn', '$2y$12$3gMZX9o7DXUCDtw5bGZfUONrtl7iz7v0rI6HbRaq1QBmNDRLe2F4a', '0909123456', '1992-08-14', 'Quận 1, TP.HCM', 'admin', 'Quản trị hệ thống', 'Cinema Central HQ', '2021-01-10', 'active', 'assets/images/default-avatar.svg'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'admin@cinemacentral.vn');

INSERT INTO users (full_name, email, password, phone, birthday, address, role, position, branch_name, hire_date, status, avatar)
SELECT 'Phạm Minh Đức', 'duc.pm@cinemacentral.vn', '$2y$12$3gMZX9o7DXUCDtw5bGZfUONrtl7iz7v0rI6HbRaq1QBmNDRLe2F4a', '0909888777', '1995-02-15', 'Quận 3, TP.HCM', 'staff', 'Quản lý', 'Cinema Central Lê Lợi', '2022-03-20', 'working', 'assets/images/default-avatar.svg'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'duc.pm@cinemacentral.vn');

INSERT INTO users (full_name, email, password, phone, birthday, address, role, position, branch_name, hire_date, status, avatar)
SELECT 'Lê Thị Bảo Vy', 'vy.lt@cinemacentral.vn', '$2y$12$3gMZX9o7DXUCDtw5bGZfUONrtl7iz7v0rI6HbRaq1QBmNDRLe2F4a', '0912345678', '1998-06-19', 'Quận 7, TP.HCM', 'staff', 'Nhân viên', 'Cinema Central Phú Mỹ Hưng', '2023-02-10', 'working', 'assets/images/default-avatar.svg'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'vy.lt@cinemacentral.vn');

INSERT INTO users (full_name, email, password, phone, birthday, address, role, status, avatar)
SELECT 'Mai Phương', 'mai.phuong@gmail.com', '$2y$12$3gMZX9o7DXUCDtw5bGZfUONrtl7iz7v0rI6HbRaq1QBmNDRLe2F4a', '0989001122', '2000-04-12', 'Đà Nẵng', 'customer', 'active', 'assets/images/default-avatar.svg'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'mai.phuong@gmail.com');

INSERT INTO users (full_name, email, password, phone, birthday, address, role, status, avatar)
SELECT 'Trần Ngọc Tú', 'ngoctu@gmail.com', '$2y$12$3gMZX9o7DXUCDtw5bGZfUONrtl7iz7v0rI6HbRaq1QBmNDRLe2F4a', '0987333444', '1999-03-03', 'Hà Nội', 'customer', 'active', 'assets/images/default-avatar.svg'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'ngoctu@gmail.com');

INSERT INTO movies (title, description, director, `cast`, genre, duration, release_date, poster, poster_url, banner, trailer_url, status)
SELECT 'Lật Mặt 7: Một Điều Ước', 'Bộ phim tình cảm gia đình cảm động của đạo diễn Lý Hải.', 'Lý Hải', 'Đinh Y Nhung, Thanh Hiền, Trương Minh Cường', 'Hành động, Tâm lý', 138, '2024-04-26', 'lat-mat-7.jpg', 'lat-mat-7.jpg', 'lat-mat-7.jpg', 'https://www.youtube.com/embed/d1ZHdosjNX8', '1'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM movies WHERE title = 'Lật Mặt 7: Một Điều Ước');

INSERT INTO movies (title, description, director, `cast`, genre, duration, release_date, poster, poster_url, banner, trailer_url, status)
SELECT 'Mai', 'Câu chuyện tình cảm nhiều biến cố của đạo diễn Trấn Thành.', 'Trấn Thành', 'Phương Anh Đào, Tuấn Trần', 'Tâm lý, Tình cảm', 131, '2024-05-10', 'mai.jpg', 'mai.jpg', 'mai.jpg', 'https://www.youtube.com/embed/EX6clvId19s', '2'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM movies WHERE title = 'Mai');

INSERT INTO movies (title, description, director, `cast`, genre, duration, release_date, poster, poster_url, banner, trailer_url, status)
SELECT 'Godzilla x Kong: Đế Chế Mới', 'Cuộc chạm trán hoành tráng giữa các quái thú khổng lồ.', 'Adam Wingard', 'Rebecca Hall, Dan Stevens', 'Hành động, Viễn tưởng', 115, '2024-03-29', 'godzilla-x-kong-de-che-moi.jpg', 'godzilla-x-kong-de-che-moi.jpg', 'godzilla-x-kong-de-che-moi.jpg', 'https://www.youtube.com/embed/5XkgG_AAQs0', '1'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM movies WHERE title = 'Godzilla x Kong: Đế Chế Mới');

INSERT INTO rooms (name, room_name, capacity, opening_time, closing_time, status, maintenance_reason)
SELECT 'Cinema Central Lê Lợi - Rạp 1', 'Cinema Central Lê Lợi - Rạp 1', 40, '08:00:00', '23:30:00', 1, NULL
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM rooms WHERE COALESCE(name, room_name) = 'Cinema Central Lê Lợi - Rạp 1');

INSERT INTO rooms (name, room_name, capacity, opening_time, closing_time, status, maintenance_reason)
SELECT 'Cinema Central Lê Lợi - Rạp 2', 'Cinema Central Lê Lợi - Rạp 2', 30, '08:30:00', '23:00:00', 1, NULL
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM rooms WHERE COALESCE(name, room_name) = 'Cinema Central Lê Lợi - Rạp 2');

INSERT INTO rooms (name, room_name, capacity, opening_time, closing_time, status, maintenance_reason)
SELECT 'Cinema Central Landmark - IMAX', 'Cinema Central Landmark - IMAX', 60, '09:00:00', '23:45:00', 1, NULL
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM rooms WHERE COALESCE(name, room_name) = 'Cinema Central Landmark - IMAX');

INSERT INTO seat_prices (seat_type, price_multiplier, description)
SELECT 'standard', 1.00, 'Ghế thường' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM seat_prices WHERE seat_type = 'standard');

INSERT INTO seat_prices (seat_type, price_multiplier, description)
SELECT 'vip', 1.30, 'Ghế VIP' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM seat_prices WHERE seat_type = 'vip');

INSERT INTO seat_prices (seat_type, price_multiplier, description)
SELECT 'couple', 1.80, 'Ghế đôi' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM seat_prices WHERE seat_type = 'couple');

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'A', 'A', 1, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(r.name, r.room_name) = 'Cinema Central Lê Lợi - Rạp 1'
  AND NOT EXISTS (
    SELECT 1 FROM seats s
    WHERE s.room_id = r.room_id AND COALESCE(s.row_name, s.seat_row) = 'A' AND s.seat_number = 1
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'A', 'A', 2, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(r.name, r.room_name) = 'Cinema Central Lê Lợi - Rạp 1'
  AND NOT EXISTS (
    SELECT 1 FROM seats s
    WHERE s.room_id = r.room_id AND COALESCE(s.row_name, s.seat_row) = 'A' AND s.seat_number = 2
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'A', 'A', 3, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(r.name, r.room_name) = 'Cinema Central Lê Lợi - Rạp 1'
  AND NOT EXISTS (
    SELECT 1 FROM seats s
    WHERE s.room_id = r.room_id AND COALESCE(s.row_name, s.seat_row) = 'A' AND s.seat_number = 3
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'A', 'A', 4, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(r.name, r.room_name) = 'Cinema Central Lê Lợi - Rạp 1'
  AND NOT EXISTS (
    SELECT 1 FROM seats s
    WHERE s.room_id = r.room_id AND COALESCE(s.row_name, s.seat_row) = 'A' AND s.seat_number = 4
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'A', 'A', 5, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(r.name, r.room_name) = 'Cinema Central Lê Lợi - Rạp 1'
  AND NOT EXISTS (
    SELECT 1 FROM seats s
    WHERE s.room_id = r.room_id AND COALESCE(s.row_name, s.seat_row) = 'A' AND s.seat_number = 5
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'B', 'B', 1, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(r.name, r.room_name) = 'Cinema Central Lê Lợi - Rạp 1'
  AND NOT EXISTS (
    SELECT 1 FROM seats s
    WHERE s.room_id = r.room_id AND COALESCE(s.row_name, s.seat_row) = 'B' AND s.seat_number = 1
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'B', 'B', 2, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(r.name, r.room_name) = 'Cinema Central Lê Lợi - Rạp 1'
  AND NOT EXISTS (
    SELECT 1 FROM seats s
    WHERE s.room_id = r.room_id AND COALESCE(s.row_name, s.seat_row) = 'B' AND s.seat_number = 2
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'B', 'B', 3, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(r.name, r.room_name) = 'Cinema Central Lê Lợi - Rạp 1'
  AND NOT EXISTS (
    SELECT 1 FROM seats s
    WHERE s.room_id = r.room_id AND COALESCE(s.row_name, s.seat_row) = 'B' AND s.seat_number = 3
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'C', 'C', 1, 'vip', 'vip', 1
FROM rooms r
WHERE COALESCE(r.name, r.room_name) = 'Cinema Central Lê Lợi - Rạp 1'
  AND NOT EXISTS (
    SELECT 1 FROM seats s
    WHERE s.room_id = r.room_id AND COALESCE(s.row_name, s.seat_row) = 'C' AND s.seat_number = 1
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'C', 'C', 2, 'vip', 'vip', 1
FROM rooms r
WHERE COALESCE(r.name, r.room_name) = 'Cinema Central Lê Lợi - Rạp 1'
  AND NOT EXISTS (
    SELECT 1 FROM seats s
    WHERE s.room_id = r.room_id AND COALESCE(s.row_name, s.seat_row) = 'C' AND s.seat_number = 2
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'A', 'A', 1, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(r.name, r.room_name) = 'Cinema Central Lê Lợi - Rạp 2'
  AND NOT EXISTS (
    SELECT 1 FROM seats s
    WHERE s.room_id = r.room_id AND COALESCE(s.row_name, s.seat_row) = 'A' AND s.seat_number = 1
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'A', 'A', 2, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(r.name, r.room_name) = 'Cinema Central Lê Lợi - Rạp 2'
  AND NOT EXISTS (
    SELECT 1 FROM seats s
    WHERE s.room_id = r.room_id AND COALESCE(s.row_name, s.seat_row) = 'A' AND s.seat_number = 2
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'B', 'B', 1, 'vip', 'vip', 1
FROM rooms r
WHERE COALESCE(r.name, r.room_name) = 'Cinema Central Lê Lợi - Rạp 2'
  AND NOT EXISTS (
    SELECT 1 FROM seats s
    WHERE s.room_id = r.room_id AND COALESCE(s.row_name, s.seat_row) = 'B' AND s.seat_number = 1
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'A', 'A', 1, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(r.name, r.room_name) = 'Cinema Central Landmark - IMAX'
  AND NOT EXISTS (
    SELECT 1 FROM seats s
    WHERE s.room_id = r.room_id AND COALESCE(s.row_name, s.seat_row) = 'A' AND s.seat_number = 1
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'A', 'A', 2, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(r.name, r.room_name) = 'Cinema Central Landmark - IMAX'
  AND NOT EXISTS (
    SELECT 1 FROM seats s
    WHERE s.room_id = r.room_id AND COALESCE(s.row_name, s.seat_row) = 'A' AND s.seat_number = 2
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'C', 'C', 1, 'vip', 'vip', 1
FROM rooms r
WHERE COALESCE(r.name, r.room_name) = 'Cinema Central Landmark - IMAX'
  AND NOT EXISTS (
    SELECT 1 FROM seats s
    WHERE s.room_id = r.room_id AND COALESCE(s.row_name, s.seat_row) = 'C' AND s.seat_number = 1
  );

INSERT INTO showtimes (movie_id, room_id, show_date, start_time, end_time, price, base_price, status)
SELECT m.movie_id, r.room_id, CURDATE() + INTERVAL 1 DAY, '18:00:00', '20:18:00', 90000, 90000, 1
FROM movies m
JOIN rooms r ON COALESCE(r.name, r.room_name) = 'Cinema Central Lê Lợi - Rạp 1'
WHERE m.title = 'Lật Mặt 7: Một Điều Ước'
  AND NOT EXISTS (
    SELECT 1 FROM showtimes s
    WHERE s.movie_id = m.movie_id AND s.room_id = r.room_id AND s.show_date = CURDATE() + INTERVAL 1 DAY AND s.start_time = '18:00:00'
  );

INSERT INTO showtimes (movie_id, room_id, show_date, start_time, end_time, price, base_price, status)
SELECT m.movie_id, r.room_id, CURDATE() + INTERVAL 2 DAY, '20:00:00', '22:18:00', 100000, 100000, 1
FROM movies m
JOIN rooms r ON COALESCE(r.name, r.room_name) = 'Cinema Central Lê Lợi - Rạp 2'
WHERE m.title = 'Lật Mặt 7: Một Điều Ước'
  AND NOT EXISTS (
    SELECT 1 FROM showtimes s
    WHERE s.movie_id = m.movie_id AND s.room_id = r.room_id AND s.show_date = CURDATE() + INTERVAL 2 DAY AND s.start_time = '20:00:00'
  );

INSERT INTO showtimes (movie_id, room_id, show_date, start_time, end_time, price, base_price, status)
SELECT m.movie_id, r.room_id, CURDATE() + INTERVAL 5 DAY, '19:00:00', '21:11:00', 95000, 95000, 1
FROM movies m
JOIN rooms r ON COALESCE(r.name, r.room_name) = 'Cinema Central Lê Lợi - Rạp 1'
WHERE m.title = 'Mai'
  AND NOT EXISTS (
    SELECT 1 FROM showtimes s
    WHERE s.movie_id = m.movie_id AND s.room_id = r.room_id AND s.show_date = CURDATE() + INTERVAL 5 DAY AND s.start_time = '19:00:00'
  );

INSERT INTO showtimes (movie_id, room_id, show_date, start_time, end_time, price, base_price, status)
SELECT m.movie_id, r.room_id, CURDATE() + INTERVAL 1 DAY, '21:00:00', '22:55:00', 120000, 120000, 1
FROM movies m
JOIN rooms r ON COALESCE(r.name, r.room_name) = 'Cinema Central Landmark - IMAX'
WHERE m.title = 'Godzilla x Kong: Đế Chế Mới'
  AND NOT EXISTS (
    SELECT 1 FROM showtimes s
    WHERE s.movie_id = m.movie_id AND s.room_id = r.room_id AND s.show_date = CURDATE() + INTERVAL 1 DAY AND s.start_time = '21:00:00'
  );

INSERT INTO promotions (code, promo_code, title, discount_type, discount_value, min_order_amount, min_amount, min_tickets, max_discount, usage_limit, used_count, budget, description, applicable_seat_types, start_date, end_date, status)
SELECT 'WELCOME10', 'WELCOME10', 'Giảm 10% cho khách mới', 'percent', 10, 100000, 100000, 1, 50000, 500, 0, 5000000, 'Áp dụng cho khách hàng mới và đơn hàng từ 100.000đ', JSON_ARRAY('standard','vip','couple'), NOW() - INTERVAL 7 DAY, NOW() + INTERVAL 30 DAY, 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM promotions WHERE COALESCE(code, promo_code) = 'WELCOME10');

INSERT INTO promotions (code, promo_code, title, discount_type, discount_value, min_order_amount, min_amount, min_tickets, max_discount, usage_limit, used_count, budget, description, applicable_seat_types, start_date, end_date, status)
SELECT 'GIAM50K', 'GIAM50K', 'Giảm 50.000đ cuối tuần', 'fixed', 50000, 200000, 200000, 2, 50000, 100, 0, 3000000, 'Áp dụng cho đơn từ 200.000đ', JSON_ARRAY('standard','vip','couple'), NOW() - INTERVAL 7 DAY, NOW() + INTERVAL 15 DAY, 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM promotions WHERE COALESCE(code, promo_code) = 'GIAM50K');

INSERT INTO orders (user_id, promotion_id, order_code, order_date, total_amount, discount_amount, final_amount, payment_method, payment_status, order_status, notes)
SELECT u.user_id, p.promotion_id, 'ORD1001', NOW() - INTERVAL 2 DAY, 180000, 18000, 162000, 'momo', 'paid', 'completed', 'Đơn mẫu đã duyệt'
FROM users u
LEFT JOIN promotions p ON COALESCE(p.code, p.promo_code) = 'WELCOME10'
WHERE u.email = 'mai.phuong@gmail.com'
  AND NOT EXISTS (SELECT 1 FROM orders WHERE order_code = 'ORD1001');

INSERT INTO orders (user_id, promotion_id, order_code, order_date, total_amount, discount_amount, final_amount, payment_method, payment_status, order_status, notes)
SELECT u.user_id, NULL, 'ORD1002', NOW() - INTERVAL 1 DAY, 90000, 0, 90000, 'cash', 'pending', 'pending', 'Đơn mẫu chờ duyệt'
FROM users u
WHERE u.email = 'ngoctu@gmail.com'
  AND NOT EXISTS (SELECT 1 FROM orders WHERE order_code = 'ORD1002');

INSERT INTO tickets (order_id, showtime_id, seat_id, price, ticket_status)
SELECT o.order_id, s.showtime_id, st.seat_id, 90000, 'paid'
FROM orders o
JOIN showtimes s ON s.movie_id = (SELECT movie_id FROM movies WHERE title = 'Lật Mặt 7: Một Điều Ước' LIMIT 1)
JOIN rooms r ON r.room_id = s.room_id AND COALESCE(r.name, r.room_name) = 'Cinema Central Lê Lợi - Rạp 1'
JOIN seats st ON st.room_id = r.room_id AND COALESCE(st.row_name, st.seat_row) = 'A' AND st.seat_number = 1
WHERE o.order_code = 'ORD1001'
  AND NOT EXISTS (SELECT 1 FROM tickets t WHERE t.order_id = o.order_id AND t.showtime_id = s.showtime_id AND t.seat_id = st.seat_id)
LIMIT 1;

INSERT INTO tickets (order_id, showtime_id, seat_id, price, ticket_status)
SELECT o.order_id, s.showtime_id, st.seat_id, 90000, 'paid'
FROM orders o
JOIN showtimes s ON s.movie_id = (SELECT movie_id FROM movies WHERE title = 'Lật Mặt 7: Một Điều Ước' LIMIT 1)
JOIN rooms r ON r.room_id = s.room_id AND COALESCE(r.name, r.room_name) = 'Cinema Central Lê Lợi - Rạp 1'
JOIN seats st ON st.room_id = r.room_id AND COALESCE(st.row_name, st.seat_row) = 'A' AND st.seat_number = 2
WHERE o.order_code = 'ORD1001'
  AND NOT EXISTS (SELECT 1 FROM tickets t WHERE t.order_id = o.order_id AND t.showtime_id = s.showtime_id AND t.seat_id = st.seat_id)
LIMIT 1;

INSERT INTO tickets (order_id, showtime_id, seat_id, price, ticket_status)
SELECT o.order_id, s.showtime_id, st.seat_id, 90000, 'reserved'
FROM orders o
JOIN showtimes s ON s.movie_id = (SELECT movie_id FROM movies WHERE title = 'Lật Mặt 7: Một Điều Ước' LIMIT 1) AND s.room_id = (SELECT room_id FROM rooms WHERE COALESCE(name, room_name) = 'Cinema Central Lê Lợi - Rạp 2' LIMIT 1)
JOIN rooms r ON r.room_id = s.room_id
JOIN seats st ON st.room_id = r.room_id AND COALESCE(st.row_name, st.seat_row) = 'A' AND st.seat_number = 1
WHERE o.order_code = 'ORD1002'
  AND NOT EXISTS (SELECT 1 FROM tickets t WHERE t.order_id = o.order_id AND t.showtime_id = s.showtime_id AND t.seat_id = st.seat_id)
LIMIT 1;

INSERT INTO payments (order_id, payment_method, amount_paid, payment_status)
SELECT o.order_id, 'momo', 162000, 'success'
FROM orders o
WHERE o.order_code = 'ORD1001'
  AND NOT EXISTS (SELECT 1 FROM payments p WHERE p.order_id = o.order_id);

INSERT INTO payments (order_id, payment_method, amount_paid, payment_status)
SELECT o.order_id, 'cash', 90000, 'pending'
FROM orders o
WHERE o.order_code = 'ORD1002'
  AND NOT EXISTS (SELECT 1 FROM payments p WHERE p.order_id = o.order_id);

UPDATE rooms SET name = COALESCE(NULLIF(name, ''), room_name), room_name = COALESCE(NULLIF(room_name, ''), name);
UPDATE seats SET row_name = COALESCE(NULLIF(row_name, ''), seat_row), seat_row = COALESCE(NULLIF(seat_row, ''), row_name);
UPDATE seats
SET type = CASE
    WHEN LOWER(COALESCE(type, seat_type, 'standard')) IN ('1', 'standard') THEN 'standard'
    WHEN LOWER(COALESCE(type, seat_type, 'standard')) IN ('2', 'vip') THEN 'vip'
    WHEN LOWER(COALESCE(type, seat_type, 'standard')) IN ('3', 'couple') THEN 'couple'
    ELSE 'standard'
END,
seat_type = CASE
    WHEN LOWER(COALESCE(seat_type, type, 'standard')) IN ('1', 'standard') THEN 'standard'
    WHEN LOWER(COALESCE(seat_type, type, 'standard')) IN ('2', 'vip') THEN 'vip'
    WHEN LOWER(COALESCE(seat_type, type, 'standard')) IN ('3', 'couple') THEN 'couple'
    ELSE 'standard'
END;
UPDATE promotions SET code = COALESCE(NULLIF(code, ''), promo_code), promo_code = COALESCE(NULLIF(promo_code, ''), code), title = COALESCE(NULLIF(title, ''), code, promo_code, 'Khuyến mãi');
UPDATE showtimes SET base_price = CASE WHEN base_price IS NULL OR base_price = 0 THEN price ELSE base_price END;
UPDATE orders o
LEFT JOIN payments p ON p.order_id = o.order_id
SET o.payment_status = CASE
        WHEN p.payment_status = 'success' THEN 'paid'
        WHEN p.payment_status IS NOT NULL THEN p.payment_status
        ELSE o.payment_status
    END,
    o.order_status = CASE
        WHEN o.order_status = 'paid' THEN 'completed'
        ELSE o.order_status
    END;

SET @schema_name := DATABASE();

SET @fk_count := (
    SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = @schema_name AND TABLE_NAME = 'seats' AND CONSTRAINT_NAME = 'fk_seat_room'
);
SET @sql := IF(@fk_count = 0,
    'ALTER TABLE seats ADD CONSTRAINT fk_seat_room FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE CASCADE',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk_count := (
    SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = @schema_name AND TABLE_NAME = 'showtimes' AND CONSTRAINT_NAME = 'fk_showtime_movie'
);
SET @sql := IF(@fk_count = 0,
    'ALTER TABLE showtimes ADD CONSTRAINT fk_showtime_movie FOREIGN KEY (movie_id) REFERENCES movies(movie_id) ON DELETE CASCADE',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk_count := (
    SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = @schema_name AND TABLE_NAME = 'showtimes' AND CONSTRAINT_NAME = 'fk_showtime_room'
);
SET @sql := IF(@fk_count = 0,
    'ALTER TABLE showtimes ADD CONSTRAINT fk_showtime_room FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE CASCADE',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk_count := (
    SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = @schema_name AND TABLE_NAME = 'orders' AND CONSTRAINT_NAME = 'fk_order_user'
);
SET @sql := IF(@fk_count = 0,
    'ALTER TABLE orders ADD CONSTRAINT fk_order_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk_count := (
    SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = @schema_name AND TABLE_NAME = 'orders' AND CONSTRAINT_NAME = 'fk_order_promotion'
);
SET @sql := IF(@fk_count = 0,
    'ALTER TABLE orders ADD CONSTRAINT fk_order_promotion FOREIGN KEY (promotion_id) REFERENCES promotions(promotion_id) ON DELETE SET NULL',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk_count := (
    SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = @schema_name AND TABLE_NAME = 'tickets' AND CONSTRAINT_NAME = 'fk_ticket_order'
);
SET @sql := IF(@fk_count = 0,
    'ALTER TABLE tickets ADD CONSTRAINT fk_ticket_order FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk_count := (
    SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = @schema_name AND TABLE_NAME = 'tickets' AND CONSTRAINT_NAME = 'fk_ticket_showtime'
);
SET @sql := IF(@fk_count = 0,
    'ALTER TABLE tickets ADD CONSTRAINT fk_ticket_showtime FOREIGN KEY (showtime_id) REFERENCES showtimes(showtime_id) ON DELETE CASCADE',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk_count := (
    SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = @schema_name AND TABLE_NAME = 'tickets' AND CONSTRAINT_NAME = 'fk_ticket_seat'
);
SET @sql := IF(@fk_count = 0,
    'ALTER TABLE tickets ADD CONSTRAINT fk_ticket_seat FOREIGN KEY (seat_id) REFERENCES seats(seat_id) ON DELETE CASCADE',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk_count := (
    SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = @schema_name AND TABLE_NAME = 'payments' AND CONSTRAINT_NAME = 'fk_payment_order'
);
SET @sql := IF(@fk_count = 0,
    'ALTER TABLE payments ADD CONSTRAINT fk_payment_order FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk_count := (
    SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = @schema_name AND TABLE_NAME = 'cancellation_requests' AND CONSTRAINT_NAME = 'fk_cancel_order'
);
SET @sql := IF(@fk_count = 0,
    'ALTER TABLE cancellation_requests ADD CONSTRAINT fk_cancel_order FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk_count := (
    SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = @schema_name AND TABLE_NAME = 'cancellation_requests' AND CONSTRAINT_NAME = 'fk_cancel_user'
);
SET @sql := IF(@fk_count = 0,
    'ALTER TABLE cancellation_requests ADD CONSTRAINT fk_cancel_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
