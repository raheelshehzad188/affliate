<?php
/**
 * Database Update - Add 'confirmed' status to commissions
 * Access: http://localhost/affliate/database_update_commission_status.php
 */

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'affiliate_db';

echo "<h2>🔄 Update Commission Status</h2>";

$conn = @new mysqli($db_host, $db_user, $db_pass);

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

if (!$conn->select_db($db_name)) {
    die("❌ Database '$db_name' not found.");
}

// Check current enum values
$result = $conn->query("SHOW COLUMNS FROM commissions WHERE Field = 'status'");
if ($result && $row = $result->fetch_assoc()) {
    $type = $row['Type'];
    
    if (strpos($type, 'confirmed') === false) {
        // Update enum to include 'confirmed'
        $sql = "ALTER TABLE `commissions` MODIFY COLUMN `status` ENUM('pending','confirmed','paid','cancelled') DEFAULT 'pending'";
        if ($conn->query($sql)) {
            echo "<p>✅ Added 'confirmed' status to commissions table</p>";
        } else {
            echo "<p>❌ Error: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>✅ 'confirmed' status already exists</p>";
    }
}

// Update existing commissions: if lead is confirmed, set commission to confirmed
echo "<h3>Updating Commission Status Based on Lead Status:</h3>";

$sql = "UPDATE commissions c 
        INNER JOIN leads l ON c.lead_id = l.id 
        SET c.status = 'confirmed' 
        WHERE l.status = 'confirmed' AND c.status = 'pending'";

if ($conn->query($sql)) {
    $affected = $conn->affected_rows;
    echo "<p>✅ Updated <strong>$affected</strong> commissions to 'confirmed' status</p>";
} else {
    echo "<p>❌ Error: " . $conn->error . "</p>";
}

// Show summary
$result = $conn->query("SELECT status, COUNT(*) as count FROM commissions GROUP BY status");
echo "<h3>Commission Status Summary:</h3>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>Status</th><th>Count</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr><td><strong>{$row['status']}</strong></td><td>{$row['count']}</td></tr>";
}
echo "</table>";

$conn->close();

echo "<hr>";
echo "<h3>✅ Update Complete!</h3>";
echo "<p><strong>Note:</strong> Ab jab admin lead confirm karega, commission status automatically 'confirmed' ho jayega.</p>";
?>

