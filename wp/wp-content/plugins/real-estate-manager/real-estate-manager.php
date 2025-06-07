<?php
/**
 * Plugin Name: Real Estate Manager
 * Description: Registers "Real Estate Object" post type, "District" taxonomy, and provides REST API for interaction.
 * Version: 1.0
 * Author: viurinets
 */

// === Register Post Type ===
function real_estate_register_post_type() {
    register_post_type('real_estate', [
        'labels' => [
            'name' => 'Real Estate Objects',
            'singular_name' => 'Real Estate Object',
            'add_new' => 'Add Object',
            'add_new_item' => 'Add New Object',
            'edit_item' => 'Edit Object',
            'new_item' => 'New Object',
            'view_item' => 'View Object',
            'search_items' => 'Search Objects',
            'not_found' => 'Not Found',
            'not_found_in_trash' => 'Not Found in Trash',
            'menu_name' => 'Real Estate'
        ],
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'real-estate'],
        'supports' => ['title', 'editor', 'thumbnail'],
        'show_in_rest' => true,
    ]);
}

// === Register Taxonomy ===
function real_estate_register_taxonomy() {
    register_taxonomy('district', 'real_estate', [
        'labels' => [
            'name' => 'Districts',
            'singular_name' => 'District',
            'search_items' => 'Search Districts',
            'all_items' => 'All Districts',
            'edit_item' => 'Edit District',
            'update_item' => 'Update District',
            'add_new_item' => 'Add New District',
            'new_item_name' => 'New District Name',
            'menu_name' => 'District',
        ],
        'hierarchical' => true,
        'public' => true,
        'show_in_rest' => true,
    ]);
    register_taxonomy_for_object_type('district', 'real_estate');
}

add_action('init', 'real_estate_register_post_type');
add_action('init', 'real_estate_register_taxonomy');


// === REST API Routes ===
add_action('rest_api_init', function () {
    register_rest_route('realty/v1', '/objects', [
        'methods' => 'GET',
        'callback' => 'get_real_estate_objects',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('realty/v1', '/objects', [
        'methods' => 'POST',
        'callback' => 'create_real_estate_object',
        'permission_callback' => function () {
            return current_user_can('edit_posts');
        }
    ]);

    register_rest_route('realty/v1', '/objects/(?P<id>\d+)', [
        'methods' => 'PUT',
        'callback' => 'update_real_estate_object',
        'permission_callback' => function () {
            return current_user_can('edit_posts');
        }
    ]);

    register_rest_route('realty/v1', '/objects/(?P<id>\d+)', [
        'methods' => 'DELETE',
        'callback' => 'delete_real_estate_object',
        'permission_callback' => function () {
            return current_user_can('delete_posts');
        }
    ]);
});

// === API: GET ===
function get_real_estate_objects($request) {
    $args = [
        'post_type' => 'real_estate',
        'posts_per_page' => -1,
        'post_status'    => ['publish'],
    ];

    if ($district = $request->get_param('district')) {
        $args['tax_query'] = [[
            'taxonomy' => 'district',
            'field' => 'slug',
            'terms' => $district,
        ]];
    }

    $query = new WP_Query($args);
    $posts = [];

    foreach ($query->posts as $post) {
        $posts[] = [
            'id' => $post->ID,
            'title' => $post->post_title,
            'content' => $post->post_content,
            'districts' => wp_get_post_terms($post->ID, 'district', ['fields' => 'names']),
            'acf' => function_exists('get_fields') ? get_fields($post->ID) : [],
        ];
    }

    return rest_ensure_response($posts);
}

// === API: POST ===
function create_real_estate_object($request) {
    $post_id = wp_insert_post([
        'post_type' => 'real_estate',
        'post_title' => $request['title'],
        'post_content' => $request['content'],
        'post_status' => 'publish',
    ]);

    if (is_wp_error($post_id)) {
        return new WP_Error('insert_error', 'Failed to create post', ['status' => 500]);
    }

    if (!empty($request['acf']) && function_exists('update_field')) {
        foreach ($request['acf'] as $key => $value) {
            update_field($key, $value, $post_id);
        }
    }

    if (!empty($request['districts'])) {
        wp_set_post_terms($post_id, $request['districts'], 'district');
    }

    return rest_ensure_response(['success' => true, 'post_id' => $post_id]);
}

// === API: PUT ===
function update_real_estate_object($request) {
    $post_id = (int) $request['id'];

    if (get_post_type($post_id) !== 'real_estate') {
        return new WP_Error('not_found', 'Post not found', ['status' => 404]);
    }

    $post_data = ['ID' => $post_id];

    // --- Update title
    if (!empty($request['title']) && is_string($request['title'])) {
        $post_data['post_title'] = sanitize_text_field($request['title']);
    }

    // --- Update content
    if (!empty($request['content']) && is_string($request['content'])) {
        $post_data['post_content'] = wp_kses_post($request['content']);
    }

    // --- Update post
    if (count($post_data) > 1) {
        wp_update_post($post_data);
    }

    // --- Update ACF fields
    if (!empty($request['acf']) && function_exists('update_field')) {
        foreach ($request['acf'] as $key => $value) {
            update_field($key, $value, $post_id);
        }
    }

    // --- Update taxonomy: district
    if (!empty($request['districts']) && is_array($request['districts'])) {
        $term_ids = [];

        foreach ($request['districts'] as $term_input) {
            $term_input = sanitize_text_field($term_input);

            // Try slug first
            $term = get_term_by('slug', $term_input, 'district');

            // Fallback to name
            if (!$term) {
                $term = get_term_by('name', $term_input, 'district');
            }

            if ($term && !is_wp_error($term)) {
                $term_ids[] = (int) $term->term_id;
            }
        }

        if (!empty($term_ids)) {
            wp_set_post_terms($post_id, $term_ids, 'district');
        }
    }

    return rest_ensure_response(['success' => true]);
}




// === API: DELETE ===
function delete_real_estate_object($request) {
    $post_id = (int) $request['id'];

    if (get_post_type($post_id) !== 'real_estate') {
        return new WP_Error('not_found', 'Post not found', ['status' => 404]);
    }

    wp_delete_post($post_id, true);

    return rest_ensure_response(['success' => true]);
}

// === Register Shortcode ===
add_shortcode('real_estate_filter', 'render_real_estate_filter');
function render_real_estate_filter() {
    ob_start();
    include plugin_dir_path(__FILE__) . 'templates/filter-template.php';
    return ob_get_clean();
}

// === Enqueue Scripts ===
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script('real-estate-filter', plugin_dir_url(__FILE__) . 'assets/js/filter.js', ['jquery'], null, true);
    wp_localize_script('real-estate-filter', 'realEstateAjax', [
        'ajax_url' => admin_url('admin-ajax.php'),
    ]);
});

