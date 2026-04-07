-- Dữ liệu mẫu để test hệ thống đặt vé
USE movie_booking;

-- Thêm cột trailer_url vào bảng Movies nếu chưa có
ALTER TABLE Movies ADD COLUMN IF NOT EXISTS trailer_url VARCHAR(255) AFTER poster_url;

-- Thêm các cột bị thiếu vào bảng Promotions
ALTER TABLE Promotions ADD COLUMN IF NOT EXISTS description TEXT AFTER discount_value;
ALTER TABLE Promotions ADD COLUMN IF NOT EXISTS min_tickets INT DEFAULT 1 AFTER description;
ALTER TABLE Promotions ADD COLUMN IF NOT EXISTS min_amount DECIMAL(12,2) DEFAULT 0 AFTER min_tickets;
ALTER TABLE Promotions ADD COLUMN IF NOT EXISTS applicable_seat_types JSON AFTER min_amount;

-- Insert user test
INSERT IGNORE INTO Users (full_name, email, password, role, status, phone, birthday, address, created_at) VALUES
('Test User', 'test@example.com',
'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
'customer', 'active', '0123456789', '1990-01-01',
'Ho Chi Minh City', NOW());

-- Insert movies
INSERT INTO Movies (title, genre, duration, release_date, status, description, poster_url, trailer_url) VALUES

('Bố Già', 'Drama', 128, '2021-03-12', 'showing',
'Câu chuyện về Ba Sang – con thứ hai trong 4 anh em ồn ào: Giàu, Sang, Phú, Quý. Ba Sang là một người ga lăng, “quá” tốt bụng và luôn hy sinh vì người khác dù họ có muốn hay không. Quân – Ba Sang’s son là một Youtuber trẻ hiện đại.',
'bo_gia.jpg', 'https://www.youtube.com/embed/jluSu8Rw6YE'),

('Hai Phượng (Furie)', 'Action', 98, '2019-02-22', 'showing',
'Hai Phượng kể về cuộc hành trình đầy gay cấn và gian của khi người mẹ vùng quê tìm cách cứu con mình thoát khỏi đường dây bắt cóc khổng lồ. Không tấc sắc trong tay, người phụ nữ thân cô thế cô làm sao chống lại bọn xã hội đen tàn ác để giành lại nguồn sống của đời mình?.',
'hai_phuong.jpg', 'https://www.youtube.com/embed/L41c4_kbDoo'),

('Mắt Biếc', 'Romance', 117, '2019-12-20', 'showing',
'Đạo diễn Victor Vũ trở lại với một tác phẩm chuyển thể từ truyện ngắn cùng tên nổi tiếng của nhà văn Nguyễn Nhật Ánh: Mắt Biếc. Phim kể về chuyện tình đơn phương của chàng thanh niên Ngạn dành cho cô bạn từ thuở nhỏ Hà Lan.',
'mat_biec.jpg', 'https://www.youtube.com/embed/ITlQ0oU7tDA'),

('Nhà Bà Nữ', 'Drama', 102, '2023-01-22', 'showing',
'Câu chuyện xoay quanh gia đình bà Nữ gồm ba thế hệ sống cùng nhau trong một ngôi nhà. Bà Nữ một tay cáng đáng mọi sự, nổi tiếng với quán bánh canh cua và cũng khét tiếng với việc kiểm soát cuộc sống của tất cả mọi người, từ con gái đến con rể. Mọi chuyện diễn ra bình thường cho đến khi cô con gái út si mê anh chàng điển trai xuất thân từ một gia đình giàu có. Truyện phim khắc họa mối quan hệ phức tạp, đa chiều xảy ra với các thành viên trong gia đình. Câu tagline (thông điệp) chính “Ai cũng có lỗi, nhưng ai cũng nghĩ mình là… nạn nhân” chứa nhiều ẩn ý về nội dung bộ phim muốn gửi gắm.',
'nha_ba_nu.jpg', 'https://www.youtube.com/embed/IkaP0KJWTsQ'),

