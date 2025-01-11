<?php
/**
 * Plugin Name: Diligent GNews Plugin
 * Description: A WordPress plugin to fetch and publish news articles using the GNews API.
 * Author Name: Usama Tasawar
 * Author URI: https://github.com/usamatasawar
 * Text Domain: diligent-gnews
 * Version: 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enqueue styles and scripts
 */
function dn_enqueue_assets() {
    wp_enqueue_style( 'dn-style', plugins_url( 'assets/css/style.css', __FILE__ ) );
    wp_enqueue_script( 'dn-scripts', plugins_url( 'assets/js/script.js', __FILE__ ), [ 'jquery' ], '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'dn_enqueue_assets' );

/**
 * Register custom post type for news
 */
function dn_register_post_type_news() {
    register_post_type( 'news', [
        'labels' => [
            'name'          => __( 'News', 'diligent-gnews' ),
            'singular_name' => __( 'News', 'diligent-gnews' ),
        ],
        'public'       => true,
        'has_archive'  => true,
        'supports'     => [ 'title', 'editor', 'thumbnail' ],
        'rewrite'      => [ 'slug' => 'news' ],
    ]);
}
add_action( 'init', 'dn_register_post_type_news' );

