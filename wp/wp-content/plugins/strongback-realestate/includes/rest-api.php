<?php

if (! defined('ABSPATH')) {
    exit;
}

add_action('rest_api_init', function () {
    $ns = 'strongback/v1';

    register_rest_route($ns, '/objects', [
        'methods'  => [WP_REST_Server::READABLE, WP_REST_Server::CREATABLE],
        'callback' => function (WP_REST_Request $req) {
            if ($req->get_method() === 'POST') {
                return sb_create_object($req);
            }
            return sb_list_objects($req);
        },
        'permission_callback' => function () {
            return current_user_can('edit_posts') || $_SERVER['REQUEST_METHOD'] === 'GET';
        },
        'args' => [
            'title'  => ['required' => true],
            'content' => ['required' => true],
        ],
    ]);

    register_rest_route($ns, '/objects/(?P<id>\d+)', [
        'methods'  => WP_REST_Server::READABLE,
        'callback' => 'sb_get_object',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route($ns, '/objects/(?P<id>\d+)', [
        'methods'  => WP_REST_Server::EDITABLE,
        'callback' => 'sb_update_object',
        'permission_callback' => function () {
            return current_user_can('edit_posts');
        },
    ]);

    register_rest_route($ns, '/objects/(?P<id>\d+)', [
        'methods'  => WP_REST_Server::DELETABLE,
        'callback' => 'sb_delete_object',
        'permission_callback' => function () {
            return current_user_can('delete_posts');
        },
    ]);
});

function sb_list_objects(WP_REST_Request $req)
{
    $posts = get_posts([
        'post_type'      => 'real_estate',
        'posts_per_page' => -1,
    ]);

    $data = [];
    foreach ($posts as $post) {
        $data[] = sb_prepare_object_response($post);
    }

    return rest_ensure_response($data);
}

function sb_get_object(WP_REST_Request $req)
{
    $id = (int) $req['id'];
    $post = get_post($id);
    if (! $post || $post->post_type !== 'real_estate') {
        return new WP_Error('not_found', 'Object not found', ['status' => 404]);
    }
    return rest_ensure_response(sb_prepare_object_response($post));
}

function sb_create_object(WP_REST_Request $req)
{
    $params = $req->get_json_params();

    $post_id = wp_insert_post([
        'post_type'   => 'real_estate',
        'post_title'  => sanitize_text_field($params['title']),
        'post_content' => sanitize_textarea_field($params['content']),
        'post_status' => 'publish',
    ], true);

    if (is_wp_error($post_id)) {
        return $post_id;
    }

    if (! empty($params['district'])) {
        wp_set_object_terms($post_id, sanitize_text_field($params['district']), 'district');
    }

    $acf_fields = [
        'house_name',
        'location_coords',
        'floors_count',
        'building_type',
        'ecological_rating',
        'images',
        'rooms',
    ];
    foreach ($acf_fields as $field) {
        if (isset($params[$field])) {
            update_field($field, $params[$field], $post_id);
        }
    }

    return rest_ensure_response(sb_prepare_object_response(get_post($post_id)));
}

function sb_update_object(WP_REST_Request $req)
{
    $id     = (int) $req['id'];
    $params = $req->get_json_params();

    $post = get_post($id);
    if (! $post || $post->post_type !== 'real_estate') {
        return new WP_Error('not_found', 'Object not found', ['status' => 404]);
    }

    $update = ['ID' => $id];
    if (isset($params['title'])) {
        $update['post_title'] = sanitize_text_field($params['title']);
    }
    if (isset($params['content'])) {
        $update['post_content'] = sanitize_textarea_field($params['content']);
    }
    wp_update_post($update);

    if (isset($params['district'])) {
        wp_set_object_terms($id, sanitize_text_field($params['district']), 'district');
    }

    foreach (['house_name', 'location_coords', 'floors_count', 'building_type', 'ecological_rating', 'images', 'rooms'] as $field) {
        if (array_key_exists($field, $params)) {
            update_field($field, $params[$field], $id);
        }
    }

    return rest_ensure_response(sb_prepare_object_response(get_post($id)));
}

function sb_delete_object(WP_REST_Request $req)
{
    $id = (int) $req['id'];
    $post = get_post($id);
    if (! $post || $post->post_type !== 'real_estate') {
        return new WP_Error('not_found', 'Object not found', ['status' => 404]);
    }
    wp_delete_post($id, true);
    return rest_ensure_response(['deleted' => true]);
}

function sb_prepare_object_response(WP_Post $post)
{
    return [
        'id'                => $post->ID,
        'title'             => $post->post_title,
        'content'           => $post->post_content,
        'district'          => wp_get_post_terms($post->ID, 'district', ['fields' => 'names']),
        'house_name'        => get_field('house_name',        $post->ID),
        'location_coords'   => get_field('location_coords',   $post->ID),
        'floors_count'      => get_field('floors_count',      $post->ID),
        'building_type'     => get_field('building_type',     $post->ID),
        'ecological_rating' => get_field('ecological_rating', $post->ID),
        'images'            => get_field('images',            $post->ID),
        'rooms'             => get_field('rooms',             $post->ID),
    ];
}
