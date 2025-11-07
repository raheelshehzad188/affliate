<?php
/**
 * Plugin Name: Simple Affiliate Plugin
 * Description: Basic affiliate system with referral tracking and commission recording.
 * Version: 1.0
 * Author: Aakilarose
 */


// Start session if not already started
add_action('wp_head', function () {
    echo '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
});


 function send_pending_approval_email($id) {


     $user = get_user_by('ID', $id);
     
      
    if (!$user) return;
    $company_name = bloginfo('name');
    $token = md5($id.time());
    $user_name=get_user_meta($id,'vendor_full_name',true);
    update_user_meta($id,'token',$token);
    $link = site_url('email-verified').'?token='.$token;
    
    ob_start();
    include_once plugin_dir_path(__FILE__) .'signup_email.php';
    $message = ob_get_clean();

    $subject = 'Verify your email';
    $headers = array('Content-Type: text/html; charset=UTF-8');
    return true;
    wp_mail($user->data->user_email, $subject, $message, $headers);
}

// 1) Affiliates کو صرف posts manage کرنے کی capability دیں
add_action('init', function() {
    $role = get_role('affiliate');
    if ($role) {
        // صرف وہ capabilities جن سے پوسٹ create/edit/publish ہو سکے
        $caps = [
            'edit_posts',
            'edit_published_posts',
            'publish_posts',
            'delete_posts',
            'upload_files',      // featured image وغیرہ کے لیے
        ];
        foreach ($caps as $cap) {
            $role->add_cap($cap);
        }
    }
});
add_action('admin_bar_menu', function($wp_admin_bar) {
    // Only for affiliate users (not admins)
    if (!current_user_can('affiliate') || current_user_can('administrator')) {
        return;
    }

    // Check if current URL contains 'wp-admin'
    if (strpos($_SERVER['REQUEST_URI'], 'wp-admin') !== false) {
        $wp_admin_bar->add_node([
            'id'    => 'affiliate-switch-to-site',
            'title' => '← Back to Dashboard',
            'href'  => home_url('/affiliate-dashboard'),
            'meta'  => ['class' => 'affiliate-dashboard']
        ]);
    } else {
        $wp_admin_bar->add_node([
            'id'    => 'affiliate-switch-to-admin',
            'title' => '← My Blog',
            'href'  => admin_url('edit.php?author=' . get_current_user_id()),
            'meta'  => ['class' => 'affiliate-dash-link']
        ]);
    }
}, 20);


// 2) Admin menu سے سب ہٹاؤ سوائے Posts کے
add_action('admin_menu', function() {
    if ( current_user_can('affiliate') && ! current_user_can('administrator') ) {
        global $menu;
        // whitelist کریں صرف edit.php (Posts) کو
        foreach ($menu as $index => $item) {
            $slug = $item[2];
            if ($slug !== 'edit.php') {
                remove_menu_page($slug);
            }
        }
    }
}, 999);

// 3) Dashboard widgets چھاپو
add_action('wp_dashboard_setup', function() {
    if ( current_user_can('affiliate') && ! current_user_can('administrator') ) {
        remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
        remove_meta_box('dashboard_activity',    'dashboard', 'normal');
        remove_meta_box('dashboard_right_now',   'dashboard', 'normal');
        remove_meta_box('dashboard_primary',     'dashboard', 'side');
    }
});

// 4) Admin Bar سے بھی غیر ضروری لنکس ہٹا دو
add_action('admin_bar_menu', function($wp_admin_bar) {
    if ( current_user_can('affiliate') && ! current_user_can('administrator') ) {
        $wp_admin_bar->remove_node('wp-logo');
        $wp_admin_bar->remove_node('site-name');
        $wp_admin_bar->remove_node('updates');
        $wp_admin_bar->remove_node('comments');
        $wp_admin_bar->remove_node('new-content'); // New > Page, User وغیرہ
        $wp_admin_bar->remove_node('edit-profile');
    }
}, 999);

// 5) لاگ ان یا Dashboard کھولتے ہی Posts لِسٹ پر ری ڈائریکٹ
add_action('admin_init', function() {
    if (
        current_user_can('affiliate') &&
        ! current_user_can('administrator') &&
        ! defined('DOING_AJAX')
    ) {
        $screen = get_current_screen();
        if ($screen && $screen->base !== 'edit' && $screen->id !== 'edit-post') {
            wp_redirect(admin_url('edit.php'));
            exit;
        }
    }
});


