<?php
/**
 * Check Admin Table Structure
 * Run this file directly in browser: http://localhost/affliate/check_admin_table_structure.php
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
    
    echo "<h2>🔍 Admin Table Structure Check</h2>";
    echo "<hr>";
    
    // Check table structure
    echo "<h3>Table Structure:</h3>";
    $structure = $conn->query("DESCRIBE admin_users");
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #f0f0f0;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    $has_id = false;
    $has_role = false;
    $has_full_name = false;
    
    while ($row = $structure->fetch_assoc()) {
        $field = $row['Field'];
        if ($field == 'id') $has_id = true;
        if ($field == 'role') $has_role = true;
        if ($field == 'full_name') $has_full_name = true;
        
        echo "<tr>";
        echo "<td><strong>{$row['Field']}</strong></td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "<td>{$row['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check for required fields
    echo "<h3>Required Fields Check:</h3>";
    echo "<ul>";
    echo "<li>" . ($has_id ? "✅" : "❌") . " ID field: " . ($has_id ? "EXISTS" : "MISSING!") . "</li>";
    echo "<li>" . ($has_role ? "✅" : "❌") . " Role field: " . ($has_role ? "EXISTS" : "MISSING!") . "</li>";
    echo "<li>" . ($has_full_name ? "✅" : "⚠️") . " Full Name field: " . ($has_full_name ? "EXISTS" : "OPTIONAL") . "</li>";
    echo "</ul>";
    
    if (!$has_id) {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; border: 2px solid #dc3545; margin: 10px 0;'>";
        echo "<h4 style='color: #721c24;'>❌ CRITICAL: ID field is missing!</h4>";
        echo "<p>This is why login is failing. Run this SQL to fix:</p>";
        echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 3px;'>";
        echo "ALTER TABLE admin_users ADD COLUMN id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;";
        echo "</pre>";
        echo "</div>";
    }
    
    // Check clinic user
    echo "<hr>";
    echo "<h3>Clinic User Data:</h3>";
    $clinic = $conn->query("SELECT * FROM admin_users WHERE username = 'clinic'");
    
    if ($clinic && $clinic->num_rows > 0) {
        $user = $clinic->fetch_assoc();
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse; margin: 10px 0;'>";
        foreach ($user as $key => $value) {
            echo "<tr>";
            echo "<td><strong>{$key}</strong></td>";
            echo "<td>" . ($value === null ? '<em>NULL</em>' : htmlspecialchars($value)) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Check if ID exists
        if (!isset($user['id']) || empty($user['id'])) {
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; border: 2px solid #dc3545; margin: 10px 0;'>";
            echo "<h4 style='color: #721c24;'>❌ Clinic user has NO ID!</h4>";
            echo "<p>This is the problem! The user exists but has no ID field.</p>";
            echo "</div>";
        } else {
            echo "<p style='color: green;'>✅ Clinic user has ID: <strong>{$user['id']}</strong></p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Clinic user not found</p>";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Error:</strong> " . $e->getMessage() . "</p>";
}
?>

