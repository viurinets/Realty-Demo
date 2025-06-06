<?php
// Підключення стилів з батьківської теми
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
});

// Реєстрація меню
register_nav_menus([
    'main_menu' => 'Головне меню',
]);
