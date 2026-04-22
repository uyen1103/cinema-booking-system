USE movie_booking;
SET NAMES utf8mb4;
SET time_zone = '+07:00';


/* ==========================================================
   FULL SAMPLE DATA CHO TOÀN BỘ CHỨC NĂNG USER + ADMIN
   - Không rút gọn các chức năng đã có
   - Đủ dữ liệu để chạy thử danh sách, chi tiết, lọc, báo cáo, duyệt hủy vé
   - Các câu lệnh đều theo hướng idempotent để hạn chế chèn trùng dữ liệu
   ========================================================== */


/* ---------- 1. TÀI KHOẢN KHÁCH HÀNG VÀ NHÂN SỰ ---------- */

INSERT INTO customers (full_name, email, password, phone, birthday, address, avatar, bank_account, e_wallet_account, status)
SELECT 'Test Customer', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0123456789', '1990-01-01', 'Quận 1, TP.HCM', 'assets/images/default-avatar.svg', 'VCB-001-999999', 'MOMO-TEST-001', 'active'
WHERE NOT EXISTS (SELECT 1 FROM customers WHERE email = 'test@example.com');

INSERT INTO customers (full_name, email, password, phone, birthday, address, avatar, bank_account, e_wallet_account, status)
SELECT 'Nguyễn Hoàng An', 'nguyenan@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000001', '1997-04-12', 'Quận 3, TP.HCM', 'assets/images/default-avatar.svg', 'VCB-001-100001', 'MOMO-AN-001', 'active'
WHERE NOT EXISTS (SELECT 1 FROM customers WHERE email = 'nguyenan@example.com');

INSERT INTO customers (full_name, email, password, phone, birthday, address, avatar, bank_account, e_wallet_account, status)
SELECT 'Trần Bình Minh', 'tranbinh@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000002', '1998-09-05', 'Quận 7, TP.HCM', 'assets/images/default-avatar.svg', 'ACB-002-100002', 'ZALOPAY-BINH-002', 'active'
WHERE NOT EXISTS (SELECT 1 FROM customers WHERE email = 'tranbinh@example.com');

INSERT INTO customers (full_name, email, password, phone, birthday, address, avatar, bank_account, e_wallet_account, status)
SELECT 'Lê Chi', 'lechi@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000003', '1999-02-18', 'Quận Bình Thạnh, TP.HCM', 'assets/images/default-avatar.svg', 'TCB-003-100003', 'MOMO-CHI-003', 'active'
WHERE NOT EXISTS (SELECT 1 FROM customers WHERE email = 'lechi@example.com');

INSERT INTO customers (full_name, email, password, phone, birthday, address, avatar, bank_account, e_wallet_account, status)
SELECT 'Phạm Mai', 'phammai@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000004', '1995-06-21', 'TP. Thủ Đức, TP.HCM', 'assets/images/default-avatar.svg', 'BIDV-004-100004', 'MOMO-MAI-004', 'active'
WHERE NOT EXISTS (SELECT 1 FROM customers WHERE email = 'phammai@example.com');

INSERT INTO customers (full_name, email, password, phone, birthday, address, avatar, bank_account, e_wallet_account, status)
SELECT 'Đỗ Quang', 'doquang@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000005', '1994-11-03', 'Quận Gò Vấp, TP.HCM', 'assets/images/default-avatar.svg', 'VCB-005-100005', 'ZALOPAY-QUANG-005', 'active'
WHERE NOT EXISTS (SELECT 1 FROM customers WHERE email = 'doquang@example.com');

INSERT INTO customers (full_name, email, password, phone, birthday, address, avatar, bank_account, e_wallet_account, status)
SELECT 'Ngô Ngọc Lan', 'ngoclan@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000006', '2000-01-14', 'Quận 5, TP.HCM', 'assets/images/default-avatar.svg', 'SAC-006-100006', 'MOMO-LAN-006', 'active'
WHERE NOT EXISTS (SELECT 1 FROM customers WHERE email = 'ngoclan@example.com');

INSERT INTO customers (full_name, email, password, phone, birthday, address, avatar, bank_account, e_wallet_account, status)
SELECT 'Trần Thư Thảo', 'thuthao@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000007', '1996-08-09', 'Quận 10, TP.HCM', 'assets/images/default-avatar.svg', 'VCB-007-100007', 'MOMO-THAO-007', 'active'
WHERE NOT EXISTS (SELECT 1 FROM customers WHERE email = 'thuthao@example.com');

INSERT INTO customers (full_name, email, password, phone, birthday, address, avatar, bank_account, e_wallet_account, status)
SELECT 'Nguyễn Minh Khang', 'minhkhang@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000008', '1993-03-28', 'Quận Phú Nhuận, TP.HCM', 'assets/images/default-avatar.svg', 'TCB-008-100008', 'ZALOPAY-KHANG-008', 'active'
WHERE NOT EXISTS (SELECT 1 FROM customers WHERE email = 'minhkhang@example.com');

INSERT INTO customers (full_name, email, password, phone, birthday, address, avatar, bank_account, e_wallet_account, status)
SELECT 'Tài khoản Khóa Mẫu', 'locked_customer@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000009', '1992-12-30', 'Quận Tân Bình, TP.HCM', 'assets/images/default-avatar.svg', 'VCB-009-100009', 'MOMO-LOCK-009', 'inactive'
WHERE NOT EXISTS (SELECT 1 FROM customers WHERE email = 'locked_customer@example.com');

INSERT INTO employees (full_name, email, password, phone, position, branch_name, hire_date, role, status, avatar)
SELECT 'Admin User', 'admin@cinemacentral.vn', '$2y$12$MswZ/nWFVAE0uU6fqAS4Fuu9ovYIA.fIb7VDY9nzF3kr3HBHHJxkK', '0900000000', 'Quản trị hệ thống', 'Cinema Central Lê Lợi', CURDATE(), 'admin', 'working', 'assets/images/default-avatar.svg'
WHERE NOT EXISTS (SELECT 1 FROM employees WHERE email = 'admin@cinemacentral.vn');

INSERT INTO employees (full_name, email, password, phone, position, branch_name, hire_date, role, status, avatar)
SELECT 'Nhân viên quầy vé', 'staff@cinemacentral.vn', '$2y$12$MswZ/nWFVAE0uU6fqAS4Fuu9ovYIA.fIb7VDY9nzF3kr3HBHHJxkK', '0911111111', 'Nhân viên bán vé', 'Cinema Central Lê Lợi', CURDATE(), 'staff', 'working', 'assets/images/default-avatar.svg'
WHERE NOT EXISTS (SELECT 1 FROM employees WHERE email = 'staff@cinemacentral.vn');

INSERT INTO employees (full_name, email, password, phone, position, branch_name, hire_date, role, status, avatar)
SELECT 'Nguyễn Thu Hà', 'operations@cinemacentral.vn', '$2y$12$MswZ/nWFVAE0uU6fqAS4Fuu9ovYIA.fIb7VDY9nzF3kr3HBHHJxkK', '0911111112', 'Điều phối vận hành', 'Cinema Central Nguyễn Du', '2023-08-10', 'staff', 'working', 'assets/images/default-avatar.svg'
WHERE NOT EXISTS (SELECT 1 FROM employees WHERE email = 'operations@cinemacentral.vn');

INSERT INTO employees (full_name, email, password, phone, position, branch_name, hire_date, role, status, avatar)
SELECT 'Lê Quốc Bảo', 'ticketdesk@cinemacentral.vn', '$2y$12$MswZ/nWFVAE0uU6fqAS4Fuu9ovYIA.fIb7VDY9nzF3kr3HBHHJxkK', '0911111113', 'Nhân viên kiểm vé', 'Cinema Central Đồng Khởi', '2024-01-15', 'staff', 'working', 'assets/images/default-avatar.svg'
WHERE NOT EXISTS (SELECT 1 FROM employees WHERE email = 'ticketdesk@cinemacentral.vn');

INSERT INTO employees (full_name, email, password, phone, position, branch_name, hire_date, role, status, avatar)
SELECT 'Trần Vũ Dũng', 'resigned@cinemacentral.vn', '$2y$12$MswZ/nWFVAE0uU6fqAS4Fuu9ovYIA.fIb7VDY9nzF3kr3HBHHJxkK', '0911111114', 'Nhân viên quầy vé', 'Cinema Central Lê Lợi', '2022-06-01', 'staff', 'resigned', 'assets/images/default-avatar.svg'
WHERE NOT EXISTS (SELECT 1 FROM employees WHERE email = 'resigned@cinemacentral.vn');

/* Mirror sang users để giữ tương thích cho phần code legacy còn sót lại */
INSERT INTO users (full_name, email, password, phone, birthday, address, bank_account, e_wallet_account, role, status, avatar, oauth_provider, oauth_id, created_at)
SELECT c.full_name, c.email, c.password, c.phone, c.birthday, c.address, c.bank_account, c.e_wallet_account,
       'customer', c.status, c.avatar, c.oauth_provider, c.oauth_id, COALESCE(c.created_at, CURRENT_TIMESTAMP)
FROM customers c
WHERE NOT EXISTS (SELECT 1 FROM users u WHERE u.email = c.email);

INSERT INTO users (full_name, email, password, phone, birthday, address, role, position, branch_name, hire_date, status, avatar, created_at)
SELECT e.full_name, e.email, e.password, e.phone, e.birthday, e.address, e.role, e.position, e.branch_name, e.hire_date, e.status, e.avatar, COALESCE(e.created_at, CURRENT_TIMESTAMP)
FROM employees e
WHERE NOT EXISTS (SELECT 1 FROM users u WHERE u.email = e.email);


/* ---------- 2. GIÁ GHẾ ---------- */
INSERT INTO seat_prices (seat_type, price_multiplier, description) SELECT 'standard', 1.00, 'Ghế thường' WHERE NOT EXISTS (SELECT 1 FROM seat_prices WHERE seat_type = 'standard');
INSERT INTO seat_prices (seat_type, price_multiplier, description) SELECT 'vip', 1.30, 'Ghế VIP' WHERE NOT EXISTS (SELECT 1 FROM seat_prices WHERE seat_type = 'vip');
INSERT INTO seat_prices (seat_type, price_multiplier, description) SELECT 'couple', 1.60, 'Ghế đôi' WHERE NOT EXISTS (SELECT 1 FROM seat_prices WHERE seat_type = 'couple');

