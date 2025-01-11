<?php
/**
 * Plugin Name: Diligent GNews Plugin
 * Description: A WordPress plugin to fetch and publish news articles using the GNews API.
 * Author: Usama Tasawar
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

// Add custom top-level menu for settings
function dn_add_custom_menu() {
    add_menu_page(
        __( 'Diligent GNews', 'diligent-gnews' ),
        __( 'Diligent GNews', 'diligent-gnews' ),
        'manage_options',
        'diligent-gnews-settings',
        'dn_render_settings_page',
        'dashicons-admin-site-alt3',
        26
    );
}
add_action( 'admin_menu', 'dn_add_custom_menu' );

function dn_render_settings_page() {
    if ( isset( $_POST['dn_gnews_api_key'] ) ) {
        // Save the API key to options table
        update_option( 'dn_gnews_api_key', sanitize_text_field( $_POST['dn_gnews_api_key'] ) );
    }

    // Get the saved API key
    $api_key = get_option( 'dn_gnews_api_key', '' );

    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Diligent GNews Settings', 'diligent-gnews' ); ?></h1>
        <form method="post">
            <?php wp_nonce_field( 'dn_gnews_settings_nonce', 'dn_gnews_nonce' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php esc_html_e( 'API Key', 'diligent-gnews' ); ?></th>
                    <td>
                        <input type="password" name="dn_gnews_api_key" value="<?php echo esc_attr( $api_key ); ?>" class="regular-text">
                    </td>
                </tr>
            </table>
            <?php submit_button( __( 'Save Settings', 'diligent-gnews' ) ); ?>
        </form>
    </div>
    <?php
}

