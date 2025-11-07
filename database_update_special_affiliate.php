<?php
/**
 * Database Update - Add is_special column to affiliates table
 * Access: http://localhost/affliate/database_update_special_affiliate.php
 */

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'affiliate_db';

echo "<h2>🔄 Add Special Affiliate Column</h2>";

$conn = @new mysqli($db_host, $db_user, $db_pass);

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

if (!$conn->select_db($db_name)) {
    die("❌ Database '$db_name' not found.");
}

// Check if column exists
$result = $conn->query("SHOW COLUMNS FROM affiliates LIKE 'is_special'");
if ($result->num_rows == 0) {
    // Add is_special column
    $sql = "ALTER TABLE `affiliates` ADD COLUMN `is_special` tinyint(1) DEFAULT 0 AFTER `status`";
    if ($conn->query($sql)) {
        echo "<p>✅ Added 'is_special' column to affiliates table</p>";
    } else {
        echo "<p>❌ Error: " . $conn->error . "</p>";
    }
} else {
    echo "<p>✅ 'is_special' column already exists</p>";
}

$conn->close();

echo "<hr>";
echo "<h3>✅ Update Complete!</h3>";
echo "<p><strong>Note:</strong> Ab admin special affiliate checkbox check kar sakta hai. Agar checked ho to affiliate apna banner image change kar sakta hai.</p>";
?>

