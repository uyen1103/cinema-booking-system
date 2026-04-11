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
<<<<<<< HEAD

INSERT IGNORE INTO movies (title, description, director, `cast`, genre, duration, release_date, poster, poster_url, banner, trailer_url, status) VALUES
('Bố Già','Câu chuyện về Ba Sang – con thứ hai trong 4 anh em ồn ào: Giàu, Sang, Phú, Quý. Ba Sang là một người ga lăng, “quá” tốt bụng và luôn hy sinh vì người khác dù họ có muốn hay không. Quân – Ba Sang’s son là một Youtuber trẻ hiện đại.','Trấn Thành','Trấn Thành, Tuấn Trần, Ngân Chi',
'Drama',128,'2021-03-12','bo_gia.jpg','bo_gia.jpg','bo_gia.jpg','https://www.youtube.com/embed/jluSu8Rw6YE',1),

('Hai Phượng (Furie)','Hai Phượng kể về cuộc hành trình đầy gay cấn và gian của khi người mẹ vùng quê tìm cách cứu con mình thoát khỏi đường dây bắt cóc khổng lồ. Không tấc sắc trong tay, người phụ nữ thân cô thế cô làm sao chống lại bọn xã hội đen tàn ác để giành lại nguồn sống của đời mình?.',
'Lê Văn Kiệt','Ngô Thanh Vân','Action',98,'2019-02-22','hai_phuong.jpg','hai_phuong.jpg','hai_phuong.jpg','https://www.youtube.com/embed/L41c4_kbDoo',1),

('Mắt Biếc','Đạo diễn Victor Vũ trở lại với một tác phẩm chuyển thể từ truyện ngắn cùng tên nổi tiếng của nhà văn Nguyễn Nhật Ánh: Mắt Biếc. Phim kể về chuyện tình đơn phương của chàng thanh niên Ngạn dành cho cô bạn từ thuở nhỏ Hà Lan.',
'Victor Vũ','Trần Nghĩa, Trúc Anh','Romance',117,'2019-12-20','mat_biec.jpg','mat_biec.jpg','mat_biec.jpg','https://www.youtube.com/embed/ITlQ0oU7tDA',1),

('Nhà Bà Nữ','CCâu chuyện xoay quanh gia đình bà Nữ gồm ba thế hệ sống cùng nhau trong một ngôi nhà. Bà Nữ một tay cáng đáng mọi sự, nổi tiếng với quán bánh canh cua và cũng khét tiếng với việc kiểm soát cuộc sống của tất cả mọi người, từ con gái đến con rể. Mọi chuyện diễn ra bình thường cho đến khi cô con gái út si mê anh chàng điển trai xuất thân từ một gia đình giàu có. Truyện phim khắc họa mối quan hệ phức tạp, đa chiều xảy ra với các thành viên trong gia đình. Câu tagline (thông điệp) chính “Ai cũng có lỗi, nhưng ai cũng nghĩ mình là… nạn nhân” chứa nhiều ẩn ý về nội dung bộ phim muốn gửi gắm.',
'Trấn Thành','Lê Giang, Uyển Ân, Song Luân','Drama',102,'2023-01-22','nha_ba_nu.jpg','nha_ba_nu.jpg','nha_ba_nu.jpg','https://www.youtube.com/embed/IkaP0KJWTsQ',1),

('Avengers: Endgame','Sau sự kiện hủy diệt tàn khốc, vũ trụ chìm trong cảnh hoang tàn. Với sự trợ giúp của những đồng minh còn sống sót, biệt đội siêu anh hùng Avengers tập hợp một lần nữa để đảo ngược hành động của Thanos và khôi phục lại trật tự của vũ trụ.',
'Anthony Russo, Joe Russo','Robert Downey Jr., Chris Evans, Mark Ruffalo','Action',181,'2019-04-26','endgame.jpg','endgame.jpg','endgame.jpg','https://www.youtube.com/embed/TcMBFSGVi1c',1),

('Spider-Man: No Way Home','PNgười Nhện: Không Còn Nhà là bộ phim tiếp nối sự kiện của phần Spider-man: Far From Home. Trong phần trước, người phản diện Mysterio đã tiết lộ danh tính thực sự của người nhện là Peter Parker, khiến cho Spiderman phải đối mặt với hàng loạt lời chỉ trích từ công chúng. Tình hình này đã ảnh hưởng không chỉ đến cuộc sống của Peter mà còn khiến cho Dì May, cậu bạn Ned và người yêu MJ bị cuốn vào những rắc rối.',
'Jon Watts','Tom Holland, Zendaya, Benedict Cumberbatch','Action',148,'2021-12-17','spiderman.jpg','spiderman.jpg','spiderman.jpg','https://www.youtube.com/embed/JfVOs4VSpmA',1),

('Titanic','Một cô gái quý tộc mười bảy tuổi phải lòng một chàng họa sĩ tốt bụng nhưng nghèo khó trên con tàu sang trọng nhưng xấu số RMS Titanic.',
'James Cameron','Leonardo DiCaprio, Kate Winslet','Romance',195,'1997-12-19','titanic.jpg','titanic.jpg','titanic.jpg','https://www.youtube.com/embed/Yo2ijvREkyo',1),

('Avatar: The Way of Water','Jake Sully sống cùng gia đình mới của mình trên mặt trăng Pandora ngoài hệ Mặt Trời. Khi một mối đe dọa quen thuộc quay trở lại để hoàn thành những gì đã bắt đầu trước đó, Jake phải hợp tác với Neytiri và quân đội của tộc Na vi để bảo vệ quê hương của họ.',
'James Cameron','Sam Worthington, Zoe Saldana','Sci-Fi',192,'2022-12-16','avatar2.jpg','avatar2.jpg','avatar2.jpgg','https://www.youtube.com/embed/d9MyW72ELq0',1),

('Joker','Arthur Fleck, một chú hề trong các bữa tiệc và một diễn viên hài độc thoại thất bại, sống một cuộc đời nghèo khó cùng người mẹ ốm yếu. Tuy nhiên, khi bị xã hội xa lánh và coi là kẻ lập dị, anh quyết định dấn thân vào cuộc sống hỗn loạn ở thành phố Gotham.',
'Todd Phillips','Joaquin Phoenix','Drama',122,'2019-10-04','joker.jpg','joker.jpg','joker.jpg','https://www.youtube.com/embed/t433PEQGErc',1),

