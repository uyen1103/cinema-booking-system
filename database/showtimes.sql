DROP TABLE IF EXISTS `showtimes`;

CREATE TABLE `showtimes` (
`showtime_id` int(11) NOT NULL AUTO_INCREMENT,
`movie_id` int(11) NOT NULL COMMENT 'ID bộ phim',
`room_id` int(11) NOT NULL COMMENT 'ID phòng chiếu',
`show_date` date NOT NULL COMMENT 'Ngày chiếu',
`start_time` time NOT NULL COMMENT 'Giờ bắt đầu',
`end_time` time NOT NULL COMMENT 'Giờ kết thúc (Dùng để check trùng lịch)',
`price` int(11) NOT NULL DEFAULT 80000 COMMENT 'Giá vé gốc (VND)',
`status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1: Mở bán, 0: Đã hủy/Đóng',
`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
`updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
PRIMARY KEY (`showtime_id`),

CONSTRAINT `fk_showtime_movie` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`movie_id`) ON DELETE CASCADE,
CONSTRAINT `fk_showtime_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
