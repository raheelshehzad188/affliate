<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Hook to create admin menu
add_action('admin_menu', 'sap_register_affiliate_menu');

function sap_register_affiliate_menu() {
    add_menu_page(
        'Affiliates',
        'Simple Affiliate',
        'read',
        'sap_affiliates',
        'sap_affiliate_dashboard_page',
        'dashicons-groups',
        25
    );

    add_submenu_page(
        'sap_affiliates',
        'Vendors',
        'Vendors',
        'manage_options',
        'sap_vendors',
        'sap_vendor_list_page'
    );
    add_submenu_page(
    'sap_affiliates',
    'Commissions',
    'Commissions',
    'manage_options',
    'sap_commissions',
    'sap_commission_list_page'
);
add_submenu_page(
    'sap_affiliates',
    'Refresh All Data',
    'Refresh All Data',
    'manage_options',
    'sap_refresh_data',
    'sap_refresh_all_data_page'
);
}
function sap_delete_all_affiliate_data() {
    // 1. Delete all posts from custom post types
    global $wpdb;

$post_types = ['affiliate_click', 'lead', 'commission'];

// Convert to SQL-safe string
$types_sql = implode("','", array_map('esc_sql', $post_types));

// Get all matching post IDs
$post_ids = $wpdb->get_col("
    SELECT ID FROM {$wpdb->posts} 
    WHERE post_type IN ('$types_sql')
");
// Delete posts and related meta
if (!empty($post_ids)) {
    $ids_str = implode(',', array_map('intval', $post_ids));

    // Delete post meta
    $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE post_id IN ($ids_str)");

    // Delete the posts
    $r = $wpdb->query("DELETE FROM {$wpdb->posts} WHERE ID IN ($ids_str)");
}


    // Get all user IDs with 'affiliate' role
$user_ids = $wpdb->get_col("
    SELECT user_id 
    FROM {$wpdb->usermeta} 
    WHERE meta_key = '{$wpdb->prefix}capabilities' 
    AND meta_value LIKE '%affiliate%'
");

if (!empty($user_ids)) {
    $ids_str = implode(',', array_map('intval', $user_ids));

    // Delete usermeta first
    $wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE user_id IN ($ids_str)");

    // Delete users
    $wpdb->query("DELETE FROM {$wpdb->users} WHERE ID IN ($ids_str)");
}

    

}

function sap_do_full_refresh() {
    $users = get_users(['role__in' => ['affiliate']]);

    $post_types = ['lead', 'commission', 'payout']; // Aapke post type slugs

    foreach ($post_types as $post_type) {
        $posts = get_posts([
            'post_type' => $post_type,
            'numberposts' => -1,
            'post_status' => 'any'
        ]);

        foreach ($posts as $post) {
            wp_delete_post($post->ID, true); // true = force delete
        }
    }
}

function sap_refresh_all_data_page() {
    if (isset($_POST['refresh_data'])) {
        sap_delete_all_affiliate_data();
        echo '<div class="updated"><p><strong>All data refreshed successfully.</strong></p></div>';
    }

    ?>
    <div class="wrap">
        <h1>Refresh All Data</h1>
        <p>This will regenerate affiliate leads and commissions data.</p>
        <form method="post">
            <input type="submit" name="refresh_data" class="button button-primary" value="Refresh Now">
        </form>
    </div>
    <?php
}

function sap_commission_list_page() {
    global $wpdb;

    // Filters
    $selected_aff = isset($_GET['aff_id']) ? intval($_GET['aff_id']) : '';
    $start_date = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : '';
    $end_date = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : '';

    $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $per_page = 10;
    $offset = ($paged - 1) * $per_page;

    $args = [
        'post_type'      => 'commission',
        'posts_per_page' => $per_page,
        'offset'         => $offset,
        'post_status'    => 'publish',
        'meta_query'     => []
    ];

    // Affiliate Filter
    if ($selected_aff) {
        $args['meta_query'][] = [
            'key'   => 'aff_id',
            'value' => $selected_aff,
        ];
    }

    // Date Range Filter
    if ($start_date || $end_date) {
        $date_query = [];

        if ($start_date) {
            $date_query['after'] = $start_date;
        }

        if ($end_date) {
            $date_query['before'] = $end_date;
        }

        $date_query['inclusive'] = true;
        $args['date_query'][] = $date_query;
    }

    $commissions = new WP_Query($args);
    ?>
    <div class="wrap">
        <h1>Commission List</h1>

        <form method="get" style="margin-bottom: 20px;">
            <input type="hidden" name="page" value="sap_commissions" />
            <label for="aff_id"><strong>Affiliate:</strong></label>
            <select name="aff_id" id="aff_id">
                <option value="">All Affiliates</option>
                <?php
                $users = get_users(['role__in' => ['subscriber', 'affiliate']]);
                foreach ($users as $user) {
                    echo '<option value="' . esc_attr($user->ID) . '"' . selected($selected_aff, $user->ID, false) . '>' . esc_html($user->display_name) . '</option>';
                }
                ?>
            </select>

            <label for="start_date"><strong>From:</strong></label>
            <input type="date" name="start_date" id="start_date" value="<?php echo esc_attr($start_date); ?>" />

            <label for="end_date"><strong>To:</strong></label>
            <input type="date" name="end_date" id="end_date" value="<?php echo esc_attr($end_date); ?>" />

            <input type="submit" value="Filter" class="button button-primary" />
        </form>

        <table class="widefat striped">
            <thead>
                <tr>
                    <th>Affiliate Name</th>
                    <th>Commission Amount</th>
                    <th>Total Sale</th>
                    <th>Lead ID</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($commissions->have_posts()) : while ($commissions->have_posts()) : $commissions->the_post();
                    $aff_id = get_post_meta(get_the_ID(), 'aff_id', true);
                    $lead_id = get_post_meta(get_the_ID(), 'lead_id', true);
                    $commission = get_post_meta(get_the_ID(), 'commission', true);
                    $total_sale = get_post_meta(get_the_ID(), 'tot_sale', true);
                    $user = get_user_by('ID', $aff_id);
                    ?>
                    <tr>
                        <td><?php echo esc_html($user ? $user->display_name : 'Unknown'); ?></td>
                        <td><?php echo number_format($commission, 2); ?></td>
                        <td><?php echo number_format($total_sale, 2); ?></td>
                        <td><?php echo esc_html($lead_id); ?></td>
                        <td><?php echo get_the_date(); ?></td>
                    </tr>
                <?php endwhile; else : ?>
                    <tr><td colspan="5">No commissions found.</td></tr>
                <?php endif; wp_reset_postdata(); ?>
            </tbody>
        </table>

        <?php
        // Pagination
        $total_posts = $commissions->found_posts;
        $total_pages = ceil($total_posts / $per_page);

        if ($total_pages > 1) {
            echo '<div class="tablenav"><div class="tablenav-pages">';
            echo paginate_links([
                'base' => add_query_arg('paged', '%#%'),
                'format' => '',
                'current' => $paged,
                'total' => $total_pages,
                'prev_text' => __('« Prev'),
                'next_text' => __('Next »'),
            ]);
            echo '</div></div>';
        }
        ?>
    </div>
    <?php
}


function sap_affiliate_dashboard_page() {
    ?>
    <div class="wrap">
        <h1>Affiliate Dashboard</h1>

        <form method="get" action="" style="margin-bottom: 20px;">
            <input type="hidden" name="page" value="sap_affiliates">
            <label>From: <input type="date" name="from" value="<?php echo esc_attr($_GET['from'] ?? ''); ?>"></label>
            <label>To: <input type="date" name="to" value="<?php echo esc_attr($_GET['to'] ?? ''); ?>"></label>
            <input type="submit" class="button button-primary" value="Filter">
        </form>

        <style>
            .sap-dashboard-boxes {
                display: flex;
                gap: 20px;
                margin-top: 30px;
                flex-wrap: wrap;
            }

            .sap-box {
                flex: 1;
                min-width: 200px;
                padding: 20px;
                color: #fff;
                border-radius: 8px;
                box-shadow: 0 4px 10px rgba(0,0,0,0.1);
                text-align: center;
            }

            .sap-vendor-box { background-color: #0073aa; }
            .sap-clicks-box { background-color: #00a32a; }
            .sap-leads-box  { background-color: #ca4a1f; }
            .sap-tleads-box  { background-color: blue; }

            .sap-box h2 {
                font-size: 32px;
                margin: 0;
            }

            .sap-box p {
                font-size: 16px;
                margin: 10px 0 0;
            }
        </style>

        <?php
        $from = isset($_GET['from']) ? $_GET['from'] : '';
        $to = isset($_GET['to']) ? $_GET['to'] : '';
            $year = date('Y');
$month = date('m');
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

$from_date = "$year-$month-01";
$to_date = "$year-$month-$daysInMonth";

        if ($from && $to) {
            $from_date = $from . ' 00:00:00';
            $to_date = $to . ' 23:59:59';
        }

            global $wpdb;

            // 1. Count new vendors
            $vendor_count = $wpdb->get_var(
                $wpdb->prepare("
                    SELECT COUNT(*) FROM $wpdb->users u
                    JOIN {$wpdb->prefix}usermeta um ON u.ID = um.user_id
                    WHERE um.meta_key = 'wp_capabilities'
                    AND um.meta_value LIKE %s
                    AND u.user_registered BETWEEN %s AND %s
                ", '%affiliate%', $from_date, $to_date)
            );

            // 2. Clicks
            $clicks = get_clicks_by_date_range(0, $from_date, $to_date);

            // 3. Leads
            $tleads = get_total_leads_count_by_date_range(0, $from_date, $to_date);
            $leads = get_confirmed_leads_count_by_date_range(0, $from_date, $to_date);

            echo '<a href="'.site_url('wp-admin/admin.php?page=sap_vendors').'"><div class="sap-dashboard-boxes">';
            echo '<div class="sap-box sap-vendor-box"><h2>' . intval($vendor_count) . '</h2><p>New Vendors</p></div></a>';
            echo '<div class="sap-box sap-clicks-box"><h2>' . intval($clicks) . '</h2><p>Clicks</p></div>';
            echo '<div class="sap-box sap-tleads-box"><h2>' . intval($tleads) . '</h2><p>Total Leads</p></div>';
            echo '<div class="sap-box sap-leads-box"><h2>' . intval($leads) . '</h2><p>Confirmed Leads</p></div>';
            echo '</div>';
            echo '</div>';
            
            
            ?>
                    <h2 style="margin-top: 40px;">Affiliate Performance</h2>
        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <th>Vendor Name</th>
                    <th>Email</th>
                    <th>Clicks</th>
                    <th>Pending Leads</th>
                    <th>Confirmed Leads</th>
                    <th>Level 1 refferals</th>
                    <th>Level 2 refferals</th>
                    <th>Level 3 refferals</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $affiliate_users = get_users([
                    'meta_key' => 'wp_capabilities',
                    'meta_value' => 'affiliate',
                    'meta_compare' => 'LIKE',
                    'fields' => ['ID', 'display_name', 'user_email']
                ]);

                foreach ($affiliate_users as $user) {
                    $clicks = get_clicks_by_date_range($user->ID, $from_date, $to_date);
                    $confirmed = get_confirmed_leads_count_by_date_range($user->ID, $from_date, $to_date);
                    $pending = get_pending_leads_count_by_date_range($user->ID, $from_date, $to_date); // Make sure this function exists
                    $level1 = get_level_1_affiliates($user->ID, $from_date, $to_date);
        $level2 = get_level_2_affiliates($user->ID, $from_date, $to_date);
        $level3 = get_level_3_affiliates($user->ID, $from_date, $to_date);

                    echo '<tr>';
                    echo '<td>' . esc_html($user->display_name) . '</td>';
                    echo '<td>' . esc_html($user->user_email) . '</td>';
                    echo '<td>' . intval($clicks) . '</td>';
                    echo '<td>' . intval($pending) . '</td>';
                    echo '<td>' . intval($confirmed) . '</td>';
                    echo '<td>' . intval(count($level1)) . '</td>';
                    echo '<td>' . intval(count($level2)) . '</td>';
                    echo '<td>' . intval(count($level3)) . '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>

            <?php
            
        ?>
    </div>
    <?php
}



// Page callback for affiliates
function sap_affiliate_list_page() {
    echo '<div class="wrap"><h1>Affiliate List</h1>';
    echo '<p>This section can list all affiliates.</p></div>';
}

// Page callback for vendors
function sap_vendor_list_page() {
    $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
    $search_query = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    $from_date = isset($_GET['from_date']) ? sanitize_text_field($_GET['from_date']) : date('Y-m-01');
    $to_date = isset($_GET['to_date']) ? sanitize_text_field($_GET['to_date']) : date('Y-m-t');
    ?>
    <div class="wrap">
        <h1>Vendors</h1>

        <!-- Filter and Search Form -->
        <form method="get" action="">
            <input type="hidden" name="page" value="sap_vendors" />

            <select name="status">
                <option value="">All Status</option>
                <option value="active" <?php selected($status_filter, 'active'); ?>>Active</option>
                <option value="pending" <?php selected($status_filter, 'pending'); ?>>Pending</option>
            </select>

            <input type="search" name="s" value="<?php echo esc_attr($search_query); ?>" placeholder="Search by name or email" />
            <input type="date" name="from_date" value="<?php echo esc_attr($from_date); ?>" />
            <input type="date" name="to_date" value="<?php echo esc_attr($to_date); ?>" />
            <input type="submit" class="button" value="Filter">

            <?php
$download_url = admin_url('admin-post.php?action=sap_download_vendor_csv');
$download_url = add_query_arg([
    'from_date' => $from_date,
    'to_date'   => $to_date,
], $download_url);

echo '<a href="' . esc_url($download_url) . '" class="button button-primary" style="margin-bottom: 15px;">Download Sheet</a>';
?>

        </form>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Website</th>
                    <th>Promotion Method</th>
                    <th>Status</th>
                    <th>Registered Date</th>
                    <th>Clicks</th>
                    <th>Pending Leads</th>
                    <th>Confirmed Leads</th>
                    <th>Commission</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $vendors = get_users([
                    'role'    => 'affiliate',
                    'orderby' => 'user_registered',
                    'order'   => 'DESC',
                    'search'  => $search_query ? "*{$search_query}*" : '',
                    'search_columns' => ['user_email', 'user_login', 'display_name'],
                ]);

                $has_results = false;

                foreach ($vendors as $vendor) {
                    $promote = get_user_meta($vendor->ID, 'vendor_promo', true);
                    $status = get_user_meta($vendor->ID, 'sap_vendor_status', true) ?: 'pending';

                    if ($status_filter && $status !== $status_filter) continue;

                    $has_results = true;

                    $status_dot = ($status === 'active')
                        ? '<span style="color: green; font-size: 20px;">●</span>'
                        : '<span style="color: red; font-size: 20px;">●</span>';

                    // 📊 Replace these with your real logic/data sources
                    $clicks = get_clicks_by_date_range($vendor->ID, $from_date, $to_date);
                    $pending_leads = get_pending_leads_count_by_date_range($vendor->ID, $from_date, $to_date);
                    $confirmed_leads = get_confirmed_leads_count_by_date_range($vendor->ID, $from_date, $to_date);
                    $commission = get_affiliate_commission_total($vendor->ID, $from_date, $to_date);

                    echo '<tr>';
                    echo '<td>' . esc_html($vendor->display_name) . '</td>';
                    echo '<td>' . esc_html($vendor->user_email) . '</td>';
                    echo '<td><a href="' . esc_url($vendor->user_url) . '" target="_blank">' . esc_html($vendor->user_url) . '</a></td>';
                    echo '<td>' . esc_html($promote) . '</td>';
                    echo '<td>' . $status_dot . ' ' . ucfirst($status) . '</td>';
                    echo '<td>' . esc_html(date('Y-m-d', strtotime($vendor->user_registered))) . '</td>';
                    echo '<td>' . intval($clicks) . '</td>';
                    echo '<td>' . intval($pending_leads) . '</td>';
                    echo '<td>' . intval($confirmed_leads) . '</td>';
                    echo '<td>$' . number_format($commission, 2) . '</td>';
                    echo '<td><a href="' . admin_url('admin.php?page=sap_vendor_detail&id=' . $vendor->ID) . '" class="button button-small">View Details</a></td>';
                    echo '</tr>';
                }

                if (!$has_results) {
                    echo '<tr><td colspan="11">No vendors found.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
}


function send_account_approved_email($email, $name) {
    $login_url = site_url('affiliate-login'); // This will generate your site's login URL

    ob_start();
    ?>
    <html>
    <body style="font-family: Arial, sans-serif;">
        <h2>Hi <?php echo esc_html($name); ?>,</h2>
        <p>🎉 Your account has been approved by the admin. You can now log in and start using our services.</p>

        <p>
            👉 <a href="<?php echo esc_url($login_url); ?>" style="display: inline-block; padding: 10px 15px; background-color: #0073aa; color: #fff; text-decoration: none; border-radius: 5px;">
                Click here to log in
            </a>
        </p>

        <p>Thank you!</p>
        <p>— Team The Advantech</p>
    </body>
    </html>
    <?php
    $message = ob_get_clean();

    $subject = '✅ Your Account Has Been Approved';
$site_name = get_bloginfo('name'); // e.g., "The Advantech"
$headers = array(
    'Content-Type: text/html; charset=UTF-8',
    'From: ' . $site_name . ' <no-reply@' . $_SERVER['SERVER_NAME'] . '>'
);

wp_mail($email, $subject, $message, $headers);
}



add_action('admin_menu', function () {
    add_submenu_page(
        null, // null to hide from sidebar
        'Vendor Detail',
        'Vendor Detail',
        'manage_options',
        'sap_vendor_detail',
        'sap_vendor_detail_page'
    );
});
function sap_vendor_detail_page() {
    // Enable error reporting for this page only
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
    
    $user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $user = get_userdata($user_id);

    if (!$user || !in_array('affiliate', (array) $user->roles)) {
        echo '<div class="wrap"><h1>Vendor not found or invalid role</h1></div>';
        return;
    }

    // Handle comprehensive form submission
    if (isset($_POST['sap_update_all']) && check_admin_referer('sap_update_all_' . $user_id)) {
        $new_status = sanitize_text_field($_POST['sap_status']);
        $new_display_name = sanitize_text_field($_POST['display_name']);
        
        // Debug output
        // echo '<div style="background: #f0f0f0; padding: 10px; margin: 10px 0; border: 1px solid #ccc;">';
        // echo '<strong>DEBUG INFO:</strong><br>';
        // echo 'POST data: ' . print_r($_POST, true) . '<br>';
        // echo 'New display name: ' . $new_display_name . '<br>';
        // echo 'New status: ' . $new_status . '<br>';
        // echo '</div>';
        
        if($new_status == 'active' && !get_user_meta($user_id,'approval_email_sent',true)) {
            //send_account_approved_email($user->data->user_email, $user->data->display_name);
            // update_user_meta($user_id,'approval_email_sent','yes');
        }

        update_user_meta($user_id, 'sap_vendor_status', $new_status);
        
        // Update display name
        wp_update_user([
            'ID' => $user_id,
            'display_name' => $new_display_name
        ]);

        // Save Affiliate Marker checkbox
        if (isset($_POST['sap_aff_marker']) && $_POST['sap_aff_marker'] == '1') {
            update_user_meta($user_id, 'sap_aff_marker', '1');
        } else {
            delete_user_meta($user_id, 'sap_aff_marker');
        }

        // Handle profile picture upload
        if (!empty($_FILES['profile_picture']['name'])) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');

            $profile_id = media_handle_upload('profile_picture', 0);
            if (!is_wp_error($profile_id)) {
                update_user_meta($user_id, 'profile_picture', $profile_id);
            }
        }

        // Handle banner image upload
        if (!empty($_FILES['banner_image']['name'])) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');

            $banner_id = media_handle_upload('banner_image', 0);
            if (!is_wp_error($banner_id)) {
                update_user_meta($user_id, 'banner_image', $banner_id);
            }
        }

        echo '<div class="notice notice-success is-dismissible"><p>All settings updated successfully!</p></div>';
        
        // Refresh user data
        $user = get_userdata($user_id);
    }

    // Get current status, default to 'pending' if not set
    $current_status = get_user_meta($user_id, 'sap_vendor_status', true);
    $vendor_website = get_user_meta($user_id, 'vendor_website', true);
    $aff_marker = get_user_meta($user_id, 'sap_aff_marker', true);

    if (!$current_status) {
        $current_status = 'pending';
    }

    echo '<div class="wrap"><h1>Vendor Detail</h1>';
    
    // Show success message if profile picture was updated
    if (isset($_GET['profile_updated']) && $_GET['profile_updated'] == '1') {
        echo '<div class="notice notice-success is-dismissible"><p>Profile picture updated successfully!</p></div>';
    }
    
    // Show success message if profile picture was deleted
    if (isset($_GET['profile_deleted']) && $_GET['profile_deleted'] == '1') {
        echo '<div class="notice notice-success is-dismissible"><p>Profile picture deleted successfully!</p></div>';
    }
    
    // Show success message if banner image was updated
    if (isset($_GET['banner_updated']) && $_GET['banner_updated'] == '1') {
        echo '<div class="notice notice-success is-dismissible"><p>Banner image updated successfully!</p></div>';
    }
    
    // Show success message if banner image was deleted
    if (isset($_GET['banner_deleted']) && $_GET['banner_deleted'] == '1') {
        echo '<div class="notice notice-success is-dismissible"><p>Banner image deleted successfully!</p></div>';
    }
    
    // Profile Picture Section
    $profile_picture_id = get_user_meta($user_id, 'profile_picture', true);
    $profile_picture_url = $profile_picture_id ? wp_get_attachment_url($profile_picture_id) : '';
    
    echo '<div style="margin-bottom: 30px;">';
    echo '<h2>Profile Picture</h2>';
    if ($profile_picture_url) {
        echo '<div style="margin-bottom: 20px;">';
        echo '<img src="' . esc_url($profile_picture_url) . '" alt="Profile Picture" style="max-width: 200px; max-height: 200px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);" />';
        echo '<br><br>';
        echo '<form method="post" style="display: inline;">';
        echo '<input type="hidden" name="action" value="delete_profile_picture" />';
        echo '<input type="hidden" name="user_id" value="' . esc_attr($user_id) . '" />';
        wp_nonce_field('delete_profile_picture_' . $user_id);
        echo '<input type="submit" name="delete_profile_picture" class="button button-secondary" value="Delete Profile Picture" onclick="return confirm(\'Are you sure you want to delete this profile picture?\');" />';
        echo '</form>';
        echo '</div>';
    } else {
        echo '<p style="color: #666; font-style: italic;">No profile picture uploaded</p>';
    }
    

    
    // Banner Image Display Section
    $banner_image_id = get_user_meta($user_id, 'banner_image', true);
    $banner_image_url = $banner_image_id ? wp_get_attachment_url($banner_image_id) : '';
    
    if ($banner_image_url) {
        echo '<div style="margin-bottom: 20px;">';
        echo '<h3>Current Banner Image:</h3>';
        echo '<img src="' . esc_url($banner_image_url) . '" alt="Banner Image" style="max-width: 400px; max-height: 200px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);" />';
        echo '</div>';
    }
    
    // Affiliate Link Section
    echo '<div style="margin-bottom: 30px;">';
    echo '<h2>Affiliate Link</h2>';
    
    // Generate affiliate link
    $author_url = get_author_posts_url($user_id);
    $affiliate_link = add_query_arg('aff', $user_id, $author_url);
    
    echo '<div style="background: #f9f9f9; padding: 15px; border-radius: 8px; border: 1px solid #ddd;">';
    echo '<p><strong>Affiliate Link:</strong></p>';
    echo '<div style="display: flex; align-items: center; gap: 10px; margin-top: 10px;">';
    echo '<input type="text" id="affiliate_link_' . $user_id . '" value="' . esc_url($affiliate_link) . '" readonly style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px; background: #fff;" />';
    echo '<button type="button" onclick="copyAffiliateLink(' . $user_id . ')" class="button button-primary">Copy Link</button>';
    echo '</div>';
    echo '</div>';
    
    echo '<script>
    function copyAffiliateLink(userId) {
        var linkInput = document.getElementById("affiliate_link_" + userId);
        linkInput.select();
        linkInput.setSelectionRange(0, 99999);
        
        try {
            document.execCommand("copy");
            alert("Affiliate link copied to clipboard!");
        } catch (err) {
            console.error("Failed to copy: ", err);
            alert("Failed to copy link. Please copy manually.");
        }
    }
    </script>';
    echo '</div>';
    
    echo '<table class="form-table"><tbody>';
    echo '<tr><th>Full Name</th><td>' . esc_html($user->first_name . ' ' . $user->last_name) . '</td></tr>';
    echo '<tr><th>Username</th><td>' . esc_html($user->user_login) . '</td></tr>';
    echo '<tr><th>Email</th><td>' . esc_html($user->user_email) . '</td></tr>';
    echo '<tr><th>Website</th><td>' . esc_html($vendor_website) . '</td></tr>';
    echo '<tr><th>Registered At</th><td>' . esc_html($user->user_registered) . '</td></tr>';

    $promote = get_user_meta($user_id, 'vendor_promo', true);
    echo '<tr><th>Promote</th><td>' . nl2br(esc_html($promote)) . '</td></tr>';

    echo '</tbody></table>';
    

    ?>
    <form method="post" action="" enctype="multipart/form-data">
        <?php wp_nonce_field('sap_update_all_' . $user_id); ?>
        <h2>Update Vendor Settings</h2>
        <table class="form-table">
            <tbody>
                <tr>
                    <th><label for="display_name">Display Name</label></th>
                    <td>
                        <input type="text" name="display_name" id="display_name" value="<?php echo esc_attr($user->display_name); ?>" class="regular-text" />
                        <p class="description">Change vendor display name</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="sap_status">Vendor Status</label></th>
                    <td>
                        <select name="sap_status" id="sap_status">
                            <option value="pending" <?php selected($current_status, 'pending'); ?>>Pending</option>
                            <option value="active" <?php selected($current_status, 'active'); ?>>Active</option>
                        </select>
                        <p class="description">Change vendor status</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="sap_aff_marker">Affiliate Marker</label></th>
                    <td>
                        <label>
                            <input type="checkbox" name="sap_aff_marker" id="sap_aff_marker" value="1" <?php checked($aff_marker, '1'); ?> />
                            Mark this user as Affiliate Marker
                        </label>
                    </td>
                </tr>
                <tr>
                    <th><label for="profile_picture">Profile Picture</label></th>
                    <td>
                        <input type="file" name="profile_picture" accept="image/*" />
                        <p class="description">Upload new profile picture (optional)</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="banner_image">Banner Image</label></th>
                    <td>
                        <input type="file" name="banner_image" accept="image/*" />
                        <p class="description">Upload new banner image (optional)</p>
                    </td>
                </tr>
            </tbody>
        </table>
        <p>
            <input type="submit" name="sap_update_all" class="button button-primary" value="Update Everything">
            <a href="<?php echo esc_url(admin_url('admin.php?page=sap_vendors')); ?>" class="button">← Back to Vendors</a>
        </p>
    </form>
    <?php
    echo '</div>';
}

// Handle profile picture upload
add_action('admin_init', 'sap_handle_profile_picture_upload');
function sap_handle_profile_picture_upload() {
    if (!isset($_POST['upload_profile_picture']) || !isset($_POST['user_id'])) {
        return;
    }

    $user_id = intval($_POST['user_id']);
    
    // Check nonce
    if (!wp_verify_nonce($_POST['_wpnonce'], 'update_profile_picture_' . $user_id)) {
        wp_die('Security check failed');
    }

    // Check if user exists and has affiliate role
    $user = get_userdata($user_id);
    if (!$user || !in_array('affiliate', (array)$user->roles)) {
        wp_die('Invalid user');
    }

    // Check if file was uploaded
    if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
        wp_die('No file uploaded or upload error');
    }

    // Include WordPress file handling functions
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    // Handle the upload
    $attachment_id = media_handle_upload('profile_picture', 0);

    if (is_wp_error($attachment_id)) {
        wp_die('Error uploading file: ' . $attachment_id->get_error_message());
    } else {
        // Update user meta with the new profile picture ID
        update_user_meta($user_id, 'profile_picture', $attachment_id);
        
        // Redirect back to the vendor detail page with success message
        wp_redirect(add_query_arg('profile_updated', '1', admin_url('admin.php?page=sap_vendor_detail&id=' . $user_id)));
        exit;
    }
}

// Handle profile picture deletion
add_action('admin_init', 'sap_handle_profile_picture_deletion');
function sap_handle_profile_picture_deletion() {
    if (!isset($_POST['delete_profile_picture']) || !isset($_POST['user_id'])) {
        return;
    }

    $user_id = intval($_POST['user_id']);
    
    // Check nonce
    if (!wp_verify_nonce($_POST['_wpnonce'], 'delete_profile_picture_' . $user_id)) {
        wp_die('Security check failed');
    }

    // Check if user exists and has affiliate role
    $user = get_userdata($user_id);
    if (!$user || !in_array('affiliate', (array)$user->roles)) {
        wp_die('Invalid user');
    }

    // Get current profile picture ID
    $profile_picture_id = get_user_meta($user_id, 'profile_picture', true);
    
    if ($profile_picture_id) {
        // Delete the attachment
        wp_delete_attachment($profile_picture_id, true);
        
        // Remove the user meta
        delete_user_meta($user_id, 'profile_picture');
        
        // Redirect back to the vendor detail page with success message
        wp_redirect(add_query_arg('profile_deleted', '1', admin_url('admin.php?page=sap_vendor_detail&id=' . $user_id)));
        exit;
    } else {
        wp_die('No profile picture found to delete');
    }
}

// Handle banner image upload
add_action('admin_init', 'sap_handle_banner_image_upload');
function sap_handle_banner_image_upload() {
    if (!isset($_POST['upload_banner_image']) || !isset($_POST['user_id'])) {
        return;
    }

    $user_id = intval($_POST['user_id']);
    
    // Check nonce
    if (!wp_verify_nonce($_POST['_wpnonce'], 'update_banner_image_' . $user_id)) {
        wp_die('Security check failed');
    }

    // Check if user exists and has affiliate role
    $user = get_userdata($user_id);
    if (!$user || !in_array('affiliate', (array)$user->roles)) {
        wp_die('Invalid user');
    }

    // Check if file was uploaded
    if (!isset($_FILES['banner_image']) || $_FILES['banner_image']['error'] !== UPLOAD_ERR_OK) {
        wp_die('No file uploaded or upload error');
    }

    // Include WordPress file handling functions
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    // Handle the upload
    $attachment_id = media_handle_upload('banner_image', 0);

    if (is_wp_error($attachment_id)) {
        wp_die('Error uploading file: ' . $attachment_id->get_error_message());
    } else {
        // Update user meta with the new banner image ID
        update_user_meta($user_id, 'banner_image', $attachment_id);
        
        // Redirect back to the vendor detail page with success message
        wp_redirect(add_query_arg('banner_updated', '1', admin_url('admin.php?page=sap_vendor_detail&id=' . $user_id)));
        exit;
    }
}

// Handle banner image deletion
add_action('admin_init', 'sap_handle_banner_image_deletion');
function sap_handle_banner_image_deletion() {
    if (!isset($_POST['delete_banner_image']) || !isset($_POST['user_id'])) {
        return;
    }

    $user_id = intval($_POST['user_id']);
    
    // Check nonce
    if (!wp_verify_nonce($_POST['_wpnonce'], 'delete_banner_image_' . $user_id)) {
        wp_die('Security check failed');
    }

    // Check if user exists and has affiliate role
    $user = get_userdata($user_id);
    if (!$user || !in_array('affiliate', (array)$user->roles)) {
        wp_die('Invalid user');
    }

    // Get current banner image ID
    $banner_image_id = get_user_meta($user_id, 'banner_image', true);
    
    if ($banner_image_id) {
        // Delete the attachment
        wp_delete_attachment($banner_image_id, true);
        
        // Remove the user meta
        delete_user_meta($user_id, 'banner_image');
        
        // Redirect back to the vendor detail page with success message
        wp_redirect(add_query_arg('banner_deleted', '1', admin_url('admin.php?page=sap_vendor_detail&id=' . $user_id)));
        exit;
    } else {
        wp_die('No banner image found to delete');
    }
}



