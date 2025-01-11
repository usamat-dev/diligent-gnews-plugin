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
    wp_localize_script('dn-scripts', 'dn_news', ['ajax_url' => admin_url('admin-ajax.php')]);

}
add_action( 'admin_enqueue_scripts', 'dn_enqueue_assets' );

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

/**
 * Add custom meta box for news
 * 
 */ 

function dn_add_news_meta_box() {
    add_meta_box(
        'dn_news_meta_box', // ID of the meta box
        __( 'News Source Info', 'diligent-gnews' ), // Title of the meta box
        'dn_news_meta_box_callback', // Callback function to display the fields
        'news', // Post type to display the meta box on
        'normal', // Context ('normal', 'side', or 'advanced')
        'high' // Priority ('high', 'core', 'default', 'low')
    );
}
add_action( 'add_meta_boxes', 'dn_add_news_meta_box' );

/**
 * Callback function to display the meta box fields
 * 
 */

function dn_news_meta_box_callback( $post ) {
    // Get existing metadata values
    $published_at = get_post_meta( $post->ID, '_news_published_at', true );
    $source_name  = get_post_meta( $post->ID, '_news_source_name', true );
    $source_url   = get_post_meta( $post->ID, '_news_source_url', true );

    // Add nonce field for security
    wp_nonce_field( 'dn_save_news_meta', 'dn_news_meta_nonce' );
    ?>
    <table class="form-table">
        <tr>
            <th><label for="dn_news_published_at"><?php esc_html_e( 'Published At', 'diligent-gnews' ); ?></label></th>
            <td>
                <input type="text" id="dn_news_published_at" name="dn_news_published_at" value="<?php echo esc_attr( $published_at ); ?>" class="regular-text" readonly />
            </td>
        </tr>
        <tr>
            <th><label for="dn_news_source_name"><?php esc_html_e( 'Source Name', 'diligent-gnews' ); ?></label></th>
            <td>
                <input type="text" id="dn_news_source_name" name="dn_news_source_name" value="<?php echo esc_attr( $source_name ); ?>" class="regular-text" readonly />
            </td>
        </tr>
        <tr>
            <th><label for="dn_news_source_url"><?php esc_html_e( 'Source URL', 'diligent-gnews' ); ?></label></th>
            <td>
                <input type="url" id="dn_news_source_url" name="dn_news_source_url" value="<?php echo esc_url( $source_url ); ?>" class="regular-text" readonly />
            </td>
        </tr>
    </table>
    <?php
}

/**
 * Save the meta box data
 * 
 */

function dn_save_news_meta( $post_id ) {
    // Check if nonce is valid
    if ( ! isset( $_POST['dn_news_meta_nonce'] ) || ! wp_verify_nonce( $_POST['dn_news_meta_nonce'], 'dn_save_news_meta' ) ) {
        return;
    }

    // If autosave, do nothing
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check if the user has permission to edit the post
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // Save the custom fields (meta fields)
    if ( isset( $_POST['dn_news_published_at'] ) ) {
        update_post_meta( $post_id, '_news_published_at', sanitize_text_field( $_POST['dn_news_published_at'] ) );
    }

    if ( isset( $_POST['dn_news_source_name'] ) ) {
        update_post_meta( $post_id, '_news_source_name', sanitize_text_field( $_POST['dn_news_source_name'] ) );
    }

    if ( isset( $_POST['dn_news_source_url'] ) ) {
        update_post_meta( $post_id, '_news_source_url', esc_url( $_POST['dn_news_source_url'] ) );
    }
}
add_action( 'save_post', 'dn_save_news_meta' );

/**
 * Add custom menu for settings
 * 
 */
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

/**
 * Render the settings page
 * 
 */
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

/**
 * Add custom submenu for fetching news
 * 
 */

function dn_add_custom_submenu() {
    add_submenu_page(
        'diligent-gnews-settings', // Parent menu slug
        __( 'Fetch News', 'diligent-gnews' ), // Submenu title
        __( 'Fetch News', 'diligent-gnews' ), // Submenu text
        'manage_options',
        'diligent-gnews-fetch-news',
        'dn_fetch_news_page' // Callback function to render the page
    );
}
add_action( 'admin_menu', 'dn_add_custom_submenu' );

