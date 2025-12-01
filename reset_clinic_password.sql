-- ============================================
-- RESET CLINIC USER PASSWORD
-- ============================================
-- Run this SQL to reset clinic user password to 'clinic123'
-- ============================================

UPDATE admin_users 
SET password = MD5('clinic123') 
WHERE username = 'clinic';

-- Verify the update
SELECT 
    id,
    username,
    email,
    full_name,
    role,
    password AS password_hash,
    created_at
FROM admin_users 
WHERE username = 'clinic';

-- ============================================
-- CLINIC USER LOGIN DETAILS:
-- ============================================
-- Username: clinic
-- Password: clinic123
-- ============================================

