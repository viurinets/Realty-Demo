<?php
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
});


register_nav_menus([
    'main_menu' => 'Головне меню',
]);

add_filter('posts_orderby', function($orderby, $query) {
    if (
        is_admin() || !$query->is_main_query()
    ) {
        return $orderby;
    }

    $should_modify = false;

    if (is_post_type_archive('real_estate') || is_page('home')) {
        $should_modify = true;
    }

    if (defined('DOING_AJAX') && DOING_AJAX && isset($_POST['action']) && $_POST['action'] === 'real_estate_filter') {
        $should_modify = true;
    }

    if ($should_modify) {
        if ($query->get('meta_key') === 'eco_rating') {
            global $wpdb;
            return "CAST({$wpdb->postmeta}.meta_value AS INTEGER) DESC";
        }
    }

    return $orderby;
}, 20, 2);