/**
 * Render the fetch news page
 * 
 */
function dn_fetch_news_page() {
    ?>
    <div class="wrap">
    <h2><?php esc_html_e( 'Fetch News from GNews API', 'diligent-gnews' ); ?></h2>

        <!-- Generate News Button -->
        <?php wp_nonce_field( 'dn_gnews_settings_nonce', 'dn_gnews_nonce' ); ?>
        <div class="dn-button-container">
        <button id="dn-fetch-news" class="button button-primary"><?php esc_html_e( 'Generate News', 'diligent-gnews' ); ?></button>

        <!-- Loader (CSS-based spinner) -->
        <div id="dn-loader"></div>

        </div>
        <!-- Table to Display Fetched News -->
        <table id="dn-news-table" class="wp-list-table widefat fixed striped posts" style="display:none;">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Title', 'diligent-gnews' ); ?></th>
                    <th><?php esc_html_e( 'Content', 'diligent-gnews' ); ?></th>
                    <th><?php esc_html_e( 'Publish Date', 'diligent-gnews' ); ?></th>
                    <th><?php esc_html_e( 'View', 'diligent-gnews' ); ?></th>
                </tr>
            </thead>
            <tbody id="dn-news-table-body">
                <!-- Fetched news will be appended here -->
            </tbody>
        </table>
    </div>
    <?php
}

/**
 * Fetch news from GNews API and display in the table
 */
function dn_fetch_news_from_api() {
    // Verify nonce for security
    if ( ! isset( $_POST['_nonce'] ) || ! wp_verify_nonce( $_POST['_nonce'], 'dn_gnews_settings_nonce' ) ) {
        wp_send_json_error( 'Invalid nonce' );
    }

    $api_key = get_option( 'dn_gnews_api_key' );
    if ( empty( $api_key ) ) {
        wp_send_json_error( 'API key is not set.' );
    }

    // Make the API request
    $response = wp_remote_get( "https://gnews.io/api/v4/top-headlines?apikey={$api_key}" );

    // Check for request errors
    if ( is_wp_error( $response ) ) {
        $error_message = $response->get_error_message();
        wp_send_json_error( 'API request failed: ' . $error_message );
    }

    // Check for a successful response (status code 200)
    $status_code = wp_remote_retrieve_response_code( $response );
    if ( $status_code !== 200 ) {
        wp_send_json_error( 'API request failed with status code: ' . $status_code );
    }

    // Decode the API response
    $data = json_decode( wp_remote_retrieve_body( $response ), true );

    // Handle cases where the response does not contain 'articles'
    if ( ! isset( $data['articles'] ) || empty( $data['articles'] ) ) {
        wp_send_json_error( 'No articles found in the API response.' );
    }

    $articles = [];
    // Loop through articles and prepare the data for the table
    foreach ( $data['articles'] as $article ) {
        if ( ! post_exists( $article['title'] ) ) {
            $post_data = [
                'post_title'   => $article['title'],
                'post_content' => $article['content'],
                'post_status'  => 'publish',
                'post_type'    => 'news',
            ];

            // Insert the post
            $post_id = wp_insert_post( $post_data );

            // Save metadata
            update_post_meta( $post_id, '_news_published_at', sanitize_text_field( $article['publishedAt'] ) );
            update_post_meta( $post_id, '_news_source_name', sanitize_text_field( $article['source']['name'] ) );
            update_post_meta( $post_id, '_news_source_url', esc_url( $article['source']['url'] ) );
        }

        // Add article to the response data
        $articles[] = [
            'title'       => $article['title'],
            'content'     => $article['content'],
            'publishedAt' => $article['publishedAt'],
            'link'        => get_permalink( $post_id ),
        ];
    }

    wp_send_json_success( $articles ); // Return the articles
}
add_action( 'wp_ajax_fetch_gnews_articles', 'dn_fetch_news_from_api' );