('Avengers: Endgame', 'Action', 181, '2019-04-26', 'showing',
'Sau sự kiện hủy diệt tàn khốc, vũ trụ chìm trong cảnh hoang tàn. Với sự trợ giúp của những đồng minh còn sống sót, biệt đội siêu anh hùng Avengers tập hợp một lần nữa để đảo ngược hành động của Thanos và khôi phục lại trật tự của vũ trụ.',
'endgame.jpg', 'https://www.youtube.com/embed/TcMBFSGVi1c'),

('Spider-Man: No Way Home', 'Action', 148, '2021-12-17', 'showing',
'Nội dung phim: Người Nhện: Không Còn Nhà (Spider-Man: No Way Home)
Người Nhện: Không Còn Nhà là bộ phim tiếp nối sự kiện của phần Spider-man: Far From Home. Trong phần trước, người phản diện Mysterio đã tiết lộ danh tính thực sự của người nhện là Peter Parker, khiến cho Spiderman phải đối mặt với hàng loạt lời chỉ trích từ công chúng. Tình hình này đã ảnh hưởng không chỉ đến cuộc sống của Peter mà còn khiến cho Dì May, cậu bạn Ned và người yêu MJ bị cuốn vào những rắc rối.',
'spiderman.jpg', 'https://www.youtube.com/embed/JfVOs4VSpmA'),

('Titanic', 'Romance', 195, '1997-12-19', 'showing',
'Một cô gái quý tộc mười bảy tuổi phải lòng một chàng họa sĩ tốt bụng nhưng nghèo khó trên con tàu sang trọng nhưng xấu số RMS Titanic.',
'titanic.jpg', 'https://www.youtube.com/embed/Yo2ijvREkyo'),

('Avatar: The Way of Water', 'Sci-Fi', 192, '2022-12-16', 'showing',
'Jake Sully sống cùng gia đình mới của mình trên mặt trăng Pandora ngoài hệ Mặt Trời. Khi một mối đe dọa quen thuộc quay trở lại để hoàn thành những gì đã bắt đầu trước đó, Jake phải hợp tác với Neytiri và quân đội của tộc Na vi để bảo vệ quê hương của họ.',
'avatar2.jpg', 'https://www.youtube.com/embed/d9MyW72ELq0' ),

('Joker', 'Drama', 122, '2019-10-04', 'showing',
'Arthur Fleck, một chú hề trong các bữa tiệc và một diễn viên hài độc thoại thất bại, sống một cuộc đời nghèo khó cùng người mẹ ốm yếu. Tuy nhiên, khi bị xã hội xa lánh và coi là kẻ lập dị, anh quyết định dấn thân vào cuộc sống hỗn loạn ở thành phố Gotham.',
'joker.jpg', 'https://www.youtube.com/embed/t433PEQGErc'),

('Dune', 'Sci-Fi', 155, '2021-10-22', 'showing',
'Paul Atreides đặt chân đến Arrakis sau khi cha anh nhận trách nhiệm cai quản hành tinh nguy hiểm này. Tuy nhiên, hỗn loạn nổ ra sau một vụ phản bội khi các thế lực xung đột để giành quyền kiểm soát melange, một nguồn tài nguyên quý giá.',
'dune.jpg', 'https://www.youtube.com/embed/8g18jFHCLXk'),

('Deadpool & Wolverine', 'Action', 122, '2026-05-10', 'coming_soon',
'Deadpool và Wolverine hợp tác trong một cuộc phiêu lưu hành động hài hước đầy bạo lực và bất ngờ.',
'deadpool_wolverine.jpg', 'https://www.youtube.com/embed/73_1biulkYk'),

('Joker: Folie à Deux', 'Thriller', 135, '2026-10-04', 'coming_soon',
'Tiếp tục câu chuyện Joker với một phiên bản tâm lý đen tối và đầy hỗn loạn.',
'joker_folie.jpg', 'https://www.youtube.com/embed/_OKAwz2MsJs'),

('Despicable Me 4', 'Animation', 105, '2026-07-01', 'coming_soon',
'Các Minions tiếp tục hành trình mới của mình với hàng loạt tình huống hài hước và cảm động.',
'despicable_me_4.jpg', 'https://www.youtube.com/embed/qQlr9-rF32A');

