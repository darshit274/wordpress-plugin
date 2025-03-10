<?php
function silverscore_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Categories Table
    $categories_table = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}silverscore_categories (
        id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        category_name VARCHAR(255) NOT NULL UNIQUE
    ) $charset_collate;";

    // Subcategories Table
    $subcategories_table = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}silverscore_subcategories (
        id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        category_id BIGINT(20) UNSIGNED NOT NULL,
        subcategory_name VARCHAR(255) NOT NULL,
        FOREIGN KEY (category_id) REFERENCES {$wpdb->prefix}silverscore_categories(id) ON DELETE CASCADE
    ) $charset_collate;";

    // Questions Table
    // $questions_table = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}silverscore_questions (
    //     id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    //     subcategory_id BIGINT(20) UNSIGNED NOT NULL,
    //     question_text TEXT NOT NULL,
    //     FOREIGN KEY (subcategory_id) REFERENCES {$wpdb->prefix}silverscore_subcategories(id) ON DELETE CASCADE
    // ) $charset_collate;";
    $questions_table = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}silverscore_questions (
        id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        subcategory_id BIGINT(20) UNSIGNED NOT NULL,
        question_text TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP, 
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (subcategory_id) REFERENCES {$wpdb->prefix}silverscore_subcategories(id) ON DELETE CASCADE
    ) $charset_collate;";

    // Options Table
    $options_table = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}silverscore_options (
        id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        question_id BIGINT(20) UNSIGNED NOT NULL,
        option_text VARCHAR(255) NOT NULL,
        score INT NOT NULL,
        FOREIGN KEY (question_id) REFERENCES {$wpdb->prefix}silverscore_questions(id) ON DELETE CASCADE
    ) $charset_collate;";

    // Quiz Results Table
    $results_table = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}silverscore_results (
        id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        total_score INT NOT NULL,
        category_scores JSON NOT NULL,
        submission_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($categories_table);
    dbDelta($subcategories_table);
    dbDelta($questions_table);
    dbDelta($options_table);
    dbDelta($results_table);
}

register_activation_hook(__FILE__, 'silverscore_create_tables');