/* ---------- 3. PHIM MẪU ---------- */

INSERT INTO movies (title, description, director, `cast`, genre, duration, release_date, poster, poster_url, banner, trailer_url, status)
SELECT 'Bố Già', 'Câu chuyện về gia đình Ba Sang và những xung đột giữa các thế hệ trong đời sống đô thị hiện đại.', 'Trấn Thành', 'Trấn Thành, Tuấn Trần, Ngân Chi', 'Drama', 128, '2021-03-12', 'bo_gia.jpg', 'bo_gia.jpg', 'bo_gia.jpg', 'https://www.youtube.com/embed/jluSu8Rw6YE', '1'
WHERE NOT EXISTS (SELECT 1 FROM movies WHERE title = 'Bố Già');

INSERT INTO movies (title, description, director, `cast`, genre, duration, release_date, poster, poster_url, banner, trailer_url, status)
SELECT 'Hai Phượng (Furie)', 'Hành trình giải cứu con gái của một người mẹ trước đường dây bắt cóc xuyên quốc gia.', 'Lê Văn Kiệt', 'Ngô Thanh Vân', 'Action', 98, '2019-02-22', 'hai_phuong.jpg', 'hai_phuong.jpg', 'hai_phuong.jpg', 'https://www.youtube.com/embed/L41c4_kbDoo', '1'
WHERE NOT EXISTS (SELECT 1 FROM movies WHERE title = 'Hai Phượng (Furie)');

INSERT INTO movies (title, description, director, `cast`, genre, duration, release_date, poster, poster_url, banner, trailer_url, status)
SELECT 'Mắt Biếc', 'Bộ phim chuyển thể từ tác phẩm nổi tiếng của Nguyễn Nhật Ánh về chuyện tình đơn phương.', 'Victor Vũ', 'Trần Nghĩa, Trúc Anh', 'Romance', 117, '2019-12-20', 'mat_biec.jpg', 'mat_biec.jpg', 'mat_biec.jpg', 'https://www.youtube.com/embed/ITlQ0oU7tDA', '1'
WHERE NOT EXISTS (SELECT 1 FROM movies WHERE title = 'Mắt Biếc');

INSERT INTO movies (title, description, director, `cast`, genre, duration, release_date, poster, poster_url, banner, trailer_url, status)
SELECT 'Nhà Bà Nữ', 'Câu chuyện gia đình với nhiều mâu thuẫn và góc nhìn khác nhau về trách nhiệm, tình thân và sự tha thứ.', 'Trấn Thành', 'Lê Giang, Uyển Ân, Song Luân', 'Drama', 102, '2023-01-22', 'nha_ba_nu.jpg', 'nha_ba_nu.jpg', 'nha_ba_nu.jpg', 'https://www.youtube.com/embed/IkaP0KJWTsQ', '1'
WHERE NOT EXISTS (SELECT 1 FROM movies WHERE title = 'Nhà Bà Nữ');

INSERT INTO movies (title, description, director, `cast`, genre, duration, release_date, poster, poster_url, banner, trailer_url, status)
SELECT 'Avengers: Endgame', 'Biệt đội Avengers tập hợp lần cuối để đảo ngược hậu quả sau cú búng tay của Thanos.', 'Anthony Russo, Joe Russo', 'Robert Downey Jr., Chris Evans, Mark Ruffalo', 'Action', 181, '2019-04-26', 'endgame.jpg', 'endgame.jpg', 'endgame.jpg', 'https://www.youtube.com/embed/TcMBFSGVi1c', '1'
WHERE NOT EXISTS (SELECT 1 FROM movies WHERE title = 'Avengers: Endgame');

INSERT INTO movies (title, description, director, `cast`, genre, duration, release_date, poster, poster_url, banner, trailer_url, status)
SELECT 'Deadpool & Wolverine', 'Deadpool và Wolverine hợp tác trong một cuộc phiêu lưu hành động pha hài đen.', 'Shawn Levy', 'Ryan Reynolds, Hugh Jackman', 'Action', 122, '2026-05-10', 'deadpool_wolverine.jpg', 'deadpool_wolverine.jpg', 'deadpool_wolverine.jpg', 'https://www.youtube.com/embed/73_1biulkYk', '2'
WHERE NOT EXISTS (SELECT 1 FROM movies WHERE title = 'Deadpool & Wolverine');

INSERT INTO movies (title, description, director, `cast`, genre, duration, release_date, poster, poster_url, banner, trailer_url, status)
SELECT 'Joker: Folie à Deux', 'Phần tiếp theo của Joker với màu sắc tâm lý đen tối và nhiều xung đột nội tâm.', 'Todd Phillips', 'Joaquin Phoenix, Lady Gaga', 'Thriller', 135, '2026-10-04', 'joker_folie.jpg', 'joker_folie.jpg', 'joker_folie.jpg', 'https://www.youtube.com/embed/_OKAwz2MsJs', '2'
WHERE NOT EXISTS (SELECT 1 FROM movies WHERE title = 'Joker: Folie à Deux');

INSERT INTO movies (title, description, director, `cast`, genre, duration, release_date, poster, poster_url, banner, trailer_url, status)
SELECT 'Lật Mặt 7', 'Bộ phim gia đình với chủ đề tình thân và sự hy sinh giữa các thế hệ.', 'Lý Hải', 'Lý Hải, Minh Hà', 'Drama', 130, '2024-04-26', 'lat-mat-7.jpg', 'lat-mat-7.jpg', 'lat-mat-7.jpg', 'https://www.youtube.com/embed/d1ZHdosjNX8', '1'
WHERE NOT EXISTS (SELECT 1 FROM movies WHERE title = 'Lật Mặt 7');

/* ---------- 4. PHÒNG CHIẾU MẪU ---------- */
INSERT IGNORE INTO rooms (name, room_name, capacity, opening_time, closing_time, status, maintenance_reason) VALUES ('CGV Vincom Đồng Khởi - Phòng IMAX 1', 'CGV Vincom Đồng Khởi - Phòng IMAX 1', 250, '08:00:00', '23:00:00', 1, NULL);
INSERT IGNORE INTO rooms (name, room_name, capacity, opening_time, closing_time, status, maintenance_reason) VALUES ('CGV Vincom Đồng Khởi - Phòng 2D 1', 'CGV Vincom Đồng Khởi - Phòng 2D 1', 150, '08:00:00', '23:00:00', 1, NULL);
INSERT IGNORE INTO rooms (name, room_name, capacity, opening_time, closing_time, status, maintenance_reason) VALUES ('Galaxy Cinema Nguyễn Du - Phòng 1', 'Galaxy Cinema Nguyễn Du - Phòng 1', 180, '08:00:00', '23:00:00', 1, NULL);
INSERT IGNORE INTO rooms (name, room_name, capacity, opening_time, closing_time, status, maintenance_reason) VALUES ('Lotte Cinema Phan Văn Trị - Phòng Deluxe', 'Lotte Cinema Phan Văn Trị - Phòng Deluxe', 160, '10:30:00', '23:30:00', 1, NULL);
INSERT IGNORE INTO rooms (name, room_name, capacity, opening_time, closing_time, status, maintenance_reason) VALUES ('Cinema Central Lê Lợi - Phòng Bảo Trì', 'Cinema Central Lê Lợi - Phòng Bảo Trì', 120, '08:00:00', '23:00:00', 0, 'Đang bảo trì hệ thống âm thanh');