('Dune','Paul Atreides đặt chân đến Arrakis sau khi cha anh nhận trách nhiệm cai quản hành tinh nguy hiểm này. Tuy nhiên, hỗn loạn nổ ra sau một vụ phản bội khi các thế lực xung đột để giành quyền kiểm soát melange, một nguồn tài nguyên quý giá.',
'Denis Villeneuve','Timothée Chalamet, Zendaya','Sci-Fi',155,'2021-10-22','dune.jpg','dune.jpg','dune.jpg','https://www.youtube.com/embed/8g18jFHCLXk',1),

('Deadpool & Wolverine','Deadpool và Wolverine hợp tác trong một cuộc phiêu lưu hành động hài hước đầy bạo lực và bất ngờ.',
'Shawn Levy','Ryan Reynolds, Hugh Jackman','Action',122,'2026-05-10','deadpool_wolverine.jpg','deadpool_wolverine.jpg','deadpool_wolverine.jpg','https://www.youtube.com/embed/73_1biulkYk',2),

('Joker: Folie à Deux','Tiếp tục câu chuyện Joker với một phiên bản tâm lý đen tối và đầy hỗn loạn.','Todd Phillips','Joaquin Phoenix, Lady Gaga','Thriller',135,'2026-10-04','joker_folie.jpg','joker_folie.jpg','joker_folie.jpg','https://www.youtube.com/embed/_OKAwz2MsJs',2),

('Despicable Me 4','Các Minions tiếp tục hành trình mới của mình với hàng loạt tình huống hài hước và cảm động.',
'Chris Renaud','Steve Carell','Animation',105,'2026-07-01','despicable_me_4.jpg','despicable_me_4.jpg','despicable_me_4.jpg','https://www.youtube.com/embed/qQlr9-rF32A',2);


-- Insert rooms
INSERT IGNORE INTO rooms (name, capacity, opening_time, closing_time, status, maintenance_reason) VALUES
('CGV Vincom Đồng Khởi - Phòng IMAX 1', 250, '08:00:00', '23:00:00', 1, NULL),
('CGV Vincom Đồng Khởi - Phòng 2D 1', 150, '08:00:00', '23:00:00', 1, NULL),
('CGV Vincom Đồng Khởi - Phòng 2D 2', 150, '08:00:00', '23:00:00', 1, NULL),
('Galaxy Cinema Nguyễn Du - Phòng 1', 180, '08:00:00', '23:00:00', 1, NULL),
('Galaxy Cinema Nguyễn Du - Phòng 2', 180, '10:00:00', '23:00:00', 1, NULL),
('Galaxy Cinema Nguyễn Du - Phòng 3', 120, '10:00:00', '23:00:00', 1, NULL),
('Lotte Cinema Phan Văn Trị - Phòng Deluxe', 160, '10:30:00', '23:30:00', 1, NULL),
('Lotte Cinema Phan Văn Trị - Phòng Standard', 150, '10:30:00', '23:30:00', 1, NULL),
('Lotte Cinema Phan Văn Trị - Phòng Deluxe 2', 120, '08:30:00', '23:30:00', 1, NULL),
('BHD Star Cách Mạng Tháng 8 - IMAX 1', 200, '08:30:00', '23:30:00', 1, NULL),
('BHD Star Cách Mạng Tháng 8 - Phòng 2', 140, '08:30:00', '23:30:00', 1, NULL),
('BHD Star Cách Mạng Tháng 8 - Phòng 3', 140, '08:30:00', '23:30:00', 1, NULL),
('Mega GS Tao Đàn - Phòng IMAX', 220, '10:15:00', '23:45:00', 1, NULL),
('Mega GS Tao Đàn - Phòng 2', 160, '08:15:00', '23:45:00', 1, NULL),
('Mega GS Tao Đàn - Phòng 3', 120, '08:15:00', '23:45:00', 1, NULL);


-- Insert seats CGV Vincom IMAX 1
INSERT IGNORE INTO seats (room_id, row_name, seat_number, type, status) VALUES
(1,'A',1,'standard',1),(1,'A',2,'standard',1),(1,'A',3,'standard',1),(1,'A',4,'standard',1),(1,'A',5,'standard',1),
(1,'A',6,'standard',1),(1,'A',7,'standard',1),(1,'A',8,'standard',1),(1,'A',9,'standard',1),(1,'A',10,'standard',1),
(1,'B',1,'standard',1),(1,'B',2,'standard',1),(1,'B',3,'standard',1),(1,'B',4,'standard',1),(1,'B',5,'standard',1),
(1,'B',6,'standard',1),(1,'B',7,'standard',1),(1,'B',8,'standard',1),(1,'B',9,'standard',1),(1,'B',10,'standard',1),
(1,'C',1,'vip',  0),(1,'C',2,'vip' ,0),(1,'C',3,'vip' ,0),(1,'C',4,'vip' ,0),(1,'C',5,'vip' ,0),
(1,'C',6,'vip' ,0),(1,'C',7,'vip' ,0),(1,'C',8,'vip' ,0),(1,'C',9,'vip' ,0),(1,'C',10,'vip' ,0),
(1,'D',  11, 'couple' , 0), (1, 'D', 12, 'couple' , 0), (1, 'D', 13, 'couple' , 0), (1, 'D', 14, 'couple' , 0), (1, 'D', 15, 'couple' , 0);

