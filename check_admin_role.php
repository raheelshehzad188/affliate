<?php
/**
 * Quick script to check and update admin role
 * Run this file directly in browser: http://localhost/affliate/check_admin_role.php
 */

// Database connection
$host = 'localhost';
$dbname = 'your_database_name'; // Change this
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Admin Users Check</h2>";
    
    // Get all admins
    $stmt = $conn->query("SELECT id, username, full_name, email, role FROM admin_users");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Username</th><th>Full Name</th><th>Email</th><th>Current Role</th><th>Action</th></tr>";
    
    foreach ($admins as $admin) {
        echo "<tr>";
        echo "<td>{$admin['id']}</td>";
        echo "<td>{$admin['username']}</td>";
        echo "<td>{$admin['full_name']}</td>";
        echo "<td>{$admin['email']}</td>";
        echo "<td><strong>{$admin['role']}</strong></td>";
        
        if ($admin['role'] != 'super_admin') {
            echo "<td><a href='?make_super={$admin['id']}' style='color: green;'>Make Super Admin</a></td>";
        } else {
            echo "<td><span style='color: red;'>Already Super Admin</span></td>";
        }
        
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Handle make super admin request
    if (isset($_GET['make_super'])) {
        $admin_id = intval($_GET['make_super']);
        $update = $conn->prepare("UPDATE admin_users SET role = 'super_admin' WHERE id = ?");
        $update->execute([$admin_id]);
        echo "<p style='color: green;'><strong>Admin #{$admin_id} is now Super Admin! Please refresh the page.</strong></p>";
        echo "<p><a href='check_admin_role.php'>Refresh Page</a></p>";
    }
    
    echo "<hr>";
    echo "<h3>Direct SQL Commands:</h3>";
    echo "<pre>";
    echo "-- Check all admins:\n";
    echo "SELECT id, username, full_name, role FROM admin_users;\n\n";
    echo "-- Make admin #1 super admin:\n";
    echo "UPDATE admin_users SET role = 'super_admin' WHERE id = 1;\n\n";
    echo "-- Make specific admin super admin (replace USERNAME):\n";
    echo "UPDATE admin_users SET role = 'super_admin' WHERE username = 'your_username';\n";
    echo "</pre>";
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please update database credentials in this file.</p>";
}
?>

