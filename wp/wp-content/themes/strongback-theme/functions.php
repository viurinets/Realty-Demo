<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_theme_support( 'title-tag' );
add_theme_support( 'post-thumbnails' );
add_theme_support( 'menus' );
register_nav_menus( [
    'primary' => __( 'Primary Menu', 'strongback-theme' ),
] );

function strongback_enqueue_assets() {
    wp_enqueue_style( 'strongback-style', get_stylesheet_uri() );
}
add_action( 'wp_enqueue_scripts', 'strongback_enqueue_assets' );
?>