-- Insert seats CGV Vincom 2D 1
INSERT IGNORE INTO seats (room_id, row_name, seat_number, type, status) VALUES
(2,'A',1,'standard',1),(2,'A',2,'standard',1),(2,'A',3,'standard',1),(2,'A',4,'standard',1),(2,'A',5,'standard',1),
(2,'A',6,'standard',1),(2,'A',7,'standard',1),(2,'A',8,'standard',1),
(2,'B',1,'standard',1),(2,'B',2,'standard',1),(2,'B',3,'standard',1),(2,'B',4,'standard',1),(2,'B',5,'standard',1),
(2,'B',6,'standard',1),(2,'B',7,'standard',1),(2,'B',8,'standard',1),
(2,'C',1,'vip' ,0),(2,'C',2,'vip' ,0),(2,'C',3,'vip' ,0),(2,'C',4,'vip' ,0),(2,'C',5,'vip' ,0),
(2,'C',6,'vip' ,0),(2,'C',7,'vip' ,0),(2,'C',8,'vip' ,0),
(2,'D',  9, 'couple' , 0), (2, 'D', 10, 'couple' , 0), (2, 'D', 11, 'couple' , 0), (2, 'D', 12, 'couple' , 0), (2, 'D', 13, 'couple' , 0);

-- Insert seats CGV Vincom 2D 2
INSERT IGNORE INTO seats (room_id, row_name, seat_number, type, status) VALUES
(3,'A',1,'standard',1),(3,'A',2,'standard',1),(3,'A',3,'standard',1),(3,'A',4,'standard',1),(3,'A',5,'standard',1),
(3,'A',6,'standard',1),(3,'A',7,'standard',1),(3,'A',8,'standard',1),
(3,'B',1,'standard',1),(3,'B',2,'standard',1),(3,'B',3,'standard',1),(3,'B',4,'standard',1),(3,'B',5,'standard',1),
(3,'B',6,'standard',1),(3,'B',7,'standard',1),(3,'B',8,'standard',1),
(3,'C',1,'vip' ,0),(3,'C',2,'vip' ,0),(3,'C',3,'vip' ,0),(3,'C',4,'vip' ,0),(3,'C',5,'vip' ,0),
(3,'C',6,'vip' ,0),(3,'C',7,'vip' ,0),(3,'C',8,'vip' ,0),
(3,'D',  9, 'couple' , 0), (3, 'D', 10, 'couple' , 0), (3, 'D', 11, 'couple' , 0), (3, 'D', 12, 'couple' , 0), (3, 'D', 13, 'couple' , 0);

-- Insert seats Galaxy Cinema Nguyễn Du - Phòng 1
INSERT IGNORE INTO seats (room_id, row_name, seat_number, type, status) VALUES
(4,'A',1,'standard',1),(4,'A',2,'standard',1),(4,'A',3,'standard',1),(4,'A',4,'standard',1),(4,'A',5,'standard',1),
(4,'A',6,'standard',1),(4,'A',7,'standard',1),
(4,'B',1,'standard',1),(4,'B',2,'standard',1),(4,'B',3,'standard',1),(4,'B',4,'standard',1),(4,'B',5,'standard',1),
(4,'B',6,'standard',1),(4,'B',7,'standard',1),
(4,'C',1,'vip' ,0),(4,'C',2,'vip' ,0),(4,'C',3,'vip' ,0),(4,'C',4,'vip' ,0),(4,'C',5,'vip' ,0),
(4,'C',6,'vip' ,0),(4,'C',7,'vip' ,0),
(4,'D',  8, 'couple' , 0), (4, 'D', 9, 'couple' , 0), (4, 'D', 10, 'couple' , 0), (4, 'D', 11, 'couple' , 0), (4, 'D', 12, 'couple' , 0);

-- Insert seats Galaxy Cinema Nguyễn Du - Phòng 2
INSERT IGNORE INTO seats (room_id, row_name, seat_number, type, status) VALUES
(5,'A',1,'standard',1),(5,'A',2,'standard',1),(5,'A',3,'standard',1),(5,'A',4,'standard',1),(5,'A',5,'standard',1),
(5,'A',6,'standard',1),(5,'A',7,'standard',1),
(5,'B',1,'standard',1),(5,'B',2,'standard',1),(5,'B',3,'standard',1),(5,'B',4,'standard',1),(5,'B',5,'standard',1),
(5,'B',6,'standard',1),(5,'B',7,'standard',1),
(5,'C',1,'vip' ,0),(5,'C',2,'vip' ,0),(5,'C',3,'vip' ,0),(5,'C',4,'vip' ,0),(5,'C',5,'vip' ,0),
(5,'C',6,'vip' ,0),(5,'C',7,'vip' ,0),
(5,'D',  8, 'couple' , 0), (5, 'D', 9, 'couple' , 0), (5, 'D', 10, 'couple' , 0), (5, 'D', 11, 'couple' , 0), (5, 'D', 12, 'couple' , 0);

-- Insert seats Galaxy Cinema Nguyễn Du - Phòng 3
INSERT IGNORE INTO seats (room_id, row_name, seat_number, type, status) VALUES
(6,'A',1,'standard',1),(6,'A',2,'standard',1),(6,'A',3,'standard',1),(6,'A',4,'standard',1),(6,'A',5,'standard',1),
(6,'B',1,'standard',1),(6,'B',2,'standard',1),(6,'B',3,'standard',1),(6,'B',4,'standard',1),(6,'B',5,'standard',1),
(6,'C',1,'vip' ,0),(6,'C',2,'vip' ,0),(6,'C',3,'vip' ,0),(6,'C',4,'vip' ,0),
(6,'D',  5, 'couple' , 0), (6, 'D', 6, 'couple' , 0), (6, 'D', 7, 'couple' , 0), (6, 'D', 8, 'couple' , 0), (6, 'D', 9, 'couple' , 0);

-- Insert seats Lotte Cinema Phan Văn Trị - Phòng Deluxe
INSERT IGNORE INTO seats (room_id, row_name, seat_number, type, status) VALUES
(7,'A',1,'standard',1),(7,'A',2,'standard',1),(7,'A',3,'standard',1),(7,'A',4,'standard',1),(7,'A',5,'standard',1),
(7,'A',6,'standard',1),(7,'A',7,'standard',1),
(7,'B',1,'standard',1),(7,'B',2,'standard',1),(7,'B',3,'standard',1),(7,'B',4,'standard',1),(7,'B',5,'standard',1),
(7,'B',6,'standard',1),(7,'B',7,'standard',1),
(7,'C',1,'vip' ,0),(7,'C',2,'vip' ,0),(7,'C',3,'vip' ,0),(7,'C',4,'vip' ,0),(7,'C',5,'vip' ,0),
(7,'D',  6, 'couple' , 0), (7, 'D', 7, 'couple' , 0), (7, 'D', 8, 'couple' , 0), (7, 'D', 9, 'couple' , 0), (7, 'D', 10, 'couple' , 0);