// === AJAX Callback ===
add_action('wp_ajax_real_estate_ajax_filter', 'real_estate_ajax_filter');
add_action('wp_ajax_nopriv_real_estate_ajax_filter', 'real_estate_ajax_filter');

function real_estate_ajax_filter() {
    $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
    $district = sanitize_text_field($_POST['district'] ?? '');
    $rooms = intval($_POST['rooms'] ?? 0);
    $area_min = isset($_POST['area_min']) && $_POST['area_min'] !== '' ? intval($_POST['area_min']) : null;
    $area_max = isset($_POST['area_max']) && $_POST['area_max'] !== '' ? intval($_POST['area_max']) : null;
    $floors = isset($_POST['floors']) && $_POST['floors'] !== '' ? intval($_POST['floors']) : null;
    $building_type = sanitize_text_field($_POST['building_type'] ?? '');
    $eco_rating = isset($_POST['eco_rating']) && $_POST['eco_rating'] !== '' ? intval($_POST['eco_rating']) : null;

    $meta_query = ['relation' => 'AND'];

    if (!is_null($area_min) && !is_null($area_max)) {
        $meta_query[] = [
            'key' => 'rooms_0_area',
            'value' => [$area_min, $area_max],
            'type' => 'NUMERIC',
            'compare' => 'BETWEEN'
        ];
    } elseif (!is_null($area_min)) {
        $meta_query[] = [
            'key' => 'rooms_0_area',
            'value' => $area_min,
            'type' => 'NUMERIC',
            'compare' => '>='
        ];
    } elseif (!is_null($area_max)) {
        $meta_query[] = [
            'key' => 'rooms_0_area',
            'value' => $area_max,
            'type' => 'NUMERIC',
            'compare' => '<='
        ];
    }

    if ($rooms) {
        $meta_query[] = [
            'key' => 'rooms_0_room_count',
            'value' => $rooms,
            'type' => 'NUMERIC',
            'compare' => '='
        ];
    }

    if (!is_null($floors)) {
        $meta_query[] = [
            'key' => 'number_of_floors',
            'value' => $floors,
            'type' => 'NUMERIC',
            'compare' => '='
        ];
    }

    if ($building_type) {
        $meta_query[] = [
            'key' => 'building_type',
            'value' => $building_type,
            'compare' => '='
        ];
    }

    if (!is_null($eco_rating)) {
        $meta_query[] = [
            'key' => 'eco_rating',
            'value' => $eco_rating,
            'type' => 'NUMERIC',
            'compare' => '='
        ];
    }

    $args = [
        'post_type' => 'real_estate',
        'posts_per_page' => 5,
        'paged' => $paged,
        'meta_query' => $meta_query,
        'meta_key' => 'eco_rating',
        'orderby' => 'meta_value',
        'order' => 'DESC',
    ];
    
    

    if ($district) {
        $args['tax_query'] = [[
            'taxonomy' => 'district',
            'field' => 'slug',
            'terms' => $district,
        ]];
    }

    $query = new WP_Query($args);
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            ?>
            <div class="real-estate-item">
                <?php if (has_post_thumbnail()) the_post_thumbnail('medium'); ?>
                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                <p><?php echo wp_trim_words(get_the_content(), 20); ?></p>
            </div>
            <?php
        }

        $max_pages = $query->max_num_pages;
        if ($max_pages > 1) {
            echo '<div class="pagination">';
            for ($i = 1; $i <= $max_pages; $i++) {
                echo '<button class="page-btn" data-page="' . $i . '">' . $i . '</button>';
            }
            echo '</div>';
        }
    } else {
        echo '<p>Nothing found.</p>';
    }
    wp_die();
}


class RealEstateQueryModifier {
    public function __construct() {
        add_action('pre_get_posts', [$this, 'modify_query']);
        add_filter('posts_orderby', [$this, 'modify_orderby'], 10, 2);
    }

    public function modify_query($query) {
        if (is_admin() || !$query->is_main_query()) {
            return;
        }

        if (
            is_post_type_archive('real_estate') ||
            is_home() || 
            is_front_page()
        ) {
            $query->set('meta_key', 'eco_rating');
            $query->set('orderby', 'meta_value');
            $query->set('order', 'DESC');
        }
    }

    public function modify_orderby($orderby, $query) {
        if (is_admin() || !$query->is_main_query()) {
            return $orderby;
        }

        if (
            is_post_type_archive('real_estate') ||
            is_home() || 
            is_front_page()
        ) {
            global $wpdb;
            return "CAST({$wpdb->postmeta}.meta_value AS INTEGER) DESC";
        }

        return $orderby;
    }
}

new RealEstateQueryModifier();