/* ---------- 5. GHẾ MẪU ---------- */

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'A', 'A', 1, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng IMAX 1'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'A'
        AND s.seat_number = 1
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'A', 'A', 2, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng IMAX 1'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'A'
        AND s.seat_number = 2
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'A', 'A', 3, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng IMAX 1'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'A'
        AND s.seat_number = 3
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'A', 'A', 4, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng IMAX 1'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'A'
        AND s.seat_number = 4
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'A', 'A', 5, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng IMAX 1'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'A'
        AND s.seat_number = 5
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'B', 'B', 1, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng IMAX 1'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'B'
        AND s.seat_number = 1
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'B', 'B', 2, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng IMAX 1'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'B'
        AND s.seat_number = 2
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'B', 'B', 3, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng IMAX 1'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'B'
        AND s.seat_number = 3
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'B', 'B', 4, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng IMAX 1'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'B'
        AND s.seat_number = 4
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'B', 'B', 5, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng IMAX 1'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'B'
        AND s.seat_number = 5
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'C', 'C', 1, 'vip', 'vip', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng IMAX 1'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'C'
        AND s.seat_number = 1
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'C', 'C', 2, 'vip', 'vip', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng IMAX 1'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'C'
        AND s.seat_number = 2
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'C', 'C', 3, 'vip', 'vip', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng IMAX 1'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'C'
        AND s.seat_number = 3
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'C', 'C', 4, 'vip', 'vip', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng IMAX 1'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'C'
        AND s.seat_number = 4
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'D', 'D', 5, 'couple', 'couple', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng IMAX 1'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'D'
        AND s.seat_number = 5
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'D', 'D', 6, 'couple', 'couple', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng IMAX 1'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'D'
        AND s.seat_number = 6
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'A', 'A', 1, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng 2D 1'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'A'
        AND s.seat_number = 1
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'A', 'A', 2, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng 2D 1'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'A'
        AND s.seat_number = 2
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'A', 'A', 3, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng 2D 1'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'A'
        AND s.seat_number = 3
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'A', 'A', 4, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng 2D 1'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'A'
        AND s.seat_number = 4
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'B', 'B', 1, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng 2D 1'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'B'
        AND s.seat_number = 1
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'B', 'B', 2, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng 2D 1'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'B'
        AND s.seat_number = 2
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'B', 'B', 3, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng 2D 1'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'B'
        AND s.seat_number = 3
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'B', 'B', 4, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng 2D 1'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'B'
        AND s.seat_number = 4
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'C', 'C', 1, 'vip', 'vip', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng 2D 1'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'C'
        AND s.seat_number = 1
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'C', 'C', 2, 'vip', 'vip', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng 2D 1'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'C'
        AND s.seat_number = 2
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'A', 'A', 1, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'Galaxy Cinema Nguyễn Du - Phòng 1'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'A'
        AND s.seat_number = 1
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'A', 'A', 2, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'Galaxy Cinema Nguyễn Du - Phòng 1'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'A'
        AND s.seat_number = 2
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'A', 'A', 3, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'Galaxy Cinema Nguyễn Du - Phòng 1'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'A'
        AND s.seat_number = 3
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'A', 'A', 4, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'Galaxy Cinema Nguyễn Du - Phòng 1'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'A'
        AND s.seat_number = 4
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'B', 'B', 1, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'Galaxy Cinema Nguyễn Du - Phòng 1'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'B'
        AND s.seat_number = 1
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'B', 'B', 2, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'Galaxy Cinema Nguyễn Du - Phòng 1'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'B'
        AND s.seat_number = 2
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'B', 'B', 3, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'Galaxy Cinema Nguyễn Du - Phòng 1'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'B'
        AND s.seat_number = 3
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'B', 'B', 4, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'Galaxy Cinema Nguyễn Du - Phòng 1'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'B'
        AND s.seat_number = 4
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'C', 'C', 1, 'vip', 'vip', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'Galaxy Cinema Nguyễn Du - Phòng 1'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'C'
        AND s.seat_number = 1
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'C', 'C', 2, 'vip', 'vip', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'Galaxy Cinema Nguyễn Du - Phòng 1'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'C'
        AND s.seat_number = 2
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'A', 'A', 1, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'Lotte Cinema Phan Văn Trị - Phòng Deluxe'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'A'
        AND s.seat_number = 1
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'A', 'A', 2, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'Lotte Cinema Phan Văn Trị - Phòng Deluxe'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'A'
        AND s.seat_number = 2
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'A', 'A', 3, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'Lotte Cinema Phan Văn Trị - Phòng Deluxe'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'A'
        AND s.seat_number = 3
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'A', 'A', 4, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'Lotte Cinema Phan Văn Trị - Phòng Deluxe'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'A'
        AND s.seat_number = 4
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'B', 'B', 1, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'Lotte Cinema Phan Văn Trị - Phòng Deluxe'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'B'
        AND s.seat_number = 1
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'B', 'B', 2, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'Lotte Cinema Phan Văn Trị - Phòng Deluxe'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'B'
        AND s.seat_number = 2
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'B', 'B', 3, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'Lotte Cinema Phan Văn Trị - Phòng Deluxe'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'B'
        AND s.seat_number = 3
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'B', 'B', 4, 'standard', 'standard', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'Lotte Cinema Phan Văn Trị - Phòng Deluxe'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'B'
        AND s.seat_number = 4
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'C', 'C', 1, 'vip', 'vip', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'Lotte Cinema Phan Văn Trị - Phòng Deluxe'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'C'
        AND s.seat_number = 1
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'C', 'C', 2, 'vip', 'vip', 1
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'Lotte Cinema Phan Văn Trị - Phòng Deluxe'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'C'
        AND s.seat_number = 2
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'A', 'A', 1, 'standard', 'standard', 0
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'Cinema Central Lê Lợi - Phòng Bảo Trì'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'A'
        AND s.seat_number = 1
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'A', 'A', 2, 'standard', 'standard', 0
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'Cinema Central Lê Lợi - Phòng Bảo Trì'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'A'
        AND s.seat_number = 2
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'A', 'A', 3, 'standard', 'standard', 0
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'Cinema Central Lê Lợi - Phòng Bảo Trì'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'A'
        AND s.seat_number = 3
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'A', 'A', 4, 'standard', 'standard', 0
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'Cinema Central Lê Lợi - Phòng Bảo Trì'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'A'
        AND s.seat_number = 4
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'B', 'B', 1, 'standard', 'standard', 0
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'Cinema Central Lê Lợi - Phòng Bảo Trì'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'B'
        AND s.seat_number = 1
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'B', 'B', 2, 'standard', 'standard', 0
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'Cinema Central Lê Lợi - Phòng Bảo Trì'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'B'
        AND s.seat_number = 2
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'B', 'B', 3, 'standard', 'standard', 0
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'Cinema Central Lê Lợi - Phòng Bảo Trì'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'B'
        AND s.seat_number = 3
  );

INSERT INTO seats (room_id, row_name, seat_row, seat_number, type, seat_type, status)
SELECT r.room_id, 'B', 'B', 4, 'standard', 'standard', 0
FROM rooms r
WHERE COALESCE(NULLIF(r.room_name,''), r.name) = 'Cinema Central Lê Lợi - Phòng Bảo Trì'
  AND NOT EXISTS (
      SELECT 1 FROM seats s
      WHERE s.room_id = r.room_id
        AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'B'
        AND s.seat_number = 4
  );

/* ---------- 6. SUẤT CHIẾU MẪU ---------- */

INSERT INTO showtimes (movie_id, room_id, show_date, start_time, end_time, price, base_price, status)
SELECT m.movie_id, r.room_id, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '17:30:00', '19:38:00', 90000, 90000, 1
FROM movies m
JOIN rooms r ON COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng IMAX 1'
WHERE m.title = 'Bố Già'
  AND NOT EXISTS (
      SELECT 1 FROM showtimes st
      WHERE st.movie_id = m.movie_id
        AND st.room_id = r.room_id
        AND st.show_date = DATE_ADD(CURDATE(), INTERVAL 1 DAY)
        AND st.start_time = '17:30:00'
  );

INSERT INTO showtimes (movie_id, room_id, show_date, start_time, end_time, price, base_price, status)
SELECT m.movie_id, r.room_id, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '15:00:00', '16:38:00', 75000, 75000, 1
FROM movies m
JOIN rooms r ON COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng 2D 1'
WHERE m.title = 'Hai Phượng (Furie)'
  AND NOT EXISTS (
      SELECT 1 FROM showtimes st
      WHERE st.movie_id = m.movie_id
        AND st.room_id = r.room_id
        AND st.show_date = DATE_ADD(CURDATE(), INTERVAL 2 DAY)
        AND st.start_time = '15:00:00'
  );

INSERT INTO showtimes (movie_id, room_id, show_date, start_time, end_time, price, base_price, status)
SELECT m.movie_id, r.room_id, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '19:00:00', '20:57:00', 80000, 80000, 1
FROM movies m
JOIN rooms r ON COALESCE(NULLIF(r.room_name,''), r.name) = 'Galaxy Cinema Nguyễn Du - Phòng 1'
WHERE m.title = 'Mắt Biếc'
  AND NOT EXISTS (
      SELECT 1 FROM showtimes st
      WHERE st.movie_id = m.movie_id
        AND st.room_id = r.room_id
        AND st.show_date = DATE_ADD(CURDATE(), INTERVAL 1 DAY)
        AND st.start_time = '19:00:00'
  );

INSERT INTO showtimes (movie_id, room_id, show_date, start_time, end_time, price, base_price, status)
SELECT m.movie_id, r.room_id, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '20:00:00', '21:42:00', 95000, 95000, 1
FROM movies m
JOIN rooms r ON COALESCE(NULLIF(r.room_name,''), r.name) = 'Lotte Cinema Phan Văn Trị - Phòng Deluxe'
WHERE m.title = 'Nhà Bà Nữ'
  AND NOT EXISTS (
      SELECT 1 FROM showtimes st
      WHERE st.movie_id = m.movie_id
        AND st.room_id = r.room_id
        AND st.show_date = DATE_ADD(CURDATE(), INTERVAL 3 DAY)
        AND st.start_time = '20:00:00'
  );

INSERT INTO showtimes (movie_id, room_id, show_date, start_time, end_time, price, base_price, status)
SELECT m.movie_id, r.room_id, DATE_ADD(CURDATE(), INTERVAL 4 DAY), '10:00:00', '13:01:00', 105000, 105000, 1
FROM movies m
JOIN rooms r ON COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng IMAX 1'
WHERE m.title = 'Avengers: Endgame'
  AND NOT EXISTS (
      SELECT 1 FROM showtimes st
      WHERE st.movie_id = m.movie_id
        AND st.room_id = r.room_id
        AND st.show_date = DATE_ADD(CURDATE(), INTERVAL 4 DAY)
        AND st.start_time = '10:00:00'
  );

INSERT INTO showtimes (movie_id, room_id, show_date, start_time, end_time, price, base_price, status)
SELECT m.movie_id, r.room_id, DATE_ADD(CURDATE(), INTERVAL 5 DAY), '18:00:00', '20:02:00', 100000, 100000, 1
FROM movies m
JOIN rooms r ON COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng 2D 1'
WHERE m.title = 'Deadpool & Wolverine'
  AND NOT EXISTS (
      SELECT 1 FROM showtimes st
      WHERE st.movie_id = m.movie_id
        AND st.room_id = r.room_id
        AND st.show_date = DATE_ADD(CURDATE(), INTERVAL 5 DAY)
        AND st.start_time = '18:00:00'
  );

INSERT INTO showtimes (movie_id, room_id, show_date, start_time, end_time, price, base_price, status)
SELECT m.movie_id, r.room_id, DATE_ADD(CURDATE(), INTERVAL 6 DAY), '20:30:00', '22:45:00', 110000, 110000, 1
FROM movies m
JOIN rooms r ON COALESCE(NULLIF(r.room_name,''), r.name) = 'Galaxy Cinema Nguyễn Du - Phòng 1'
WHERE m.title = 'Joker: Folie à Deux'
  AND NOT EXISTS (
      SELECT 1 FROM showtimes st
      WHERE st.movie_id = m.movie_id
        AND st.room_id = r.room_id
        AND st.show_date = DATE_ADD(CURDATE(), INTERVAL 6 DAY)
        AND st.start_time = '20:30:00'
  );