-- Insert seats Lotte Cinema Phan Văn Trị - Phòng Standard
INSERT IGNORE INTO seats (room_id, row_name, seat_number, type, status) VALUES
(8,'A',1,'standard',1),(8,'A',2,'standard',1),(8,'A',3,'standard',1),(8,'A',4,'standard',1),(8,'A',5,'standard',1),
(8,'A',6,'standard',1),(8,'A',7,'standard',1),(8,'A',8,'standard',1),
(8,'B',1,'standard',1),(8,'B',2,'standard',1),(8,'B',3,'standard',1),(8,'B',4,'standard',1),(8,'B',5,'standard',1),
(8,'B',6,'standard',1),(8,'B',7,'standard',1),(8,'B',8,'standard',1),
(8,'C',1,'vip' ,0),(8,'C',2,'vip' ,0),(8,'C',3,'vip' ,0),(8,'C',4,'vip' ,0),(8,'C',5,'vip' ,0),
(8,'D',  6, 'couple' , 0), (8, 'D', 7, 'couple' , 0), (8, 'D', 8, 'couple' , 0), (8, 'D', 9, 'couple' , 0), (8, 'D', 10, 'couple' , 0);

-- Insert seats Lotte Cinema Phan Văn Trị - Phòng Deluxe 2
INSERT IGNORE INTO seats (room_id, row_name, seat_number, type, status) VALUES
(9,'A',1,'standard',1),(9,'A',2,'standard',1),(9,'A',3,'standard',1),(9,'A',4,'standard',1),(9,'A',5,'standard',1),
(9,'A',6,'standard',1),(9,'A',7,'standard',1),
(9,'B',1,'standard',1),(9,'B',2,'standard',1),(9,'B',3,'standard',1),(9,'B',4,'standard',1),(9,'B',5,'standard',1),
(9,'B',6,'standard',1),(9,'B',7,'standard',1),
(9,'C',1,'vip' ,0),(9,'C',2,'vip' ,0),(9,'C',3,'vip' ,0),(9,'C',4,'vip' ,0),
(9,'D',  5, 'couple' , 0), (9, 'D', 6, 'couple' , 0), (9, 'D', 7, 'couple' , 0), (9, 'D', 8, 'couple' , 0), (9, 'D', 9, 'couple' , 0);

-- Insert seats BHD Star Cách Mạng Tháng 8 - IMAX 1
INSERT IGNORE INTO seats (room_id, row_name, seat_number, type, status) VALUES
(10,'A',1,'standard',1),(10,'A',2,'standard',1),(10,'A',3,'standard',1),(10,'A',4,'standard',1),(10,'A',5,'standard',1),
(10,'A',6,'standard',1),(10,'A',7,'standard',1),(10,'A',8,'standard',1),
(10,'B',1,'standard',1),(10,'B',2,'standard',1),(10,'B',3,'standard',1),(10,'B',4,'standard',1),(10,'B',5,'standard',1),
(10,'B',6,'standard',1),(10,'B',7,'standard',1),(10,'B',8,'standard',1),
(10,'C',1,'vip' ,0),(10,'C',2,'vip' ,0),(10,'C',3,'vip' ,0),(10,'C',4,'vip' ,0),(10,'C',5,'vip' ,0),
(10,'D',  6, 'couple' , 0), (10, 'D', 7, 'couple' , 0), (10, 'D', 8, 'couple' , 0), (10, 'D', 9, 'couple' , 0), (10, 'D', 10, 'couple' , 0);

-- Insert seats BHD Star Cách Mạng Tháng 8 - Phòng 2
INSERT IGNORE INTO seats (room_id, row_name, seat_number, type, status) VALUES
(11,'A',1,'standard',1),(11,'A',2,'standard',1),(11,'A',3,'standard',1),(11,'A',4,'standard',1),(11,'A',5,'standard',1),
(11,'A',6,'standard',1),(11,'A',7,'standard',1),
(11,'B',1,'standard',1),(11,'B',2,'standard',1),(11,'B',3,'standard',1),(11,'B',4,'standard',1),(11,'B',5,'standard',1),
(11,'B',6,'standard',1),(11,'B',7,'standard',1),
(11,'C',1,'vip' ,0),(11,'C',2,'vip' ,0),(11,'C',3,'vip' ,0),(11,'C',4,'vip' ,0),
(11,'D',  5, 'couple' , 0), (11, 'D', 6, 'couple' , 0), (11, 'D', 7, 'couple' , 0), (11, 'D', 8, 'couple' , 0), (11, 'D', 9, 'couple' , 0);

-- Insert seats BHD Star Cách Mạng Tháng 8 - Phòng 3
INSERT IGNORE INTO seats (room_id, row_name, seat_number, type, status) VALUES
(12,'A',1,'standard',1),(12,'A',2,'standard',1),(12,'A',3,'standard',1),(12,'A',4,'standard',1),(12,'A',5,'standard',1),
(12,'A',6,'standard',1),(12,'A',7,'standard',1),
(12,'B',1,'standard',1),(12,'B',2,'standard',1),(12,'B',3,'standard',1),(12,'B',4,'standard',1),(12,'B',5,'standard',1),
(12,'B',6,'standard',1),(12,'B',7,'standard',1),
(12,'C',1,'vip' ,0),(12,'C',2,'vip' ,0),(12,'C',3,'vip' ,0),(12,'C',4,'vip' ,0),
(12,'D',  5, 'couple' , 0), (12, 'D', 6, 'couple' , 0), (12, 'D', 7, 'couple' , 0), (12, 'D', 8, 'couple' , 0), (12, 'D', 9, 'couple' , 0);

