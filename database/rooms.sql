DROP TABLE IF EXISTS `rooms`;

CREATE TABLE `rooms` (
  `room_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tên phòng chiếu (vd: Rạp 1, Rạp IMAX...)',
  `capacity` int(11) NOT NULL DEFAULT 0 COMMENT 'Tổng số lượng ghế trong phòng',
  `opening_time` time NOT NULL DEFAULT '08:00:00' COMMENT 'Giờ mở cửa',
  `closing_time` time NOT NULL DEFAULT '23:59:00' COMMENT 'Giờ đóng cửa',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1: Đang hoạt động, 0: Đang bảo trì',
  `maintenance_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Lý do bảo trì nếu rạp đóng cửa',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`room_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;