-- Insert rooms
INSERT INTO Rooms (room_name, capacity) VALUES
('CGV Vincom Đồng Khởi - Phòng IMAX 1', 250),
('CGV Vincom Đồng Khởi - Phòng 2D 1', 150),
('CGV Vincom Đồng Khởi - Phòng 2D 2', 150),
('Galaxy Cinema Nguyễn Du - Phòng 1', 180),
('Galaxy Cinema Nguyễn Du - Phòng 2', 180),
('Galaxy Cinema Nguyễn Du - Phòng 3', 120),
('Lotte Cinema Phan Văn Trị - Phòng Deluxe', 160),
('Lotte Cinema Phan Văn Trị - Phòng Standard', 150),
('Lotte Cinema Phan Văn Trị - Phòng Deluxe 2', 120),
('BHD Star Cách Mạng Tháng 8 - IMAX 1', 200),
('BHD Star Cách Mạng Tháng 8 - Phòng 2', 140),
('BHD Star Cách Mạng Tháng 8 - Phòng 3', 140),
('Mega GS Tao Đàn - Phòng IMAX', 220),
('Mega GS Tao Đàn - Phòng 2', 160),
('Mega GS Tao Đàn - Phòng 3', 160);

-- Insert seats CGV Vincom IMAX 1
INSERT INTO Seats (room_id, seat_row, seat_number, seat_type) VALUES
(1,'A',1,'standard'),(1,'A',2,'standard'),(1,'A',3,'standard'),(1,'A',4,'standard'),(1,'A',5,'standard'),
(1,'A',6,'standard'),(1,'A',7,'standard'),(1,'A',8,'standard'),(1,'A',9,'standard'),(1,'A',10,'standard'),
(1,'B',1,'standard'),(1,'B',2,'standard'),(1,'B',3,'standard'),(1,'B',4,'standard'),(1,'B',5,'standard'),
(1,'B',6,'standard'),(1,'B',7,'standard'),(1,'B',8,'standard'),(1,'B',9,'standard'),(1,'B',10,'standard'),
(1,'C',1,'vip'),(1,'C',2,'vip'),(1,'C',3,'vip'),(1,'C',4,'vip'),(1,'C',5,'vip'),
(1,'C',6,'vip'),(1,'C',7,'vip'),(1,'C',8,'vip'),(1,'C',9,'vip'),(1,'C',10,'vip'),
(1,'D',1,'couple'),(1,'D',2,'couple'),(1,'D',3,'couple'),(1,'D',4,'couple'),(1,'D',5,'couple');

-- Insert seats CGV Vincom 2D 1
INSERT INTO Seats (room_id, seat_row, seat_number, seat_type) VALUES
(2,'A',1,'standard'),(2,'A',2,'standard'),(2,'A',3,'standard'),(2,'A',4,'standard'),(2,'A',5,'standard'),
(2,'A',6,'standard'),(2,'A',7,'standard'),(2,'A',8,'standard'),
(2,'B',1,'standard'),(2,'B',2,'standard'),(2,'B',3,'standard'),(2,'B',4,'standard'),(2,'B',5,'standard'),
(2,'B',6,'standard'),(2,'B',7,'standard'),(2,'B',8,'standard'),
(2,'C',1,'vip'),(2,'C',2,'vip'),(2,'C',3,'vip'),(2,'C',4,'vip'),(2,'C',5,'vip'),
(2,'D',1,'couple'),(2,'D',2,'couple');

-- Insert seats CGV Vincom 2D 2
INSERT INTO Seats (room_id, seat_row, seat_number, seat_type) VALUES
(3,'A',1,'standard'),(3,'A',2,'standard'),(3,'A',3,'standard'),(3,'A',4,'standard'),(3,'A',5,'standard'),
(3,'A',6,'standard'),(3,'A',7,'standard'),(3,'A',8,'standard'),
(3,'B',1,'standard'),(3,'B',2,'standard'),(3,'B',3,'standard'),(3,'B',4,'standard'),(3,'B',5,'standard'),
(3,'B',6,'standard'),(3,'B',7,'standard'),(3,'B',8,'standard'),
(3,'C',1,'vip'),(3,'C',2,'vip'),(3,'C',3,'vip'),(3,'C',4,'vip'),(3,'C',5,'vip'),
(3,'D',1,'couple'),(3,'D',2,'couple');