-- Insert seats Mega GS Tao Đàn - Phòng IMAX
INSERT IGNORE INTO seats (room_id, row_name, seat_number, type, status) VALUES
(13,'A',1,'standard',1),(13,'A',2,'standard',1),(13,'A',3,'standard',1),(13,'A',4,'standard',1),(13,'A',5,'standard',1),
(13,'A',6,'standard',1),(13,'A',7,'standard',1),(13,'A',8,'standard',1),
(13,'B',1,'standard',1),(13,'B',2,'standard',1),(13,'B',3,'standard',1),(13,'B',4,'standard',1),(13,'B',5,'standard',1),
(13,'B',6,'standard',1),(13,'B',7,'standard',1),(13,'B',8,'standard',1),
(13,'C',1,'vip' ,0),(13,'C',2,'vip' ,0),(13,'C',3,'vip' ,0),(13,'C',4,'vip' ,0),(13,'C',5,'vip' ,0),
(13,'D',  6, 'couple' , 0), (13, 'D', 7, 'couple' , 0), (13, 'D', 8, 'couple' , 0), (13, 'D', 9, 'couple' , 0), (13, 'D', 10, 'couple' , 0);

-- Insert seats Mega GS Tao Đàn - Phòng 2
INSERT IGNORE INTO seats (room_id, row_name, seat_number, type, status) VALUES
(14,'A',1,'standard',1),(14,'A',2,'standard',1),(14,'A',3,'standard',1),(14,'A',4,'standard',1),(14,'A',5,'standard',1),
(14,'A',6,'standard',1),(14,'A',7,'standard',1),
(14,'B',1,'standard',1),(14,'B',2,'standard',1),(14,'B',3,'standard',1),(14,'B',4,'standard',1),(14,'B',5,'standard',1),
(14,'B',6,'standard',1),(14,'B',7,'standard',1),
(14,'C',1,'vip' ,0),(14,'C',2,'vip' ,0),(14,'C',3,'vip' ,0),(14,'C',4,'vip' ,0),(14,'C',5,'vip' ,0),
(14,'D',  6, 'couple' , 0), (14, 'D', 7, 'couple' , 0), (14, 'D', 8, 'couple' , 0), (14, 'D', 9, 'couple' , 0), (14, 'D', 10, 'couple' , 0);

-- Insert seats Mega GS Tao Đàn - Phòng 3
INSERT IGNORE INTO seats (room_id, row_name, seat_number, type, status) VALUES
(15,'A',1,'standard',1),(15,'A',2,'standard',1),(15,'A',3,'standard',1),(15,'A',4,'standard',1),(15,'A',5,'standard',1),
(15,'A',6,'standard',1),(15,'A',7,'standard',1),
(15,'B',1,'standard',1),(15,'B',2,'standard',1),(15,'B',3,'standard',1),(15,'B',4,'standard',1),(15,'B',5,'standard',1),
(15,'B',6,'standard',1),(15,'B',7,'standard',1),
(15,'C',1,'vip' ,0),(15,'C',2,'vip' ,0),(15,'C',3,'vip' ,0),(15,'C',4,'vip' ,0),(15,'C',5,'vip' ,0),
(15,'D',  6, 'couple' , 0), (15, 'D', 7, 'couple' , 0), (15, 'D', 8, 'couple' , 0), (15, 'D', 9, 'couple' , 0), (15, 'D', 10, 'couple' , 0);

