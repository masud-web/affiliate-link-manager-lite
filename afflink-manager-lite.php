<?php
/*
Plugin Name: Affiliate Link Manager Lite
Plugin URI: https://www.linkerstech.com/plugins
Description: Easily manage affiliate links with clean slugs like /go/your-link, track link clicks with reports and charts, and insert links via shortcode or Gutenberg block. Lightweight, fast, and beginner-friendly.
Version: 1.0
Author: Masudul Alam
Author URI: https://masud.best/
Text Domain: afflink-manager-lite
*/

if (!defined('ABSPATH')) exit;

define('AFFLINK_MANAGER_LITE_OPTION', 'afflink_manager_lite_links');
define('AFFLINK_MANAGER_LITE_LOG_TABLE', 'afflink_click_logs');
define('AFFLINK_MANAGER_LITE_NOTES_OPTION', 'afflink_manager_lite_notes');

register_activation_hook(__FILE__, function () {
    global $wpdb;
    $table = $wpdb->prefix . AFFLINK_MANAGER_LITE_LOG_TABLE;
    $wpdb->query("CREATE TABLE IF NOT EXISTS $table (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        slug VARCHAR(255),
        clicked_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
});

// Enqueue Chart.js
add_action('admin_enqueue_scripts', function($hook) {
    if (strpos($hook, 'afflink-click-report') !== false) {
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', [], null, true);
    }
});

// Includes
require_once plugin_dir_path(__FILE__) . 'includes/admin-page.php';
require_once plugin_dir_path(__FILE__) . 'includes/click-report.php';

// Handle Redirect and Logging
add_action('init', function () {
    $request_uri = trim($_SERVER['REQUEST_URI'], '/');
    if (strpos($request_uri, 'go/') === 0) {
        $slug = substr($request_uri, 3);
        $links = get_option(AFFLINK_MANAGER_LITE_OPTION, []);
        if (isset($links[$slug])) {
            global $wpdb;
            $table = $wpdb->prefix . AFFLINK_MANAGER_LITE_LOG_TABLE;
            $wpdb->insert($table, ['slug' => $slug]);
            wp_redirect($links[$slug], 301);
            exit;
        }
    }
});

// Shortcode support [afflink slug="exonhost"]
add_shortcode('afflink', function ($atts) {
    $atts = shortcode_atts(['slug' => ''], $atts);
    if ($atts['slug']) {
        return esc_url(home_url('/go/' . sanitize_title($atts['slug'])));
    }
    return '';
});

// Gutenberg Block Enqueue
add_action('enqueue_block_editor_assets', function () {
    wp_enqueue_script(
        'afflink-block',
        plugins_url('assets/block.js', __FILE__),
        ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components'],
        null,
        true
    );
});
