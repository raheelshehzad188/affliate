-- ============================================
-- CREATE/UPDATE CLINIC SUB-ADMIN USER
-- ============================================
-- Run this SQL to create or update the clinic sub-admin user
-- ============================================

-- Create clinic user (if doesn't exist) or update password
INSERT INTO admin_users (username, email, password, full_name, role)
VALUES ('clinic', 'clinic@gm.com', MD5('clinic123'), 'clinic', 'admin')
ON DUPLICATE KEY UPDATE 
    password = MD5('clinic123'),
    full_name = 'clinic',
    email = 'clinic@gm.com',
    role = 'admin';

-- Show clinic user details
SELECT 
    id,
    username,
    email,
    full_name,
    role,
    created_at,
    last_login
FROM admin_users 
WHERE username = 'clinic';

-- ============================================
-- CLINIC USER LOGIN DETAILS:
-- ============================================
-- Username: clinic
-- Password: clinic123
-- Email: clinic@gm.com
-- Role: admin (sub-admin)
-- Login URL: http://localhost/affliate/admin/login
-- ============================================