-- Insert showtimes (thời gian khớp duration)
INSERT IGNORE INTO showtimes (movie_id, room_id, show_date, start_time, end_time, price, base_price, status) VALUES
(1, 2, CURDATE() + INTERVAL 3 DAY, '10:00:00', '11:48:00', 70000, 70000, 1),
(1, 4, CURDATE() + INTERVAL 4 DAY, '10:30:00', '12:18:00', 80000, 80000, 1),
(1, 8, CURDATE() + INTERVAL 6 DAY, '12:30:00', '14:18:00', 85000, 85000, 1),
(1, 11, CURDATE() + INTERVAL 2 DAY, '15:00:00', '16:48:00', 75000, 75000, 1),
(1, 1, CURDATE() + INTERVAL 1 DAY, '17:30:00', '19:18:00', 90000, 90000, 1),
(1, 5, CURDATE() + INTERVAL 2 DAY, '20:00:00', '21:48:00', 100000, 100000,1),
(2, 3, CURDATE() + INTERVAL 3 DAY, '10:15:00', '11:53:00', 70000, 70000, 1),
(2, 6, CURDATE() + INTERVAL 4 DAY, '12:30:00', '14:08:00', 75000, 75000, 1),
(2, 7, CURDATE() + INTERVAL 2 DAY, '15:00:00', '16:38:00', 85000, 85000, 1),
(2, 12, CURDATE() + INTERVAL 2 DAY, '17:30:00', '19:08:00', 80000, 80000, 1),
(2, 13, CURDATE() + INTERVAL 3 DAY, '20:00:00', '21:38:00', 95000, 95000, 1),
(3, 4, CURDATE() + INTERVAL 1 DAY, '10:00:00', '11:57:00', 70000, 70000, 1),
(3, 9, CURDATE() + INTERVAL 5 DAY, '12:30:00', '14:27:00', 75000, 75000, 1),
(3, 2, CURDATE() + INTERVAL 1 DAY, '15:00:00', '16:57:00', 85000, 85000, 1),
(3, 10, CURDATE() + INTERVAL 1 DAY, '19:27:00', '21:27:00', 90000, 90000, 1),
(3, 3, CURDATE() + INTERVAL 2 DAY, '20:00:00', '21:57:00', 100000, 100000, 1),
(4, 5, CURDATE() + INTERVAL 2 DAY, '10:00:00', '11:42:00', 75000, 75000, 1),
(4, 7, CURDATE() + INTERVAL 3 DAY, '12:30:00', '14:12:00', 85000, 85000, 1),
(4, 11, CURDATE() + INTERVAL 3 DAY, '15:00:00', '16:42:00', 80000, 80000, 1),
(4, 14, CURDATE() + INTERVAL 3 DAY, '17:30:00', '19:12:00', 90000, 90000, 1),
(4, 1, CURDATE() + INTERVAL 4 DAY, '20:00:00', '21:42:00', 110000, 110000, 1),
(5, 1, CURDATE() + INTERVAL 4 DAY, '10:00:00', '13:01:00', 105000, 105000, 1),
(5, 8, CURDATE() + INTERVAL 4 DAY, '14:30:00', '17:30:00', 120000, 120000, 1),
(5, 12, CURDATE() + INTERVAL 5 DAY, '18:00:00', '21:00:00', 130000, 130000, 1),
(6, 6, CURDATE() + INTERVAL 5 DAY, '10:30:00', '12:58:00', 90000, 90000, 1),
(6, 9, CURDATE() + INTERVAL 2 DAY, '13:30:00', '15:58:00', 95000, 95000, 1),
(6, 4, CURDATE() + INTERVAL 2 DAY, '16:30:00', '18:58:00', 100000, 100000, 1),
(7, 2, CURDATE() + INTERVAL 2 DAY, '17:00:00', '20:15:00', 110000, 110000, 1),
(8, 1, CURDATE() + INTERVAL 3 DAY, '12:00:00', '15:12:00', 150000, 150000, 1),
(8, 13, CURDATE() + INTERVAL 2 DAY, '15:30:00', '18:42:00', 140000, 140000, 1),
(8, 10, CURDATE() + INTERVAL 1 DAY, '19:00:00', '22:12:00', 150000, 150000, 1),
(9, 5, CURDATE() + INTERVAL 1 DAY, '10:30:00', '12:32:00', 75000, 75000, 1),
(9, 9, CURDATE() + INTERVAL 1 DAY, '12:30:00', '14:32:00', 80000, 80000, 1),
(9, 14, CURDATE() + INTERVAL 6 DAY, '15:00:00', '17:02:00', 85000, 85000, 1),
(9, 3, CURDATE() + INTERVAL 3 DAY, '17:30:00', '19:32:00', 90000, 90000, 1),
(9, 2, CURDATE() + INTERVAL 2 DAY, '20:00:00', '22:02:00', 100000, 100000, 1),
(1, 2, CURDATE() + INTERVAL 4 DAY, '14:00:00', '15:48:00', 70000, 70000, 1),
(1, 2, CURDATE() + INTERVAL 2 DAY, '18:30:00', '20:18:00', 80000, 80000, 1),
(2, 3, CURDATE() + INTERVAL 3 DAY, '14:30:00', '16:08:00', 75000, 75000, 1),
(2, 3, CURDATE() + INTERVAL 5 DAY, '18:00:00', '19:38:00', 85000, 85000, 1),
(5, 1, CURDATE() + INTERVAL 1 DAY, '14:00:00', '17:01:00', 105000, 105000, 1),
(6, 8, CURDATE() + INTERVAL 1 DAY, '15:00:00', '17:28:00', 90000, 90000, 1),
(7, 3, CURDATE() + INTERVAL 4 DAY, '14:00:00', '17:15:00', 95000, 95000, 1),
(8, 1, CURDATE() + INTERVAL 2 DAY, '17:30:00', '20:42:00', 155000, 155000, 1),
(9, 5, CURDATE() + INTERVAL 3 DAY, '15:30:00', '17:32:00', 80000, 80000, 1);


-- Insert promotions
INSERT IGNORE INTO promotions (code, promo_code, title, discount_type, discount_value, description, min_tickets, min_amount, applicable_seat_types, min_order_amount, max_discount, usage_limit, used_count, budget, start_date, end_date, status) VALUES
('CINEMA10', 'CINEMA10', 'Giảm 10% cho tất cả đơn hàng', 'percent', 10, 'Giảm 10% cho tất cả đơn hàng', 1, 0, JSON_ARRAY('standard', 'vip', 'couple'), 0, 100000, 1000, 0, 0, NOW() - INTERVAL 7 DAY, NOW() + INTERVAL 15 DAY, 1),
('STUDENT20', 'STUDENT20', 'Giảm 20% cho học sinh sinh viên - chỉ áp dụng ghế thường', 'percent', 20, 'Giảm 20% cho học sinh sinh viên - chỉ áp dụng ghế thường', 1, 0, JSON_ARRAY('standard'), 0, 100000, 500, 0, 0, NOW() - INTERVAL 7 DAY, NOW() + INTERVAL 20 DAY, 1),
('SAVE50000', 'SAVE50000', 'Giảm 50,000đ cho đơn hàng từ 200,000đ trở lên', 'fixed', 50000, 'Giảm 50,000đ cho đơn hàng từ 200,000đ trở lên', 1, 0, JSON_ARRAY('standard', 'vip', 'couple'), 200000, 50000, 2000, 0, 0, NOW() - INTERVAL 7 DAY, NOW() + INTERVAL 25 DAY, 1),
('FAMILY15', 'FAMILY15', 'Giảm 15% cho gia đình - tối thiểu 3 vé', 'percent', 15, 'Giảm 15% cho gia đình - tối thiểu 3 vé', 3, 0, JSON_ARRAY('standard', 'vip', 'couple'), 0, 150000, 3000, 0, 0, NOW() - INTERVAL 7 DAY, NOW() + INTERVAL 30 DAY, 1),
('VIP30', 'VIP30', 'Giảm 30% cho ghế VIP - tối thiểu 2 ghế VIP', 'percent', 30, 'Giảm 30% cho ghế VIP - tối thiểu 2 ghế VIP', 2, 0, JSON_ARRAY('vip', 'couple'), 0, 300000, 1000, 0, 0, NOW() - INTERVAL 7 DAY, NOW() + INTERVAL 30 DAY, 1);


=======
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
-- Clear existing showtimes
DELETE FROM Showtimes;

