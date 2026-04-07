CREATE DATABASE IF NOT EXISTS movie_booking;
USE movie_booking;

-- Users
CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    birthday DATE DEFAULT NULL,
    address VARCHAR(255) DEFAULT NULL,
    role ENUM('customer','staff','admin') NOT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Employees
CREATE TABLE Employees (
    employee_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    manager_id INT,
    employee_code VARCHAR(20) UNIQUE NOT NULL,
    position VARCHAR(50) NOT NULL,
    status ENUM('working','resigned') DEFAULT 'working',

    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (manager_id) REFERENCES Employees(employee_id)
) ENGINE=InnoDB;

-- Movies
CREATE TABLE Movies (
    movie_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    genre VARCHAR(100) NOT NULL,
    duration INT NOT NULL,
    release_date DATE,
    status ENUM('showing','coming_soon','stopped') DEFAULT 'showing',
    description TEXT,
    poster_url VARCHAR(255),
    trailer_url VARCHAR(255)
) ENGINE=InnoDB;

-- Rooms
CREATE TABLE Rooms (
    room_id INT AUTO_INCREMENT PRIMARY KEY,
    room_name VARCHAR(50) UNIQUE NOT NULL,
    capacity INT NOT NULL
) ENGINE=InnoDB;

-- Seats
CREATE TABLE Seats (
    seat_id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT,
    seat_row CHAR(2) NOT NULL,
    seat_number INT NOT NULL,
    seat_type ENUM('standard','vip','couple') DEFAULT 'standard',

    UNIQUE(room_id, seat_row, seat_number),
    FOREIGN KEY (room_id) REFERENCES Rooms(room_id)
) ENGINE=InnoDB;

-- Showtimes
CREATE TABLE Showtimes (
    showtime_id INT AUTO_INCREMENT PRIMARY KEY,
    movie_id INT,
    room_id INT,
    show_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    base_price DECIMAL(10,2) NOT NULL,

    FOREIGN KEY (movie_id) REFERENCES Movies(movie_id),
    FOREIGN KEY (room_id) REFERENCES Rooms(room_id)
) ENGINE=InnoDB;

-- Promotions
CREATE TABLE Promotions (
    promotion_id INT AUTO_INCREMENT PRIMARY KEY,
    promo_code VARCHAR(30) UNIQUE NOT NULL,
    discount_type ENUM('percent','fixed') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    description TEXT,
    min_tickets INT DEFAULT 1,
    min_amount DECIMAL(12,2) DEFAULT 0,
    applicable_seat_types JSON,
    start_date DATETIME,
    end_date DATETIME
) ENGINE=InnoDB;

-- Orders
CREATE TABLE Orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    promotion_id INT NULL,
    order_code VARCHAR(30) UNIQUE NOT NULL,
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(12,2) NOT NULL,
    discount_amount DECIMAL(12,2) DEFAULT 0,
    final_amount DECIMAL(12,2) NOT NULL,
    order_status ENUM('pending','paid','cancelled') DEFAULT 'pending',

    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (promotion_id) REFERENCES Promotions(promotion_id)
) ENGINE=InnoDB;

-- Tickets
CREATE TABLE Tickets (
    ticket_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    showtime_id INT,
    seat_id INT,
    price DECIMAL(10,2) NOT NULL,
    ticket_status ENUM('reserved','paid','cancelled') DEFAULT 'reserved',

    UNIQUE(showtime_id, seat_id),
    FOREIGN KEY (order_id) REFERENCES Orders(order_id),
    FOREIGN KEY (showtime_id) REFERENCES Showtimes(showtime_id),
    FOREIGN KEY (seat_id) REFERENCES Seats(seat_id)
) ENGINE=InnoDB;

-- Payments
CREATE TABLE Payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNIQUE,
    payment_method ENUM('cash','bank_transfer','momo','zalopay','vnpay') NOT NULL,
    amount_paid DECIMAL(12,2) NOT NULL,
    payment_status ENUM('pending','success','failed') DEFAULT 'pending',

    FOREIGN KEY (order_id) REFERENCES Orders(order_id)
) ENGINE=InnoDB;

-- CancellationRequests
CREATE TABLE CancellationRequests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    user_id INT,
    reason TEXT NOT NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    request_date DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (order_id) REFERENCES Orders(order_id),
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
) ENGINE=InnoDB;

-- Reports
CREATE TABLE Reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    report_type ENUM('revenue','ticket','customer') NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    employee_id INT,

    FOREIGN KEY (employee_id) REFERENCES Employees(employee_id)
) ENGINE=InnoDB;