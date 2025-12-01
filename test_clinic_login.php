<?php
/**
 * Test Clinic User Login
 * Run this file directly in browser: http://localhost/affliate/test_clinic_login.php
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
    
    echo "<h2>🔐 Clinic User Login Test</h2>";
    echo "<hr>";
    
    // Get clinic user from database
    $result = $conn->query("SELECT * FROM admin_users WHERE username = 'clinic'");
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        echo "<h3>✅ Clinic User Found in Database:</h3>";
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Field</th><th>Value</th></tr>";
        echo "<tr><td>ID</td><td>{$user['id']}</td></tr>";
        echo "<tr><td>Username</td><td><strong>{$user['username']}</strong></td></tr>";
        echo "<tr><td>Email</td><td>{$user['email']}</td></tr>";
        echo "<tr><td>Full Name</td><td>{$user['full_name']}</td></tr>";
        echo "<tr><td>Role</td><td><strong>{$user['role']}</strong></td></tr>";
        echo "<tr><td>Password Hash (DB)</td><td style='font-family: monospace; font-size: 10px;'>{$user['password']}</td></tr>";
        echo "<tr><td>Created At</td><td>{$user['created_at']}</td></tr>";
        echo "</table>";
        
        echo "<hr>";
        echo "<h3>🔍 Password Verification Test:</h3>";
        
        // Test different passwords
        $test_passwords = ['clinic123', 'clinic', 'Clinic123', 'CLINIC123'];
        
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Test Password</th><th>MD5 Hash</th><th>Matches DB?</th><th>Status</th></tr>";
        
        $found_match = false;
        foreach ($test_passwords as $test_pass) {
            $test_hash = md5($test_pass);
            $matches = ($test_hash === $user['password']);
            
            if ($matches) {
                $found_match = true;
            }
            
            echo "<tr style='background: " . ($matches ? '#d4edda' : '#fff') . ";'>";
            echo "<td><strong>{$test_pass}</strong></td>";
            echo "<td style='font-family: monospace; font-size: 10px;'>{$test_hash}</td>";
            echo "<td>" . ($matches ? '✅ YES' : '❌ NO') . "</td>";
            echo "<td>" . ($matches ? '<strong style="color: green;">MATCH FOUND!</strong>' : 'No match') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        if (!$found_match) {
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; border: 2px solid #dc3545;'>";
            echo "<h4 style='color: #721c24;'>❌ Password Mismatch!</h4>";
            echo "<p>The password in database doesn't match any common passwords.</p>";
            echo "<p><strong>Solution: Reset the password using the SQL below.</strong></p>";
            echo "</div>";
        } else {
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; border: 2px solid #28a745;'>";
            echo "<h4 style='color: #155724;'>✅ Password Match Found!</h4>";
            echo "<p>If login still doesn't work, check the login code.</p>";
            echo "</div>";
        }
        
        echo "<hr>";
        echo "<h3>🔧 CodeIgniter Login Test:</h3>";
        
        // Simulate CodeIgniter login check
        $test_password = 'clinic123';
        $test_hash = md5($test_password);
        
        echo "<p><strong>Testing CodeIgniter login logic:</strong></p>";
        echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 3px;'>";
        echo "Username: clinic\n";
        echo "Password: {$test_password}\n";
        echo "MD5 Hash: {$test_hash}\n";
        echo "DB Hash: {$user['password']}\n";
        echo "Match: " . ($test_hash === $user['password'] ? 'YES ✅' : 'NO ❌') . "\n";
        echo "</pre>";
        
        if ($test_hash === $user['password']) {
            echo "<p style='color: green;'><strong>✅ Password verification should work!</strong></p>";
            echo "<p>If login still fails, the issue might be in the login controller or session.</p>";
        } else {
            echo "<p style='color: red;'><strong>❌ Password doesn't match. Need to reset password.</strong></p>";
        }
        
    } else {
        echo "<div style='background: #f8d7da; padding: 20px; border-radius: 5px; margin: 10px 0; border: 2px solid #dc3545;'>";
        echo "<h4 style='color: #721c24;'>❌ Clinic User NOT Found!</h4>";
        echo "<p>The clinic user does not exist in the database.</p>";
        echo "<p>Run the create_clinic_user.sql file to create it.</p>";
        echo "</div>";
    }
    
    echo "<hr>";
    echo "<h3>💡 Fix Password (if needed):</h3>";
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; border: 1px solid #ffc107;'>";
    echo "<p><strong>Run this SQL to reset clinic password to 'clinic123':</strong></p>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 3px;'>";
    echo "UPDATE admin_users SET password = MD5('clinic123') WHERE username = 'clinic';";
    echo "</pre>";
    echo "</div>";
    
    echo "<hr>";
    echo "<h3>📋 Login Details (if password is clinic123):</h3>";
    echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; border: 1px solid #0c5460;'>";
    echo "<p><strong>Username:</strong> clinic</p>";
    echo "<p><strong>Password:</strong> clinic123</p>";
    echo "<p><strong>Login URL:</strong> <a href='admin/login' target='_blank'>http://localhost/affliate/admin/login</a></p>";
    echo "</div>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p>Please check your database configuration.</p>";
}
?>