// if (!function_exists('dd')) {
//     function dd($data) {
//         echo "<pre>";
//         var_dump($data);
//         die();
//     }
// }
// Track affiliate referral in session
add_action('init', 'sap_track_affiliate_referral');
function sap_track_affiliate_referral() {
    if (isset($_GET['ref'])) {
        $ref = sanitize_text_field($_GET['ref']);
        $user = get_user_by('login', $ref);

        if ($user && in_array('affiliate', (array)$user->roles)) {
            $_SESSION['sap_affiliate_ref'] = $user->ID;
        }
    }
}

// On plugin activation
register_activation_hook(__FILE__, 'sap_plugin_activate');
function sap_plugin_activate() {
    // Create dashboard, login, signup, and email verified pages
    sap_create_page('Affiliate Dashboard', '[affiliate_dashboard]');
    sap_create_page('Affiliate Login', '[affiliate_login_form]');
    sap_create_page('Affiliate Signup', '[affiliate_signup_form]');
    sap_create_page('Email Verified', '[affiliate_email_verified]');
}
add_shortcode('affiliate_email_verified', 'sap_affiliate_email_verified_content');
function custom_email_verification_css() {
    ?>
    <style>
        .ver_outer{
            text-align:center;
        }
        .post-515 #login_link{
            display:none !important;
        }
        
        .post-14888 .form-outer,.post-14889 .form-outer{
            width:60% !important;
            margin:0 auto;
        }
        .post-14888 .entry-title,.post-14889 .entry-title{
            width:60% !important;
            margin:0 auto;
        }
        .ver_outer img{}
        .ver_outer h2{}
        .ver_outer p{}
    </style>
    <?php
}
add_action('wp_head', 'custom_email_verification_css');

function sap_affiliate_email_verified_content() {
    ob_start();
    $token = isset($_GET['token']) ? $_GET['token'] : '';

$users = get_users([
    'meta_key'   => 'token',
    'meta_value' => $token,
    'number'     => 1
]);

$user = reset($users);
$is_verified = false;
if($token && isset($user->ID) && $user->ID)
{
    update_user_meta($user->ID, 'token', ' ');
    update_user_meta($user->ID, 'sap_update_status_', 'active');
    update_user_meta($user->ID, 'sap_vendor_status', 'active');
    $is_verified = true;
}
if ($is_verified): ?>
        <div class="ver_outer">
        <img src="https://static-00.iconduck.com/assets.00/success-icon-512x512-qdg1isa0.png" alt="Success" style="width: 50px; height: 50px;" />
        <h2>Email Verified Successfully!</h2>
        <p>Thank you for verifying your email address. You can now 
            <a href="<?php echo site_url('/affiliate-login'); ?>">log in</a> to your affiliate account.
        </p>
        </div>
    <?php else: ?>
        <div class="ver_outer">
        <img src="https://static.vecteezy.com/system/resources/previews/026/526/158/non_2x/error-icon-vector.jpg" alt="Invalid" style="width: 50px; height: 50px;" />
        <h2>Invalid Verification Link</h2>
        <p>The verification link is invalid or has expired. Please request a new verification email.</p>
        </div>
    <?php endif; ?>
    <?php
    return ob_get_clean();
}



function sap_create_page($title, $shortcode) {
    if (!get_page_by_title($title)) {
        wp_insert_post([
            'post_title'   => $title,
            'post_content' => $shortcode,
            'post_status'  => 'publish',
            'post_type'    => 'page'
        ]);
    }
}

// Record affiliate commission after order
add_action('woocommerce_thankyou', 'sap_record_affiliate_commission');
function sap_record_affiliate_commission($order_id) {
    if (!session_id()) {
        session_start();
    }
    if (!isset($_SESSION['sap_affiliate_ref'])) return;

    $affiliate_id = $_SESSION['sap_affiliate_ref'];
    $order = wc_get_order($order_id);
    $amount = $order->get_total();
    $commission = round($amount * 0.10, 2);

    global $wpdb;
    $table = $wpdb->prefix . 'affiliate_commissions';
    $wpdb->insert($table, [
        'affiliate_id' => $affiliate_id,
        'order_id'     => $order_id,
        'amount'       => $amount,
        'commission'   => $commission,
        'created_at'   => current_time('mysql')
    ]);

    unset($_SESSION['sap_affiliate_ref']);
}

// Shortcode: [affiliate_dashboard]


// AJAX handler function



