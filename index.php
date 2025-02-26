<?php
/*
Plugin Name: ShareBus Zoho CRM WordPress Plugin
Description: A custom plugin to perform CRUD operations on Zoho CRM data.
Version: 1.0
Author: Smartmates
*/

if (!defined('ABSPATH'))
    exit; // Prevent direct access

/*
================================== LIVE START ===================================
*/
// Shortcode for trips date page
function zc_trips_date_shortcode()
{
    wp_enqueue_style('zc-crud-style', plugins_url('assets/css/trip-dates-pages1.css', __FILE__));
    ob_start();
    include plugin_dir_path(__FILE__) . 'templates/trips-date-page.php';
    return ob_get_clean();
}
add_shortcode('zc_trips_date', 'zc_trips_date_shortcode');

// Shortcode for trip details page
function zc_trip_details_shortcode()
{
    wp_enqueue_style('zc-crud-style', plugins_url('assets/css/trip-details-pages2.css', __FILE__));
    ob_start();
    include plugin_dir_path(__FILE__) . 'templates/trip-details-page.php';
    return ob_get_clean();
}
add_shortcode('zc_trip_details', 'zc_trip_details_shortcode');


/*
================================== LIVE END ===================================
*/

/*
================================== TEST START ===================================
*/
function zc_trips_date_test_shortcode()
{
    wp_enqueue_style('zc-crud-style', plugins_url('test/assets/css/trip-dates-page1.css', __FILE__));
    ob_start();
    include plugin_dir_path(__FILE__) . 'test/trips-date-page.php';
    return ob_get_clean();
}
add_shortcode('zc_trips_date_test', 'zc_trips_date_test_shortcode');

// Shortcode for trip details page
function zc_trip_details_test_shortcode()
{
    wp_enqueue_style('zc-crud-style', plugins_url('test/assets/css/trip-details-page3.css', __FILE__));
    ob_start();
    include plugin_dir_path(__FILE__) . 'test/trip-details-page.php';
    return ob_get_clean();
}
add_shortcode('zc_trip_details_test', 'zc_trip_details_test_shortcode');
/*
================================== TEST END ===================================
*/