-- thêm tài khoản admin
INSERT INTO Users (full_name, email, password, role, status) VALUES 
('Admin User', 'admin@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active')
ON DUPLICATE KEY UPDATE 
password = VALUES(password),
role = 'admin',
status = 'active';

-- Email: admin@test.com
-- Password: password