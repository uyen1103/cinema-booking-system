-- Patch tương thích cho các module admin 4.3.12 -> 4.3.19
-- Dùng khi bạn đã import schema cũ của project và muốn đồng bộ nhanh dữ liệu.
USE movie_booking;

-- USERS
ALTER TABLE users ADD COLUMN phone VARCHAR(20) NULL;
ALTER TABLE users ADD COLUMN birthday DATE NULL;
ALTER TABLE users ADD COLUMN address VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN avatar VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN position VARCHAR(100) NULL;
ALTER TABLE users ADD COLUMN branch_name VARCHAR(150) NULL;
ALTER TABLE users ADD COLUMN hire_date DATE NULL;
ALTER TABLE users ADD COLUMN created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE users ADD COLUMN oauth_provider VARCHAR(50) NULL;
ALTER TABLE users ADD COLUMN oauth_id VARCHAR(100) NULL;
ALTER TABLE users MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'active';

UPDATE users u
LEFT JOIN employees e ON e.user_id = u.user_id
SET
    u.position = COALESCE(NULLIF(u.position, ''), e.position),
    u.status = CASE
        WHEN u.role = 'staff' AND (u.status IS NULL OR u.status = '' OR u.status IN ('active','inactive')) THEN COALESCE(e.status, 'working')
        ELSE u.status
    END
WHERE u.role = 'staff';

-- PROMOTIONS
ALTER TABLE promotions ADD COLUMN code VARCHAR(30) NULL;
ALTER TABLE promotions ADD COLUMN title VARCHAR(160) NULL;
ALTER TABLE promotions ADD COLUMN min_order_amount DECIMAL(12,2) NOT NULL DEFAULT 0;
ALTER TABLE promotions ADD COLUMN max_discount DECIMAL(12,2) NULL;
ALTER TABLE promotions ADD COLUMN usage_limit INT NULL;
ALTER TABLE promotions ADD COLUMN used_count INT NOT NULL DEFAULT 0;
ALTER TABLE promotions ADD COLUMN budget DECIMAL(12,2) NOT NULL DEFAULT 0;
ALTER TABLE promotions ADD COLUMN description TEXT NULL;
ALTER TABLE promotions ADD COLUMN image_path VARCHAR(255) NULL;
ALTER TABLE promotions ADD COLUMN status TINYINT NOT NULL DEFAULT 1;
ALTER TABLE promotions ADD COLUMN created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE promotions ADD COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

UPDATE promotions SET code = COALESCE(NULLIF(code, ''), promo_code);
UPDATE promotions SET title = COALESCE(NULLIF(title, ''), CONCAT('Khuyến mãi ', COALESCE(code, promotion_id)));

-- ORDERS
ALTER TABLE orders ADD COLUMN payment_method VARCHAR(30) NOT NULL DEFAULT 'cash';
ALTER TABLE orders ADD COLUMN payment_status VARCHAR(20) NOT NULL DEFAULT 'pending';
ALTER TABLE orders ADD COLUMN notes VARCHAR(255) NULL;
ALTER TABLE orders ADD COLUMN created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE orders ADD COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE orders MODIFY COLUMN order_status VARCHAR(20) NOT NULL DEFAULT 'pending';

UPDATE orders o
LEFT JOIN payments pm ON pm.order_id = o.order_id
SET
    o.payment_method = COALESCE(pm.payment_method, o.payment_method),
    o.payment_status = CASE
        WHEN pm.payment_status = 'success' THEN 'paid'
        WHEN pm.payment_status IS NOT NULL THEN pm.payment_status
        ELSE o.payment_status
    END;

UPDATE orders SET order_status = 'completed' WHERE order_status = 'paid';
