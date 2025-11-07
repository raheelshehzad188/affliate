<?php
function hubspot_lead($token,$data)
{
    $access_token = $token;

// Correct structure for HubSpot Contacts API
$rawDate = $data['booking_date'] ?? null;

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

if ($rawDate) {
    // Try with format YYYY-MM-DD
    $date = DateTime::createFromFormat('Y-m-d H:i:s', $rawDate . ' 00:00:00', new DateTimeZone('UTC'));

    if ($date instanceof DateTime) {
        $timestamp = $date->getTimestamp() * 1000;
        $data['booking_date'] = $timestamp;
    } else {
        // Log and remove booking_date if invalid
        error_log("❌ Invalid date format in booking_date: " . $rawDate);
        unset($data['booking_date']);
    }
} else {
    unset($data['booking_date']); // if empty/null
}


$leadData = [
    "properties" => $data
];

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'https://api.hubapi.com/crm/v3/objects/contacts');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $access_token
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($leadData));

// Run request
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Output raw response to debug
return json_decode($response,true);
    
}
add_action('wp_ajax_save_ach_confirmation', 'save_ach_confirmation_callback');
add_action('wp_ajax_nopriv_save_ach_confirmation', 'save_ach_confirmation_callback'); // if non-logged-in users allowed

function save_ach_confirmation_callback() {
    // Get and sanitize values
    
    $ach_id     = isset($_POST['ach_id']) ? intval($_POST['ach_id']) : 0;
    $sale       = isset($_POST['sale_amount']) ? floatval($_POST['sale_amount']) : 0;
    $feedback   = isset($_POST['feedback']) ? sanitize_textarea_field($_POST['feedback']) : '';

    if (!$ach_id || !$sale) {
        wp_send_json_error("Missing or invalid fields.");
    }
    $current_status = 'pending';

    // Save to database (you can replace this with custom logic or post_meta/save into custom table)
    $new_status = ($current_status === 'confirmed') ? 'pending' : 'confirmed';
    // dd($new_status);
    update_post_meta($ach_id, 'status', $new_status);
    update_post_meta($ach_id, 'sale', $sale);
    //transfer commission here
    process_commissions_by_lead($ach_id, $sale);
    update_post_meta($ach_id, 'feedback', $feedback);

    wp_send_json_success(['new_status' => $new_status]);
}

add_action('admin_menu', 'sap_register_leads_menu');

function sap_register_leads_menu() {
    add_submenu_page(
        'sap_affiliates',
        'Leads',
        'Leads',
        'manage_options',
        'sap_leads',
        'sap_leads_list_page'
    );
}