INSERT INTO Showtimes (movie_id, room_id, show_date, start_time, end_time, base_price) VALUES
(1, 2, '2026-04-15', '10:00:00', '11:48:00', 70000),
(1, 4, '2026-04-15', '10:30:00', '12:18:00', 80000),
(1, 8, '2026-04-15', '12:30:00', '14:18:00', 85000),
(1, 11, '2026-04-15', '15:00:00', '16:48:00', 75000),
(1, 1, '2026-04-15', '17:30:00', '19:18:00', 90000),
(1, 5, '2026-04-15', '20:00:00', '21:48:00', 100000),
(2, 3, '2026-04-15', '10:15:00', '11:53:00', 70000),
(2, 6, '2026-04-15', '12:30:00', '14:08:00', 75000),
(2, 7, '2026-04-15', '15:00:00', '16:38:00', 85000),
(2, 12, '2026-04-15', '17:30:00', '19:08:00', 80000),
(2, 13, '2026-04-15', '20:00:00', '21:38:00', 95000),
(3, 4, '2026-04-15', '10:00:00', '11:57:00', 70000),
(3, 9, '2026-04-15', '12:30:00', '14:27:00', 75000),
(3, 2, '2026-04-15', '15:00:00', '16:57:00', 85000),
(3, 10, '2026-04-15', '17:30:00', '19:27:00', 90000),
(3, 3, '2026-04-15', '20:00:00', '21:57:00', 100000),
(4, 5, '2026-04-15', '10:00:00', '11:42:00', 75000),
(4, 7, '2026-04-15', '12:30:00', '14:12:00', 85000),
(4, 11, '2026-04-15', '15:00:00', '16:42:00', 80000),
(4, 14, '2026-04-15', '17:30:00', '19:12:00', 90000),
(4, 1, '2026-04-15', '20:00:00', '21:42:00', 110000),
(5, 1, '2026-04-15', '10:00:00', '13:01:00', 100000),
(5, 13, '2026-04-15', '13:30:00', '16:31:00', 95000),
(5, 10, '2026-04-15', '17:30:00', '20:31:00', 120000),
(6, 8, '2026-04-15', '10:00:00', '12:28:00', 85000),
(6, 4, '2026-04-15', '13:00:00', '15:28:00', 90000),
(6, 12, '2026-04-15', '15:30:00', '17:58:00', 95000),
(6, 6, '2026-04-15', '18:00:00', '20:28:00', 105000),
(7, 3, '2026-04-15', '10:00:00', '13:15:00', 90000),
(7, 14, '2026-04-15', '13:30:00', '16:45:00', 95000),
(7, 2, '2026-04-15', '17:00:00', '20:15:00', 110000),
(8, 1, '2026-04-15', '12:00:00', '15:12:00', 150000),
(8, 13, '2026-04-15', '15:30:00', '18:42:00', 140000),
(8, 10, '2026-04-15', '19:00:00', '22:12:00', 150000),
(9, 5, '2026-04-15', '10:30:00', '12:32:00', 75000),
(9, 9, '2026-04-15', '13:00:00', '15:02:00', 80000),
(9, 7, '2026-04-15', '15:30:00', '17:32:00', 90000),
(9, 11, '2026-04-15', '18:00:00', '20:02:00', 95000),
(9, 3, '2026-04-15', '20:30:00', '22:32:00', 105000),
(10, 2, '2026-04-15', '10:00:00', '12:35:00', 90000),
(10, 8, '2026-04-15', '13:00:00', '15:35:00', 95000),
(10, 4, '2026-04-15', '15:30:00', '18:05:00', 100000),
(10, 15, '2026-04-15', '18:00:00', '20:35:00', 110000),
(10, 1, '2026-04-15', '20:30:00', '23:05:00', 120000),
(1, 4, '2026-04-16', '10:00:00', '11:48:00', 70000),
(1, 7, '2026-04-16', '12:30:00', '14:18:00', 75000),
(1, 2, '2026-04-16', '15:00:00', '16:48:00', 80000),
(1, 11, '2026-04-16', '17:30:00', '19:18:00', 85000),
(1, 1, '2026-04-16', '20:00:00', '21:48:00', 100000),
(1, 5, '2026-04-16', '22:00:00', '23:48:00', 95000),
(4, 3, '2026-04-16', '10:15:00', '11:57:00', 70000),
(4, 6, '2026-04-16', '12:30:00', '14:12:00', 75000),
(4, 8, '2026-04-16', '15:00:00', '16:42:00', 85000),
(4, 12, '2026-04-16', '17:30:00', '19:12:00', 90000),
(4, 9, '2026-04-16', '20:00:00', '21:42:00', 105000),
(6, 1, '2026-04-16', '10:00:00', '12:28:00', 90000),
(6, 13, '2026-04-16', '13:00:00', '15:28:00', 95000),
(6, 10, '2026-04-16', '15:30:00', '17:58:00', 100000),
(6, 4, '2026-04-16', '18:00:00', '20:28:00', 110000),
(6, 2, '2026-04-16', '20:30:00', '22:58:00', 120000),
(8, 1, '2026-04-16', '10:00:00', '13:12:00', 140000),
(8, 10, '2026-04-16', '13:30:00', '16:42:00', 135000),
(8, 13, '2026-04-16', '17:00:00', '20:12:00', 150000),
(10, 5, '2026-04-16', '10:30:00', '13:05:00', 85000),
(10, 14, '2026-04-16', '13:30:00', '16:05:00', 90000),
(10, 7, '2026-04-16', '16:00:00', '18:35:00', 100000),
(10, 3, '2026-04-16', '19:00:00', '21:35:00', 115000),
(5, 1, '2026-04-17', '10:00:00', '13:01:00', 100000),
(5, 10, '2026-04-17', '13:30:00', '16:31:00', 110000),
(5, 13, '2026-04-17', '17:00:00', '20:01:00', 120000),
(1, 2, '2026-04-17', '10:00:00', '11:48:00', 75000),
(1, 6, '2026-04-17', '12:30:00', '14:18:00', 75000),
(1, 8, '2026-04-17', '15:00:00', '16:48:00', 85000),
(2, 4, '2026-04-18', '10:00:00', '11:48:00', 70000),
(2, 7, '2026-04-18', '12:30:00', '14:18:00', 75000),
(2, 2, '2026-04-18', '15:00:00', '16:48:00', 80000),
(3, 11, '2026-04-18', '17:30:00', '19:18:00', 85000),
(3, 1, '2026-04-18', '20:00:00', '21:48:00', 100000),
(3, 5, '2026-04-18', '22:00:00', '23:48:00', 95000),
(4, 3, '2026-04-19', '10:15:00', '11:57:00', 70000),
(4, 6, '2026-04-19', '12:30:00', '14:12:00', 75000),
(4, 8, '2026-04-19', '15:00:00', '16:42:00', 85000),
(5, 12, '2026-04-19', '17:30:00', '19:12:00', 90000),
(5, 9, '2026-04-19', '20:00:00', '21:42:00', 105000),
(6, 1, '2026-04-20', '10:00:00', '12:28:00', 90000),
(6, 13, '2026-04-20', '13:00:00', '15:28:00', 95000),
(6, 10, '2026-04-20', '15:30:00', '17:58:00', 100000),
(7, 4, '2026-04-20', '18:00:00', '20:28:00', 110000),
(7, 2, '2026-04-20', '20:30:00', '22:58:00', 120000),
(8, 1, '2026-04-21', '10:00:00', '13:12:00', 140000),
(8, 10, '2026-04-21', '13:30:00', '16:42:00', 135000),
(8, 13, '2026-04-21', '17:00:00', '20:12:00', 150000),
(9, 5, '2026-04-21', '10:30:00', '13:05:00', 85000),
(9, 14, '2026-04-21', '13:30:00', '16:05:00', 90000),
(9, 7, '2026-04-21', '16:00:00', '18:35:00', 100000),
(10, 3, '2026-04-21', '19:00:00', '21:35:00', 115000),
(1, 1, '2026-04-22', '10:00:00', '13:01:00', 100000),
(1, 10, '2026-04-22', '13:30:00', '16:31:00', 110000),
(1, 13, '2026-04-22', '17:00:00', '20:01:00', 120000),
(2, 2, '2026-04-22', '10:00:00', '11:48:00', 75000),
(2, 6, '2026-04-22', '12:30:00', '14:18:00', 75000),
(2, 8, '2026-04-22', '15:00:00', '16:48:00', 85000),
(1, 11, '2026-04-15', '17:30:00', '19:18:00', 90000),
(1, 4, '2026-04-15', '20:00:00', '21:48:00', 100000),
(4, 3, '2026-04-15', '10:15:00', '11:57:00', 75000),
(4, 9, '2026-04-15', '12:30:00', '14:12:00', 80000),
(4, 7, '2026-04-15', '15:00:00', '16:42:00', 90000),
(4, 5, '2026-04-15', '17:30:00', '19:12:00', 95000),
(4, 14, '2026-04-15', '20:00:00', '21:42:00', 105000),
(9, 12, '2026-04-15', '10:30:00', '12:32:00', 75000),
(9, 2, '2026-04-15', '13:00:00', '15:02:00', 80000),
(9, 15, '2026-04-15', '15:30:00', '17:32:00', 90000),
(9, 4, '2026-04-15', '18:00:00', '20:02:00', 100000),
(9, 11, '2026-04-15', '20:30:00', '22:32:00', 110000),
(6, 1, '2026-04-15', '10:00:00', '12:28:00', 100000),
(6, 3, '2026-04-15', '13:00:00', '15:28:00', 95000),
(6, 8, '2026-04-15', '15:30:00', '17:58:00', 105000),
(6, 10, '2026-04-15', '18:00:00', '20:28:00', 120000),
(7, 1, '2026-04-16', '10:00:00', '13:15:00', 95000),
(7, 13, '2026-04-16', '13:30:00', '16:45:00', 100000),
(7, 10, '2026-04-16', '17:00:00', '20:15:00', 120000),
(10, 2, '2026-04-16', '10:00:00', '12:35:00', 90000),
(10, 5, '2026-04-16', '13:00:00', '15:35:00', 95000),
(10, 8, '2026-04-16', '15:30:00', '18:05:00', 105000),
(10, 4, '2026-04-16', '18:00:00', '20:35:00', 115000),
(10, 12, '2026-04-16', '20:30:00', '23:05:00', 125000),
(2, 3, '2026-04-16', '10:15:00', '11:53:00', 75000),
(2, 6, '2026-04-16', '12:30:00', '14:08:00', 80000),
(2, 9, '2026-04-16', '15:00:00', '16:38:00', 90000),
(2, 11, '2026-04-16', '17:30:00', '19:08:00', 95000),
(2, 7, '2026-04-16', '20:00:00', '21:38:00', 105000),
(3, 14, '2026-04-16', '10:00:00', '11:57:00', 75000),
(3, 2, '2026-04-16', '12:30:00', '14:27:00', 80000),
(3, 15, '2026-04-16', '15:00:00', '16:57:00', 90000),
(3, 4, '2026-04-16', '17:30:00', '19:27:00', 100000),
(3, 1, '2026-04-16', '20:00:00', '21:57:00', 115000);

