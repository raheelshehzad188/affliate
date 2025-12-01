<?php
/**
 * Test Signup Process
 * Run this file directly in browser: http://localhost/affliate/test_signup.php
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
    
    echo "<h2>🔍 Signup Process Test</h2>";
    echo "<hr>";
    
    // Check if affiliates table exists
    echo "<h3>1. Database Table Check:</h3>";
    $table_check = $conn->query("SHOW TABLES LIKE 'affiliates'");
    if ($table_check->num_rows > 0) {
        echo "<p style='color: green;'>✅ Affiliates table exists</p>";
        
        // Check table structure
        $structure = $conn->query("DESCRIBE affiliates");
        echo "<h4>Table Structure:</h4>";
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #f0f0f0;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        
        $required_fields = ['id', 'username', 'email', 'password', 'full_name', 'slug', 'status', 'verification_token', 'created_at'];
        $missing_fields = [];
        
        while ($row = $structure->fetch_assoc()) {
            $field_name = $row['Field'];
            $key = array_search($field_name, $required_fields);
            if ($key !== false) {
                unset($required_fields[$key]);
            }
            
            echo "<tr>";
            echo "<td><strong>{$row['Field']}</strong></td>";
            echo "<td>{$row['Type']}</td>";
            echo "<td>{$row['Null']}</td>";
            echo "<td>{$row['Key']}</td>";
            echo "<td>{$row['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        if (!empty($required_fields)) {
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; border: 2px solid #dc3545; margin: 10px 0;'>";
            echo "<h4 style='color: #721c24;'>❌ Missing Required Fields:</h4>";
            echo "<ul>";
            foreach ($required_fields as $field) {
                echo "<li>{$field}</li>";
            }
            echo "</ul>";
            echo "<p>Run database_schema.sql to fix this.</p>";
            echo "</div>";
        } else {
            echo "<p style='color: green;'>✅ All required fields exist</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Affiliates table does NOT exist!</p>";
        echo "<p>Run database_schema.sql to create the table.</p>";
        $conn->close();
        exit;
    }
    
    // Test signup data
    echo "<hr>";
    echo "<h3>2. Test Signup Data:</h3>";
    
    $test_data = [
        'username' => 'testuser' . time(),
        'email' => 'test' . time() . '@example.com',
        'password' => 'test123',
        'full_name' => 'Test User',
        'website' => 'https://example.com',
        'promote_method' => 'Social media marketing',
        'status' => 'pending'
    ];
    
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 3px;'>";
    print_r($test_data);
    echo "</pre>";
    
    // Test insert
    echo "<h3>3. Test Database Insert:</h3>";
    
    $password_hash = md5($test_data['password']);
    $verification_token = md5($test_data['email'] . time());
    $created_at = date('Y-m-d H:i:s');
    
    // Generate slug
    $slug = strtolower(trim($test_data['full_name']));
    $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    if (strlen($slug) < 3) {
        $slug = $test_data['username'] . '-' . substr(md5(time()), 0, 5);
    }
    
    $sql = "INSERT INTO affiliates (username, email, password, full_name, slug, website, promote_method, status, verification_token, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssss", 
        $test_data['username'],
        $test_data['email'],
        $password_hash,
        $test_data['full_name'],
        $slug,
        $test_data['website'],
        $test_data['promote_method'],
        $test_data['status'],
        $verification_token,
        $created_at
    );
    
    if ($stmt->execute()) {
        $insert_id = $conn->insert_id;
        echo "<p style='color: green;'>✅ Test insert successful! ID: {$insert_id}</p>";
        
        // Clean up test data
        $conn->query("DELETE FROM affiliates WHERE id = {$insert_id}");
        echo "<p style='color: blue;'>ℹ️ Test data cleaned up</p>";
    } else {
        echo "<p style='color: red;'>❌ Test insert failed: " . $stmt->error . "</p>";
    }
    
    $stmt->close();
    
    // Check for existing users
    echo "<hr>";
    echo "<h3>4. Existing Affiliates:</h3>";
    $existing = $conn->query("SELECT id, username, email, status, created_at FROM affiliates ORDER BY id DESC LIMIT 5");
    if ($existing && $existing->num_rows > 0) {
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Username</th><th>Email</th><th>Status</th><th>Created</th></tr>";
        while ($row = $existing->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['username']}</td>";
            echo "<td>{$row['email']}</td>";
            echo "<td>{$row['status']}</td>";
            echo "<td>{$row['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No affiliates found in database.</p>";
    }
    
    echo "<hr>";
    echo "<h3>💡 Summary:</h3>";
    echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; border: 1px solid #0c5460;'>";
    echo "<p><strong>If all tests passed:</strong></p>";
    echo "<ul>";
    echo "<li>✅ Database table exists</li>";
    echo "<li>✅ All required fields are present</li>";
    echo "<li>✅ Test insert works</li>";
    echo "</ul>";
    echo "<p><strong>Try signing up at:</strong> <a href='auth/signup' target='_blank'>http://localhost/affliate/auth/signup</a></p>";
    echo "</div>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Error:</strong> " . $e->getMessage() . "</p>";
}
?>