-- Insert seats Galaxy Cinema Nguyễn Du - Phòng 1
INSERT INTO Seats (room_id, seat_row, seat_number, seat_type) VALUES
(4,'A',1,'standard'),(4,'A',2,'standard'),(4,'A',3,'standard'),(4,'A',4,'standard'),(4,'A',5,'standard'),
(4,'A',6,'standard'),(4,'A',7,'standard'),
(4,'B',1,'standard'),(4,'B',2,'standard'),(4,'B',3,'standard'),(4,'B',4,'standard'),(4,'B',5,'standard'),
(4,'B',6,'standard'),(4,'B',7,'standard'),
(4,'C',1,'vip'),(4,'C',2,'vip'),(4,'C',3,'vip'),(4,'C',4,'vip'),(4,'C',5,'vip'),
(4,'D',1,'couple'),(4,'D',2,'couple'),(4,'D',3,'couple');

-- Insert seats Galaxy Cinema Nguyễn Du - Phòng 2
INSERT INTO Seats (room_id, seat_row, seat_number, seat_type) VALUES
(5,'A',1,'standard'),(5,'A',2,'standard'),(5,'A',3,'standard'),(5,'A',4,'standard'),(5,'A',5,'standard'),
(5,'A',6,'standard'),(5,'A',7,'standard'),
(5,'B',1,'standard'),(5,'B',2,'standard'),(5,'B',3,'standard'),(5,'B',4,'standard'),(5,'B',5,'standard'),
(5,'B',6,'standard'),(5,'B',7,'standard'),
(5,'C',1,'vip'),(5,'C',2,'vip'),(5,'C',3,'vip'),(5,'C',4,'vip'),(5,'C',5,'vip'),
(5,'D',1,'couple'),(5,'D',2,'couple'),(5,'D',3,'couple');

-- Insert seats Galaxy Cinema Nguyễn Du - Phòng 3
INSERT INTO Seats (room_id, seat_row, seat_number, seat_type) VALUES
(6,'A',1,'standard'),(6,'A',2,'standard'),(6,'A',3,'standard'),(6,'A',4,'standard'),(6,'A',5,'standard'),
(6,'B',1,'standard'),(6,'B',2,'standard'),(6,'B',3,'standard'),(6,'B',4,'standard'),(6,'B',5,'standard'),
(6,'C',1,'vip'),(6,'C',2,'vip'),(6,'C',3,'vip'),(6,'C',4,'vip'),
(6,'D',1,'couple'),(6,'D',2,'couple');

-- Insert seats Lotte Cinema Phan Văn Trị - Phòng Deluxe
INSERT INTO Seats (room_id, seat_row, seat_number, seat_type) VALUES
(7,'A',1,'standard'),(7,'A',2,'standard'),(7,'A',3,'standard'),(7,'A',4,'standard'),(7,'A',5,'standard'),
(7,'A',6,'standard'),(7,'A',7,'standard'),
(7,'B',1,'standard'),(7,'B',2,'standard'),(7,'B',3,'standard'),(7,'B',4,'standard'),(7,'B',5,'standard'),
(7,'B',6,'standard'),(7,'B',7,'standard'),
(7,'C',1,'vip'),(7,'C',2,'vip'),(7,'C',3,'vip'),(7,'C',4,'vip'),(7,'C',5,'vip'),
(7,'D',1,'couple'),(7,'D',2,'couple');

-- Insert seats Lotte Cinema Phan Văn Trị - Phòng Standard
INSERT INTO Seats (room_id, seat_row, seat_number, seat_type) VALUES
(8,'A',1,'standard'),(8,'A',2,'standard'),(8,'A',3,'standard'),(8,'A',4,'standard'),(8,'A',5,'standard'),
(8,'A',6,'standard'),(8,'A',7,'standard'),(8,'A',8,'standard'),
(8,'B',1,'standard'),(8,'B',2,'standard'),(8,'B',3,'standard'),(8,'B',4,'standard'),(8,'B',5,'standard'),
(8,'B',6,'standard'),(8,'B',7,'standard'),(8,'B',8,'standard'),
(8,'C',1,'vip'),(8,'C',2,'vip'),(8,'C',3,'vip'),(8,'C',4,'vip'),(8,'C',5,'vip'),
(8,'D',1,'couple'),(8,'D',2,'couple');