INSERT INTO showtimes (movie_id, room_id, show_date, start_time, end_time, price, base_price, status)
SELECT m.movie_id, r.room_id, DATE_SUB(CURDATE(), INTERVAL 20 DAY), '18:00:00', '20:08:00', 90000, 90000, 1
FROM movies m
JOIN rooms r ON COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng IMAX 1'
WHERE m.title = 'Bố Già'
  AND NOT EXISTS (
      SELECT 1 FROM showtimes st
      WHERE st.movie_id = m.movie_id
        AND st.room_id = r.room_id
        AND st.show_date = DATE_SUB(CURDATE(), INTERVAL 20 DAY)
        AND st.start_time = '18:00:00'
  );

INSERT INTO showtimes (movie_id, room_id, show_date, start_time, end_time, price, base_price, status)
SELECT m.movie_id, r.room_id, DATE_SUB(CURDATE(), INTERVAL 45 DAY), '14:00:00', '15:38:00', 75000, 75000, 1
FROM movies m
JOIN rooms r ON COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng 2D 1'
WHERE m.title = 'Hai Phượng (Furie)'
  AND NOT EXISTS (
      SELECT 1 FROM showtimes st
      WHERE st.movie_id = m.movie_id
        AND st.room_id = r.room_id
        AND st.show_date = DATE_SUB(CURDATE(), INTERVAL 45 DAY)
        AND st.start_time = '14:00:00'
  );

INSERT INTO showtimes (movie_id, room_id, show_date, start_time, end_time, price, base_price, status)
SELECT m.movie_id, r.room_id, DATE_SUB(CURDATE(), INTERVAL 75 DAY), '19:00:00', '20:57:00', 80000, 80000, 1
FROM movies m
JOIN rooms r ON COALESCE(NULLIF(r.room_name,''), r.name) = 'Galaxy Cinema Nguyễn Du - Phòng 1'
WHERE m.title = 'Mắt Biếc'
  AND NOT EXISTS (
      SELECT 1 FROM showtimes st
      WHERE st.movie_id = m.movie_id
        AND st.room_id = r.room_id
        AND st.show_date = DATE_SUB(CURDATE(), INTERVAL 75 DAY)
        AND st.start_time = '19:00:00'
  );

INSERT INTO showtimes (movie_id, room_id, show_date, start_time, end_time, price, base_price, status)
SELECT m.movie_id, r.room_id, DATE_SUB(CURDATE(), INTERVAL 100 DAY), '20:00:00', '21:42:00', 95000, 95000, 1
FROM movies m
JOIN rooms r ON COALESCE(NULLIF(r.room_name,''), r.name) = 'Lotte Cinema Phan Văn Trị - Phòng Deluxe'
WHERE m.title = 'Nhà Bà Nữ'
  AND NOT EXISTS (
      SELECT 1 FROM showtimes st
      WHERE st.movie_id = m.movie_id
        AND st.room_id = r.room_id
        AND st.show_date = DATE_SUB(CURDATE(), INTERVAL 100 DAY)
        AND st.start_time = '20:00:00'
  );

INSERT INTO showtimes (movie_id, room_id, show_date, start_time, end_time, price, base_price, status)
SELECT m.movie_id, r.room_id, DATE_SUB(CURDATE(), INTERVAL 130 DAY), '09:30:00', '12:31:00', 105000, 105000, 1
FROM movies m
JOIN rooms r ON COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng IMAX 1'
WHERE m.title = 'Avengers: Endgame'
  AND NOT EXISTS (
      SELECT 1 FROM showtimes st
      WHERE st.movie_id = m.movie_id
        AND st.room_id = r.room_id
        AND st.show_date = DATE_SUB(CURDATE(), INTERVAL 130 DAY)
        AND st.start_time = '09:30:00'
  );

INSERT INTO showtimes (movie_id, room_id, show_date, start_time, end_time, price, base_price, status)
SELECT m.movie_id, r.room_id, DATE_ADD(CURDATE(), INTERVAL 7 DAY), '16:00:00', '18:02:00', 100000, 100000, 0
FROM movies m
JOIN rooms r ON COALESCE(NULLIF(r.room_name,''), r.name) = 'Lotte Cinema Phan Văn Trị - Phòng Deluxe'
WHERE m.title = 'Deadpool & Wolverine'
  AND NOT EXISTS (
      SELECT 1 FROM showtimes st
      WHERE st.movie_id = m.movie_id
        AND st.room_id = r.room_id
        AND st.show_date = DATE_ADD(CURDATE(), INTERVAL 7 DAY)
        AND st.start_time = '16:00:00'
  );

/* ---------- 7. KHUYẾN MÃI MẪU ---------- */

INSERT INTO promotions (code, promo_code, title, discount_type, discount_value, description, min_tickets, min_amount, applicable_seat_types, min_order_amount, max_discount, usage_limit, used_count, budget, start_date, end_date, status)
SELECT 'CINEMA10', 'CINEMA10', 'Giảm 10% cho tất cả đơn hàng', 'percent', 10, 'Giảm 10% cho tất cả đơn hàng', 1, 0, JSON_ARRAY('standard','vip','couple'), 0, 100000, 1000, 0, 0, NOW() - INTERVAL 7 DAY, NOW() + INTERVAL 15 DAY, 1
WHERE NOT EXISTS (SELECT 1 FROM promotions WHERE COALESCE(code, promo_code) = 'CINEMA10');

INSERT INTO promotions (code, promo_code, title, discount_type, discount_value, description, min_tickets, min_amount, applicable_seat_types, min_order_amount, max_discount, usage_limit, used_count, budget, start_date, end_date, status)
SELECT 'STUDENT20', 'STUDENT20', 'Giảm 20% cho học sinh sinh viên', 'percent', 20, 'Áp dụng chủ yếu cho ghế thường', 1, 0, JSON_ARRAY('standard'), 0, 100000, 500, 0, 0, NOW() - INTERVAL 7 DAY, NOW() + INTERVAL 20 DAY, 1
WHERE NOT EXISTS (SELECT 1 FROM promotions WHERE COALESCE(code, promo_code) = 'STUDENT20');

INSERT INTO promotions (code, promo_code, title, discount_type, discount_value, description, min_tickets, min_amount, applicable_seat_types, min_order_amount, max_discount, usage_limit, used_count, budget, start_date, end_date, status)
SELECT 'SAVE50000', 'SAVE50000', 'Giảm 50.000đ cho đơn từ 200.000đ', 'fixed', 50000, 'Khuyến mãi theo giá trị đơn hàng', 1, 0, JSON_ARRAY('standard','vip','couple'), 200000, 50000, 2000, 0, 0, NOW() - INTERVAL 7 DAY, NOW() + INTERVAL 25 DAY, 1
WHERE NOT EXISTS (SELECT 1 FROM promotions WHERE COALESCE(code, promo_code) = 'SAVE50000');

INSERT INTO promotions (code, promo_code, title, discount_type, discount_value, description, min_tickets, min_amount, applicable_seat_types, min_order_amount, max_discount, usage_limit, used_count, budget, start_date, end_date, status)
SELECT 'FAMILY15', 'FAMILY15', 'Giảm 15% cho nhóm gia đình', 'percent', 15, 'Áp dụng khi đặt từ 3 vé', 3, 0, JSON_ARRAY('standard','vip','couple'), 0, 150000, 3000, 0, 0, NOW() - INTERVAL 7 DAY, NOW() + INTERVAL 30 DAY, 1
WHERE NOT EXISTS (SELECT 1 FROM promotions WHERE COALESCE(code, promo_code) = 'FAMILY15');

INSERT INTO promotions (code, promo_code, title, discount_type, discount_value, description, min_tickets, min_amount, applicable_seat_types, min_order_amount, max_discount, usage_limit, used_count, budget, start_date, end_date, status)
SELECT 'EXPIRED30', 'EXPIRED30', 'Khuyến mãi đã hết hạn', 'percent', 30, 'Dùng để kiểm thử bộ lọc khuyến mãi hết hạn', 1, 0, JSON_ARRAY('standard','vip'), 0, 100000, 100, 0, 0, NOW() - INTERVAL 30 DAY, NOW() - INTERVAL 1 DAY, 0
WHERE NOT EXISTS (SELECT 1 FROM promotions WHERE COALESCE(code, promo_code) = 'EXPIRED30');

INSERT INTO promotions (code, promo_code, title, discount_type, discount_value, description, min_tickets, min_amount, applicable_seat_types, min_order_amount, max_discount, usage_limit, used_count, budget, start_date, end_date, status)
SELECT 'INACTIVE25', 'INACTIVE25', 'Khuyến mãi tạm dừng', 'percent', 25, 'Dùng để kiểm thử trạng thái khuyến mãi tạm dừng', 1, 0, JSON_ARRAY('standard','vip','couple'), 0, 100000, 100, 0, 0, NOW() - INTERVAL 5 DAY, NOW() + INTERVAL 40 DAY, 0
WHERE NOT EXISTS (SELECT 1 FROM promotions WHERE COALESCE(code, promo_code) = 'INACTIVE25');

/* ---------- 8. ĐƠN HÀNG, VÉ, THANH TOÁN MẪU ---------- */

INSERT INTO orders (customer_id, user_id, promotion_id, order_code, order_date, total_amount, discount_amount, final_amount, payment_method, payment_status, order_status, notes, created_by_employee_id, updated_by_employee_id)
SELECT c.customer_id, c.customer_id, p.promotion_id, 'ORDDEMO001', DATE_SUB(NOW(), INTERVAL 22 DAY), 180000, 18000, 162000, 'bank_transfer', 'paid', 'completed', 'Đơn hàng mẫu đã thanh toán', NULL, NULL
FROM customers c
LEFT JOIN promotions p ON COALESCE(p.code, p.promo_code) = 'CINEMA10'
LEFT JOIN employees e ON 1=0
WHERE c.email = 'nguyenan@example.com'
  AND NOT EXISTS (SELECT 1 FROM orders WHERE order_code = 'ORDDEMO001');

