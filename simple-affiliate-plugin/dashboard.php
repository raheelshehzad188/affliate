<?php

// Add this in your functions.php or plugin file
add_action('wp_ajax_sap_ajax_password_reset', 'sap_ajax_password_reset_callback');
function sap_ajax_password_reset_callback() {
    if (!is_user_logged_in()) {
        echo '<p style="color:red;">❌ You must be logged in.</p>';
        wp_die();
    }

    $user = wp_get_current_user();
    $new_password = sanitize_text_field($_POST['new_password']);
    $confirm_password = sanitize_text_field($_POST['confirm_password']);

    // Match check
    if ($new_password !== $confirm_password) {
        echo '<p style="color:red;">❌ Passwords do not match.</p>';
        wp_die();
    }

    // Strength check
    $errors = [];
    if (strlen($new_password) < 8) {
        $errors[] = "Minimum 8 characters";
    }
    if (!preg_match('/[A-Z]/', $new_password)) {
        $errors[] = "At least 1 uppercase letter";
    }
    if (!preg_match('/[a-z]/', $new_password)) {
        $errors[] = "At least 1 lowercase letter";
    }
    if (!preg_match('/[0-9]/', $new_password)) {
        $errors[] = "At least 1 number";
    }
    if (!preg_match('/[\W]/', $new_password)) {
        $errors[] = "At least 1 special character (e.g. !@#$)";
    }

    if (!empty($errors)) {
        echo '<p style="color:red;">❌ Weak password. Please include:</p><ul>';
        foreach ($errors as $error) {
            echo '<li style="color:gray;">• ' . esc_html($error) . '</li>';
        }
        echo '</ul>';
        echo '<p style="color:gray;">🔐 Suggested Strong Password: <strong>' . wp_generate_password(12, true, true) . '</strong></p>';
        wp_die();
    }

    // All good — update password
    wp_set_password($new_password, $user->ID);
    echo '<p style="color:green;">✅ Password updated successfully.</p>';
    wp_die();
}