-- Insert seats Lotte Cinema Phan Văn Trị - Phòng Deluxe 2
INSERT INTO Seats (room_id, seat_row, seat_number, seat_type) VALUES
(9,'A',1,'standard'),(9,'A',2,'standard'),(9,'A',3,'standard'),(9,'A',4,'standard'),(9,'A',5,'standard'),
(9,'B',1,'standard'),(9,'B',2,'standard'),(9,'B',3,'standard'),(9,'B',4,'standard'),(9,'B',5,'standard'),
(9,'C',1,'vip'),(9,'C',2,'vip'),(9,'C',3,'vip'),(9,'C',4,'vip'),
(9,'D',1,'couple'),(9,'D',2,'couple');

-- Insert seats BHD Star Cách Mạng Tháng 8 - IMAX 1
INSERT INTO Seats (room_id, seat_row, seat_number, seat_type) VALUES
(10,'A',1,'standard'),(10,'A',2,'standard'),(10,'A',3,'standard'),(10,'A',4,'standard'),(10,'A',5,'standard'),
(10,'A',6,'standard'),(10,'A',7,'standard'),(10,'A',8,'standard'),
(10,'B',1,'standard'),(10,'B',2,'standard'),(10,'B',3,'standard'),(10,'B',4,'standard'),(10,'B',5,'standard'),
(10,'B',6,'standard'),(10,'B',7,'standard'),(10,'B',8,'standard'),
(10,'C',1,'vip'),(10,'C',2,'vip'),(10,'C',3,'vip'),(10,'C',4,'vip'),(10,'C',5,'vip'),
(10,'D',1,'couple'),(10,'D',2,'couple'),(10,'D',3,'couple');

-- Insert seats BHD Star Cách Mạng Tháng 8 - Phòng 2
INSERT INTO Seats (room_id, seat_row, seat_number, seat_type) VALUES
(11,'A',1,'standard'),(11,'A',2,'standard'),(11,'A',3,'standard'),(11,'A',4,'standard'),(11,'A',5,'standard'),
(11,'A',6,'standard'),(11,'A',7,'standard'),
(11,'B',1,'standard'),(11,'B',2,'standard'),(11,'B',3,'standard'),(11,'B',4,'standard'),(11,'B',5,'standard'),
(11,'B',6,'standard'),(11,'B',7,'standard'),
(11,'C',1,'vip'),(11,'C',2,'vip'),(11,'C',3,'vip'),(11,'C',4,'vip'),
(11,'D',1,'couple'),(11,'D',2,'couple');

-- Insert seats BHD Star Cách Mạng Tháng 8 - Phòng 3
INSERT INTO Seats (room_id, seat_row, seat_number, seat_type) VALUES
(12,'A',1,'standard'),(12,'A',2,'standard'),(12,'A',3,'standard'),(12,'A',4,'standard'),(12,'A',5,'standard'),
(12,'A',6,'standard'),(12,'A',7,'standard'),
(12,'B',1,'standard'),(12,'B',2,'standard'),(12,'B',3,'standard'),(12,'B',4,'standard'),(12,'B',5,'standard'),
(12,'B',6,'standard'),(12,'B',7,'standard'),
(12,'C',1,'vip'),(12,'C',2,'vip'),(12,'C',3,'vip'),(12,'C',4,'vip'),
(12,'D',1,'couple'),(12,'D',2,'couple');

-- Insert seats Mega GS Tao Đàn - Phòng IMAX
INSERT INTO Seats (room_id, seat_row, seat_number, seat_type) VALUES
(13,'A',1,'standard'),(13,'A',2,'standard'),(13,'A',3,'standard'),(13,'A',4,'standard'),(13,'A',5,'standard'),
(13,'A',6,'standard'),(13,'A',7,'standard'),(13,'A',8,'standard'),
(13,'B',1,'standard'),(13,'B',2,'standard'),(13,'B',3,'standard'),(13,'B',4,'standard'),(13,'B',5,'standard'),
(13,'B',6,'standard'),(13,'B',7,'standard'),(13,'B',8,'standard'),
(13,'C',1,'vip'),(13,'C',2,'vip'),(13,'C',3,'vip'),(13,'C',4,'vip'),(13,'C',5,'vip'),
(13,'D',1,'couple'),(13,'D',2,'couple'),(13,'D',3,'couple');