INSERT INTO orders (customer_id, user_id, promotion_id, order_code, order_date, total_amount, discount_amount, final_amount, payment_method, payment_status, order_status, notes, created_by_employee_id, updated_by_employee_id)
SELECT c.customer_id, c.customer_id, p.promotion_id, 'ORDDEMO002', DATE_SUB(NOW(), INTERVAL 1 DAY), 150000, 0, 150000, 'cash', 'pending', 'pending', 'Đơn chờ thanh toán để kiểm thử bộ lọc', NULL, NULL
FROM customers c
LEFT JOIN promotions p ON 1=0
LEFT JOIN employees e ON 1=0
WHERE c.email = 'tranbinh@example.com'
  AND NOT EXISTS (SELECT 1 FROM orders WHERE order_code = 'ORDDEMO002');

INSERT INTO orders (customer_id, user_id, promotion_id, order_code, order_date, total_amount, discount_amount, final_amount, payment_method, payment_status, order_status, notes, created_by_employee_id, updated_by_employee_id)
SELECT c.customer_id, c.customer_id, p.promotion_id, 'ORDDEMO003', DATE_SUB(NOW(), INTERVAL 46 DAY), 225000, 50000, 175000, 'momo', 'paid', 'completed', 'Đơn sử dụng mã giảm giá cố định', NULL, NULL
FROM customers c
LEFT JOIN promotions p ON COALESCE(p.code, p.promo_code) = 'SAVE50000'
LEFT JOIN employees e ON 1=0
WHERE c.email = 'lechi@example.com'
  AND NOT EXISTS (SELECT 1 FROM orders WHERE order_code = 'ORDDEMO003');

INSERT INTO orders (customer_id, user_id, promotion_id, order_code, order_date, total_amount, discount_amount, final_amount, payment_method, payment_status, order_status, notes, created_by_employee_id, updated_by_employee_id)
SELECT c.customer_id, c.customer_id, p.promotion_id, 'ORDDEMO004', DATE_SUB(NOW(), INTERVAL 2 DAY), 95000, 0, 95000, 'zalopay', 'refunded', 'cancelled', 'Đơn đã hủy và hoàn tiền', NULL, NULL
FROM customers c
LEFT JOIN promotions p ON 1=0
LEFT JOIN employees e ON 1=0
WHERE c.email = 'phammai@example.com'
  AND NOT EXISTS (SELECT 1 FROM orders WHERE order_code = 'ORDDEMO004');

INSERT INTO orders (customer_id, user_id, promotion_id, order_code, order_date, total_amount, discount_amount, final_amount, payment_method, payment_status, order_status, notes, created_by_employee_id, updated_by_employee_id)
SELECT c.customer_id, c.customer_id, p.promotion_id, 'ORDDEMO005', DATE_SUB(NOW(), INTERVAL 76 DAY), 208000, 0, 208000, 'cash', 'paid', 'completed', 'Đơn tạo bởi nhân viên quầy vé', e.employee_id, e.employee_id
FROM customers c
LEFT JOIN promotions p ON 1=0
LEFT JOIN employees e ON e.email = 'staff@cinemacentral.vn'
WHERE c.email = 'doquang@example.com'
  AND NOT EXISTS (SELECT 1 FROM orders WHERE order_code = 'ORDDEMO005');

INSERT INTO orders (customer_id, user_id, promotion_id, order_code, order_date, total_amount, discount_amount, final_amount, payment_method, payment_status, order_status, notes, created_by_employee_id, updated_by_employee_id)
SELECT c.customer_id, c.customer_id, p.promotion_id, 'ORDDEMO006', DATE_SUB(NOW(), INTERVAL 1 DAY), 180000, 36000, 144000, 'bank_transfer', 'paid', 'completed', 'Đơn có yêu cầu hủy đang chờ duyệt', NULL, NULL
FROM customers c
LEFT JOIN promotions p ON COALESCE(p.code, p.promo_code) = 'STUDENT20'
LEFT JOIN employees e ON 1=0
WHERE c.email = 'test@example.com'
  AND NOT EXISTS (SELECT 1 FROM orders WHERE order_code = 'ORDDEMO006');

INSERT INTO orders (customer_id, user_id, promotion_id, order_code, order_date, total_amount, discount_amount, final_amount, payment_method, payment_status, order_status, notes, created_by_employee_id, updated_by_employee_id)
SELECT c.customer_id, c.customer_id, p.promotion_id, 'ORDDEMO007', DATE_SUB(NOW(), INTERVAL 2 DAY), 160000, 0, 160000, 'vnpay', 'paid', 'completed', 'Đơn có yêu cầu hủy bị từ chối', NULL, NULL
FROM customers c
LEFT JOIN promotions p ON 1=0
LEFT JOIN employees e ON 1=0
WHERE c.email = 'ngoclan@example.com'
  AND NOT EXISTS (SELECT 1 FROM orders WHERE order_code = 'ORDDEMO007');

INSERT INTO orders (customer_id, user_id, promotion_id, order_code, order_date, total_amount, discount_amount, final_amount, payment_method, payment_status, order_status, notes, created_by_employee_id, updated_by_employee_id)
SELECT c.customer_id, c.customer_id, p.promotion_id, 'ORDDEMO008', DATE_SUB(NOW(), INTERVAL 3 DAY), 210000, 0, 210000, 'cash', 'refunded', 'cancelled', 'Đơn tạo bởi admin và đã duyệt hủy', e.employee_id, e.employee_id
FROM customers c
LEFT JOIN promotions p ON 1=0
LEFT JOIN employees e ON e.email = 'admin@cinemacentral.vn'
WHERE c.email = 'minhkhang@example.com'
  AND NOT EXISTS (SELECT 1 FROM orders WHERE order_code = 'ORDDEMO008');

INSERT INTO orders (customer_id, user_id, promotion_id, order_code, order_date, total_amount, discount_amount, final_amount, payment_method, payment_status, order_status, notes, created_by_employee_id, updated_by_employee_id)
SELECT c.customer_id, c.customer_id, p.promotion_id, 'ORDDEMO009', DATE_SUB(NOW(), INTERVAL 1 DAY), 200000, 0, 200000, 'credit_card', 'failed', 'pending', 'Đơn thanh toán thất bại để kiểm thử', NULL, NULL
FROM customers c
LEFT JOIN promotions p ON 1=0
LEFT JOIN employees e ON 1=0
WHERE c.email = 'thuthao@example.com'
  AND NOT EXISTS (SELECT 1 FROM orders WHERE order_code = 'ORDDEMO009');

INSERT INTO orders (customer_id, user_id, promotion_id, order_code, order_date, total_amount, discount_amount, final_amount, payment_method, payment_status, order_status, notes, created_by_employee_id, updated_by_employee_id)
SELECT c.customer_id, c.customer_id, p.promotion_id, 'ORDDEMO010', DATE_SUB(NOW(), INTERVAL 100 DAY), 190000, 0, 190000, 'cash', 'paid', 'completed', 'Đơn hoàn tất trong quá khứ', e.employee_id, e.employee_id
FROM customers c
LEFT JOIN promotions p ON 1=0
LEFT JOIN employees e ON e.email = 'admin@cinemacentral.vn'
WHERE c.email = 'test@example.com'
  AND NOT EXISTS (SELECT 1 FROM orders WHERE order_code = 'ORDDEMO010');

INSERT INTO orders (customer_id, user_id, promotion_id, order_code, order_date, total_amount, discount_amount, final_amount, payment_method, payment_status, order_status, notes, created_by_employee_id, updated_by_employee_id)
SELECT c.customer_id, c.customer_id, p.promotion_id, 'ORDDEMO011', DATE_SUB(NOW(), INTERVAL 130 DAY), 315000, 47250, 267750, 'momo', 'paid', 'completed', 'Đơn nhóm gia đình có khuyến mãi', NULL, NULL
FROM customers c
LEFT JOIN promotions p ON COALESCE(p.code, p.promo_code) = 'FAMILY15'
LEFT JOIN employees e ON 1=0
WHERE c.email = 'tranbinh@example.com'
  AND NOT EXISTS (SELECT 1 FROM orders WHERE order_code = 'ORDDEMO011');

INSERT INTO orders (customer_id, user_id, promotion_id, order_code, order_date, total_amount, discount_amount, final_amount, payment_method, payment_status, order_status, notes, created_by_employee_id, updated_by_employee_id)
SELECT c.customer_id, c.customer_id, p.promotion_id, 'ORDDEMO012', DATE_SUB(NOW(), INTERVAL 4 DAY), 220000, 50000, 170000, 'zalopay', 'paid', 'completed', 'Đơn hoàn tất cho suất chiếu sắp tới', NULL, NULL
FROM customers c
LEFT JOIN promotions p ON COALESCE(p.code, p.promo_code) = 'SAVE50000'
LEFT JOIN employees e ON 1=0
WHERE c.email = 'lechi@example.com'
  AND NOT EXISTS (SELECT 1 FROM orders WHERE order_code = 'ORDDEMO012');

INSERT INTO tickets (order_id, showtime_id, seat_id, price, ticket_status)
SELECT o.order_id, st.showtime_id, s.seat_id, 90000, 'paid'
FROM orders o
JOIN movies m ON m.title = 'Bố Già'
JOIN rooms r ON COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng IMAX 1'
JOIN showtimes st ON st.movie_id = m.movie_id AND st.room_id = r.room_id AND st.show_date = DATE_SUB(CURDATE(), INTERVAL 20 DAY) AND st.start_time = '18:00:00'
JOIN seats s ON s.room_id = r.room_id AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'B' AND s.seat_number = 1
WHERE o.order_code = 'ORDDEMO001'
  AND NOT EXISTS (
      SELECT 1 FROM tickets t
      WHERE t.showtime_id = st.showtime_id AND t.seat_id = s.seat_id
  );

INSERT INTO tickets (order_id, showtime_id, seat_id, price, ticket_status)
SELECT o.order_id, st.showtime_id, s.seat_id, 90000, 'paid'
FROM orders o
JOIN movies m ON m.title = 'Bố Già'
JOIN rooms r ON COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng IMAX 1'
JOIN showtimes st ON st.movie_id = m.movie_id AND st.room_id = r.room_id AND st.show_date = DATE_SUB(CURDATE(), INTERVAL 20 DAY) AND st.start_time = '18:00:00'
JOIN seats s ON s.room_id = r.room_id AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'B' AND s.seat_number = 2
WHERE o.order_code = 'ORDDEMO001'
  AND NOT EXISTS (
      SELECT 1 FROM tickets t
      WHERE t.showtime_id = st.showtime_id AND t.seat_id = s.seat_id
  );

