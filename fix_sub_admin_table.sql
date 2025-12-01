-- ============================================
-- FIX SUB-ADMIN TABLE - Add Missing Columns
-- ============================================
-- This SQL will fix the admin_users table to support sub-admin creation
-- Run this in phpMyAdmin or MySQL command line
-- ============================================

-- Step 1: Check and add 'role' column if missing
SET @dbname = DATABASE();
SET @tablename = "admin_users";
SET @columnname = "role";

SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 'Column role already exists.' AS result;",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " ENUM('admin','super_admin') DEFAULT 'admin' AFTER `full_name`;")
));

PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Step 2: Check and add 'full_name' column if missing
SET @columnname = "full_name";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 'Column full_name already exists.' AS result;",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " varchar(255) DEFAULT NULL AFTER `password`;")
));

PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Step 3: Check and add 'last_login' column if missing
SET @columnname = "last_login";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 'Column last_login already exists.' AS result;",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " datetime DEFAULT NULL AFTER `created_at`;")
));

PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Step 4: Create clinic user if it doesn't exist
INSERT INTO admin_users (username, email, password, full_name, role)
SELECT 'clinic', 'clinic@gm.com', MD5('clinic123'), 'clinic', 'admin'
WHERE NOT EXISTS (
    SELECT 1 FROM admin_users WHERE username = 'clinic' OR email = 'clinic@gm.com'
);

-- Step 5: Verify the table structure
SELECT 'Verification - Current admin_users table structure:' AS info;
DESCRIBE admin_users;

-- Step 6: Show all admin users
SELECT 'All Admin Users:' AS info;
SELECT id, username, email, full_name, role, created_at, last_login FROM admin_users;

-- ============================================
-- CLINIC USER LOGIN DETAILS:
-- ============================================
-- Username: clinic
-- Password: clinic123
-- Email: clinic@gm.com
-- Role: admin (sub-admin)
-- ============================================