function sap_leads_list_page() {
    global $wpdb;

    $table = $wpdb->prefix . 'posts';
    $meta = $wpdb->prefix . 'postmeta';

    $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $per_page = 10;
    $offset = ($paged - 1) * $per_page;

    // Filters
    $start_date = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : '';
    $end_date = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : '';
    $affiliate_id = isset($_GET['affiliate_id']) ? sanitize_text_field($_GET['affiliate_id']) : '';
    $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';

    // Get all users with role 'affiliate' to filter affiliate id names
    $affiliates = get_users(['role' => 'affiliate']);
    $affiliate_map = [];
    foreach ($affiliates as $affiliate) {
        $affiliate_map[$affiliate->ID] = $affiliate->display_name;
    }

    $query = "SELECT p.ID, p.post_title, p.post_date,
        pm_name.meta_value AS name,
        pm_phone.meta_value AS phone,
        pm_location.meta_value AS location,
        pm_aff.meta_value AS affiliate_id,
        pm_status.meta_value AS status
        FROM $table p
        LEFT JOIN $meta pm_name ON p.ID = pm_name.post_id AND pm_name.meta_key = 'name'
        LEFT JOIN $meta pm_phone ON p.ID = pm_phone.post_id AND pm_phone.meta_key = 'phone'
        LEFT JOIN $meta pm_location ON p.ID = pm_location.post_id AND pm_location.meta_key = 'location'
        LEFT JOIN $meta pm_aff ON p.ID = pm_aff.post_id AND pm_aff.meta_key = 'affiliate_id'
        LEFT JOIN $meta pm_status ON p.ID = pm_status.post_id AND pm_status.meta_key = 'status'
        WHERE p.post_type = 'lead' AND p.post_status = 'publish'";

    if ($start_date && $end_date) {
        $query .= $wpdb->prepare(" AND p.post_date BETWEEN %s AND %s", $start_date . " 00:00:00", $end_date . " 23:59:59");
    }

    if ($affiliate_id) {
        $query .= $wpdb->prepare(" AND pm_aff.meta_value = %s", $affiliate_id);
    }

    if ($status_filter) {
        $query .= $wpdb->prepare(" AND pm_status.meta_value = %s", $status_filter);
    }

    $query .= " ORDER BY p.post_date DESC LIMIT $offset, $per_page";

    $leads = $wpdb->get_results($query);

    // Count total for pagination
    $count_query = "SELECT COUNT(*) FROM $table p
        LEFT JOIN $meta pm_aff ON p.ID = pm_aff.post_id AND pm_aff.meta_key = 'affiliate_id'
        LEFT JOIN $meta pm_status ON p.ID = pm_status.post_id AND pm_status.meta_key = 'status'
        WHERE p.post_type = 'lead' AND p.post_status = 'publish'";

    if ($start_date && $end_date) {
        $count_query .= $wpdb->prepare(" AND p.post_date BETWEEN %s AND %s", $start_date . " 00:00:00", $end_date . " 23:59:59");
    }
    if ($affiliate_id) {
        $count_query .= $wpdb->prepare(" AND pm_aff.meta_value = %s", $affiliate_id);
    }
    if ($status_filter) {
        $count_query .= $wpdb->prepare(" AND pm_status.meta_value = %s", $status_filter);
    }

    $total = $wpdb->get_var($count_query);
    $total_pages = ceil($total / $per_page);
    $nonce = wp_create_nonce("toggle_lead_status_nonce");
    ?>
    <div class="wrap">
        <style>
            /* Modal Overlay */
#leadDetailModal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    display: none;
    z-index: 9999;

    /* Center Content */
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Modal Box */
.lead-modal-content {
    background: #fff;
    padding: 30px;
    max-width: 600px;
    width: 90%;
    border-radius: 8px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.3);
    position: relative;
    animation: fadeIn 0.3s ease-in-out;
}

/* Close Button */
.close-lead-modal {
    margin-top: 20px;
    background-color: #0073aa;
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    cursor: pointer;
}

.close-lead-modal:hover {
    background-color: #005f8d;
}
/* Modal backdrop (blur effect + dark background) */
#modalBackdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.5); /* dark background */
  display: none;
  justify-content: center;
  align-items: center;
  z-index: 999;
}

/* Popup box */
#confirmPopup {
  background: white;
  padding: 20px 25px;
  border-radius: 10px;
  max-width: 400px;
  width: 90%;
  box-shadow: 0 4px 20px rgba(0,0,0,0.3);
  font-family: sans-serif;
}

/* Input spacing */
#confirmPopup input, 
#confirmPopup textarea {
  width: 100%;
  margin-bottom: 15px;
  padding: 8px 10px;
  border: 1px solid #ccc;
  border-radius: 5px;
  resize: none;
}

/* Buttons */
#confirmPopup button {
  padding: 8px 14px;
  margin-right: 10px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

#confirmPopup button[type="submit"] {
  background-color: #28a745;
  color: white;
}

#confirmPopup button[type="button"] {
  background-color: #dc3545;
  color: white;
}


/* Optional animation */
@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}

        </style>
        
    <!-- Modal Backdrop -->
<div id="modalBackdrop">
  <div id="confirmPopup">
    <form onsubmit="return submitConfirmation();">
      <input type="hidden" id="achId" />

      <label>Kitni Sale Hui Hai?</label>
      <input type="number" id="saleAmount" required />

      <label>Admin Feedback:</label>
      <textarea id="adminFeedback" rows="4" required></textarea>

      <button type="submit">Submit</button>
      <button type="button" onclick="closePopup()">Cancel</button>
    </form>
  </div>