INSERT INTO tickets (order_id, showtime_id, seat_id, price, ticket_status)
SELECT o.order_id, st.showtime_id, s.seat_id, 75000, 'reserved'
FROM orders o
JOIN movies m ON m.title = 'Hai Phượng (Furie)'
JOIN rooms r ON COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng 2D 1'
JOIN showtimes st ON st.movie_id = m.movie_id AND st.room_id = r.room_id AND st.show_date = DATE_ADD(CURDATE(), INTERVAL 2 DAY) AND st.start_time = '15:00:00'
JOIN seats s ON s.room_id = r.room_id AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'A' AND s.seat_number = 1
WHERE o.order_code = 'ORDDEMO002'
  AND NOT EXISTS (
      SELECT 1 FROM tickets t
      WHERE t.showtime_id = st.showtime_id AND t.seat_id = s.seat_id
  );

INSERT INTO tickets (order_id, showtime_id, seat_id, price, ticket_status)
SELECT o.order_id, st.showtime_id, s.seat_id, 75000, 'reserved'
FROM orders o
JOIN movies m ON m.title = 'Hai Phượng (Furie)'
JOIN rooms r ON COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng 2D 1'
JOIN showtimes st ON st.movie_id = m.movie_id AND st.room_id = r.room_id AND st.show_date = DATE_ADD(CURDATE(), INTERVAL 2 DAY) AND st.start_time = '15:00:00'
JOIN seats s ON s.room_id = r.room_id AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'A' AND s.seat_number = 2
WHERE o.order_code = 'ORDDEMO002'
  AND NOT EXISTS (
      SELECT 1 FROM tickets t
      WHERE t.showtime_id = st.showtime_id AND t.seat_id = s.seat_id
  );

INSERT INTO tickets (order_id, showtime_id, seat_id, price, ticket_status)
SELECT o.order_id, st.showtime_id, s.seat_id, 75000, 'paid'
FROM orders o
JOIN movies m ON m.title = 'Hai Phượng (Furie)'
JOIN rooms r ON COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng 2D 1'
JOIN showtimes st ON st.movie_id = m.movie_id AND st.room_id = r.room_id AND st.show_date = DATE_SUB(CURDATE(), INTERVAL 45 DAY) AND st.start_time = '14:00:00'
JOIN seats s ON s.room_id = r.room_id AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'A' AND s.seat_number = 1
WHERE o.order_code = 'ORDDEMO003'
  AND NOT EXISTS (
      SELECT 1 FROM tickets t
      WHERE t.showtime_id = st.showtime_id AND t.seat_id = s.seat_id
  );

INSERT INTO tickets (order_id, showtime_id, seat_id, price, ticket_status)
SELECT o.order_id, st.showtime_id, s.seat_id, 75000, 'paid'
FROM orders o
JOIN movies m ON m.title = 'Hai Phượng (Furie)'
JOIN rooms r ON COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng 2D 1'
JOIN showtimes st ON st.movie_id = m.movie_id AND st.room_id = r.room_id AND st.show_date = DATE_SUB(CURDATE(), INTERVAL 45 DAY) AND st.start_time = '14:00:00'
JOIN seats s ON s.room_id = r.room_id AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'A' AND s.seat_number = 2
WHERE o.order_code = 'ORDDEMO003'
  AND NOT EXISTS (
      SELECT 1 FROM tickets t
      WHERE t.showtime_id = st.showtime_id AND t.seat_id = s.seat_id
  );

INSERT INTO tickets (order_id, showtime_id, seat_id, price, ticket_status)
SELECT o.order_id, st.showtime_id, s.seat_id, 75000, 'paid'
FROM orders o
JOIN movies m ON m.title = 'Hai Phượng (Furie)'
JOIN rooms r ON COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng 2D 1'
JOIN showtimes st ON st.movie_id = m.movie_id AND st.room_id = r.room_id AND st.show_date = DATE_SUB(CURDATE(), INTERVAL 45 DAY) AND st.start_time = '14:00:00'
JOIN seats s ON s.room_id = r.room_id AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'B' AND s.seat_number = 1
WHERE o.order_code = 'ORDDEMO003'
  AND NOT EXISTS (
      SELECT 1 FROM tickets t
      WHERE t.showtime_id = st.showtime_id AND t.seat_id = s.seat_id
  );

INSERT INTO tickets (order_id, showtime_id, seat_id, price, ticket_status)
SELECT o.order_id, st.showtime_id, s.seat_id, 95000, 'cancelled'
FROM orders o
JOIN movies m ON m.title = 'Nhà Bà Nữ'
JOIN rooms r ON COALESCE(NULLIF(r.room_name,''), r.name) = 'Lotte Cinema Phan Văn Trị - Phòng Deluxe'
JOIN showtimes st ON st.movie_id = m.movie_id AND st.room_id = r.room_id AND st.show_date = DATE_ADD(CURDATE(), INTERVAL 3 DAY) AND st.start_time = '20:00:00'
JOIN seats s ON s.room_id = r.room_id AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'A' AND s.seat_number = 1
WHERE o.order_code = 'ORDDEMO004'
  AND NOT EXISTS (
      SELECT 1 FROM tickets t
      WHERE t.showtime_id = st.showtime_id AND t.seat_id = s.seat_id
  );

INSERT INTO tickets (order_id, showtime_id, seat_id, price, ticket_status)
SELECT o.order_id, st.showtime_id, s.seat_id, 104000, 'paid'
FROM orders o
JOIN movies m ON m.title = 'Mắt Biếc'
JOIN rooms r ON COALESCE(NULLIF(r.room_name,''), r.name) = 'Galaxy Cinema Nguyễn Du - Phòng 1'
JOIN showtimes st ON st.movie_id = m.movie_id AND st.room_id = r.room_id AND st.show_date = DATE_SUB(CURDATE(), INTERVAL 75 DAY) AND st.start_time = '19:00:00'
JOIN seats s ON s.room_id = r.room_id AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'C' AND s.seat_number = 1
WHERE o.order_code = 'ORDDEMO005'
  AND NOT EXISTS (
      SELECT 1 FROM tickets t
      WHERE t.showtime_id = st.showtime_id AND t.seat_id = s.seat_id
  );

INSERT INTO tickets (order_id, showtime_id, seat_id, price, ticket_status)
SELECT o.order_id, st.showtime_id, s.seat_id, 104000, 'paid'
FROM orders o
JOIN movies m ON m.title = 'Mắt Biếc'
JOIN rooms r ON COALESCE(NULLIF(r.room_name,''), r.name) = 'Galaxy Cinema Nguyễn Du - Phòng 1'
JOIN showtimes st ON st.movie_id = m.movie_id AND st.room_id = r.room_id AND st.show_date = DATE_SUB(CURDATE(), INTERVAL 75 DAY) AND st.start_time = '19:00:00'
JOIN seats s ON s.room_id = r.room_id AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'C' AND s.seat_number = 2
WHERE o.order_code = 'ORDDEMO005'
  AND NOT EXISTS (
      SELECT 1 FROM tickets t
      WHERE t.showtime_id = st.showtime_id AND t.seat_id = s.seat_id
  );

INSERT INTO tickets (order_id, showtime_id, seat_id, price, ticket_status)
SELECT o.order_id, st.showtime_id, s.seat_id, 90000, 'paid'
FROM orders o
JOIN movies m ON m.title = 'Bố Già'
JOIN rooms r ON COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng IMAX 1'
JOIN showtimes st ON st.movie_id = m.movie_id AND st.room_id = r.room_id AND st.show_date = DATE_ADD(CURDATE(), INTERVAL 1 DAY) AND st.start_time = '17:30:00'
JOIN seats s ON s.room_id = r.room_id AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'A' AND s.seat_number = 3
WHERE o.order_code = 'ORDDEMO006'
  AND NOT EXISTS (
      SELECT 1 FROM tickets t
      WHERE t.showtime_id = st.showtime_id AND t.seat_id = s.seat_id
  );

INSERT INTO tickets (order_id, showtime_id, seat_id, price, ticket_status)
SELECT o.order_id, st.showtime_id, s.seat_id, 90000, 'paid'
FROM orders o
JOIN movies m ON m.title = 'Bố Già'
JOIN rooms r ON COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng IMAX 1'
JOIN showtimes st ON st.movie_id = m.movie_id AND st.room_id = r.room_id AND st.show_date = DATE_ADD(CURDATE(), INTERVAL 1 DAY) AND st.start_time = '17:30:00'
JOIN seats s ON s.room_id = r.room_id AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'A' AND s.seat_number = 4
WHERE o.order_code = 'ORDDEMO006'
  AND NOT EXISTS (
      SELECT 1 FROM tickets t
      WHERE t.showtime_id = st.showtime_id AND t.seat_id = s.seat_id
  );

INSERT INTO tickets (order_id, showtime_id, seat_id, price, ticket_status)
SELECT o.order_id, st.showtime_id, s.seat_id, 80000, 'paid'
FROM orders o
JOIN movies m ON m.title = 'Mắt Biếc'
JOIN rooms r ON COALESCE(NULLIF(r.room_name,''), r.name) = 'Galaxy Cinema Nguyễn Du - Phòng 1'
JOIN showtimes st ON st.movie_id = m.movie_id AND st.room_id = r.room_id AND st.show_date = DATE_ADD(CURDATE(), INTERVAL 1 DAY) AND st.start_time = '19:00:00'
JOIN seats s ON s.room_id = r.room_id AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'A' AND s.seat_number = 1
WHERE o.order_code = 'ORDDEMO007'
  AND NOT EXISTS (
      SELECT 1 FROM tickets t
      WHERE t.showtime_id = st.showtime_id AND t.seat_id = s.seat_id
  );

