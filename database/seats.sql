DROP TABLE IF EXISTS `seats`;
CREATE TABLE `seats` (
  `seat_id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) NOT NULL COMMENT 'Ghế này thuộc phòng nào',
  `row_name` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Hàng ghế (vd: A, B, C...)',
  `seat_number` int(11) NOT NULL COMMENT 'Số ghế (vd: 1, 2, 3...)',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1: Standard, 2: VIP, 3: Sweetbox',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1: Hoạt động, 0: Hỏng/Không sử dụng',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`seat_id`),
  -- Liên kết khóa ngoại với bảng rooms
  CONSTRAINT `fk_seat_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;