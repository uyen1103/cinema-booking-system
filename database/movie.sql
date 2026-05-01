DROP TABLE IF EXISTS `movies`;

CREATE TABLE `movies` (
`movie_id` int(11) NOT NULL AUTO_INCREMENT,
`title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tên phim',
`description` text COLLATE utf8mb4_unicode_ci COMMENT 'Nội dung tóm tắt',
`director` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Đạo diễn',
`cast` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Dàn diễn viên',
`genre` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Thể loại (vd: Hành động, Hài...)',
`duration` int(11) NOT NULL COMMENT 'Thời lượng phim (tính bằng phút)',
`release_date` date NOT NULL COMMENT 'Ngày khởi chiếu',
`poster` varchar(255) DEFAULT 'assets/images/default-poster.jpg' COMMENT 'Đường dẫn ảnh Poster dọc',
`banner` varchar(255) DEFAULT 'assets/images/default-banner.jpg' COMMENT 'Đường dẫn ảnh Banner ngang (nếu có)',
`trailer_url` varchar(255) DEFAULT NULL COMMENT 'Đường dẫn video Trailer Youtube',
`status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1: Đang chiếu, 2: Sắp chiếu, 0: Ngừng chiếu',
`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
`updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
PRIMARY KEY (`movie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;