<?php
/*
Plugin Name: ShareBus Zoho CRM WordPress Plugin
Description: A custom plugin to perform CRUD operations on Zoho CRM data.
Version: 1.0
Author: Smartmates
*/

if (!defined('ABSPATH')) exit; // Prevent direct access

// Initialize plugin
function zc_crud_init() {
    // Register necessary hooks, enqueue assets, etc.
}
add_action('plugins_loaded', 'zc_crud_init');

// Shortcode for trips date page
function zc_trips_date_shortcode() {
    ob_start();
    // Fetch data from CRM

    include plugin_dir_path(__FILE__) . 'templates/trips-date-page.php';
    return ob_get_clean();
}
add_shortcode('zc_trips_date', 'zc_trips_date_shortcode');

// Shortcode for trip details page
function zc_trip_details_shortcode() {
    ob_start();
    // Fetch data from CRM

    include plugin_dir_path(__FILE__) . 'templates/trip-details-page.php';
    return ob_get_clean();
}
add_shortcode('zc_trip_details', 'zc_trip_details_shortcode');

// Enqueue CSS and JavaScript for admin page
function zc_crud_enqueue_admin_assets($hook) {
    if ($hook != 'toplevel_page_zc-crud') return;
    wp_enqueue_style('zc-crud-style', plugins_url('assets/css/style.css', __FILE__));
    wp_enqueue_script('zc-crud-script', plugins_url('assets/js/script.js', __FILE__), array('jquery'), null, true);
    wp_localize_script('zc-crud-script', 'zcCrud', ['ajax_url' => admin_url('admin-ajax.php')]);
}
add_action('admin_enqueue_scripts', 'zc_crud_enqueue_admin_assets');

// Enqueue CSS and JavaScript for front-end pages
function zc_crud_enqueue_frontend_assets() {
    if (has_shortcode(get_post()->post_content, 'zc_trips_date') || has_shortcode(get_post()->post_content, 'zc_trip_details')) {
        wp_enqueue_style('zc-crud-style', plugins_url('assets/css/style.css', __FILE__));
        wp_enqueue_script('zc-crud-script', plugins_url('assets/js/script.js', __FILE__), array('jquery'), null, true);
        wp_localize_script('zc-crud-script', 'zcCrud', ['ajax_url' => admin_url('admin-ajax.php')]);
    }
}
add_action('wp_enqueue_scripts', 'zc_crud_enqueue_frontend_assets');
