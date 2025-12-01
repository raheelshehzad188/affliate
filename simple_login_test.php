<?php
/**
 * Simple Login Test - Direct Database Check
 * Run this file directly in browser: http://localhost/affliate/simple_login_test.php
 */

// Database connection
$host = 'localhost';
$dbname = 'affiliate_db';
$username = 'root';
$password = '';

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    echo "<h2>🔍 Simple Login Test</h2>";
    echo "<hr>";
    
    // Test login directly
    $login_username = 'clinic';
    $login_password = 'clinic123';
    $password_hash = md5($login_password);
    
    echo "<h3>Testing Login:</h3>";
    echo "<p>Username: <strong>{$login_username}</strong></p>";
    echo "<p>Password: <strong>{$login_password}</strong></p>";
    echo "<p>MD5 Hash: <strong>{$password_hash}</strong></p>";
    echo "<hr>";
    
    // Query to find user
    $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $login_username, $password_hash);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        echo "<div style='background: #d4edda; padding: 20px; border-radius: 5px; border: 2px solid #28a745;'>";
        echo "<h3 style='color: #155724;'>✅ Login Should Work!</h3>";
        echo "<p>User found in database with matching password.</p>";
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Field</th><th>Value</th></tr>";
        echo "<tr><td>ID</td><td>{$user['id']}</td></tr>";
        echo "<tr><td>Username</td><td>{$user['username']}</td></tr>";
        echo "<tr><td>Email</td><td>{$user['email']}</td></tr>";
        echo "<tr><td>Full Name</td><td>" . (isset($user['full_name']) ? $user['full_name'] : 'NULL') . "</td></tr>";
        echo "<tr><td>Role</td><td>" . (isset($user['role']) ? $user['role'] : 'NULL') . "</td></tr>";
        echo "</table>";
        echo "</div>";
        
        echo "<hr>";
        echo "<h3>🔧 Possible Issues:</h3>";
        echo "<ol>";
        
        // Check if role is missing
        if (!isset($user['role']) || empty($user['role'])) {
            echo "<li style='color: red;'><strong>Role is missing!</strong> Run: <code>ALTER TABLE admin_users ADD COLUMN role ENUM('admin','super_admin') DEFAULT 'admin' AFTER full_name;</code></li>";
        } else {
            echo "<li style='color: green;'>✅ Role is set: {$user['role']}</li>";
        }
        
        // Check if full_name is missing
        if (!isset($user['full_name']) || empty($user['full_name'])) {
            echo "<li style='color: orange;'>⚠️ Full name is NULL (this is OK, will use username)</li>";
        } else {
            echo "<li style='color: green;'>✅ Full name is set: {$user['full_name']}</li>";
        }
        
        echo "<li>Check browser console for JavaScript errors</li>";
        echo "<li>Check if cookies are enabled in browser</li>";
        echo "<li>Check server error logs (usually in application/logs/)</li>";
        echo "<li>Try clearing browser cache and cookies</li>";
        echo "</ol>";
        
        echo "<hr>";
        echo "<h3>💡 Try This:</h3>";
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; border: 1px solid #ffc107;'>";
        echo "<p><strong>1. Open browser console (F12)</strong></p>";
        echo "<p><strong>2. Try logging in again</strong></p>";
        echo "<p><strong>3. Check for any errors in console</strong></p>";
        echo "<p><strong>4. Check Network tab to see if login request is being sent</strong></p>";
        echo "</div>";
        
    } else {
        echo "<div style='background: #f8d7da; padding: 20px; border-radius: 5px; border: 2px solid #dc3545;'>";
        echo "<h3 style='color: #721c24;'>❌ Login Failed!</h3>";
        echo "<p>User not found or password doesn't match.</p>";
        echo "<p>Run this SQL to reset password:</p>";
        echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 3px;'>";
        echo "UPDATE admin_users SET password = MD5('clinic123') WHERE username = 'clinic';";
        echo "</pre>";
        echo "</div>";
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Error:</strong> " . $e->getMessage() . "</p>";
}
?>

