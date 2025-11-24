-- ============================================
-- ALL CHANGES MADE TODAY - SQL MIGRATION FILE
-- ============================================
-- Date: Today
-- Description: Sub-Admin Management & Activity Logs System
-- ============================================

-- ============================================
-- 1. ACTIVITY LOGS TABLE
-- ============================================
-- This table stores all admin activities (lead confirmations, affiliate updates, etc.)
-- Only super_admin can view these logs

CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `admin_name` varchar(255) DEFAULT NULL,
  `action_type` varchar(50) NOT NULL,
  `action_description` text NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `old_data` text DEFAULT NULL,
  `new_data` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  KEY `action_type` (`action_type`),
  KEY `entity_type` (`entity_type`),
  KEY `created_at` (`created_at`),
  FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 2. ADMIN_USERS TABLE UPDATE (if needed)
-- ============================================
-- Make sure admin_users table has 'role' column
-- If it doesn't exist, run this:

-- Check if role column exists, if not add it
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
  "SELECT 'Column already exists.'",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " ENUM('admin','super_admin') DEFAULT 'admin' AFTER `full_name`;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================
-- 3. ADMIN_USERS TABLE UPDATE - Add last_login column (if needed)
-- ============================================
-- Check if last_login column exists, if not add it
SET @columnname = "last_login";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 'Column already exists.'",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " datetime DEFAULT NULL AFTER `created_at`;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================
-- NOTES:
-- ============================================
-- 1. Activity logs will automatically record:
--    - Lead confirmations (when admin confirms a lead)
--    - Affiliate updates (when admin updates affiliate details)
--    - Affiliate status changes (when admin changes affiliate status)
--    - Sub-admin creation, updates, and deletions
--
-- 2. Only super_admin can:
--    - View activity logs
--    - Add/edit/delete sub-admins
--    - Access sub-admin management pages
--
-- 3. Regular admins (role='admin') can:
--    - Manage affiliates
--    - Confirm leads
--    - View commissions
--    - But their actions will be logged
--
-- 4. To make an admin super_admin, update the role:
--    UPDATE admin_users SET role = 'super_admin' WHERE id = 1;
--
-- ============================================
-- VERIFICATION QUERIES
-- ============================================
-- Run these to verify the changes:

-- Check if activity_logs table exists
SELECT 'Activity Logs Table' AS 'Check', 
       CASE WHEN COUNT(*) > 0 THEN 'EXISTS' ELSE 'NOT FOUND' END AS 'Status'
FROM information_schema.tables 
WHERE table_schema = DATABASE() AND table_name = 'activity_logs';

-- Check admin_users table structure
DESCRIBE admin_users;

-- Check activity_logs table structure
DESCRIBE activity_logs;

-- View all admins with their roles
SELECT id, username, full_name, role, created_at, last_login FROM admin_users;

