<?php
/**
 * Plugin Name: Real Estate Manager
 * Description: Реєструє тип запису "Об'єкт нерухомості", таксономію "Район" і реалізує REST API для взаємодії.
 * Version: 1.0
 * Author: viurinets
 */

// === Register Post Type ===
function real_estate_register_post_type() {
    register_post_type('real_estate', [
        'labels' => [
            'name' => 'Обʼєкти нерухомості',
            'singular_name' => 'Обʼєкт нерухомості',
            'add_new' => 'Додати обʼєкт',
            'add_new_item' => 'Додати новий обʼєкт',
            'edit_item' => 'Редагувати обʼєкт',
            'new_item' => 'Новий обʼєкт',
            'view_item' => 'Переглянути обʼєкт',
            'search_items' => 'Шукати обʼєкти',
            'not_found' => 'Не знайдено',
            'not_found_in_trash' => 'Не знайдено в кошику',
            'menu_name' => 'Нерухомість'
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
            'name' => 'Райони',
            'singular_name' => 'Район',
            'search_items' => 'Пошук районів',
            'all_items' => 'Всі райони',
            'edit_item' => 'Редагувати район',
            'update_item' => 'Оновити район',
            'add_new_item' => 'Додати новий район',
            'new_item_name' => 'Нова назва району',
            'menu_name' => 'Район',
        ],
        'hierarchical' => true,
        'public' => true,
        'show_in_rest' => true,
    ]);
}

add_action('init', 'real_estate_register_post_type');
add_action('init', 'real_estate_register_taxonomy');

// === Optional Dummy Data ===
add_action('init', function () {
    if (get_option('real_estate_dummy_data_insertedd')) return;

    for ($i = 1; $i <= 10; $i++) {
        $post_id = wp_insert_post([
            'post_title' => "Будинок $i",
            'post_type' => 'real_estate',
            'post_status' => 'publish',
        ]);

        update_field('назва_будинку', "Будинок $i", $post_id);
        update_field('координати_місцезнаходження', "50.4" . rand(100,999) . ", 30.5" . rand(100,999), $post_id);
        update_field('кількість_поверхів', rand(1, 20), $post_id);
        update_field('тип_будівлі', ['панель', 'цегла', 'піноблок'][rand(0,2)], $post_id);
        update_field('екологічність', rand(1, 5), $post_id);

        if (function_exists('have_rows')) {
            $rooms = rand(1, 3);
            for ($j = 0; $j < $rooms; $j++) {
                add_row('приміщення', [
                    'площа' => rand(20, 100),
                    'кіл_кімнат' => rand(1, 5),
                    'балкон' => rand(0,1) ? 'так' : 'ні',
                    'санвузол' => rand(0,1) ? 'так' : 'ні',
                ], $post_id);
            }
        }

        $districts = ['Центр', 'Поділ', 'Соломʼянка', 'Троєщина'];
        wp_set_object_terms($post_id, $districts[array_rand($districts)], 'district');
    }

    update_option('real_estate_dummy_data_insertedd', true);
});

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

    wp_update_post([
        'ID' => $post_id,
        'post_title' => $request['title'],
        'post_content' => $request['content'],
    ]);

    if (!empty($request['acf']) && function_exists('update_field')) {
        foreach ($request['acf'] as $key => $value) {
            update_field($key, $value, $post_id);
        }
    }

    if (!empty($request['districts'])) {
        wp_set_post_terms($post_id, $request['districts'], 'district');
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
            'key' => 'приміщення_0_площа',
            'value' => [$area_min, $area_max],
            'type' => 'NUMERIC',
            'compare' => 'BETWEEN'
        ];
    } elseif (!is_null($area_min)) {
        $meta_query[] = [
            'key' => 'приміщення_0_площа',
            'value' => $area_min,
            'type' => 'NUMERIC',
            'compare' => '>='
        ];
    } elseif (!is_null($area_max)) {
        $meta_query[] = [
            'key' => 'приміщення_0_площа',
            'value' => $area_max,
            'type' => 'NUMERIC',
            'compare' => '<='
        ];
    }

    if ($rooms) {
        $meta_query[] = [
            'key' => 'приміщення_0_кіл_кімнат',
            'value' => $rooms,
            'type' => 'NUMERIC',
            'compare' => '='
        ];
    }

    if (!is_null($floors)) {
        $meta_query[] = [
            'key' => 'кількість_поверхів',
            'value' => $floors,
            'type' => 'NUMERIC',
            'compare' => '='
        ];
    }

    if ($building_type) {
        $meta_query[] = [
            'key' => 'тип_будівлі',
            'value' => $building_type,
            'compare' => '='
        ];
    }

    if (!is_null($eco_rating)) {
        $meta_query[] = [
            'key' => 'екологічність',
            'value' => $eco_rating,
            'type' => 'NUMERIC',
            'compare' => '='
        ];
    }

    $args = [
        'post_type' => 'real_estate',
        'posts_per_page' => 5,
        'paged' => $paged,
        'meta_query' => $meta_query
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
        echo '<p>Нічого не знайдено.</p>';
    }
    wp_die();
}

class RealEstateQueryModifier {
    public function __construct() {
        add_action('pre_get_posts', [$this, 'modify_query']);
    }

    public function modify_query($query) {
        // Виконується лише для основного запиту на фронтенді
        if (is_admin() || !$query->is_main_query()) {
            return;
        }

        // Застосовуємо сортування тільки для архіву типу 'real_estate'
        if (is_post_type_archive('real_estate')) {
            $query->set('meta_key', 'екологічність');
            $query->set('orderby', 'meta_value_num');
            $query->set('order', 'DESC'); // або 'ASC', якщо потрібен зворотній порядок
        }
    }
}

// Ініціалізуємо клас
new RealEstateQueryModifier();