// AJAX handler function to validate URL
add_action('wp_ajax_sap_check_affiliate_url', 'sap_check_affiliate_url');
function sap_check_affiliate_url() {
    // Check nonce for security
    check_ajax_referer('sap_nonce');

    if (empty($_POST['url'])) {
        wp_send_json_error('URL is required.');
    }

    $url = esc_url_raw($_POST['url']);
    $site_url = get_site_url();

    // Check if the URL starts with our site URL
    if (strpos($url, $site_url) !== 0) {
        wp_send_json_error('Invalid URL. The URL must belong to this site.');
    }

    wp_send_json_success();
}

// functions.php ya kisi custom plugin mein daalein
function set_affiliate_cookie_from_url() {
    if (isset($_GET['aff'])) {
        $affiliate_id = sanitize_text_field($_GET['aff']);

        // Cookie set for 30 din (30*24*60*60 seconds)
        setcookie('affiliate_id', $affiliate_id, time() + (30 * DAY_IN_SECONDS), "/");

        // Also set in $_COOKIE superglobal so it’s accessible in same request
        $_COOKIE['affiliate_id'] = $affiliate_id;
    }
}
add_action('init', 'set_affiliate_cookie_from_url');

// Login form shortcode
add_shortcode('affiliate_login_form', function() {
    if (is_user_logged_in()) return '<p>You are already logged in.</p>';

    ob_start();
    echo '<form method="post">';
    if(isset($_SESSION['login_error']) && $_SESSION['login_error'])
    {
        echo $_SESSION['login_error'];
        unset($_SESSION['login_error']);
    }

        echo '<div class="form-outer"><p><input type="text" name="sap_username" placeholder="Username" required></p>
        <p><input type="password" name="sap_password" placeholder="Password" required></p>
        <p>    <div class="g-recaptcha" data-sitekey="6LdYMGYrAAAAAGaDFrHMkpzrK5rjTHzaUkq6SKju"></div>
</p>
        <p><button type="submit" name="sap_login_submit">Login</button></p>
    </form></div>';
    return ob_get_clean();
});

// 1. Shortcode for Affiliate Form
add_shortcode('affiliate_signup_form', 'sap_custom_affiliate_form');
function sap_custom_affiliate_form() {
    
    ob_start();
    
    

    if (isset($_POST['sap_register_submit'])) {
        
        ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
        global $wpdb;


        $full_name = sanitize_text_field($_POST['sap_full_name']);
        $username  = sanitize_text_field($_POST['sap_username']);
        $email     = sanitize_email($_POST['sap_email']);
        $password  = sanitize_text_field($_POST['sap_password']);
        $website   = $_POST['sap_website'];
        $promote   = sanitize_textarea_field($_POST['promote']);
        
        

        $user_id = wp_create_user($username, $password, $email);

        if (!is_wp_error($user_id)) {
            
            // Assign affiliate role
            $user = new WP_User($user_id);
            $user->set_role('affiliate');

            // Add custom user meta
            update_user_meta($user_id, 'vendor_full_name', $full_name);
            update_user_meta($user_id, 'vendor_website', $website);
            update_user_meta($user_id, 'vendor_promo', $promote);
            update_user_meta($user_id, 'sap_update_status_', 'pending');

            if (isset($_COOKIE['affiliate_id'])) {
                $referrer_id = sanitize_text_field($_COOKIE['affiliate_id']);
                update_user_meta($user_id, 'referred_by_affiliate', $referrer_id);
            }
            $r = send_pending_approval_email($user_id);

             echo '<div style="padding: 15px; background: #d4edda; color: #155724; border-radius: 4px; margin-bottom: 20px;">
        your account has been created. Please verify your email to login.
    </div>';
        } else {
            echo '<p>Error: ' . esc_html($user_id->get_error_message()) . '</p>';
        }
    }

    ?>
    <div class="form-outer">
    <form method="post">
        <p><input type="text" name="sap_full_name" placeholder="Full Name" required></p>
        <p><input type="text" name="sap_username" placeholder="Username" required></p>
        <p><input type="email" name="sap_email" placeholder="Email"></p>
        <p><input type="password" name="sap_password" placeholder="Password" required></p>
        <p><input type="url" name="sap_website" placeholder="Website"/></p>
        <p><textarea placeholder="How you promote us?" name="promote"></textarea></p>
        <p><button type="submit" name="sap_register_submit">Signup</button><br>
        <p id="login_link">
        Have already account? <a href="<?= site_url('affiliate-login'); ?>">Login</a>
        </p>
        </p>
    </form>
    </div>
    <?php

    return ob_get_clean();
}




