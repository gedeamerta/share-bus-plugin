    <?php
    /*
    Plugin Name: ShareBus Zoho CRM Wordpress Plugin
    Description: A custom plugin to perform CRUD operations on Zoho CRM data using MVC architecture.
    Version: 1.0
    Author: Your Name
    */

    if (!defined('ABSPATH')) exit; // Prevent direct access

    // Initialize plugin
    function zc_crud_init() {
        // Register necessary hooks, enqueue assets, etc.
    }
    add_action('plugins_loaded', 'zc_crud_init');

    function zc_trips_date_shortcode() {
        ob_start();
        // fetch from crm

        include plugin_dir_path(__FILE__) . 'templates/trips-date-page.php';
        return ob_get_clean();
    }
    add_shortcode('zc_trips_date', 'zc_trips_date_shortcode');

    function zc_crud_enqueue_assets($hook) {
        if ($hook != 'toplevel_page_zc-crud') return;
        wp_enqueue_style('zc-crud-style', plugins_url('assets/css/style.css', __FILE__));
        wp_enqueue_script('zc-crud-script', plugins_url('assets/js/script.js', __FILE__), array('jquery'), null, true);
        wp_localize_script('zc-crud-script', 'zcCrud', ['ajax_url' => admin_url('admin-ajax.php')]);
    }
    add_action('admin_enqueue_scripts', 'zc_crud_enqueue_assets');