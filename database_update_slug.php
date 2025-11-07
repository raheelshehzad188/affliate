<?php
/**
 * Database Update - Add Slug Column
 * Ye file existing database mein slug column add karegi
 * Access: http://localhost/affliate/database_update_slug.php
 */

// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = ''; // XAMPP default is empty
$db_name = 'affiliate_db';

echo "<h2>🔄 Database Update - Slug Column</h2>";

// Connect to MySQL
$conn = @new mysqli($db_host, $db_user, $db_pass);

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

echo "<p>✅ Connected to MySQL server</p>";

// Select database
if (!$conn->select_db($db_name)) {
    die("❌ Database '$db_name' not found. Please run database_fresh.php first.");
}

echo "<p>✅ Selected database '$db_name'</p>";

// Check if slug column exists
$result = $conn->query("SHOW COLUMNS FROM affiliates LIKE 'slug'");
if ($result && $result->num_rows > 0) {
    echo "<p>✅ Slug column already exists</p>";
} else {
    // Add slug column
    $sql = "ALTER TABLE `affiliates` ADD COLUMN `slug` VARCHAR(100) NULL AFTER `full_name`";
    if ($conn->query($sql)) {
        echo "<p>✅ Added 'slug' column to affiliates table</p>";
        
        // Add unique index
        $sql = "ALTER TABLE `affiliates` ADD UNIQUE KEY `slug` (`slug`)";
        if ($conn->query($sql)) {
            echo "<p>✅ Added unique index on slug column</p>";
        }
    } else {
        echo "<p>❌ Error adding slug column: " . $conn->error . "</p>";
    }
}

// Generate slugs for existing affiliates
echo "<h3>Generating Slugs for Existing Affiliates:</h3>";

$result = $conn->query("SELECT id, username, full_name, slug FROM affiliates");
if ($result && $result->num_rows > 0) {
    $updated = 0;
    $skipped = 0;
    
    while ($row = $result->fetch_assoc()) {
        // If slug already exists, skip
        if (!empty($row['slug'])) {
            $skipped++;
            continue;
        }
        
        // Generate slug
        $base = !empty($row['full_name']) ? $row['full_name'] : $row['username'];
        $slug = strtolower(trim($base));
        $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        if (strlen($slug) < 3) {
            $slug = $row['username'] . '-' . substr(md5($row['id']), 0, 5);
        }
        
        // Check if slug exists
        $original_slug = $slug;
        $counter = 1;
        $check_result = $conn->query("SELECT id FROM affiliates WHERE slug = '$slug' AND id != {$row['id']}");
        while ($check_result && $check_result->num_rows > 0) {
            $slug = $original_slug . '-' . $counter;
            $counter++;
            $check_result = $conn->query("SELECT id FROM affiliates WHERE slug = '$slug' AND id != {$row['id']}");
        }
        
        // Update affiliate with slug
        $slug_escaped = $conn->real_escape_string($slug);
        $update_sql = "UPDATE affiliates SET slug = '$slug_escaped' WHERE id = {$row['id']}";
        if ($conn->query($update_sql)) {
            echo "<p>✅ Generated slug for: <strong>{$row['full_name']}</strong> → <code>$slug</code></p>";
            $updated++;
        } else {
            echo "<p>❌ Error updating affiliate ID {$row['id']}: " . $conn->error . "</p>";
        }
    }
    
    echo "<p><strong>✅ Updated: $updated affiliates</strong></p>";
    if ($skipped > 0) {
        echo "<p>⏭️ Skipped: $skipped affiliates (already had slug)</p>";
    }
} else {
    echo "<p>ℹ️ No affiliates found in database</p>";
}

// Verify
echo "<h3>Verification:</h3>";
$result = $conn->query("SELECT COUNT(*) as total, COUNT(slug) as with_slug FROM affiliates");
if ($result) {
    $row = $result->fetch_assoc();
    echo "<p>Total Affiliates: <strong>{$row['total']}</strong></p>";
    echo "<p>With Slug: <strong>{$row['with_slug']}</strong></p>";
    
    if ($row['total'] == $row['with_slug']) {
        echo "<p style='color: green;'><strong>✅ All affiliates have slugs!</strong></p>";
    } else {
        echo "<p style='color: orange;'><strong>⚠️ Some affiliates are missing slugs</strong></p>";
    }
}

$conn->close();

echo "<hr>";
echo "<h3>✅ Update Complete!</h3>";
echo "<p><a href='admin/login' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Go to Admin Login</a></p>";
echo "<p><strong>Note:</strong> Ab har affiliate ka apna unique landing page hoga: <code>domain.com/affiliate-slug</code></p>";
?>

