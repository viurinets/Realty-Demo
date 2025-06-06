<?php
function understrap_child_enqueue_styles() {
  wp_enqueue_style( 'understrap-parent-style', get_template_directory_uri() . '/style.css' );

  wp_enqueue_style( 'understrap-child-style',
    get_stylesheet_directory_uri() . '/style.css',
    array( 'understrap-parent-style' ),
    wp_get_theme()->get('Version')
  );
}
add_action( 'wp_enqueue_scripts', 'understrap_child_enqueue_styles' );