</div>
        <h1>Leads</h1>
        <form method="get">
            <input type="hidden" name="page" value="sap_leads">
            <label>From: <input type="date" name="start_date" value="<?php echo esc_attr($start_date); ?>"></label>
            <label>To: <input type="date" name="end_date" value="<?php echo esc_attr($end_date); ?>"></label>
            
            <label>Affiliate:
                <select name="affiliate_id">
                    <option value="">-- All Affiliates --</option>
                    <?php foreach ($affiliate_map as $id => $name): ?>
                        <option value="<?php echo esc_attr($id); ?>" <?php selected($affiliate_id, $id); ?>><?php echo esc_html($name); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>Status:
                <select name="status">
                    <option value="">-- All Status --</option>
                    <option value="confirmed" <?php selected($status_filter, 'confirmed'); ?>>Confirmed</option>
                    <option value="pending" <?php selected($status_filter, 'pending'); ?>>Pending</option>
                </select>
            </label>

            <input type="submit" class="button" value="Filter">
        </form>

        <table class="wp-list-table widefat striped">
            <thead>
                <tr>
                    <th>Name</th><th>Phone</th><th>Location</th><th>Affiliate</th><th>Status</th><th>Genrate Date</th><th>Action</th><th>Detail</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($leads): foreach ($leads as $lead): ?>
                    <tr id="lead-<?php echo esc_attr($lead->ID); ?>">
                        <td><?php echo esc_html($lead->name); ?></td>
                        <td><?php echo esc_html($lead->phone); ?></td>
                        <td><?php echo esc_html($lead->location); ?></td>
                        <td><?php 
                            // Show affiliate user name
                            echo isset($affiliate_map[$lead->affiliate_id]) ? esc_html($affiliate_map[$lead->affiliate_id]) : esc_html($lead->affiliate_id); 
                        ?></td>
                        <td class="status"><?php echo esc_html($lead->status); ?></td>
                        <td><?php echo esc_html(date('Y-m-d H:i:s', strtotime($lead->post_date))); ?></td>
                        <td>
                            <button class="button toggle-status-btn" data-id="<?php echo esc_attr($lead->ID); ?>" data-status="<?php echo esc_attr($lead->status); ?>">
                                <?php echo ($lead->status === 'confirmed') ? 'Unconfirm' : 'Confirm'; ?>
                            </button>
                        </td>
                        <td>
                            <a href="javascript:void(0);" class="button view-lead-detail" data-id="<?php echo esc_attr( $lead->ID ); ?>">View Detail</a>


                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="7">No leads found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div id="leadDetailModal" style="display:none;">
  <div class="lead-modal-content" style="background:#fff; padding:20px; border-radius:10px;">
    <h2>Lead Detail</h2>
    <div id="lead-detail-body">Loading...</div>
    <button class="button close-lead-modal">Close</button>
  </div>
</div>


        <?php if ($total_pages > 1): ?>
            <div class="tablenav-pages">
                <span class="pagination-links">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a class="button<?php if ($paged == $i) echo ' button-primary'; ?>" href="<?php echo esc_url(add_query_arg(['paged' => $i])); ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                </span>
            </div>
        <?php endif; ?>
    </div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<script type="text/javascript">
    var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
</script>

    <script type="text/javascript">
    function showPopup(achId) {
  document.getElementById('achId').value = achId;
  document.getElementById('modalBackdrop').style.display = 'flex';
}

function closePopup() {
  document.getElementById('modalBackdrop').style.display = 'none';
}
function submitConfirmation() {
    const achId = document.getElementById('achId').value;
    const sale = document.getElementById('saleAmount').value;
    const feedback = document.getElementById('adminFeedback').value;

    const data = {
        action: 'save_ach_confirmation',  // WordPress action hook
        ach_id: achId,
        sale_amount: sale,
        feedback: feedback
    };

    fetch(ajaxurl, {  // ajaxurl is provided by WP (see step 2)
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(data).toString()
    })
    .then(res => res.json())
    .then(data => {
        closePopup();
        if (data.success) {
                        const mid = '#lead-' + achId;
                        var ntxt = innerText = (data.data.new_status === 'confirmed') ? 'Unconfirm' : 'Confirmed';
                        $(mid+' .status').text(innerText);
                        $(mid+' .toggle-status-btn').text(innerText);
                    } else {
                        alert('Error: ' + data.message);
                    }
    });

    return false; // prevent default
}


    jQuery(document).ready(function($) {
        
    $('.view-lead-detail').on('click', function () {
        var lead_id = $(this).data('id');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_lead_detail',
                lead_id: lead_id
            },
            success: function (response) {
                $('#lead-detail-body').html(response);
                $('#leadDetailModal').show();
            }
        });
    });

    $('.close-lead-modal').on('click', function () {
        $('#leadDetailModal').hide();
    });
});

    document.addEventListener('DOMContentLoaded', function () {
        const buttons = document.querySelectorAll('.toggle-status-btn');
        buttons.forEach(btn => {
            btn.addEventListener('click', function () {
                const leadId = this.dataset.id;
                const currentStatus = this.dataset.status;
                if(currentStatus == 'pending')
                {
                showPopup(leadId);
                return 0;
                }
                const button = this;

                fetch(ajaxurl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'toggle_lead_status',
                        lead_id: leadId,
                        current_status: currentStatus,
                        _wpnonce: '<?php echo $nonce; ?>'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const row = document.getElementById('lead-' + leadId);
                        row.querySelector('.status').innerText = data.new_status;
                        button.innerText = (data.data.new_status === 'confirmed') ? 'Unconfirm' : 'Confirm';

                        button.dataset.status = data.data.new_status;
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            });
        });
    });
    </script>
    <?php
}

