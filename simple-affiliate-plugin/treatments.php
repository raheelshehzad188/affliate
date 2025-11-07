<?php
// Custom Post Type: Treatments
function create_treatments_cpt() {

    $labels = array(
        'name'                  => _x( 'Treatments', 'Post Type General Name', 'textdomain' ),
        'singular_name'         => _x( 'Treatment', 'Post Type Singular Name', 'textdomain' ),
        'menu_name'             => __( 'Treatments', 'textdomain' ),
        'name_admin_bar'        => __( 'Treatment', 'textdomain' ),
        'archives'              => __( 'Treatment Archives', 'textdomain' ),
        'attributes'            => __( 'Treatment Attributes', 'textdomain' ),
        'parent_item_colon'     => __( 'Parent Treatment:', 'textdomain' ),
        'all_items'             => __( 'All Treatments', 'textdomain' ),
        'add_new_item'          => __( 'Add New Treatment', 'textdomain' ),
        'add_new'               => __( 'Add New', 'textdomain' ),
        'new_item'              => __( 'New Treatment', 'textdomain' ),
        'edit_item'             => __( 'Edit Treatment', 'textdomain' ),
        'update_item'           => __( 'Update Treatment', 'textdomain' ),
        'view_item'             => __( 'View Treatment', 'textdomain' ),
        'view_items'            => __( 'View Treatments', 'textdomain' ),
        'search_items'          => __( 'Search Treatment', 'textdomain' ),
        'not_found'             => __( 'Not found', 'textdomain' ),
        'not_found_in_trash'    => __( 'Not found in Trash', 'textdomain' ),
        'featured_image'        => __( 'Featured Image', 'textdomain' ),
        'set_featured_image'    => __( 'Set featured image', 'textdomain' ),
        'remove_featured_image' => __( 'Remove featured image', 'textdomain' ),
        'use_featured_image'    => __( 'Use as featured image', 'textdomain' ),
        'insert_into_item'      => __( 'Insert into treatment', 'textdomain' ),
        'uploaded_to_this_item' => __( 'Uploaded to this treatment', 'textdomain' ),
        'items_list'            => __( 'Treatments list', 'textdomain' ),
        'items_list_navigation' => __( 'Treatments list navigation', 'textdomain' ),
        'filter_items_list'     => __( 'Filter treatments list', 'textdomain' ),
    );

    $args = array(
        'label'                 => __( 'Treatment', 'textdomain' ),
        'description'           => __( 'Post type for treatments', 'textdomain' ),
        'labels'                => $labels,
        'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
        'taxonomies'            => array( 'category', 'post_tag' ),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-heart', // WP dashicon
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true, // Gutenberg editor enable
    );

    register_post_type( 'treatments', $args );

}
add_action( 'init', 'create_treatments_cpt', 0 );
