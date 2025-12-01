<?php
/**
 * Check Clinic Sub-Admin User
 * Run this file directly in browser: http://localhost/affliate/check_clinic_user.php
 */

// Database connection
$host = 'localhost';
$dbname = 'affiliate_db'; // Change if different
$username = 'root';
$password = '';

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    echo "<h2>🔍 Sub-Admin User Check</h2>";
    echo "<hr>";
    
    // Check if admin_users table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'admin_users'");
    if ($table_check->num_rows == 0) {
        echo "<p style='color: red;'><strong>❌ ERROR: admin_users table does not exist!</strong></p>";
        echo "<p>Please run the database schema first.</p>";
        $conn->close();
        exit;
    }
    
    // Check table structure
    echo "<h3>📋 Table Structure:</h3>";
    $structure = $conn->query("DESCRIBE admin_users");
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $structure->fetch_assoc()) {
        echo "<tr>";
        echo "<td><strong>{$row['Field']}</strong></td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check for clinic user
    echo "<h3>👤 Clinic User Search:</h3>";
    $clinic_user = $conn->query("SELECT * FROM admin_users WHERE username = 'clinic' OR email LIKE '%clinic%'");
    
    if ($clinic_user && $clinic_user->num_rows > 0) {
        echo "<div style='background: #d4edda; padding: 20px; border-radius: 5px; margin: 10px 0; border: 2px solid #28a745;'>";
        echo "<h4 style='color: #155724;'>✅ Clinic User Found!</h4>";
        
        while ($user = $clinic_user->fetch_assoc()) {
            echo "<div style='background: white; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
            echo "<h4>Login Details:</h4>";
            echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
            echo "<tr><th>Field</th><th>Value</th></tr>";
            echo "<tr><td><strong>ID</strong></td><td>{$user['id']}</td></tr>";
            echo "<tr><td><strong>Username</strong></td><td><strong style='color: blue; font-size: 18px;'>{$user['username']}</strong></td></tr>";
            echo "<tr><td><strong>Email</strong></td><td>{$user['email']}</td></tr>";
            echo "<tr><td><strong>Full Name</strong></td><td>{$user['full_name']}</td></tr>";
            echo "<tr><td><strong>Role</strong></td><td><strong style='color: " . ($user['role'] == 'super_admin' ? 'red' : 'green') . ";'>{$user['role']}</strong></td></tr>";
            echo "<tr><td><strong>Created At</strong></td><td>{$user['created_at']}</td></tr>";
            if (isset($user['last_login']) && $user['last_login']) {
                echo "<tr><td><strong>Last Login</strong></td><td>{$user['last_login']}</td></tr>";
            }
            echo "</table>";
            echo "<p style='color: red; margin-top: 15px;'><strong>⚠️ Password is encrypted. If you need to reset it, use the SQL below.</strong></p>";
            echo "</div>";
        }
        
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 20px; border-radius: 5px; margin: 10px 0; border: 2px solid #dc3545;'>";
        echo "<h4 style='color: #721c24;'>❌ Clinic User NOT Found!</h4>";
        echo "<p>The sub-admin 'clinic' does not exist in the database.</p>";
        echo "<p>This might be why sub-admins are not being added. Let me check for any errors...</p>";
        echo "</div>";
    }
    
    // Show all admin users
    echo "<h3>📊 All Admin Users:</h3>";
    $all_admins = $conn->query("SELECT id, username, email, full_name, role, created_at, last_login FROM admin_users ORDER BY id");
    
    if ($all_admins && $all_admins->num_rows > 0) {
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>ID</th><th>Username</th><th>Email</th><th>Full Name</th><th>Role</th><th>Created</th><th>Last Login</th>";
        echo "</tr>";
        
        while ($admin = $all_admins->fetch_assoc()) {
            $row_color = ($admin['username'] == 'clinic') ? 'background: #d4edda;' : '';
            echo "<tr style='$row_color'>";
            echo "<td>{$admin['id']}</td>";
            echo "<td><strong>{$admin['username']}</strong></td>";
            echo "<td>{$admin['email']}</td>";
            echo "<td>{$admin['full_name']}</td>";
            echo "<td><strong style='color: " . ($admin['role'] == 'super_admin' ? 'red' : 'green') . ";'>{$admin['role']}</strong></td>";
            echo "<td>{$admin['created_at']}</td>";
            echo "<td>" . ($admin['last_login'] ? $admin['last_login'] : 'Never') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>No admin users found in database!</p>";
    }
    
    // Check for common issues
    echo "<h3>🔧 Database Issues Check:</h3>";
    
    // Check if role column exists
    $role_check = $conn->query("SHOW COLUMNS FROM admin_users LIKE 'role'");
    if ($role_check->num_rows == 0) {
        echo "<p style='color: red;'>❌ <strong>ISSUE FOUND:</strong> 'role' column is missing from admin_users table!</p>";
        echo "<p>This is why sub-admins cannot be created. Run the SQL fix below.</p>";
    } else {
        echo "<p style='color: green;'>✅ 'role' column exists</p>";
    }
    
    // Check if full_name column exists
    $fullname_check = $conn->query("SHOW COLUMNS FROM admin_users LIKE 'full_name'");
    if ($fullname_check->num_rows == 0) {
        echo "<p style='color: red;'>❌ <strong>ISSUE FOUND:</strong> 'full_name' column is missing from admin_users table!</p>";
        echo "<p>This is why sub-admins cannot be created. Run the SQL fix below.</p>";
    } else {
        echo "<p style='color: green;'>✅ 'full_name' column exists</p>";
    }
    
    // Check if last_login column exists
    $lastlogin_check = $conn->query("SHOW COLUMNS FROM admin_users LIKE 'last_login'");
    if ($lastlogin_check->num_rows == 0) {
        echo "<p style='color: orange;'>⚠️ 'last_login' column is missing (optional but recommended)</p>";
    } else {
        echo "<p style='color: green;'>✅ 'last_login' column exists</p>";
    }
    
    echo "<hr>";
    echo "<h3>💡 SQL Fixes:</h3>";
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; border: 1px solid #ffc107;'>";
    echo "<p><strong>If clinic user doesn't exist, create it with this SQL:</strong></p>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 3px;'>";
    echo "INSERT INTO admin_users (username, email, password, full_name, role) \n";
    echo "VALUES ('clinic', 'clinic@gm.com', MD5('clinic123'), 'clinic', 'admin');";
    echo "</pre>";
    echo "<p><strong>To reset clinic password (if user exists):</strong></p>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 3px;'>";
    echo "UPDATE admin_users SET password = MD5('clinic123') WHERE username = 'clinic';";
    echo "</pre>";
    echo "</div>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p>Please check your database configuration in application/config/database.php</p>";
}
?>