add_action('wp_ajax_toggle_lead_status', 'toggle_lead_status_callback');
function toggle_lead_status_callback() {
    check_ajax_referer('toggle_lead_status_nonce');

    $lead_id = intval($_POST['lead_id']);
    $current_status = sanitize_text_field($_POST['current_status']);

    if (!current_user_can('edit_post', $lead_id)) {
        wp_send_json_error(['message' => 'Permission denied']);
    }

    $new_status = ($current_status === 'confirmed') ? 'pending' : 'confirmed';
    update_post_meta($lead_id, 'status', $new_status);

    wp_send_json_success(['new_status' => $new_status]);
}





// Register custom post type
add_action('init', 'alc_register_lead_post_type');
function alc_register_lead_post_type() {
    register_post_type('lead', [
        'labels' => [
            'name' => 'Leads',
            'singular_name' => 'Lead',
            'menu_name' => 'Leads'
        ],
        'public' => false,
        'show_ui' => true,
        'supports' => ['title'],
        'menu_icon' => 'dashicons-id',
    ]);
}

// Shortcode to display form

add_shortcode('lead_form', 'alc_render_lead_form');
function alc_render_lead_form() {
    ob_start();
    ?>
    
    <form id="leadForm" class="alc-lead-form">
        <div class="alc-form-row">
            <div class="alc-field"><input type="text" name="name" placeholder="Your Name" required></div>
            <div class="alc-field"><input type="text" name="phone" placeholder="Phone Number" required></div>
        </div>
        <div class="alc-form-row">
            <div class="alc-field"><input type="text" name="email" placeholder="Email" required></div>
            <div class="alc-field"><input type="date" name="prefer_date" placeholder="Prefer date" required></div>
        </div>
        <div class="alc-form-row">
            <div class="alc-field">
                <select name="location" required>
                    <option value="">Select City</option>
                    <option value="Lahore">Lahore</option>
                    <option value="Islamabad">Islamabad</option>
                </select>
            </div>
            <div class="alc-field">
                <textarea name="detail" placeholder="Note" rows="1"></textarea>
            </div>
        </div>

        <input type="hidden" name="affiliate_id" id="affiliate_id">
        <div id="thankYouMsg" style="display:none; color:green;">Thank you! Your info has been submitted.</div>
        <div class="alc-submit-row">
            <button type="submit">Submit</button>
        </div>
    </form>
    <script>
    
    (function(){
        let affId = new URLSearchParams(window.location.search).get('aff');
        if (affId) document.cookie = "affiliate_id=" + affId + "; path=/; max-age=" + 60*60*24*30;
    })();
    function getCookie(name){
        let m = document.cookie.match('(^| )' + name + '=([^;]+)');
        return m ? m[2] : '';
    }
    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('affiliate_id').value = getCookie('affiliate_id');
        document.getElementById('leadForm').addEventListener('submit', function(e){
            e.preventDefault();
            const fd = new FormData(this);
            fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=alc_submit_lead', {
                method: 'POST', body: fd
            }).then(r => r.text()).then(res => {
                if(res === 'success'){
                    this.reset(); document.getElementById('thankYouMsg').style.display = 'block';
                } else { alert('Error submitting lead'); }
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
function insert_custom_post_with_wpdb( $title, $post_type = 'lead', $post_status = 'publish' ) {
    global $wpdb;

    // Validate and sanitize
    if ( empty( $title ) ) {
        return false;
    }
    
        $post_date = current_time('mysql'); // Local time
        $post_date_gmt = current_time('mysql', 1); // GMT time

    $post_title = sanitize_text_field( $title );
    $post_name  = sanitize_title( $post_title );
    $current_time = current_time( 'mysql' );
    $current_time_gmt = current_time( 'mysql', 1 );

    // Prepare post data
    $data = [
        'post_author'           => get_current_user_id(),
        'post_date'             => $post_date,
        'post_date_gmt'         => $post_date_gmt,
        'post_content'          => '',
        'post_title'            => $post_title,
        'post_excerpt'          => '',
        'post_status'           => $post_status,
        'comment_status'        => 'closed',
        'ping_status'           => 'closed',
        'post_password'         => '',
        'post_name'             => $post_name,
        'to_ping'               => '',
        'pinged'                => '',
        'post_modified'         => $current_time,
        'post_modified_gmt'     => $current_time_gmt,
        'post_content_filtered' => '',
        'post_parent'           => 0,
        'guid'                  => '',
        'menu_order'            => 0,
        'post_type'             => $post_type,
        'post_mime_type'        => '',
        'comment_count'         => 0
    ];

    // Insert into posts table
    $inserted = $wpdb->insert( $wpdb->posts, $data );

    if ( $inserted ) {
        $post_id = $wpdb->insert_id;

        // Update GUID
        $guid = get_site_url() . '/?post_type=' . $post_type . '&p=' . $post_id;
        $wpdb->update( $wpdb->posts, [ 'guid' => $guid ], [ 'ID' => $post_id ] );

        // Trigger hooks
        // do_action( 'save_post', $post_id, null, false );
        // do_action( 'save_post_' . $post_type, $post_id, null, false );
        // do_action( 'wp_insert_post', $post_id, null, false );

        return $post_id;
    }

    return false;
}

// Handle lead submission
add_action('wp_ajax_alc_submit_lead', 'alc_handle_lead_submission');
add_action('wp_ajax_nopriv_alc_submit_lead', 'alc_handle_lead_submission');
function alc_handle_lead_submission() {
    
    $name = sanitize_text_field($_POST['name'] ?? '');
    $phone = sanitize_text_field($_POST['phone'] ?? '');
    $email = sanitize_text_field($_POST['email'] ?? '');
    $location = sanitize_text_field($_POST['location'] ?? '');
    $affiliate_id = sanitize_text_field($_POST['affiliate_id'] ?? '');
    $detail = sanitize_text_field($_POST['detail'] ?? '');
    $prefer_date = sanitize_text_field($_POST['prefer_date'] ?? '');
    $post_id = insert_custom_post_with_wpdb( $name );
    if ($post_id && !is_wp_error($post_id)) {
        update_post_meta($post_id, 'name', $name);
        update_post_meta($post_id, 'email', $email);
        update_post_meta($post_id, 'phone', $phone);
        update_post_meta($post_id, 'location', $location);
        update_post_meta($post_id, 'affiliate_id', $affiliate_id);
        $hubspot_token = get_user_meta($affiliate_id,'sap_hubspot_token',true);
        // Admin Email (jahan bhejna hai)
$admin_email = get_option('admin_email'); // WP admin email
$admin_email = 'londonaestheticsappointments@gmail.com'; // WP admin email
// $admin_email = 'raheelshehzad188@gmail.com'; // WP admin email
$subject = "New Lead Notification";

// HTML Email Table
$message = '
<html>
<head>
<title>New Lead Notification</title>
<style>
    table {
        border-collapse: collapse;
        width: 100%;
        font-family: Arial, sans-serif;
    }
    th, td {
        border: 1px solid #ddd;
        padding: 8px;
    }
    th {
        background-color: #f4f4f4;
        text-align: left;
    }
</style>
</head>
<body>
<h2>New Lead Details</h2>
<table>
    <tr>
        <th>Email</th>
        <td>' . esc_html($email) . '</td>
    </tr>
    <tr>
        <th>Name</th>
        <td>' . esc_html($name) . '</td>
    </tr>
    <tr>
        <th>Phone</th>
        <td>' . esc_html($phone) . '</td>
    </tr>
    <tr>
        <th>Preferred Booking Date</th>
        <td>' . esc_html($prefer_date) . '</td>
    </tr>
</table>
</body>
</html>
';

// Email Headers (HTML support)
$headers = array('Content-Type: text/html; charset=UTF-8');

// Send Email
$t = wp_mail('raheelshehzad188@gmail.com', $subject, $message, $headers);
$t = wp_mail($admin_email, $subject, $message, $headers);
        if($hubspot_token)
        {
            
            $data = array(
                "email" => $email,
                "firstname" => $name,
                "phone" => $phone,
                "booking_date" => $prefer_date,
                );
                

            $r = hubspot_lead($hubspot_token,$data);
        }
        update_post_meta($post_id, 'detail', $detail);
        update_post_meta($post_id, 'prefer_date', $prefer_date);
        update_post_meta($post_id, 'status', 'pending');
        echo 'success';
    } else {
        echo 'fail';
    }
    wp_die();
}

// Add custom columns
add_filter('manage_lead_posts_columns', 'alc_lead_custom_columns');
function alc_lead_custom_columns($columns) {
    $columns['phone'] = 'Phone';
    $columns['location'] = 'Location';
    $columns['affiliate_id'] = 'Affiliate ID';
    $columns['status'] = 'Status';
    $columns['action'] = 'Action';
    return $columns;
}

add_action('manage_lead_posts_custom_column', 'alc_lead_custom_column_data', 10, 2);
function alc_lead_custom_column_data($column, $post_id) {
    switch ($column) {
        case 'phone':
            echo esc_html(get_post_meta($post_id, 'phone', true));
            break;
        case 'location':
            echo esc_html(get_post_meta($post_id, 'location', true));
            break;
        case 'affiliate_id':
            echo esc_html(get_post_meta($post_id, 'affiliate_id', true));
            break;
        case 'status':
            echo esc_html(get_post_meta($post_id, 'status', true));
            break;
        case 'action':
            $status = get_post_meta($post_id, 'status', true);
            if ($status !== 'confirmed') {
                echo '<button class="button confirm-lead" data-id="' . esc_attr($post_id) . '">Confirm</button>';
            } else {
                echo 'Confirmed';
            }
            break;
    }
}

// Enqueue admin JS
add_action('admin_enqueue_scripts', 'alc_admin_script');
function alc_admin_script($hook) {
    if ($hook !== 'edit.php' || get_post_type() !== 'lead') return;
    wp_enqueue_script('alc-admin-js', plugin_dir_url(__FILE__) . 'admin.js', ['jquery'], false, true);
    wp_localize_script('alc-admin-js', 'alc_ajax', ['url' => admin_url('admin-ajax.php')]);
}

// Handle AJAX confirm
add_action('wp_ajax_alc_confirm_lead', 'alc_confirm_lead');
function alc_confirm_lead() {
    $post_id = intval($_POST['post_id']);
    if (get_post_type($post_id) === 'lead') {
        update_post_meta($post_id, 'status', 'confirmed');
        echo 'confirmed';
    } else {
        echo 'fail';
    }
    wp_die();
}
add_action('wp_ajax_get_lead_detail', 'get_lead_detail_callback');

function get_lead_detail_callback() {
    $lead_id = intval($_POST['lead_id']);
    
    // Example: Assuming you stored leads as custom post type or in custom table
    $lead = get_post($lead_id); // if using CPT
    if (!$lead) {
        echo 'Lead not found.';
        wp_die();
    }
    $status = get_post_meta($lead_id, 'status', true);
    $sale = get_post_meta($lead_id, 'sale', true);
    //transfer commission here
    $feedback = get_post_meta($lead_id, 'feedback', true);

    echo '<p><strong>Name:</strong> ' . esc_html($lead->post_title) . '</p>';
    echo '<p><strong>Email:</strong> ' . esc_html(get_post_meta($lead_id, 'email', true)) . '</p>';
    echo '<p><strong>Phone:</strong> ' . esc_html(get_post_meta($lead_id, 'phone', true)) . '</p>';
    echo '<p><strong>Prefer date:</strong> ' . esc_html(get_post_meta($lead_id, 'prefer_date', true)) . '</p>';
    echo '<p><strong>Message:</strong> ' . esc_html(get_post_meta($lead_id, 'detail', true)) . '</p>';
    if($status == 'confirmed' && $sale)
    {
    echo '<p><strong>Total Sale:</strong> ' . esc_html($sale) . '</p>';
    echo '<p><strong>Sale feedback:</strong> ' . esc_html($feedback) . '</p>';
    }

    wp_die();
}

