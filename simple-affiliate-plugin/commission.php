<?php

function register_commission_post_type() {
    $labels = array(
        'name' => 'Commissions',
        'singular_name' => 'Commission',
        'add_new' => 'Add New Commission',
        'add_new_item' => 'Add New Commission',
        'edit_item' => 'Edit Commission',
        'new_item' => 'New Commission',
        'view_item' => 'View Commission',
        'search_items' => 'Search Commissions',
        'not_found' => 'No Commissions Found',
        'menu_name' => 'Commissions'
    );

    $args = array(
        'labels' => $labels,
        'public' => false,
        'show_ui' => true,
        'menu_icon' => 'dashicons-money-alt',
        'supports' => array('title'),
        'has_archive' => false,
    );

    register_post_type('commission', $args);
}
add_action('init', 'register_commission_post_type');

// now metabox
function add_commission_meta_boxes() {
    add_meta_box(
        'commission_meta_box',
        'Commission Details',
        'render_commission_meta_box',
        'commission',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_commission_meta_boxes');

function render_commission_meta_box($post) {
    wp_nonce_field('save_commission_meta', 'commission_meta_nonce');

    $lead_id   = get_post_meta($post->ID, 'lead_id', true);
    $aff_id    = get_post_meta($post->ID, 'aff_id', true);
    $tot_sale  = get_post_meta($post->ID, 'tot_sale', true);
    $commission = get_post_meta($post->ID, 'commission', true);
    ?>

    <p><label>Lead ID:</label>
    <input type="text" name="lead_id" value="<?php echo esc_attr($lead_id); ?>" style="width:100%;"></p>

    <p><label>Affiliate ID:</label>
    <input type="text" name="aff_id" value="<?php echo esc_attr($aff_id); ?>" style="width:100%;"></p>

    <p><label>Total Sale:</label>
    <input type="number" step="0.01" name="tot_sale" value="<?php echo esc_attr($tot_sale); ?>" style="width:100%;"></p>

    <p><label>Commission Amount:</label>
    <input type="number" step="0.01" name="commission" value="<?php echo esc_attr($commission); ?>" style="width:100%;"></p>

    <?php
}
function save_commission_meta_fields($post_id) {
    if (!isset($_POST['commission_meta_nonce']) || !wp_verify_nonce($_POST['commission_meta_nonce'], 'save_commission_meta')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    if (isset($_POST['lead_id'])) {
        update_post_meta($post_id, 'lead_id', sanitize_text_field($_POST['lead_id']));
    }
    if (isset($_POST['aff_id'])) {
        update_post_meta($post_id, 'aff_id', sanitize_text_field($_POST['aff_id']));
    }
    if (isset($_POST['tot_sale'])) {
        update_post_meta($post_id, 'tot_sale', floatval($_POST['tot_sale']));
    }
    if (isset($_POST['commission'])) {
        update_post_meta($post_id, 'commission', floatval($_POST['commission']));
    }
}
add_action('save_post_commission', 'save_commission_meta_fields');
function process_commissions_by_lead($lead_id, $total_sale) {
    if (!$lead_id || !$total_sale) return;

    // STEP 1: Get affiliate ID from lead
    $aff_id = get_post_meta($lead_id,'affiliate_id',true); // You must define this function affiliate_id

    if (!$aff_id) return;

    $commission_structure = [
        ['percent' => 10, 'aff_id' => $aff_id],
        ['percent' => 5,  'aff_id' => get_parent_affiliate($aff_id)],
        ['percent' => 2,  'aff_id' => get_parent_affiliate(get_parent_affiliate($aff_id))],
        ['percent' => 1,  'aff_id' => get_parent_affiliate(get_parent_affiliate(get_parent_affiliate($aff_id)))],
    ];

    foreach ($commission_structure as $level) {
        $aff = $level['aff_id'];
        $percent = $level['percent'];

        if (!$aff) continue;

        $amount = round(($total_sale * $percent) / 100, 2);

        // Create commission post
        wp_insert_post([
            'post_type'   => 'commission',
            'post_title'  => "Commission for Aff ID: $aff",
            'post_status' => 'publish',
            'meta_input'  => [
                'lead_id'    => $lead_id,
                'aff_id'     => $aff,
                'tot_sale'   => $total_sale,
                'commission' => $amount,
            ]
        ]);
    }
}
function get_parent_affiliate($aff_id)
{
    return get_user_meta($aff_id,'referred_by_affiliate',true);
}

?>