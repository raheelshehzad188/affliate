<?php
/**
 * Fresh Database Setup
 * Ye file existing database ko drop karke fresh database create karegi
 * Access: http://localhost/affliate/database_fresh.php
 */

// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = ''; // XAMPP default is empty
$db_name = 'affiliate_db';

echo "<h2>🔄 Fresh Database Setup</h2>";
echo "<p style='color: red;'><strong>⚠️ WARNING: This will DELETE all existing data!</strong></p>";

// Connect to MySQL
$conn = @new mysqli($db_host, $db_user, $db_pass);

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

echo "<p>✅ Connected to MySQL server</p>";

// Drop existing database
$sql = "DROP DATABASE IF EXISTS `$db_name`";
if ($conn->query($sql)) {
    echo "<p>✅ Dropped existing database '$db_name' (if existed)</p>";
} else {
    echo "<p>⚠️ Error dropping database: " . $conn->error . "</p>";
}

// Create fresh database
$sql = "CREATE DATABASE `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
if ($conn->query($sql)) {
    echo "<p>✅ Created fresh database '$db_name'</p>";
} else {
    echo "<p>❌ Error creating database: " . $conn->error . "</p>";
    exit;
}

// Select database
$conn->select_db($db_name);

// Read and execute SQL file
$sql_file = __DIR__ . '/database_schema.sql';
if (file_exists($sql_file)) {
    $sql_content = file_get_contents($sql_file);
    
    // Remove comments and split by semicolon
    $sql_content = preg_replace('/--.*$/m', '', $sql_content);
    $sql_content = preg_replace('/\/\*.*?\*\//s', '', $sql_content);
    
    $queries = array_filter(array_map('trim', explode(';', $sql_content)));
    
    $success_count = 0;
    $error_count = 0;
    
    echo "<h3>Creating Tables:</h3>";
    
    foreach ($queries as $query) {
        if (!empty($query) && strlen($query) > 10) {
            if ($conn->query($query)) {
                $success_count++;
                // Extract table name if CREATE TABLE
                if (preg_match('/CREATE TABLE.*?`(\w+)`/i', $query, $matches)) {
                    echo "<p>✅ Created table: <strong>{$matches[1]}</strong></p>";
                } elseif (preg_match('/INSERT INTO.*?`(\w+)`/i', $query, $matches)) {
                    echo "<p>✅ Inserted into: <strong>{$matches[1]}</strong></p>";
                }
            } else {
                $error_count++;
                if (strpos($conn->error, 'already exists') === false) {
                    echo "<p>⚠️ Query error: " . $conn->error . "</p>";
                }
            }
        }
    }
    
    echo "<p><strong>✅ Total queries executed: $success_count</strong></p>";
    if ($error_count > 0) {
        echo "<p>❌ Errors: $error_count</p>";
    }
} else {
    echo "<p>❌ SQL file not found: $sql_file</p>";
}

// Verify all tables
$tables = ['affiliates', 'admin_users', 'leads', 'commissions', 'affiliate_clicks'];
echo "<h3>📋 Verifying Tables:</h3>";
$all_tables_exist = true;
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        // Count rows
        $count_result = $conn->query("SELECT COUNT(*) as count FROM `$table`");
        $count = $count_result ? $count_result->fetch_assoc()['count'] : 0;
        echo "<p>✅ Table '<strong>$table</strong>' exists ($count rows)</p>";
    } else {
        echo "<p>❌ Table '<strong>$table</strong>' NOT found</p>";
        $all_tables_exist = false;
    }
}

// Check admin user
echo "<h3>👤 Admin User:</h3>";
$result = $conn->query("SELECT * FROM admin_users WHERE username = 'admin'");
if ($result && $result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    echo "<p>✅ Admin user exists</p>";
    echo "<div style='background: #f0f0f0; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<p><strong>Default Login Credentials:</strong></p>";
    echo "<ul>";
    echo "<li>Username: <strong>admin</strong></li>";
    echo "<li>Password: <strong>admin123</strong></li>";
    echo "<li>Email: <strong>{$admin['email']}</strong></li>";
    echo "</ul>";
    echo "<p style='color: red;'><strong>⚠️ IMPORTANT: Change password after first login!</strong></p>";
    echo "</div>";
} else {
    echo "<p>⚠️ No admin user found. Creating default admin...</p>";
    
    // Create default admin (MD5 hash)
    $password_hash = md5('admin123');
    $sql = "INSERT INTO admin_users (username, email, password, full_name, role) 
            VALUES ('admin', 'admin@example.com', '$password_hash', 'Administrator', 'super_admin')";
    if ($conn->query($sql)) {
        echo "<p>✅ Default admin created</p>";
    } else {
        echo "<p>❌ Error creating admin: " . $conn->error . "</p>";
    }
}

$conn->close();

echo "<hr>";
if ($all_tables_exist) {
    echo "<h3 style='color: green;'>✅ Fresh Database Setup Complete!</h3>";
    echo "<p><a href='admin/login' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Go to Admin Login</a></p>";
    echo "<p><a href='auth/signup' style='display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin-top: 10px;'>Go to Affiliate Signup</a></p>";
} else {
    echo "<h3 style='color: red;'>❌ Setup incomplete. Please check errors above.</h3>";
}
?>