INSERT INTO tickets (order_id, showtime_id, seat_id, price, ticket_status)
SELECT o.order_id, st.showtime_id, s.seat_id, 80000, 'paid'
FROM orders o
JOIN movies m ON m.title = 'Mắt Biếc'
JOIN rooms r ON COALESCE(NULLIF(r.room_name,''), r.name) = 'Galaxy Cinema Nguyễn Du - Phòng 1'
JOIN showtimes st ON st.movie_id = m.movie_id AND st.room_id = r.room_id AND st.show_date = DATE_ADD(CURDATE(), INTERVAL 1 DAY) AND st.start_time = '19:00:00'
JOIN seats s ON s.room_id = r.room_id AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'A' AND s.seat_number = 2
WHERE o.order_code = 'ORDDEMO007'
  AND NOT EXISTS (
      SELECT 1 FROM tickets t
      WHERE t.showtime_id = st.showtime_id AND t.seat_id = s.seat_id
  );

INSERT INTO tickets (order_id, showtime_id, seat_id, price, ticket_status)
SELECT o.order_id, st.showtime_id, s.seat_id, 105000, 'cancelled'
FROM orders o
JOIN movies m ON m.title = 'Avengers: Endgame'
JOIN rooms r ON COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng IMAX 1'
JOIN showtimes st ON st.movie_id = m.movie_id AND st.room_id = r.room_id AND st.show_date = DATE_ADD(CURDATE(), INTERVAL 4 DAY) AND st.start_time = '10:00:00'
JOIN seats s ON s.room_id = r.room_id AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'A' AND s.seat_number = 1
WHERE o.order_code = 'ORDDEMO008'
  AND NOT EXISTS (
      SELECT 1 FROM tickets t
      WHERE t.showtime_id = st.showtime_id AND t.seat_id = s.seat_id
  );

INSERT INTO tickets (order_id, showtime_id, seat_id, price, ticket_status)
SELECT o.order_id, st.showtime_id, s.seat_id, 105000, 'cancelled'
FROM orders o
JOIN movies m ON m.title = 'Avengers: Endgame'
JOIN rooms r ON COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng IMAX 1'
JOIN showtimes st ON st.movie_id = m.movie_id AND st.room_id = r.room_id AND st.show_date = DATE_ADD(CURDATE(), INTERVAL 4 DAY) AND st.start_time = '10:00:00'
JOIN seats s ON s.room_id = r.room_id AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'A' AND s.seat_number = 2
WHERE o.order_code = 'ORDDEMO008'
  AND NOT EXISTS (
      SELECT 1 FROM tickets t
      WHERE t.showtime_id = st.showtime_id AND t.seat_id = s.seat_id
  );

INSERT INTO tickets (order_id, showtime_id, seat_id, price, ticket_status)
SELECT o.order_id, st.showtime_id, s.seat_id, 100000, 'reserved'
FROM orders o
JOIN movies m ON m.title = 'Deadpool & Wolverine'
JOIN rooms r ON COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng 2D 1'
JOIN showtimes st ON st.movie_id = m.movie_id AND st.room_id = r.room_id AND st.show_date = DATE_ADD(CURDATE(), INTERVAL 5 DAY) AND st.start_time = '18:00:00'
JOIN seats s ON s.room_id = r.room_id AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'A' AND s.seat_number = 1
WHERE o.order_code = 'ORDDEMO009'
  AND NOT EXISTS (
      SELECT 1 FROM tickets t
      WHERE t.showtime_id = st.showtime_id AND t.seat_id = s.seat_id
  );

INSERT INTO tickets (order_id, showtime_id, seat_id, price, ticket_status)
SELECT o.order_id, st.showtime_id, s.seat_id, 100000, 'reserved'
FROM orders o
JOIN movies m ON m.title = 'Deadpool & Wolverine'
JOIN rooms r ON COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng 2D 1'
JOIN showtimes st ON st.movie_id = m.movie_id AND st.room_id = r.room_id AND st.show_date = DATE_ADD(CURDATE(), INTERVAL 5 DAY) AND st.start_time = '18:00:00'
JOIN seats s ON s.room_id = r.room_id AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'A' AND s.seat_number = 2
WHERE o.order_code = 'ORDDEMO009'
  AND NOT EXISTS (
      SELECT 1 FROM tickets t
      WHERE t.showtime_id = st.showtime_id AND t.seat_id = s.seat_id
  );

INSERT INTO tickets (order_id, showtime_id, seat_id, price, ticket_status)
SELECT o.order_id, st.showtime_id, s.seat_id, 95000, 'paid'
FROM orders o
JOIN movies m ON m.title = 'Nhà Bà Nữ'
JOIN rooms r ON COALESCE(NULLIF(r.room_name,''), r.name) = 'Lotte Cinema Phan Văn Trị - Phòng Deluxe'
JOIN showtimes st ON st.movie_id = m.movie_id AND st.room_id = r.room_id AND st.show_date = DATE_SUB(CURDATE(), INTERVAL 100 DAY) AND st.start_time = '20:00:00'
JOIN seats s ON s.room_id = r.room_id AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'A' AND s.seat_number = 1
WHERE o.order_code = 'ORDDEMO010'
  AND NOT EXISTS (
      SELECT 1 FROM tickets t
      WHERE t.showtime_id = st.showtime_id AND t.seat_id = s.seat_id
  );

INSERT INTO tickets (order_id, showtime_id, seat_id, price, ticket_status)
SELECT o.order_id, st.showtime_id, s.seat_id, 95000, 'paid'
FROM orders o
JOIN movies m ON m.title = 'Nhà Bà Nữ'
JOIN rooms r ON COALESCE(NULLIF(r.room_name,''), r.name) = 'Lotte Cinema Phan Văn Trị - Phòng Deluxe'
JOIN showtimes st ON st.movie_id = m.movie_id AND st.room_id = r.room_id AND st.show_date = DATE_SUB(CURDATE(), INTERVAL 100 DAY) AND st.start_time = '20:00:00'
JOIN seats s ON s.room_id = r.room_id AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'A' AND s.seat_number = 2
WHERE o.order_code = 'ORDDEMO010'
  AND NOT EXISTS (
      SELECT 1 FROM tickets t
      WHERE t.showtime_id = st.showtime_id AND t.seat_id = s.seat_id
  );

INSERT INTO tickets (order_id, showtime_id, seat_id, price, ticket_status)
SELECT o.order_id, st.showtime_id, s.seat_id, 105000, 'paid'
FROM orders o
JOIN movies m ON m.title = 'Avengers: Endgame'
JOIN rooms r ON COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng IMAX 1'
JOIN showtimes st ON st.movie_id = m.movie_id AND st.room_id = r.room_id AND st.show_date = DATE_SUB(CURDATE(), INTERVAL 130 DAY) AND st.start_time = '09:30:00'
JOIN seats s ON s.room_id = r.room_id AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'A' AND s.seat_number = 1
WHERE o.order_code = 'ORDDEMO011'
  AND NOT EXISTS (
      SELECT 1 FROM tickets t
      WHERE t.showtime_id = st.showtime_id AND t.seat_id = s.seat_id
  );

INSERT INTO tickets (order_id, showtime_id, seat_id, price, ticket_status)
SELECT o.order_id, st.showtime_id, s.seat_id, 105000, 'paid'
FROM orders o
JOIN movies m ON m.title = 'Avengers: Endgame'
JOIN rooms r ON COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng IMAX 1'
JOIN showtimes st ON st.movie_id = m.movie_id AND st.room_id = r.room_id AND st.show_date = DATE_SUB(CURDATE(), INTERVAL 130 DAY) AND st.start_time = '09:30:00'
JOIN seats s ON s.room_id = r.room_id AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'A' AND s.seat_number = 2
WHERE o.order_code = 'ORDDEMO011'
  AND NOT EXISTS (
      SELECT 1 FROM tickets t
      WHERE t.showtime_id = st.showtime_id AND t.seat_id = s.seat_id
  );

INSERT INTO tickets (order_id, showtime_id, seat_id, price, ticket_status)
SELECT o.order_id, st.showtime_id, s.seat_id, 105000, 'paid'
FROM orders o
JOIN movies m ON m.title = 'Avengers: Endgame'
JOIN rooms r ON COALESCE(NULLIF(r.room_name,''), r.name) = 'CGV Vincom Đồng Khởi - Phòng IMAX 1'
JOIN showtimes st ON st.movie_id = m.movie_id AND st.room_id = r.room_id AND st.show_date = DATE_SUB(CURDATE(), INTERVAL 130 DAY) AND st.start_time = '09:30:00'
JOIN seats s ON s.room_id = r.room_id AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'B' AND s.seat_number = 1
WHERE o.order_code = 'ORDDEMO011'
  AND NOT EXISTS (
      SELECT 1 FROM tickets t
      WHERE t.showtime_id = st.showtime_id AND t.seat_id = s.seat_id
  );

INSERT INTO tickets (order_id, showtime_id, seat_id, price, ticket_status)
SELECT o.order_id, st.showtime_id, s.seat_id, 110000, 'paid'
FROM orders o
JOIN movies m ON m.title = 'Joker: Folie à Deux'
JOIN rooms r ON COALESCE(NULLIF(r.room_name,''), r.name) = 'Galaxy Cinema Nguyễn Du - Phòng 1'
JOIN showtimes st ON st.movie_id = m.movie_id AND st.room_id = r.room_id AND st.show_date = DATE_ADD(CURDATE(), INTERVAL 6 DAY) AND st.start_time = '20:30:00'
JOIN seats s ON s.room_id = r.room_id AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'B' AND s.seat_number = 1
WHERE o.order_code = 'ORDDEMO012'
  AND NOT EXISTS (
      SELECT 1 FROM tickets t
      WHERE t.showtime_id = st.showtime_id AND t.seat_id = s.seat_id
  );