-- Insert seats Mega GS Tao Đàn - Phòng 2
INSERT INTO Seats (room_id, seat_row, seat_number, seat_type) VALUES
(14,'A',1,'standard'),(14,'A',2,'standard'),(14,'A',3,'standard'),(14,'A',4,'standard'),(14,'A',5,'standard'),
(14,'A',6,'standard'),(14,'A',7,'standard'),
(14,'B',1,'standard'),(14,'B',2,'standard'),(14,'B',3,'standard'),(14,'B',4,'standard'),(14,'B',5,'standard'),
(14,'B',6,'standard'),(14,'B',7,'standard'),
(14,'C',1,'vip'),(14,'C',2,'vip'),(14,'C',3,'vip'),(14,'C',4,'vip'),(14,'C',5,'vip'),
(14,'D',1,'couple'),(14,'D',2,'couple');

-- Insert seats Mega GS Tao Đàn - Phòng 3
INSERT INTO Seats (room_id, seat_row, seat_number, seat_type) VALUES
(15,'A',1,'standard'),(15,'A',2,'standard'),(15,'A',3,'standard'),(15,'A',4,'standard'),(15,'A',5,'standard'),
(15,'A',6,'standard'),(15,'A',7,'standard'),
(15,'B',1,'standard'),(15,'B',2,'standard'),(15,'B',3,'standard'),(15,'B',4,'standard'),(15,'B',5,'standard'),
(15,'B',6,'standard'),(15,'B',7,'standard'),
(15,'C',1,'vip'),(15,'C',2,'vip'),(15,'C',3,'vip'),(15,'C',4,'vip'),(15,'C',5,'vip'),
(15,'D',1,'couple'),(15,'D',2,'couple');

