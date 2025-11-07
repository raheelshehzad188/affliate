<?php
/**
 * Database Setup Helper
 * Run this file once to create database and tables
 * Access: http://localhost/affliate/database_setup.php
 */

// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = ''; // XAMPP default is empty
$db_name = 'affiliate_db';

// Connect to MySQL
$conn = @new mysqli($db_host, $db_user, $db_pass);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Database Setup</h2>";

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
if ($conn->query($sql)) {
    echo "<p>✅ Database '$db_name' created successfully or already exists.</p>";
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
    
    foreach ($queries as $query) {
        if (!empty($query)) {
            if ($conn->query($query)) {
                $success_count++;
            } else {
                $error_count++;
                echo "<p>⚠️ Query error: " . $conn->error . "</p>";
            }
        }
    }
    
    echo "<p>✅ Executed queries: $success_count</p>";
    if ($error_count > 0) {
        echo "<p>❌ Errors: $error_count</p>";
    }
} else {
    echo "<p>❌ SQL file not found: $sql_file</p>";
}

// Verify tables
$tables = ['affiliates', 'admin_users', 'leads', 'commissions', 'affiliate_clicks'];
echo "<h3>Verifying Tables:</h3>";
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "<p>✅ Table '$table' exists</p>";
    } else {
        echo "<p>❌ Table '$table' NOT found</p>";
    }
}

// Check admin user
$result = $conn->query("SELECT COUNT(*) as count FROM admin_users");
if ($result) {
    $row = $result->fetch_assoc();
    if ($row['count'] > 0) {
        echo "<p>✅ Admin user exists</p>";
        echo "<p><strong>Default Login:</strong></p>";
        echo "<ul>";
        echo "<li>Username: <strong>admin</strong></li>";
        echo "<li>Password: <strong>admin123</strong></li>";
        echo "</ul>";
        echo "<p style='color: red;'><strong>⚠️ IMPORTANT: Change password after first login!</strong></p>";
    } else {
        echo "<p>⚠️ No admin user found. Creating default admin...</p>";
        
        // Create default admin (MD5 hash)
        $password_hash = md5('admin123');
        $sql = "INSERT INTO admin_users (username, email, password, full_name, role) 
                VALUES ('admin', 'admin@example.com', '$password_hash', 'Administrator', 'super_admin')";
        if ($conn->query($sql)) {
            echo "<p>✅ Default admin created</p>";
        }
    }
}

$conn->close();

echo "<hr>";
echo "<h3>✅ Setup Complete!</h3>";
echo "<p><a href='admin/login'>Go to Admin Login</a></p>";
echo "<p><a href='auth/signup'>Go to Affiliate Signup</a></p>";
?>