INSERT INTO tickets (order_id, showtime_id, seat_id, price, ticket_status)
SELECT o.order_id, st.showtime_id, s.seat_id, 110000, 'paid'
FROM orders o
JOIN movies m ON m.title = 'Joker: Folie à Deux'
JOIN rooms r ON COALESCE(NULLIF(r.room_name,''), r.name) = 'Galaxy Cinema Nguyễn Du - Phòng 1'
JOIN showtimes st ON st.movie_id = m.movie_id AND st.room_id = r.room_id AND st.show_date = DATE_ADD(CURDATE(), INTERVAL 6 DAY) AND st.start_time = '20:30:00'
JOIN seats s ON s.room_id = r.room_id AND COALESCE(NULLIF(s.row_name,''), s.seat_row) = 'B' AND s.seat_number = 2
WHERE o.order_code = 'ORDDEMO012'
  AND NOT EXISTS (
      SELECT 1 FROM tickets t
      WHERE t.showtime_id = st.showtime_id AND t.seat_id = s.seat_id
  );

INSERT INTO payments (order_id, payment_method, amount_paid, payment_status, created_at)
SELECT o.order_id, 'bank_transfer', 162000, 'paid', o.order_date
FROM orders o
WHERE o.order_code = 'ORDDEMO001'
  AND NOT EXISTS (SELECT 1 FROM payments p WHERE p.order_id = o.order_id);

INSERT INTO payments (order_id, payment_method, amount_paid, payment_status, created_at)
SELECT o.order_id, 'cash', 0, 'pending', o.order_date
FROM orders o
WHERE o.order_code = 'ORDDEMO002'
  AND NOT EXISTS (SELECT 1 FROM payments p WHERE p.order_id = o.order_id);

INSERT INTO payments (order_id, payment_method, amount_paid, payment_status, created_at)
SELECT o.order_id, 'momo', 175000, 'paid', o.order_date
FROM orders o
WHERE o.order_code = 'ORDDEMO003'
  AND NOT EXISTS (SELECT 1 FROM payments p WHERE p.order_id = o.order_id);

INSERT INTO payments (order_id, payment_method, amount_paid, payment_status, created_at)
SELECT o.order_id, 'zalopay', 95000, 'refunded', o.order_date
FROM orders o
WHERE o.order_code = 'ORDDEMO004'
  AND NOT EXISTS (SELECT 1 FROM payments p WHERE p.order_id = o.order_id);

INSERT INTO payments (order_id, payment_method, amount_paid, payment_status, created_at)
SELECT o.order_id, 'cash', 208000, 'paid', o.order_date
FROM orders o
WHERE o.order_code = 'ORDDEMO005'
  AND NOT EXISTS (SELECT 1 FROM payments p WHERE p.order_id = o.order_id);

INSERT INTO payments (order_id, payment_method, amount_paid, payment_status, created_at)
SELECT o.order_id, 'bank_transfer', 144000, 'paid', o.order_date
FROM orders o
WHERE o.order_code = 'ORDDEMO006'
  AND NOT EXISTS (SELECT 1 FROM payments p WHERE p.order_id = o.order_id);

INSERT INTO payments (order_id, payment_method, amount_paid, payment_status, created_at)
SELECT o.order_id, 'vnpay', 160000, 'paid', o.order_date
FROM orders o
WHERE o.order_code = 'ORDDEMO007'
  AND NOT EXISTS (SELECT 1 FROM payments p WHERE p.order_id = o.order_id);

INSERT INTO payments (order_id, payment_method, amount_paid, payment_status, created_at)
SELECT o.order_id, 'cash', 210000, 'refunded', o.order_date
FROM orders o
WHERE o.order_code = 'ORDDEMO008'
  AND NOT EXISTS (SELECT 1 FROM payments p WHERE p.order_id = o.order_id);

INSERT INTO payments (order_id, payment_method, amount_paid, payment_status, created_at)
SELECT o.order_id, 'credit_card', 0, 'failed', o.order_date
FROM orders o
WHERE o.order_code = 'ORDDEMO009'
  AND NOT EXISTS (SELECT 1 FROM payments p WHERE p.order_id = o.order_id);

INSERT INTO payments (order_id, payment_method, amount_paid, payment_status, created_at)
SELECT o.order_id, 'cash', 190000, 'paid', o.order_date
FROM orders o
WHERE o.order_code = 'ORDDEMO010'
  AND NOT EXISTS (SELECT 1 FROM payments p WHERE p.order_id = o.order_id);

INSERT INTO payments (order_id, payment_method, amount_paid, payment_status, created_at)
SELECT o.order_id, 'momo', 267750, 'paid', o.order_date
FROM orders o
WHERE o.order_code = 'ORDDEMO011'
  AND NOT EXISTS (SELECT 1 FROM payments p WHERE p.order_id = o.order_id);

INSERT INTO payments (order_id, payment_method, amount_paid, payment_status, created_at)
SELECT o.order_id, 'zalopay', 170000, 'paid', o.order_date
FROM orders o
WHERE o.order_code = 'ORDDEMO012'
  AND NOT EXISTS (SELECT 1 FROM payments p WHERE p.order_id = o.order_id);

/* ---------- 9. YÊU CẦU HỦY VÉ MẪU ---------- */

INSERT INTO cancellation_requests (order_id, customer_id, user_id, reason, status, request_date, processed_by_employee_id, processed_note, processed_at)
SELECT o.order_id, c.customer_id, c.customer_id, 'Bận việc đột xuất nên không thể đến rạp.', 'pending', DATE_SUB(NOW(), INTERVAL 12 HOUR), NULL, NULL, NULL
FROM orders o
JOIN customers c ON c.email = 'test@example.com' AND c.customer_id = o.customer_id
LEFT JOIN employees e ON 1=0
WHERE o.order_code = 'ORDDEMO006'
  AND NOT EXISTS (SELECT 1 FROM cancellation_requests cr WHERE cr.order_id = o.order_id);

INSERT INTO cancellation_requests (order_id, customer_id, user_id, reason, status, request_date, processed_by_employee_id, processed_note, processed_at)
SELECT o.order_id, c.customer_id, c.customer_id, 'Muốn đổi suất chiếu khác phù hợp hơn.', 'rejected', DATE_SUB(NOW(), INTERVAL 1 DAY), e.employee_id, 'Yêu cầu không đáp ứng điều kiện hủy theo chính sách hiện hành.', DATE_SUB(NOW(), INTERVAL 20 HOUR)
FROM orders o
JOIN customers c ON c.email = 'ngoclan@example.com' AND c.customer_id = o.customer_id
LEFT JOIN employees e ON e.email = 'staff@cinemacentral.vn'
WHERE o.order_code = 'ORDDEMO007'
  AND NOT EXISTS (SELECT 1 FROM cancellation_requests cr WHERE cr.order_id = o.order_id);

INSERT INTO cancellation_requests (order_id, customer_id, user_id, reason, status, request_date, processed_by_employee_id, processed_note, processed_at)
SELECT o.order_id, c.customer_id, c.customer_id, 'Không thể tham gia suất chiếu đã đặt.', 'approved', DATE_SUB(NOW(), INTERVAL 36 HOUR), e.employee_id, 'Đã duyệt hủy và hoàn tiền về ví điện tử.', DATE_SUB(NOW(), INTERVAL 30 HOUR)
FROM orders o
JOIN customers c ON c.email = 'phammai@example.com' AND c.customer_id = o.customer_id
LEFT JOIN employees e ON e.email = 'admin@cinemacentral.vn'
WHERE o.order_code = 'ORDDEMO004'
  AND NOT EXISTS (SELECT 1 FROM cancellation_requests cr WHERE cr.order_id = o.order_id);

INSERT INTO cancellation_requests (order_id, customer_id, user_id, reason, status, request_date, processed_by_employee_id, processed_note, processed_at)
SELECT o.order_id, c.customer_id, c.customer_id, 'Gia đình thay đổi kế hoạch nên cần hủy vé.', 'approved', DATE_SUB(NOW(), INTERVAL 30 HOUR), e.employee_id, 'Đã duyệt hủy theo yêu cầu khách hàng.', DATE_SUB(NOW(), INTERVAL 24 HOUR)
FROM orders o
JOIN customers c ON c.email = 'minhkhang@example.com' AND c.customer_id = o.customer_id
LEFT JOIN employees e ON e.email = 'operations@cinemacentral.vn'
WHERE o.order_code = 'ORDDEMO008'
  AND NOT EXISTS (SELECT 1 FROM cancellation_requests cr WHERE cr.order_id = o.order_id);

/* ---------- 10. BÁO CÁO MẪU ---------- */

INSERT INTO reports (report_type, created_at, employee_id)
SELECT 'revenue', DATE_SUB(NOW(), INTERVAL 1 DAY), e.employee_id
FROM employees e
WHERE e.email = 'admin@cinemacentral.vn'
  AND NOT EXISTS (
      SELECT 1 FROM reports r
      WHERE r.report_type = 'revenue' AND r.employee_id = e.employee_id AND DATE(r.created_at) = DATE(DATE_SUB(NOW(), INTERVAL 1 DAY))
  );

INSERT INTO reports (report_type, created_at, employee_id)
SELECT 'ticket', DATE_SUB(NOW(), INTERVAL 2 DAY), e.employee_id
FROM employees e
WHERE e.email = 'staff@cinemacentral.vn'
  AND NOT EXISTS (
      SELECT 1 FROM reports r
      WHERE r.report_type = 'ticket' AND r.employee_id = e.employee_id AND DATE(r.created_at) = DATE(DATE_SUB(NOW(), INTERVAL 2 DAY))
  );

INSERT INTO reports (report_type, created_at, employee_id)
SELECT 'customer', DATE_SUB(NOW(), INTERVAL 3 DAY), e.employee_id
FROM employees e
WHERE e.email = 'operations@cinemacentral.vn'
  AND NOT EXISTS (
      SELECT 1 FROM reports r
      WHERE r.report_type = 'customer' AND r.employee_id = e.employee_id AND DATE(r.created_at) = DATE(DATE_SUB(NOW(), INTERVAL 3 DAY))
  );

/* ---------- 11. ĐỒNG BỘ LẠI used_count CỦA KHUYẾN MÃI ---------- */
UPDATE promotions p
SET p.used_count = (
    SELECT COUNT(*)
    FROM orders o
    WHERE o.promotion_id = p.promotion_id
      AND o.payment_status IN ('paid', 'success')
      AND o.order_status <> 'cancelled'
);
