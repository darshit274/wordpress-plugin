<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit; // Exit if uninstall.php is accessed directly
}

global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}silverscore_users");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}silverscore_questions");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}silverscore_options");

// Delete plugin options (if any)
delete_option('silverscore_survey_questions_data');
