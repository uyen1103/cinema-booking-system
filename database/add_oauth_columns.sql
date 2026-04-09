-- Thêm các cột để hỗ trợ OAuth vào bảng Users
ALTER TABLE Users ADD COLUMN oauth_provider VARCHAR(50) NULL;
ALTER TABLE Users ADD COLUMN oauth_id VARCHAR(255) NULL;
ALTER TABLE Users ADD COLUMN avatar_url VARCHAR(255) NULL;
ALTER TABLE Users ADD COLUMN phone VARCHAR(20);
ALTER TABLE Users ADD COLUMN birthday DATE;
ALTER TABLE Users ADD COLUMN address VARCHAR(255);

-- Tạo UNIQUE constraint cho oauth_provider và oauth_id
ALTER TABLE Users ADD UNIQUE KEY unique_oauth (oauth_provider, oauth_id);