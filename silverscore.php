<?php
/*
Plugin Name: SilverScore Survey
Description: A survey plugin with question management and user scoring.
Version: 1.1
Author: Your Name
*/

if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'includes/db-setup.php';
require_once plugin_dir_path(__FILE__) . 'includes/user-registration.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-questions.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-users.php';
require_once plugin_dir_path(__FILE__) . 'includes/quiz-display.php';
require_once plugin_dir_path(__FILE__) . 'includes/user-dashboard.php';


// Run database setup on activation
register_activation_hook(__FILE__, 'silverscore_create_tables');

// Register shortcodes
add_shortcode('silverscore_register', 'silverscore_register_form');
add_shortcode('silverscore_quiz', 'silverscore_quiz_display');

// Add admin menu
function silverscore_add_admin_menu() {
    add_menu_page('SilverScore', 'SilverScore', 'manage_options', 'silverscore', 'silverscore_questions_page');
    add_submenu_page('silverscore', 'Manage Questions', 'Questions', 'manage_options', 'silverscore_questions', 'silverscore_questions_page');
    add_submenu_page('silverscore', 'Registered Users', 'Users', 'manage_options', 'silverscore_users', 'silverscore_users_page'); // New submenu
}
add_action('admin_menu', 'silverscore_add_admin_menu');

function silverscore_enqueue_assets() {
    wp_enqueue_style('silverscore-style', plugin_dir_url(__FILE__) . 'assets/style.css');
    wp_enqueue_script('silverscore-script', plugin_dir_url(__FILE__) . 'assets/script.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'silverscore_enqueue_assets');
add_action('admin_enqueue_scripts', 'silverscore_enqueue_assets'); // Load in admin panel too


// Run database setup and create quiz page on activation
function silverscore_plugin_activate() {
    silverscore_create_tables(); // Call the existing function to set up the database

    // Check if a quiz page already exists
    $quiz_page = get_page_by_path('silver-score-quiz');
    if (!$quiz_page) {
        // Create the quiz page
        $page_data = array(
            'post_title'    => 'SilverScore Quiz',
            'post_content'  => '[silverscore_quiz]', // Add the shortcode
            'post_status'   => 'publish',
            'post_type'     => 'page'
        );
        wp_insert_post($page_data);
    }
}

register_activation_hook(__FILE__, 'silverscore_plugin_activate');


// Disable admin bar for subscribers
function silverscore_hide_admin_bar_for_subscribers($show) {
    if (current_user_can('subscriber')) {
        return false;
    }
    return $show;
}
add_filter('show_admin_bar', 'silverscore_hide_admin_bar_for_subscribers');

// Redirect subscribers away from admin panel
function silverscore_redirect_subscribers_from_admin() {
    if (current_user_can('subscriber') && !defined('DOING_AJAX')) {
        wp_redirect(home_url()); // Redirect to homepage or another page
        exit;
    }
}
add_action('admin_init', 'silverscore_redirect_subscribers_from_admin');


function silverscore_create_dashboard_page() {
    $dashboard_page = array(
        'post_title'    => 'User Dashboard',
        'post_content'  => '[silverscore_user_dashboard]',
        'post_status'   => 'publish',
        'post_author'   => 1,
        'post_type'     => 'page'
    );

    // Check if page already exists
    $existing_page = get_page_by_title('User Dashboard');
    if (!$existing_page) {
        wp_insert_post($dashboard_page);
    }
}
register_activation_hook(__FILE__, 'silverscore_create_dashboard_page');
