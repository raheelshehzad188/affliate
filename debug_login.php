<?php
/**
 * Debug Login Issue
 * Run this file directly in browser: http://localhost/affliate/debug_login.php
 */

// Bootstrap CodeIgniter
define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'development');
define('BASEPATH', __DIR__ . '/system/');

require_once BASEPATH . 'core/CodeIgniter.php';

echo "<h2>🔍 Login Debugging</h2>";
echo "<hr>";

// Test 1: Check database connection
echo "<h3>1. Database Connection Test:</h3>";
try {
    $result = $CI->db->query("SELECT 1");
    echo "<p style='color: green;'>✅ Database connection successful</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Test 2: Check if clinic user exists
echo "<h3>2. Clinic User Check:</h3>";
$clinic = $CI->db->where('username', 'clinic')->get('admin_users')->row();
if ($clinic) {
    echo "<p style='color: green;'>✅ Clinic user found</p>";
    echo "<pre>";
    echo "ID: " . $clinic->id . "\n";
    echo "Username: " . $clinic->username . "\n";
    echo "Email: " . $clinic->email . "\n";
    echo "Full Name: " . (isset($clinic->full_name) ? $clinic->full_name : 'NOT SET') . "\n";
    echo "Role: " . (isset($clinic->role) ? $clinic->role : 'NOT SET') . "\n";
    echo "Password Hash: " . $clinic->password . "\n";
    echo "</pre>";
} else {
    echo "<p style='color: red;'>❌ Clinic user NOT found</p>";
    exit;
}

// Test 3: Test password verification
echo "<h3>3. Password Verification Test:</h3>";
$test_password = 'clinic123';
$test_hash = md5($test_password);
$db_hash = $clinic->password;

echo "<pre>";
echo "Test Password: {$test_password}\n";
echo "MD5 Hash: {$test_hash}\n";
echo "DB Hash: {$db_hash}\n";
echo "Match: " . ($test_hash === $db_hash ? 'YES ✅' : 'NO ❌') . "\n";
echo "</pre>";

if ($test_hash !== $db_hash) {
    echo "<p style='color: red;'>❌ Password hash mismatch!</p>";
    exit;
}

// Test 4: Test Admin_model verify_login
echo "<h3>4. Admin_model verify_login Test:</h3>";
$CI->load->model('Admin_model');
$admin = $CI->Admin_model->verify_login('clinic', 'clinic123');

if ($admin) {
    echo "<p style='color: green;'>✅ verify_login returned admin object</p>";
    echo "<pre>";
    echo "Admin ID: " . (isset($admin->id) ? $admin->id : 'NOT SET') . "\n";
    echo "Username: " . (isset($admin->username) ? $admin->username : 'NOT SET') . "\n";
    echo "Full Name: " . (isset($admin->full_name) ? $admin->full_name : 'NOT SET') . "\n";
    echo "Role: " . (isset($admin->role) ? $admin->role : 'NOT SET') . "\n";
    echo "</pre>";
    
    // Test 5: Test session
    echo "<h3>5. Session Test:</h3>";
    
    $session_data = [
        'admin_id' => $admin->id,
        'admin_username' => $admin->username,
        'admin_name' => isset($admin->full_name) ? $admin->full_name : $admin->username,
        'admin_role' => isset($admin->role) ? $admin->role : 'admin'
    ];
    
    $CI->session->set_userdata($session_data);
    
    echo "<p>Session data set:</p>";
    echo "<pre>";
    print_r($session_data);
    echo "</pre>";
    
    // Verify session
    $session_admin_id = $CI->session->userdata('admin_id');
    if ($session_admin_id) {
        echo "<p style='color: green;'>✅ Session saved successfully - Admin ID: {$session_admin_id}</p>";
    } else {
        echo "<p style='color: red;'>❌ Session NOT saved!</p>";
        echo "<p>Session config check:</p>";
        echo "<pre>";
        echo "Session driver: " . $CI->config->item('sess_driver') . "\n";
        echo "Session save path: " . $CI->config->item('sess_save_path') . "\n";
        echo "</pre>";
    }
    
} else {
    echo "<p style='color: red;'>❌ verify_login returned FALSE</p>";
    echo "<p>This means the login verification failed even though password matches.</p>";
}

// Test 6: Check session configuration
echo "<h3>6. Session Configuration:</h3>";
echo "<pre>";
echo "Session Driver: " . $CI->config->item('sess_driver') . "\n";
echo "Session Cookie Name: " . $CI->config->item('sess_cookie_name') . "\n";
echo "Session Expiration: " . $CI->config->item('sess_expiration') . "\n";
echo "Session Save Path: " . $CI->config->item('sess_save_path') . "\n";
echo "</pre>";

// Test 7: Direct database query test
echo "<h3>7. Direct Database Query Test:</h3>";
$direct_query = $CI->db->query("SELECT * FROM admin_users WHERE username = 'clinic' AND password = MD5('clinic123')");
$direct_result = $direct_query->row();

if ($direct_result) {
    echo "<p style='color: green;'>✅ Direct query found user with matching password</p>";
} else {
    echo "<p style='color: red;'>❌ Direct query did NOT find matching user</p>";
}

echo "<hr>";
echo "<h3>💡 Summary:</h3>";
if ($admin && $CI->session->userdata('admin_id')) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; border: 2px solid #28a745;'>";
    echo "<p style='color: #155724;'><strong>✅ All tests passed! Login should work.</strong></p>";
    echo "<p>If login still doesn't work, check browser console for JavaScript errors or check server error logs.</p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; border: 2px solid #dc3545;'>";
    echo "<p style='color: #721c24;'><strong>❌ Issue found! Check the tests above.</strong></p>";
    echo "</div>";
}
?>

