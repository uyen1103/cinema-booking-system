-- Update Users table với các trường mới
ALTER TABLE Users ADD COLUMN phone VARCHAR(20) AFTER email;
ALTER TABLE Users ADD COLUMN birthday DATE AFTER phone;
ALTER TABLE Users ADD COLUMN address VARCHAR(255) AFTER birthday;
ALTER TABLE Users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER address;