-- Insert showtimes (thời gian khớp duration)
INSERT INTO Showtimes (movie_id, room_id, show_date, start_time, end_time, base_price) VALUES
(1, 2, '2026-04-05', '10:00:00', '11:48:00', 70000),
(1, 4, '2026-04-05', '10:30:00', '12:18:00', 80000),
(1, 8, '2026-04-05', '12:30:00', '14:18:00', 85000),
(1, 11, '2026-04-05', '15:00:00', '16:48:00', 75000),
(1, 1, '2026-04-05', '17:30:00', '19:18:00', 90000),
(1, 5, '2026-04-05', '20:00:00', '21:48:00', 100000),
(2, 3, '2026-04-05', '10:15:00', '11:53:00', 70000),
(2, 6, '2026-04-05', '12:30:00', '14:08:00', 75000),
(2, 7, '2026-04-05', '15:00:00', '16:38:00', 85000),
(2, 12, '2026-04-05', '17:30:00', '19:08:00', 80000),
(2, 13, '2026-04-05', '20:00:00', '21:38:00', 95000),
(3, 4, '2026-04-05', '10:00:00', '11:57:00', 70000),
(3, 9, '2026-04-05', '12:30:00', '14:27:00', 75000),
(3, 2, '2026-04-05', '15:00:00', '16:57:00', 85000),
(3, 10, '2026-04-05', '17:30:00', '19:27:00', 90000),
(3, 3, '2026-04-05', '20:00:00', '21:57:00', 100000),
(4, 5, '2026-04-05', '10:00:00', '11:42:00', 75000),
(4, 7, '2026-04-05', '12:30:00', '14:12:00', 85000),
(4, 11, '2026-04-05', '15:00:00', '16:42:00', 80000),
(4, 14, '2026-04-05', '17:30:00', '19:12:00', 90000),
(4, 1, '2026-04-05', '20:00:00', '21:42:00', 110000),
(5, 1, '2026-04-05', '10:00:00', '13:01:00', 100000),
(5, 13, '2026-04-05', '13:30:00', '16:31:00', 95000),
(5, 10, '2026-04-05', '17:30:00', '20:31:00', 120000),
(6, 8, '2026-04-05', '10:00:00', '12:28:00', 85000),
(6, 4, '2026-04-05', '13:00:00', '15:28:00', 90000),
(6, 12, '2026-04-05', '15:30:00', '17:58:00', 95000),
(6, 6, '2026-04-05', '18:00:00', '20:28:00', 105000),
(7, 3, '2026-04-05', '10:00:00', '13:15:00', 90000),
(7, 14, '2026-04-05', '13:30:00', '16:45:00', 95000),
(7, 2, '2026-04-05', '17:00:00', '20:15:00', 110000),
(8, 1, '2026-04-05', '12:00:00', '15:12:00', 150000),
(8, 13, '2026-04-05', '15:30:00', '18:42:00', 140000),
(8, 10, '2026-04-05', '19:00:00', '22:12:00', 150000),
(9, 5, '2026-04-05', '10:30:00', '12:32:00', 75000),
(9, 9, '2026-04-05', '13:00:00', '15:02:00', 80000),
(9, 7, '2026-04-05', '15:30:00', '17:32:00', 90000),
(9, 11, '2026-04-05', '18:00:00', '20:02:00', 95000),
(9, 3, '2026-04-05', '20:30:00', '22:32:00', 105000),
(10, 2, '2026-04-05', '10:00:00', '12:35:00', 90000),
(10, 8, '2026-04-05', '13:00:00', '15:35:00', 95000),
(10, 4, '2026-04-05', '15:30:00', '18:05:00', 100000),
(10, 15, '2026-04-05', '18:00:00', '20:35:00', 110000),
(10, 1, '2026-04-05', '20:30:00', '23:05:00', 120000),
(1, 4, '2026-04-06', '10:00:00', '11:48:00', 70000),
(1, 7, '2026-04-06', '12:30:00', '14:18:00', 75000),
(1, 2, '2026-04-06', '15:00:00', '16:48:00', 80000),
(1, 11, '2026-04-06', '17:30:00', '19:18:00', 85000),
(1, 1, '2026-04-06', '20:00:00', '21:48:00', 100000),
(1, 5, '2026-04-06', '22:00:00', '23:48:00', 95000),
(4, 3, '2026-04-06', '10:15:00', '11:57:00', 70000),
(4, 6, '2026-04-06', '12:30:00', '14:12:00', 75000),
(4, 8, '2026-04-06', '15:00:00', '16:42:00', 85000),
(4, 12, '2026-04-06', '17:30:00', '19:12:00', 90000),
(4, 9, '2026-04-06', '20:00:00', '21:42:00', 105000),
(6, 1, '2026-04-06', '10:00:00', '12:28:00', 90000),
(6, 13, '2026-04-06', '13:00:00', '15:28:00', 95000),
(6, 10, '2026-04-06', '15:30:00', '17:58:00', 100000),
(6, 4, '2026-04-06', '18:00:00', '20:28:00', 110000),
(6, 2, '2026-04-06', '20:30:00', '22:58:00', 120000),
(8, 1, '2026-04-06', '10:00:00', '13:12:00', 140000),
(8, 10, '2026-04-06', '13:30:00', '16:42:00', 135000),
(8, 13, '2026-04-06', '17:00:00', '20:12:00', 150000),
(10, 5, '2026-04-06', '10:30:00', '13:05:00', 85000),
(10, 14, '2026-04-06', '13:30:00', '16:05:00', 90000),
(10, 7, '2026-04-06', '16:00:00', '18:35:00', 100000),
(10, 3, '2026-04-06', '19:00:00', '21:35:00', 115000),
(5, 1, '2026-04-07', '10:00:00', '13:01:00', 100000),
(5, 10, '2026-04-07', '13:30:00', '16:31:00', 110000),
(5, 13, '2026-04-07', '17:00:00', '20:01:00', 120000),
(1, 2, '2026-04-07', '10:00:00', '11:48:00', 75000),
(1, 6, '2026-04-07', '12:30:00', '14:18:00', 75000),
(1, 8, '2026-04-07', '15:00:00', '16:48:00', 85000),
(1, 11, '2026-04-07', '17:30:00', '19:18:00', 90000),
(1, 4, '2026-04-07', '20:00:00', '21:48:00', 100000),
(4, 3, '2026-04-07', '10:15:00', '11:57:00', 75000),
(4, 9, '2026-04-07', '12:30:00', '14:12:00', 80000),
(4, 7, '2026-04-07', '15:00:00', '16:42:00', 90000),
(4, 5, '2026-04-07', '17:30:00', '19:12:00', 95000),
(4, 14, '2026-04-07', '20:00:00', '21:42:00', 105000),
(9, 12, '2026-04-07', '10:30:00', '12:32:00', 75000),
(9, 2, '2026-04-07', '13:00:00', '15:02:00', 80000),
(9, 15, '2026-04-07', '15:30:00', '17:32:00', 90000),
(9, 4, '2026-04-07', '18:00:00', '20:02:00', 100000),
(9, 11, '2026-04-07', '20:30:00', '22:32:00', 110000),
(6, 1, '2026-04-07', '10:00:00', '12:28:00', 100000),
(6, 3, '2026-04-07', '13:00:00', '15:28:00', 95000),
(6, 8, '2026-04-07', '15:30:00', '17:58:00', 105000),
(6, 10, '2026-04-07', '18:00:00', '20:28:00', 120000),
(7, 1, '2026-04-08', '10:00:00', '13:15:00', 95000),
(7, 13, '2026-04-08', '13:30:00', '16:45:00', 100000),
(7, 10, '2026-04-08', '17:00:00', '20:15:00', 120000),
(10, 2, '2026-04-08', '10:00:00', '12:35:00', 90000),
(10, 5, '2026-04-08', '13:00:00', '15:35:00', 95000),
(10, 8, '2026-04-08', '15:30:00', '18:05:00', 105000),
(10, 4, '2026-04-08', '18:00:00', '20:35:00', 115000),
(10, 12, '2026-04-08', '20:30:00', '23:05:00', 125000),
(2, 3, '2026-04-08', '10:15:00', '11:53:00', 75000),
(2, 6, '2026-04-08', '12:30:00', '14:08:00', 80000),
(2, 9, '2026-04-08', '15:00:00', '16:38:00', 90000),
(2, 11, '2026-04-08', '17:30:00', '19:08:00', 95000),
(2, 7, '2026-04-08', '20:00:00', '21:38:00', 105000),
(3, 14, '2026-04-08', '10:00:00', '11:57:00', 75000),
(3, 2, '2026-04-08', '12:30:00', '14:27:00', 80000),
(3, 15, '2026-04-08', '15:00:00', '16:57:00', 90000),
(3, 4, '2026-04-08', '17:30:00', '19:27:00', 100000),
(3, 1, '2026-04-08', '20:00:00', '21:57:00', 115000);

-- Insert promotions
INSERT INTO Promotions (promo_code, discount_type, discount_value, description, min_tickets, min_amount, applicable_seat_types, start_date, end_date) VALUES
('CINEMA10','percent',10,'Giảm 10% cho tất cả đơn hàng',1,0,'["standard","vip","couple"]','2026-01-01 00:00:00','2026-12-31 23:59:59'),
('STUDENT20','percent',20,'Giảm 20% cho học sinh sinh viên - chỉ áp dụng ghế thường',1,0,'["standard"]','2026-01-01 00:00:00','2026-12-31 23:59:59'),
('SAVE50000','fixed',50000,'Giảm 50,000đ cho đơn hàng từ 200,000đ trở lên',1,200000,'["standard","vip","couple"]','2026-01-01 00:00:00','2026-12-31 23:59:59'),
('FAMILY15','percent',15,'Giảm 15% cho gia đình - tối thiểu 3 vé',3,0,'["standard","vip","couple"]','2026-01-01 00:00:00','2026-12-31 23:59:59'),
('VIP30','percent',30,'Giảm 30% cho ghế VIP - tối thiểu 2 ghế VIP',2,0,'["vip"]','2026-01-01 00:00:00','2026-12-31 23:59:59');