add_action('wp_enqueue_scripts', 'enqueue_my_profile_scripts');
function enqueue_my_profile_scripts() {
    wp_enqueue_script('my-profile-js', plugin_dir_url(__FILE__) . 'js/profile.js', array('jquery'), null, true);
    wp_localize_script('my-profile-js', 'my_ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
}

add_action('wp_ajax_upload_profile_picture', 'upload_profile_picture_callback');

function upload_profile_picture_callback() {
    if (!function_exists('wp_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }
    $uploadedfile = $_FILES['file'];
    $upload_overrides = array('test_form' => false);
    $movefile = wp_handle_upload($uploadedfile, $upload_overrides);

    if ($movefile && !isset($movefile['error'])) {
        $filename = $movefile['file'];
        $filetype = wp_check_filetype(basename($filename), null);
        $attachment = array(
            'post_mime_type' => $filetype['type'],
            'post_title' => sanitize_file_name(basename($filename)),
            'post_content' => '',
            'post_status' => 'inherit'
        );
        $attach_id = wp_insert_attachment($attachment, $filename);
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attach_id, $filename);
        wp_update_attachment_metadata($attach_id, $attach_data);

        // Save in user meta
        update_user_meta(get_current_user_id(), 'profile_picture', $attach_id);

        wp_send_json_success(['url' => wp_get_attachment_url($attach_id)]);
    } else {
        wp_send_json_error();
    }
}

add_action('init', 'sap_handle_logout');
function get_affiliate_sale_total($aff_id, $start_date, $end_date) {
    // Validate inputs
    if (!$aff_id || !$start_date || !$end_date) {
        return 0;
    }

    // Convert dates to Y-m-d format (optional)
    $start_date = date('Y-m-d', strtotime($start_date));
    $end_date   = date('Y-m-d', strtotime($end_date . ' 23:59:59'));

    // Query commission posts
    $commissions = get_posts([
        'post_type'   => 'commission',
        'numberposts' => -1,
        'meta_key'    => 'aff_id',
        'meta_value'  => $aff_id,
        'date_query'  => [
            [
                'after'     => $start_date,
                'before'    => $end_date,
                'inclusive' => true,
            ]
        ],
        'fields' => 'ids', // we only need IDs
    ]);

    $total_commission = 0;

    foreach ($commissions as $commission_id) {
        $amount = get_post_meta($commission_id, 'tot_sale', true);
        $total_commission += floatval($amount);
    }

    return $total_commission;
}
function get_affiliate_commission_total($aff_id, $start_date, $end_date) {
    // Validate inputs
    if (!$aff_id || !$start_date || !$end_date) {
        return 0;
    }

    // Convert dates to Y-m-d format (optional)
    $start_date = date('Y-m-d', strtotime($start_date));
    $end_date   = date('Y-m-d', strtotime($end_date . ' 23:59:59'));

    // Query commission posts
    $commissions = get_posts([
        'post_type'   => 'commission',
        'numberposts' => -1,
        'meta_key'    => 'aff_id',
        'meta_value'  => $aff_id,
        'date_query'  => [
            [
                'after'     => $start_date,
                'before'    => $end_date,
                'inclusive' => true,
            ]
        ],
        'fields' => 'ids', // we only need IDs
    ]);

    $total_commission = 0;

    foreach ($commissions as $commission_id) {
        $amount = get_post_meta($commission_id, 'commission', true);
        $total_commission += floatval($amount);
    }

    return $total_commission;
}

function show_affiliate_commissions() {
    $affiliate_user_id = isset($_COOKIE['affiliate_user_id']) ? intval($_COOKIE['affiliate_user_id']) : 0;
    
    if (!$affiliate_user_id) {
        return '<p>You must be logged in to view your commissions.</p>';
    }

    $user_id = $affiliate_user_id;

    // Commission posts linked to this affiliate
    $commissions = get_posts([
        'post_type'  => 'commission',
        'numberposts' => -1,
        'meta_key'   => 'aff_id',
        'meta_value' => $user_id,
        'orderby'    => 'date',
        'order'      => 'DESC',
    ]);

    if (!$commissions) {
        return '<p>No commissions found.</p>';
    }

    $output = '<table style="width:100%; border-collapse: collapse;">';
    $output .= '<tr><th style="border:1px solid #ccc;padding:8px;">Lead ID</th><th style="border:1px solid #ccc;padding:8px;">Total Sale</th><th style="border:1px solid #ccc;padding:8px;">Commission</th><th style="border:1px solid #ccc;padding:8px;">Date</th></tr>';

    foreach ($commissions as $commission) {
        $lead_id   = get_post_meta($commission->ID, 'lead_id', true);
        $tot_sale  = get_post_meta($commission->ID, 'tot_sale', true);
        $amount    = get_post_meta($commission->ID, 'commission', true);
        $date      = get_the_date('', $commission);

        $output .= "<tr>
            <td style='border:1px solid #ccc;padding:8px;'>$lead_id</td>
            <td style='border:1px solid #ccc;padding:8px;'>$tot_sale</td>
            <td style='border:1px solid #ccc;padding:8px;'>$amount</td>
            <td style='border:1px solid #ccc;padding:8px;'>$date</td>
        </tr>";
    }

    $output .= '</table>';

    return $output;
}
function sap_handle_logout() {
    if (isset($_GET['sap_logout'])) {
        wp_logout();
        // Use wp_redirect() instead of JavaScript for cleaner redirects
            echo '<script>window.location.href = "' . home_url() . '";</script>';
        exit;
    }
}

function sap_output_profile_copy_link() {
    $affiliate_user_id = isset($_COOKIE['affiliate_user_id']) ? intval($_COOKIE['affiliate_user_id']) : 0;
    
    if ( $affiliate_user_id ) {
        $user = get_user_by('ID', $affiliate_user_id);
        $author_id = $affiliate_user_id;
        // Get user profile URL (author archive)
        $profile_url = get_author_posts_url($author_id);

        // Append ?aff=author_id
        $full_url = add_query_arg('aff', $author_id, $profile_url);

        // Escape for safe output
        $safe_url = esc_url($full_url);

        // Output link/button with JS
        ?>
    <div class="link-buttons">
    <div>
    <a href="#"  class="sap-link-input" onclick="copy_link('<?php echo $safe_url; ?>')">
            Copy My Profile Link
        </a>
        </div>
    <div>
    <a href="#" onclick="copy_link('<?php echo site_url('affiliate-signup'); ?>?aff=<?= $author_id ?>')"  class="sap-link-input">
            Copy My Register Link
        </a>
        </div>
        </div>
    <script>
        function copy_link(link)
        {
            const urlToCopy = link;

            // Copy to clipboard
            if (navigator.clipboard && window.isSecureContext) {
                // navigator clipboard api method'
                navigator.clipboard.writeText(urlToCopy).then(function() {
                    alert('Profile link copied to clipboard:\n' + urlToCopy);
                }, function(err) {
                    alert('Failed to copy link: ' + err);
                });
            } else {
                // fallback method
                let textArea = document.createElement("textarea");
                textArea.value = urlToCopy;
                // Avoid scrolling to bottom
                textArea.style.position = "fixed";
                textArea.style.top = 0;
                textArea.style.left = 0;
                textArea.style.width = '2em';
                textArea.style.height = '2em';
                textArea.style.padding = 0;
                textArea.style.border = 'none';
                textArea.style.outline = 'none';
                textArea.style.boxShadow = 'none';
                textArea.style.background = 'transparent';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();

                try {
                    let successful = document.execCommand('copy');
                    if (successful) {
                        alert('Profile link copied to clipboard:\n' + urlToCopy);
                    } else {
                        alert('Failed to copy the profile link');
                    }
                } catch (err) {
                    alert('Oops, unable to copy');
                }

                document.body.removeChild(textArea);
            }
        
        }
    </script>
    <?php
    } else {
        echo 'You need to be logged in to copy your profile link.';
    }
}

add_shortcode('affiliate_dashboard', 'sap_affiliate_dashboard');

function sap_affiliate_dashboard() {
    
    // Check for affiliate cookie first
    $affiliate_user_id = isset($_COOKIE['affiliate_user_id']) ? intval($_COOKIE['affiliate_user_id']) : 0;
    
    if (!$affiliate_user_id) {
        return '<p>Please login to view your dashboard.</p>';
    }
    $user = new WP_User($affiliate_user_id);
    if (!$user) {
        return '<p>Invalid user session. Please login again.</p>';
    }

    global $wpdb;
    $table = $wpdb->prefix . 'affiliate_commissions';
    $rows = $wpdb->get_results("SELECT * FROM $table WHERE affiliate_id = {$affiliate_user_id}");

    $message = '';

    

    // Handle profile update form submission
        
    
    if (isset($_POST['sap_profile_update_nonce']) && wp_verify_nonce($_POST['sap_profile_update_nonce'], 'sap_profile_update')) {
    $new_name = sanitize_text_field($_POST['sap_name']);
    $new_bio = sanitize_textarea_field($_POST['sap_bio']);
    $sap_hubspot_token = sanitize_textarea_field($_POST['sap_hubspot_token']);


    // Update display name
    wp_update_user([
        'ID' => $affiliate_user_id,
        'display_name' => $new_name,
    ]);

    // Update bio (description)
    update_user_meta($affiliate_user_id, 'description', $new_bio);
    $r = update_user_meta($affiliate_user_id, 'sap_hubspot_token', $sap_hubspot_token);

    // Handle profile picture upload
    if (!empty($_FILES['sap_profile_pic']['name'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $profile_id = media_handle_upload('sap_profile_pic', 0);

        if (!is_wp_error($profile_id)) {
            update_user_meta($affiliate_user_id, 'profile_picture', $profile_id);
        } else {
            $message = "Error uploading profile picture: " . $profile_id->get_error_message();
        }
    }

    // Handle cover image upload
    $message = '';

// Password Reset Form STARTS here
if (isset($_POST['sap_reset_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password === $confirm_password) {
        wp_set_password($new_password, $affiliate_user_id);
        $message .= '<p style="color:green;">Password successfully updated.</p>';
    } else {
        $message .= '<p style="color:red;">Passwords do not match.</p>';
    }
}

$message .= '
    <h3>Reset Your Password</h3>
    <form method="POST">
        <label>New Password:</label><br>
        <input type="password" name="new_password" required><br><br>

        <label>Confirm Password:</label><br>
        <input type="password" name="confirm_password" required><br><br>

        <button type="submit" name="sap_reset_password">Update Password</button>
    </form>
';
// Password Reset Form ENDS here

    if (!empty($_FILES['sap_cover_img']['name'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $cover_id = media_handle_upload('sap_cover_img', 0);

        if (!is_wp_error($cover_id)) {
            update_user_meta($affiliate_user_id, 'cover_image', $cover_id);
        } else {
            $message = "Error uploading cover image: " . $cover_id->get_error_message();
        }
    }

    $message = "Profile updated successfully.";
    $user = get_user_by('ID', $affiliate_user_id); // Refresh user data
    
}


    // Handle logout request



    ob_start();
    ?>
        <!--    <style>-->
        <!--    .sap-dashboard {-->
        <!--        display: flex;-->
        <!--        min-height: 500px;-->
        <!--        font-family: Arial, sans-serif;-->
        <!--        width: 100%;-->
        <!--        max-width: 1200px;-->
        <!--        margin: 0 auto;-->
        <!--        padding: 20px;-->
        <!--        box-sizing: border-box;-->
        <!--    }-->
        <!--    .sap-sidebar {-->
        <!--        width: 220px;-->
        <!--        background: #111;-->
        <!--        color: #fff;-->
        <!--        padding: 20px;-->
        <!--        box-sizing: border-box;-->
        <!--    }-->
        <!--    .sap-sidebar h3 {-->
        <!--        margin-top: 0;-->
        <!--        margin-bottom: 15px;-->
        <!--        font-weight: normal;-->
        <!--        font-size: 20px;-->
        <!--        border-bottom: 1px solid #444;-->
        <!--        padding-bottom: 10px;-->
        <!--    }-->
        <!--    .sap-sidebar ul {-->
        <!--        list-style: none;-->
        <!--        padding: 0;-->
        <!--    }-->
        <!--    .sap-sidebar ul li {-->
        <!--        margin-bottom: 12px;-->
        <!--    }-->
        <!--    .sap-sidebar ul li a {-->
        <!--        color: #fff;-->
        <!--        text-decoration: none;-->
        <!--        font-size: 15px;-->
        <!--        cursor: pointer;-->
        <!--        transition: color 0.3s;-->
        <!--    }-->
        <!--    .sap-sidebar ul li a:hover {-->
        <!--        color: #4caf50;-->
        <!--    }-->
        <!--    .sap-content {-->
        <!--        flex: 1;-->
        <!--        background: #fff;-->
        <!--        padding: 25px;-->
        <!--        box-sizing: border-box;-->
        <!--        color: #222;-->
        <!--    }-->
        <!--    .sap-commission-table {-->
        <!--        width: 100%;-->
        <!--        border-collapse: collapse;-->
        <!--        margin-top: 20px;-->
        <!--    }-->
        <!--    .sap-commission-table th, .sap-commission-table td {-->
        <!--        border: 1px solid #ddd;-->
        <!--        padding: 10px;-->
        <!--        text-align: left;-->
        <!--    }-->
        <!--    .sap-commission-table th {-->
        <!--        background: #f5f5f5;-->
        <!--    }-->
        <!--    .sap-message {-->
        <!--        margin-top: 10px;-->
        <!--        font-weight: bold;-->
        <!--    }-->
        <!--    .error-msg {-->
        <!--        color: #d33;-->
        <!--    }-->
        <!--    .success-msg {-->
        <!--        color: #2a8;-->
        <!--        word-break: break-word;-->
        <!--    }-->
        <!--    .sap-link-input, .sap-profile-input, .sap-profile-textarea {-->
        <!--        width: 100%;-->
        <!--        padding: 8px;-->
        <!--        font-size: 14px;-->
        <!--        margin-top: 8px;-->
        <!--        box-sizing: border-box;-->
        <!--        border: 1px solid #ccc;-->
        <!--        border-radius: 3px;-->
        <!--    }-->
        <!--    .sap-btn {-->
        <!--        margin-top: 12px;-->
        <!--        padding: 10px 18px;-->
        <!--        background: #4caf50;-->
        <!--        border: none;-->
        <!--        color: white;-->
        <!--        cursor: pointer;-->
        <!--        font-size: 15px;-->
        <!--        border-radius: 3px;-->
        <!--        transition: background 0.3s;-->
        <!--    }-->
        <!--    .sap-btn:hover {-->
        <!--        background: #388e3c;-->
        <!--    }-->
        <!--        canvas {-->
        <!--  max-width: 100%;-->
        <!--height: 500px !important; /* Fix height *-->
        <!--  background: white;-->
        <!--  border: 1px solid #ddd;-->
        <!--  padding: 10px;-->
        <!--  box-shadow: 0 2px 6px rgba(0,0,0,0.1);-->
        <!--}-->
        <!--/*---shoib work----*/-->
        <!--.dashboard-row {-->
        <!--      display: flex;-->
        <!--      justify-content: space-between;-->
        <!--      gap: 20px;-->
        <!--    }-->

        <!--    .card {-->
        <!--      flex: 1;-->
        <!--      background-color: white;-->
        <!--      padding: 20px;-->
        <!--      border-radius: 10px;-->
        <!--      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);-->
        <!--      text-align: center;-->
        <!--      transition: transform 0.2s ease;-->
        <!--    }-->

        <!--    .card:hover {-->
        <!--      transform: translateY(-5px);-->
        <!--    }-->

        <!--    .card h2 {-->
        <!--      font-size: 24px;-->
        <!--      margin-bottom: 10px;-->
        <!--      color: #333;-->
        <!--    }-->

        <!--    .card p {-->
        <!--      font-size: 32px;-->
        <!--      font-weight: bold;-->
        <!--      color: #007BFF;-->
        <!--    }-->

        <!--/* Responsive */-->
        <!--    @media (max-width: 768px) {-->
        <!--      .dashboard-row {-->
        <!--        flex-direction: column;-->
        <!--      }-->
        <!--    }-->
        <!--    </style>-->


        <style>
            

            #welcomeMsg {
      color: lightgreen;
      font-size: 20px;
      animation: blink 1s infinite;
    }
    @keyframes blink {
  0% { opacity: 1; }
  50% { opacity: 0; }
  100% { opacity: 1; }
}
        .link-buttons{
            display:flex;
            gap:10px;
            margin-top: 30px;
        }
            .sap-dashboard {
                display: flex;
                min-height: 500px;
                font-family: Arial, sans-serif;
                width: 100%;
                /*max-width: 1200px;*/
                margin: 0 auto;
                padding: 20px;
                box-sizing: border-box;
                flex-wrap: wrap; /* Allow flexbox items to wrap on smaller screens */
            }
            .sap-sidebar {
                width: 220px;
                background: #5C163D;
                color: #fff;
                padding: 20px;
                box-sizing: border-box;
                margin-right: 20px;
                border-radius:30px;
            }
            .sap-sidebar h3 {
                margin-top: 0;
                margin-bottom: 15px;
                font-weight: normal;
                font-size: 20px;
                border-bottom: 1px solid white;
                padding-bottom: 10px;
            }
            .sap-sidebar ul {
                list-style: none;
                padding: 0;
            }
            .sap-sidebar ul li {
                margin-bottom: 12px;
            }
            .sap-sidebar ul li a {
                color: #fff;
                text-decoration: none;
                font-size: 15px;
                cursor: pointer;
                transition: color 0.3s;
            }
            .sap-sidebar ul li a:hover {
                color: #4caf50;
            }
            .sap-content {
                flex: 1;
                background: #fff;
                padding: 0px;
                box-sizing: border-box;
                color: #222;
                margin-right:17px;
            }
            .sap-commission-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }
            .sap-commission-table th, .sap-commission-table td {
                border: 1px solid #ddd;
                padding: 10px;
                text-align: left;
            }
            .sap-commission-table th {
                background: #f5f5f5;
            }
            .sap-message {
                margin-top: 10px;
                font-weight: bold;
            }
            .error-msg {
                color: #d33;
            }
            .success-msg {
                color: #2a8;
                word-break: break-word;
            }
            .sap-link-input, .sap-profile-input, .sap-profile-textarea {
                max-width: 400px;
                padding: 8px;
                font-size: 14px;
                margin-top: 8px;
                box-sizing: border-box;
                border: 1px solid #ccc;
                border-radius: 3px;
                border: 2px solid #C68F4E;
                
            }
            .sap-link-input,
.sap-profile-input,
.sap-profile-textarea {
    max-width: 100%;
    width: 100%;
    padding: 12px 16px;
    font-size: 15px;
    margin-top: 10px;
    box-sizing: border-box;
    border: 2px solid #C68F4E;
    border-radius: 10px;
    background-color: #fff;
    color: #333;
    font-family: 'Segoe UI', sans-serif;
    transition: all 0.3s ease;
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
}

/* Focus effect */
.sap-link-input:focus,
.sap-profile-input:focus,
.sap-profile-textarea:focus {
    border-color: #5C163D;
    box-shadow: 0 0 0 4px rgba(92, 22, 61, 0.2);
    outline: none;
}

            
            .sap-btn {
                margin-top: 12px;
                padding: 10px 18px;
                background: #4caf50;
                border: none;
                color: white;
                cursor: pointer;
                font-size: 15px;
                border-radius: 20px;
                transition: background 0.3s;
            }
            .sap-btn:hover {
                background: #388e3c;
            }
        
            /* Card Layout */
            /*.dashboard-row {*/
            /*    display: flex;*/
            /*    justify-content: space-between;*/
            /*    gap: 20px;*/
            /*    margin-bottom: 20px;*/
            /*}*/
            
            
            .dashboard-row {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); /* Create responsive grid */
            gap: 20px;
            margin-bottom: 20px;
            }
            
            
            .card {
                flex: 1;
                background-color: white;
                padding: 10px;
                border-radius: 10px;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                text-align: center;
                transition: transform 0.2s ease;
            }
            .card:hover {
                transform: translateY(-5px);
            }
            .card h2 {
                font-size: 24px;
                margin-bottom: 10px;
                color: #333;
            }
            .card p {
                font-size: 32px;
                font-weight: bold;
                color: #007BFF;
            }
        
            /* Canvas Styling */
            canvas {
                max-width: 100%;
                height: 400px !important; /* Fix height */
                background: white;
                border: 1px solid #ddd;
                padding: 10px;
                box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            }
            
            .abc{
                display: flex; 
                align-items: center; 
                /*justify-content:center;*/
                gap: 10px;
                /*border: 2px solid white;*/
                margin: 0 0 10px 0;
            }
            
            .menu-list{
                list-style: none;
                margin: 0;
                padding: 0;
            }
         
        
            /* Responsive Design */
            @media (max-width: 768px) {
                .sap-sidebar {
                    width: 100%;
                    margin-bottom: 20px;
                }
                .sap-dashboard {
                    flex-direction: column;
                }
                .dashboard-row {
                grid-template-columns: repeat(2, 1fr); /* Display 2 cards per row on mobile 
                }
                
                .sap-content {
                    padding: 15px;
                }
                
                .card {
                    margin-bottom: 20px;
                }
            }
        
            /* Larger screen adjustments */
            @media (min-width: 768px) {
                .sap-sidebar {
                    width: 250px;
                }
                .dashboard-row {
                grid-template-columns: repeat(3, 1fr); /* Display 3 cards per row on larger screens */
            }
            }
        </style>
        <div class="sap-dashboard">
            <div class="sap-sidebar">
                <h3>Affiliate Dashboard</h3>
                <ul>
                    <!--<li><a href="#" onclick="showSection('dashboard');return false;">Dashboard</a></li>-->

                    <!--<li><a href="#" onclick="showSection('generate_link');return false;">Generate Affiliate Link</a></li>-->

                    <!--<li><a href="#" onclick="showSection('edit_profile');return false;">Edit Profile</a></li>-->

                    <!--<div style="display:flex">-->
                    <!--     <img src="<?= site_url(); ?>/wp-content/uploads/2025/06/logout.png" style="width: 24px; height: auto; filter: brightness(0) invert(1);" alt="Logout Image">-->
                    <!--  <li><a href="<?php echo esc_url(add_query_arg('sap_logout', '1')); ?>">Logout</a></li>-->
                    <!--</div>-->




                    <!--      <div class="abc">-->
                    <!--    <img src="<?= site_url(); ?>/wp-content/uploads/2025/06/speedometer1.png" -->
                    <!--         style="width: 16px; height: auto; filter: brightness(0) invert(1);" -->
                    <!--         alt="Logout Image">-->

                    <!--               <li style="list-style: none; margin: 0; padding: 0;"><a href="#" onclick="showSection('dashboard');return false;">Dashboard</a></li>-->


                    <!--      </div>-->

                    <!--     <div class="abc">-->
                    <!--    <img src="<?= site_url(); ?>/wp-content/uploads/2025/06/link.png" -->
                    <!--         style="width: 24px; height: auto; filter: brightness(0) invert(1);" -->
                    <!--         alt="Logout Image">-->
                    <!--    <li class="menu-list"><a href="#" onclick="showSection('generate_link');return false;">Generate Affiliate Link</a></li>-->
                    <!--      </div>-->

                    <!--      <div class="abc" >-->
                    <!--    <img src="<?= site_url(); ?>/wp-content/uploads/2025/06/user-avatar.png" -->
                    <!--         style="width: 24px; height: auto; filter: brightness(0) invert(1);" -->
                    <!--         alt="Logout Image">-->
                    <!--    <li class="menu-list"><a href="#" onclick="showSection('edit_profile');return false;">Edit Profile</a></li>-->
                    <!--</div>-->

                    <!--      <div class="abc">-->
                    <!--    <img src="<?= site_url(); ?>/wp-content/uploads/2025/06/logout.png" -->
                    <!--         style="width: 24px; height: auto; filter: brightness(0) invert(1);" -->
                    <!--         alt="Logout Image">-->
                    <!--    <li class="menu-list">-->
                    <!--        <a href="<?php echo esc_url(add_query_arg('sap_logout', '1')); ?>" -->
                    <!--           style="text-decoration: none; font-size: 16px; font-weight: bold;">-->
                    <!--            Logout-->
                    <!--        </a>-->
                    <!--    </li>-->
                    <!--</div>-->
                    <div class="abc">
                        <img src="<?= site_url(); ?>wp-content/uploads/2025/08/dashboard.png" style="width: 16px; height: auto; filter: brightness(0) invert(1);" alt="Logout Image">

                        <li style="list-style: none; margin: 0; padding: 0;"><a href="#" onclick="showSection('dashboard');return false;">Dashboard</a></li>
                    </div>
                    <div class="abc">
                        <img src="<?= site_url(); ?>/wp-content/uploads/2025/06/speedometer1.png" style="width: 16px; height: auto; filter: brightness(0) invert(1);" alt="Logout Image">

                        <li style="list-style: none; margin: 0; padding: 0;"><a href="#" onclick="showSection('commissions');return false;">commissions</a></li>
                    </div>

                    <div class="abc">
                        <img src="<?= site_url(); ?>/wp-content/uploads/2025/06/link.png" style="width: 16px; height: auto; filter: brightness(0) invert(1);" alt="Logout Image">
                        <li style="list-style: none; margin: 0; padding: 0;">
                            <a href="#" onclick="showSection('generate_link');return false;">Affiliate Link
                            </a>
                        </li>
                    </div>

                    <div class="abc">
                        <img src="<?= site_url(); ?>/wp-content/uploads/2025/06/user-avatar.png" style="width: 16px; height: auto; filter: brightness(0) invert(1);" alt="Logout Image">
                        <li style="list-style: none; margin: 0; padding: 0;">
                            <a href="#" onclick="showSection('edit_profile');return false;">Edit Profile</a>
                        </li>
                    </div>
                    <div class="abc">
                        <img src="<?= site_url(); ?>/wp-content/uploads/2025/06/user-avatar.png" style="width: 16px; height: auto; filter: brightness(0) invert(1);" alt="Logout Image">
                        <li style="list-style: none; margin: 0; padding: 0;">
                            <a href="#" onclick="showSection('change_pass');return false;">Change Password</a>
                        </li>
                    </div>

                    <div class="abc">
                        <img src="<?= site_url(); ?>/wp-content/uploads/2025/06/logout.png" style="width: 16px; height: auto; filter: brightness(0) invert(1);" alt="Logout Image">
                        <li style="list-style: none; margin: 0; padding: 0;">
                            <a href="<?php echo esc_url(add_query_arg('sap_logout', '1')); ?>">
                        Logout
                    </a>
                        </li>
                    </div>



                </ul>
            </div>

            <div class="sap-content">
                <?php if ($message): ?>
                    <div class="sap-message success-msg">
                        <?php echo esc_html($message); ?>
                    </div>
                    <?php endif; ?>
                        <div id="commissions" class="sap-section">
                            <h2>Commissions</h2>
                            <?php
                            echo show_affiliate_commissions();
                            ?>
                        </div>
                        <?php
                            require_once "new_dashboard.php";

                            ?>
                            

                        <div id="generate_link" class="sap-section" style="display:none;">
                            <h2>Generate Affiliate Links</h2>
                            <?php 
                    sap_output_profile_copy_link();
                ?>
                                <?php if (isset($error)) : ?>
                                    <div class="sap-message error-msg">
                                        <?php echo esc_html($error); ?>
                                    </div>
                                    <?php elseif (isset($success)) : ?>
                                        <div class="sap-message success-msg">
                                            Your affiliate link:
                                            <br>
                                            <a href="<?php echo esc_url($success); ?>" target="_blank">
                                                <?php echo esc_html($success); ?>
                                            </a>
                                        </div>
                                        <?php endif; ?>
                        </div>

                        <?php
                        
                        require_once "edit_profile.php";
                        
                        require_once "change_pass.php";

                        ?>










            </div>
        </div> 





        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script>
  const welcomeDiv = document.getElementById('welcomeMsg');

  // Check localStorage if message was already hidden
  if (localStorage.getItem('welcomeHidden') === 'true') {
    welcomeDiv.style.display = 'none';
  } else {
    // Start 5 minute timer (5 minutes = 300000 milliseconds)
    setTimeout(() => {
      welcomeDiv.style.display = 'none';
      localStorage.setItem('welcomeHidden', 'true');
    }, 5000); 
  }
</script>

<script type="text/javascript">
  google.charts.load("current", {packages:['corechart']});
  google.charts.setOnLoadCallback(drawChart);

  function drawChart() {
    var data = google.visualization.arrayToDataTable([
      ["Date", "Clicks", "Pending Requests", "Confirm Requests"],
      <?php
      $d = get_affiliate_clicks_data_callback();
      foreach($d as $k=> $v)
      {
          ?>
          ["<?= $v['date'] ?>", <?= $v['clicks'] ?>, <?= $v['pending'] ?>, <?= $v['confirm'] ?>],
          <?php
      }
      ?>
    ]);

    var options = {
      chartArea: {width: '60%'},
      title: "Daily Performance",
      hAxis: {
        title: 'Count',
        minValue: 0
      },
      vAxis: {
        title: 'Date'
      },
      bars: 'horizontal',
      colors: ['#4285F4', '#FF9900', '#0F9D58']
    };

    var chart = new google.visualization.BarChart(document.getElementById("barchart_values"));
    chart.draw(data, options);
  }
</script>


        <script>
            $('#generate-affiliate-link').on('click', function (e) {
                e.preventDefault();
                $('#affiliate-link-result').html('Generating...');
                $('#copy-link-btn').hide();
        
                jQuery.ajax({
                    url: '<?php echo admin_url("admin-ajax.php"); ?>',
                    type: 'POST',
                    data: {
                        action: 'generate_affiliate_link',
                        link :$('#affiliate_link').val()
                    },
                    success: function (response) {
                        $('#affiliate-link-result').html('<strong>Your Link:</strong> ' + response);
                        $('#copy-link-btn').show().data('link', response);
                    },
                    error: function () {
                        $('#affiliate-link-result').html('Error generating link.');
                        $('#copy-link-btn').hide();
                    }
                });
            });
        
            jQuery('#copy-link-btn').on('click', function () {
                const link = jQuery(this).data('link');
                if (!link) return;
        
                // Create temporary input to copy text
                const tempInput = document.createElement('input');
                tempInput.value = link;
                document.body.appendChild(tempInput);
                tempInput.select();
                tempInput.setSelectionRange(0, 99999); // For mobile devices
        
                try {
                    const successful = document.execCommand('copy');
                    if (successful) {
                        alert('Link copied to clipboard!');
                    } else {
                        alert('Failed to copy link. Please copy manually.');
                    }
                } catch (err) {
                    alert('Oops, unable to copy!');
                }
        
                document.body.removeChild(tempInput);
            });
            function showSection(id) {
                var sections = document.querySelectorAll('.sap-section');
                sections.forEach(function(s){
                    s.style.display = 'none';
                });
                document.getElementById(id).style.display = 'block';
            }
            // Show commissions section by default
            showSection('dashboard');
        </script>

        <?php
    return ob_get_clean();
}
add_action('wp_ajax_generate_affiliate_link', 'generate_affiliate_link_callback');
add_action('wp_ajax_nopriv_generate_affiliate_link', 'generate_affiliate_link_callback');

function generate_affiliate_link_callback() {
    $user_id = get_current_user_id();
    if (!$user_id) {
        echo 'You must be logged in.';
        wp_die();
    }

    $input_link = trim(sanitize_text_field($_POST['link']));
        $site_url = home_url();

        if (strpos($input_link, $site_url) !== 0) {
            echo $error = '<span style="color:red;">Invalid link! Link must be from your site domain.</span>';
            exit();
        } else {
            $affiliate_id_encoded = $user_id;
            $separator = (strpos($input_link, '?') === false) ? '?' : '&';
            $generated_link = $input_link . $separator . "aff={$affiliate_id_encoded}";
            echo $success = $generated_link;
            exit();
        }
}
function register_affiliate_click_post_type() {
    register_post_type('affiliate_click', [
        'labels' => [
            'name' => 'Affiliate Clicks',
            'singular_name' => 'Affiliate Click',
        ],
        'public' => false,
        'show_ui' => true,
        'supports' => ['title'],
        'menu_icon' => 'dashicons-location', // optional
    ]);
}
add_action('init', 'register_affiliate_click_post_type');
add_action('init', function () {
    if (isset($_GET['aff'])) {
        $affiliate_id = intval($_GET['aff']);
        $ip = $_SERVER['REMOTE_ADDR'];

        // Get country from IP
        $country = '';
        $ip_info = @json_decode(file_get_contents("http://ip-api.com/json/{$ip}"));
        if ($ip_info && $ip_info->status === 'success') {
            $country = $ip_info->country;
        }

        // Create post
        $post_id = wp_insert_post([
            'post_type' => 'affiliate_click',
            'post_title' => $ip,
            'post_status' => 'publish',
        ]);

        if ($post_id && !is_wp_error($post_id)) {
            update_post_meta($post_id, 'affiliate_id', $affiliate_id);
            update_post_meta($post_id, 'ip_address', $ip);
            update_post_meta($post_id, 'country', $country);
        }

        // Redirect to your landing page
        // wp_redirect(home_url('/your-target-page'));
        // exit;
    }
});

add_action('add_meta_boxes', function () {
    add_meta_box('click_meta_box', 'Click Info', function ($post) {
        $user_info = get_userdata(get_post_meta($post->ID, 'affiliate_id', true));
$affiliate_name = $user_info ? $user_info->display_name : 'Unknown';

        echo '<strong>Affiliate:</strong> ' . $affiliate_name . '<br>';
        echo '<strong>IP Address:</strong> ' . get_post_meta($post->ID, 'ip_address', true) . '<br>';
        echo '<strong>Country:</strong> ' . get_post_meta($post->ID, 'country', true);
    }, 'affiliate_click');
});
add_action('wp_ajax_get_affiliate_clicks_data', 'get_affiliate_clicks_data_callback');
add_action('wp_ajax_nopriv_get_affiliate_clicks_data', 'get_affiliate_clicks_data_callback');
function get_pending_leads_count_by_date($user_id, $date) {
    global $wpdb;

    $count = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(*)
        FROM {$wpdb->prefix}posts p
        INNER JOIN {$wpdb->prefix}postmeta m1 ON p.ID = m1.post_id
        INNER JOIN {$wpdb->prefix}postmeta m2 ON p.ID = m2.post_id
        WHERE p.post_type = 'lead'
          AND p.post_status = 'publish'
          AND DATE(p.post_date) = %s
          AND m1.meta_key = 'affiliate_id'
          AND m1.meta_value = %d
          AND m2.meta_key = 'status'
          AND m2.meta_value = 'pending'
    ", $date, $user_id));

    return intval($count);
}
function get_pending_leads_count_by_date_range($user_id, $from_date, $to_date) {
    global $wpdb;

    $query = "
        SELECT COUNT(*)
        FROM {$wpdb->prefix}posts p
        INNER JOIN {$wpdb->prefix}postmeta m1 ON p.ID = m1.post_id
        INNER JOIN {$wpdb->prefix}postmeta m2 ON p.ID = m2.post_id
        WHERE p.post_type = 'lead'
          AND p.post_status = 'publish'
          AND DATE(p.post_date) BETWEEN %s AND %s
          AND m2.meta_key = 'status'
          AND m2.meta_value = 'pending'
    ";

    $params = [$from_date, $to_date];

    if ($user_id > 0) {
        $query .= " AND m1.meta_key = 'affiliate_id' AND m1.meta_value = %d";
        $params[] = $user_id;
    }

    $prepared_query = $wpdb->prepare($query, ...$params);
    $count = $wpdb->get_var($prepared_query);

    return intval($count);
}


function get_clicks_by_date_range($user_id, $from_date, $to_date) {
    global $wpdb;

    $query = "
        SELECT COUNT(*)
        FROM {$wpdb->prefix}posts p
        INNER JOIN {$wpdb->prefix}postmeta m ON p.ID = m.post_id
        WHERE p.post_type = 'affiliate_click'
          AND p.post_status = 'publish'
          AND m.meta_key = 'affiliate_id'
          AND DATE(p.post_date) BETWEEN %s AND %s
    ";

    $params = [$from_date, $to_date];

    // Apply user_id filter only if it's greater than 0
    if ($user_id > 0) {
        $query .= " AND m.meta_value = %d";
        $params[] = $user_id;
    }

    $prepared_query = $wpdb->prepare($query, ...$params);
    $clicks = $wpdb->get_var($prepared_query);

    return intval($clicks);
}



function get_clicks_by_date($user_id, $date) {
    global $wpdb;

    $clicks = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(*)
        FROM {$wpdb->prefix}posts p
        INNER JOIN {$wpdb->prefix}postmeta m ON p.ID = m.post_id
        WHERE p.post_type = 'affiliate_click'
          AND p.post_status = 'publish'
          AND m.meta_key = 'affiliate_id'
          AND m.meta_value = %d
          AND DATE(p.post_date) = %s
    ", $user_id, $date));

    return intval($clicks);
}
function get_confirmed_leads_count_by_date($user_id,$date) {
    if (!function_exists('wp_get_current_user')) {
        require_once ABSPATH . 'wp-includes/pluggable.php';
    }
    global $wpdb;

    $count = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(*)
        FROM {$wpdb->prefix}posts p
        INNER JOIN {$wpdb->prefix}postmeta m1 ON p.ID = m1.post_id
        INNER JOIN {$wpdb->prefix}postmeta m2 ON p.ID = m2.post_id
        WHERE p.post_type = 'lead'
          AND p.post_status = 'publish'
          AND DATE(p.post_date) = %s
          AND m1.meta_key = 'affiliate_id'
          AND m1.meta_value = %d
          AND m2.meta_key = 'status'
          AND m2.meta_value = 'confirmed'
    ", $date, $user_id));

    return intval($count);
}
// 🔹 Function 1: Get Level 1 referrals
// 🔹 Function 2: Get Level 2 referral count
function get_level_2_affiliates_count($user_id, $start_date, $end_date) {
    global $wpdb;

    // Step 1: Get Level 1 referrals
    $level1_users = get_level_1_affiliates($user_id, $start_date, $end_date);

    if (empty($level1_users)) {
        return 0;
    }

    $start = date('Y-m-d 00:00:00', strtotime($start_date));
    $end = date('Y-m-d 23:59:59', strtotime($end_date));

    // Step 2: Count Level 2 referrals (referred by Level 1 users)
    $placeholders = implode(',', array_fill(0, count($level1_users), '%d'));

    $query = "
        SELECT COUNT(*)
        FROM {$wpdb->users} u
        INNER JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
        WHERE um.meta_key = 'referred_by_affiliate'
        AND um.meta_value IN ($placeholders)
        AND u.user_registered BETWEEN %s AND %s
    ";

    $prepared = array_merge($level1_users, [$start, $end]);

    $count = $wpdb->get_var($wpdb->prepare($query, ...$prepared));

    return intval($count);
}
function get_affiliates_by_referrers($referrer_ids, $start_date, $end_date) {
    global $wpdb;

    if (empty($referrer_ids)) return [];

    $placeholders = implode(',', array_fill(0, count($referrer_ids), '%d'));
    $start = date('Y-m-d 00:00:00', strtotime($start_date));
    $end   = date('Y-m-d 23:59:59', strtotime($end_date));

    $query = "
        SELECT u.ID
        FROM {$wpdb->users} u
        INNER JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
        WHERE um.meta_key = 'referred_by_affiliate'
        AND um.meta_value IN ($placeholders)
        AND u.user_registered BETWEEN %s AND %s
    ";

    $prepared = array_merge($referrer_ids, [$start, $end]);
    return $wpdb->get_col($wpdb->prepare($query, ...$prepared));
}
function get_level_1_affiliates($user_id, $start_date, $end_date) {
    return get_affiliates_by_referrers([$user_id], $start_date, $end_date);
}
function get_level_2_affiliates($user_id, $start_date, $end_date) {
    $level1 = get_level_1_affiliates($user_id, $start_date, $end_date);
    return get_affiliates_by_referrers($level1, $start_date, $end_date);
}
function get_level_3_affiliates($user_id, $start_date, $end_date) {
    $level2 = get_level_2_affiliates($user_id, $start_date, $end_date);
    return get_affiliates_by_referrers($level2, $start_date, $end_date);
}
function get_level_4_affiliates($user_id, $start_date, $end_date) {
    $level3 = get_level_3_affiliates($user_id, $start_date, $end_date);
    return get_affiliates_by_referrers($level3, $start_date, $end_date);
}





function get_confirmed_leads_count_by_date_range($user_id, $from_date, $to_date) {
    global $wpdb;

    $query = "
        SELECT COUNT(DISTINCT p.ID)
        FROM {$wpdb->prefix}posts p
        LEFT JOIN {$wpdb->prefix}postmeta affiliate_meta ON p.ID = affiliate_meta.post_id AND affiliate_meta.meta_key = 'affiliate_id'
        INNER JOIN {$wpdb->prefix}postmeta status_meta ON p.ID = status_meta.post_id AND status_meta.meta_key = 'status' AND status_meta.meta_value = 'confirmed'
        WHERE p.post_type = 'lead'
          AND p.post_status = 'publish'
          AND DATE(p.post_date) BETWEEN %s AND %s
    ";

    $params = [$from_date, $to_date];

    if ($user_id > 0) {
        $query .= " AND affiliate_meta.meta_value = %d";
        $params[] = $user_id;
    }

    $prepared_query = $wpdb->prepare($query, ...$params);
    $count = $wpdb->get_var($prepared_query);

    // 🔍 Debug actual SQL query
    if (isset($_GET['sql'])) {
        echo '<pre>' . esc_html($wpdb->last_query) . '</pre>';
        echo '<strong>Count:</strong> ' . intval($count);
    }

    return intval($count);
}

function get_total_leads_count_by_date_range($user_id, $from_date, $to_date) {
    global $wpdb;

    $query = "
        SELECT COUNT(DISTINCT p.ID)
        FROM {$wpdb->prefix}posts p
        INNER JOIN {$wpdb->prefix}postmeta m1 ON p.ID = m1.post_id
        WHERE p.post_type = 'lead'
          AND p.post_status = 'publish'
          AND DATE(p.post_date) BETWEEN %s AND %s
    ";

    $params = [$from_date, $to_date];

    if ($user_id > 0) {
        $query .= " AND m1.meta_key = 'affiliate_id' AND m1.meta_value = %d";
        $params[] = $user_id;
    }

    $prepared_query = $wpdb->prepare($query, ...$params);
    $count = $wpdb->get_var($prepared_query);

    // 🔍 Debug: Show actual executed query
    if (isset($_GET['sql'])) {
        echo '<pre>' . esc_html($wpdb->last_query) . '</pre>';
        echo '<strong>Count:</strong> ' . intval($count);
    }

    return intval($count);
}






function get_affiliate_clicks_data_callback() {
    if ( ! function_exists( 'wp_get_current_user' ) ) {
        require_once ABSPATH . 'wp-includes/pluggable.php';
    }

    $user = wp_get_current_user();
    if (!$user) {
        wp_send_json_error('User not logged in');
        wp_die();
    }

    $user_id = $user->ID;

    $d = array();

    // Month start and end
    $start = new DateTime(date('Y-m-01')); // start of month
    $end = new DateTime(); // today's date
    $end = $end->modify('+1 day');

    // Iterate week by week
    $week_number = 1;
    while ($start < $end) {
        // Calculate week start and end
        $week_start = clone $start;
        $week_end = clone $week_start;
        $week_end->modify('+6 days');
        if ($week_end > $end) {
            $week_end = clone $end;
        }

        // Fetch weekly data
        $clicks = get_clicks_by_date_range($user_id, $week_start->format('Y-m-d'), $week_end->format('Y-m-d'));
        $pending = get_pending_leads_count_by_date_range($user_id, $week_start->format('Y-m-d'), $week_end->format('Y-m-d'));
        $confirm = get_confirmed_leads_count_by_date_range($user_id, $week_start->format('Y-m-d'), $week_end->format('Y-m-d'));

        $d[] = array(
            'date' => "Week $week_number ",
            'clicks' => $clicks,
            'pending' => $pending,
            'confirm' => $confirm,
        );

        // Move to next week
        $start->modify('+7 days');
        $week_number++;
    }

    return $d;
}

if(isset($_GET['affiliate-stats']))
{
 get_affiliate_clicks_data_callback();   
}
add_action('admin_post_sap_download_vendor_csv', 'sap_download_vendor_csv');

function sap_download_vendor_csv() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }

    // Get dates from GET request
    $from_date = isset($_GET['from_date']) ? sanitize_text_field($_GET['from_date']) : date('Y-m-01');
    $to_date   = isset($_GET['to_date']) ? sanitize_text_field($_GET['to_date']) : date('Y-m-t');

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=vendors.csv');
    $output = fopen('php://output', 'w');

    // CSV header
    fputcsv($output, ['Name', 'Email', 'Clicks', 'Pending Leads', 'Confirm Leads', 'Commissions']);

    $vendors = get_users([
        'role'    => 'affiliate',
        'orderby' => 'user_registered',
        'order'   => 'DESC',
    ]);

    foreach ($vendors as $vendor) {
        $name   = $vendor->display_name;
        $email  = $vendor->user_email;

        // Your stat functions
        $clicks         = get_clicks_by_date_range($vendor->ID, $from_date, $to_date);
        $pending_leads  = get_pending_leads_count_by_date_range($vendor->ID, $from_date, $to_date);
        $confirm_leads  = get_confirmed_leads_count_by_date_range($vendor->ID, $from_date, $to_date);
        $commission     = get_affiliate_commission_total($vendor->ID, $from_date, $to_date);

        fputcsv($output, [
            $name,
            $email,
            $clicks ?: 0,
            $pending_leads ?: 0,
            $confirm_leads ?: 0,
            $commission ?: 0,
        ]);
    }

    fclose($output);
    exit;
}
