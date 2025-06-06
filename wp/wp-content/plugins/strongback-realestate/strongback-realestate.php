<?php
/**
 * Plugin Name:     StrongBack Real Estate
 * Plugin URI:      https://realtydemo.onrender.com/
 * Description:     Registers CPT "Real Estate" & taxonomy "District", adds REST API, filter-form shortcode, widget & AJAX search with pagination
 * Version:         1.0.3
 * Author:          michael
 * Author URI:      https://github.com/mmmihaeel
 * Text Domain:     strongback-realestate
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function sb_register_real_estate_cpt() {
    $labels = [
        'name'               => 'Real Estate',
        'singular_name'      => 'Real Estate Item',
        'menu_name'          => 'Real Estate',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Real Estate',
        'edit_item'          => 'Edit Real Estate',
        'new_item'           => 'New Real Estate',
        'view_item'          => 'View Real Estate',
        'search_items'       => 'Search Real Estate',
        'not_found'          => 'No Real Estate found',
        'not_found_in_trash' => 'No Real Estate in Trash',
    ];
    $args = [
        'labels'             => $labels,
        'public'             => true,
        'show_in_rest'       => true,        
        'has_archive'        => true,        
        'menu_icon'          => 'dashicons-building',
        'supports'           => ['title','editor','thumbnail'],
        'rewrite'            => ['slug'=>'real-estate'],
    ];
    register_post_type( 'real_estate', $args );
}
add_action( 'init', 'sb_register_real_estate_cpt' );


function sb_register_district_taxonomy() {
    $labels = [
        'name'              => 'Districts',
        'singular_name'     => 'District',
        'menu_name'         => 'Districts',
        'all_items'         => 'All Districts',
        'edit_item'         => 'Edit District',
        'view_item'         => 'View District',
        'update_item'       => 'Update District',
        'add_new_item'      => 'Add New District',
        'new_item_name'     => 'New District Name',
        'search_items'      => 'Search Districts',
    ];
    $args = [
        'labels'        => $labels,
        'hierarchical'  => true,
        'show_in_rest'  => true,
        'rewrite'       => ['slug'=>'district'],
    ];
    register_taxonomy( 'district', 'real_estate', $args );
}
add_action( 'init', 'sb_register_district_taxonomy' );


require_once plugin_dir_path( __FILE__ ) . 'includes/rest-api.php';


require_once plugin_dir_path( __FILE__ ) . 'includes/filter-form.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-realestate-query.php';


add_shortcode( 'sb_filter', function() {
    ob_start();
    sb_render_filter_form();
    return ob_get_clean();
});


class SB_Filter_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'sb_filter_widget',
            'StrongBack Filter',
            [ 'description' => 'Displays real-estate filter form' ]
        );
    }

    public function form( $instance ) {
        echo '<p>No settings available.</p>';
    }

    public function update( $new_instance, $old_instance ) {
        return $new_instance;
    }

    public function widget( $args, $instance ) {
        echo $args['before_widget'];
        sb_render_filter_form();
        echo $args['after_widget'];
    }
}
add_action( 'widgets_init', function() {
    register_widget( 'SB_Filter_Widget' );
});


add_action( 'wp_ajax_sb_search', 'sb_ajax_search' );
add_action( 'wp_ajax_nopriv_sb_search', 'sb_ajax_search' );
function sb_ajax_search() {
    $paged    = ! empty( $_REQUEST['paged'] ) ? max(1, intval( $_REQUEST['paged'] )) : 1;
    $per_page = 5;

    $meta_query = [];
    $tax_query  = [];
    if ( ! empty( $_REQUEST['house_name'] ) ) {
        $meta_query[] = [
            'key'     => 'house_name',
            'value'   => sanitize_text_field( $_REQUEST['house_name'] ),
            'compare' => 'LIKE',
        ];
    }
    if ( ! empty( $_REQUEST['floors_count'] ) ) {
        $meta_query[] = [
            'key'     => 'floors_count',
            'value'   => intval( $_REQUEST['floors_count'] ),
            'compare' => '=',
            'type'    => 'NUMERIC',
        ];
    }
    if ( ! empty( $_REQUEST['building_type'] ) ) {
        $meta_query[] = [
            'key'   => 'building_type',
            'value' => sanitize_text_field( $_REQUEST['building_type'] ),
        ];
    }
    if ( ! empty( $_REQUEST['ecological_rating'] ) ) {
        $meta_query[] = [
            'key'     => 'ecological_rating',
            'value'   => intval( $_REQUEST['ecological_rating'] ),
            'compare' => '=',
            'type'    => 'NUMERIC',
        ];
    }
    if ( ! empty( $_REQUEST['district'] ) ) {
        $tax_query[] = [
            'taxonomy' => 'district',
            'field'    => 'slug',
            'terms'    => sanitize_text_field( $_REQUEST['district'] ),
        ];
    }

    $count_args = [
        'post_type'      => 'real_estate',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'meta_query'     => $meta_query,
        'tax_query'      => $tax_query,
    ];
    $count_q     = new WP_Query( $count_args );
    $total_posts = count( $count_q->posts );
    $max_pages   = $per_page > 0 ? ceil( $total_posts / $per_page ) : 1;

    $args = [
        'post_type'      => 'real_estate',
        'posts_per_page' => $per_page,
        'paged'          => $paged,
        'no_found_rows'  => true,
        'meta_query'     => $meta_query,
        'tax_query'      => $tax_query,
        'meta_key'       => 'ecological_rating',
        'orderby'        => 'meta_value',
        'meta_type'      => 'NUMERIC',
        'order'          => 'DESC',
    ];
    $q = new WP_Query( $args );

    $items = [];
    while ( $q->have_posts() ) {
        $q->the_post();
        $items[] = [
            'title'   => get_the_title(),
            'link'    => get_permalink(),
            'excerpt' => get_the_excerpt(),
            'image'   => get_the_post_thumbnail_url( null, 'thumbnail' ),
        ];
    }
    wp_reset_postdata();

    wp_send_json_success( [
        'items'     => $items,
        'max_pages' => (int) $max_pages,
        'current'   => (int) $paged,
    ] );
}