// Shortcode: [affiliate_profile]
add_shortcode('affiliate_profile', 'sap_affiliate_profile');
function sap_affiliate_profile() {
    if (!isset($_GET['affiliate'])) {
        return '<p>No affiliate specified.</p>';
    }

    $username = sanitize_user($_GET['affiliate']);
    $user = get_user_by('login', $username);

    if (!$user || !in_array('affiliate', (array)$user->roles)) {
        return '<p>Affiliate not found.</p>';
    }

    ob_start();
    echo '<div class="affiliate-profile">';
    echo get_avatar($user->ID, 96);
    echo '<h2>' . esc_html($user->display_name) . '</h2>';
    echo '<p><strong>Bio:</strong> ' . esc_html(get_user_meta($user->ID, 'description', true)) . '</p>';
    echo '<p><strong>Affiliate Link:</strong> <code>' . esc_url(home_url('?ref=' . $user->user_login)) . '</code></p>';
    echo '</div>';
    return ob_get_clean();
}

// Create profile page on plugin activation
add_action('init', function() {
    if (get_option('sap_profile_page_created')) return;
    sap_create_page('Affiliate Profile', '[affiliate_profile]');
    update_option('sap_profile_page_created', 1);
});
include_once plugin_dir_path(__FILE__) . 'admin.php'; // ✅ Correct
include_once plugin_dir_path(__FILE__) . 'capture.php'; // ✅ Correct
include_once plugin_dir_path(__FILE__) . 'dashboard.php'; // ✅ Correct
include_once plugin_dir_path(__FILE__) . 'commission.php'; // ✅ Correct
include_once plugin_dir_path(__FILE__) . 'treatments.php'; // ✅ Correct
function custom_affiliate_login() {
    if (isset($_POST['sap_username']) && isset($_POST['sap_password'])) {
        
        if (isset($_POST['g-recaptcha-response']) && $_POST['g-recaptcha-response']) {
            $response = $_POST['g-recaptcha-response'];
            $remoteip = $_SERVER['REMOTE_ADDR'];
            $secret = '6LdYMGYrAAAAAFzvFCpTRVskyofHBDfetCbbmINU';
        
            $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secret&response=$response&remoteip=$remoteip");
            $captcha_success = json_decode($verify);
        
            if ($captcha_success->success) {
                
                
            } else {
                $_SESSION['login_error'] = '<p style="color:red">CAPTCHA verification failed. Try again.</p>';
            }
        }
        else
        {
            $_SESSION['login_error'] = '<p style="color:red">CAPTCHA verification is required!.</p>';
        }
        
        if(!isset($_SESSION['login_error']))
        {
            ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$username = $_POST['sap_username']; // Form se liya gaya username
$password = $_POST['sap_password']; // Form se liya gaya password

// Username se user ka data nikalna
$user = get_user_by('login', $username);

    
            // $user = wp_signon($creds, false);

            if (!($user && wp_check_password( $password, $user->user_pass, $user->ID ))) {
                $_SESSION['login_error'] =  '<p style="color:red">Invalid credentials</p>';
            } else {
                
                $status = get_user_meta($user->ID, 'sap_vendor_status', true);
    
                if ($status === 'active' || true) {
                        // Ensure user has affiliate role
                        $user_obj = new WP_User($user->ID);
                        if (!in_array('affiliate', (array)$user_obj->roles)) {
                            //$user_obj->set_role('affiliate');
                        }
                        
                        // Set affiliate cookie on successful login
                        setcookie('affiliate_user_id', $user->ID, time() + (30 * DAY_IN_SECONDS), "/");
                        $_COOKIE['affiliate_user_id'] = $user->ID;
                        
                        // Proceed (redirect or show dashboard)
                        
            
                        // ✅ Very important: no output before this line
                        wp_redirect(home_url('/affiliate-dashboard'));
                        exit;
                } else {
                    // Maybe logout and show a message
                    wp_logout();
                    $_SESSION['login_error'] = '<p style="color:red;">Your account is not active. Current status: ' . esc_html($status) . '</p>';
                }
            }
        }
    }
}
add_action('init', 'custom_affiliate_login');

// Ensure affiliate role is preserved during login
add_action('wp_login', 'sap_preserve_affiliate_role', 10, 2);
function sap_preserve_affiliate_role($user_login, $user) {
    // Check if user should have affiliate role
    $status = get_user_meta($user->ID, 'sap_vendor_status', true);
    if ($status === 'active' || $status === 'pending') {
        $user_obj = new WP_User($user->ID);
        if (!in_array('affiliate', (array)$user_obj->roles)) {
            $user_obj->set_role('affiliate');
        }
    }
}