-- Insert promotions
INSERT INTO Promotions (promo_code, discount_type, discount_value, description, min_tickets, min_amount, applicable_seat_types, start_date, end_date) VALUES
('CINEMA10','percent',10,'Giảm 10% cho tất cả đơn hàng',1,0,'["standard","vip","couple"]','2026-01-01 00:00:00','2026-12-31 23:59:59'),
('STUDENT20','percent',20,'Giảm 20% cho học sinh sinh viên - chỉ áp dụng ghế thường',1,0,'["standard"]','2026-01-01 00:00:00','2026-12-31 23:59:59'),
('SAVE50000','fixed',50000,'Giảm 50,000đ cho đơn hàng từ 200,000đ trở lên',1,200000,'["standard","vip","couple"]','2026-01-01 00:00:00','2026-12-31 23:59:59'),
('FAMILY15','percent',15,'Giảm 15% cho gia đình - tối thiểu 3 vé',3,0,'["standard","vip","couple"]','2026-01-01 00:00:00','2026-12-31 23:59:59'),
('VIP30','percent',30,'Giảm 30% cho ghế VIP - tối thiểu 2 ghế VIP',2,0,'["vip"]','2026-01-01 00:00:00','2026-12-31 23:59:59');


-- Insert default pricing
INSERT INTO SeatPrices (seat_type, price_multiplier, description) VALUES
('standard', 1.00, 'Ghế thường'),
('vip', 1.20, 'Ghế VIP'),
('couple', 1.40, 'Ghế đôi');

>>>>>>> e6dd52a270c28a0b25e9fda68fc5622028b7af4d
