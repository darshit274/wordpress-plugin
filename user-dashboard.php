<?php

function silverscore_user_dashboard_shortcode() {
    ob_start();
    
    // Restrict access to logged-in users
    if (!is_user_logged_in()) {
        echo '<p>You must be logged in to access your dashboard. <a href="' . wp_login_url() . '">Login here</a></p>';
        return;
    }

    // Get current user data
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    $user_email = $current_user->user_email;
    $user_name = $current_user->display_name;

    // Fetch user quiz results with category scores
    global $wpdb;
    $table_results = $wpdb->prefix . 'silverscore_results';
    $table_categories = $wpdb->prefix . 'silverscore_categories';

    $quiz_results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT r.*, u.user_login 
             FROM $table_results r 
             JOIN {$wpdb->prefix}users u ON r.user_id = u.ID 
             WHERE r.user_id = %d 
             ORDER BY r.submission_date DESC",
            $user_id
        )
    );

    ?>

    <div class="silverscore-dashboard">
        <h2>Welcome, <?php echo esc_html($user_name); ?></h2>

        <h3>Your Profile</h3>
        <form method="post">
            <label for="email">Email:</label>
            <input type="email" name="email" value="<?php echo esc_attr($user_email); ?>" required>
            
            <label for="password">New Password (leave blank to keep unchanged):</label>
            <input type="password" name="password">

            <input type="submit" name="update_profile" value="Update Profile">
        </form>

        <?php
        // Handle profile updates
        if (isset($_POST['update_profile'])) {
            $new_email = sanitize_email($_POST['email']);
            $new_password = sanitize_text_field($_POST['password']);

            if (!empty($new_email)) {
                wp_update_user(['ID' => $user_id, 'user_email' => $new_email]);
                echo '<p>Email updated successfully!</p>';
            }

            if (!empty($new_password)) {
                wp_set_password($new_password, $user_id);
                echo '<p>Password updated successfully! Please log in again.</p>';
            }
        }
        ?>

        <h3>Your Quiz Results</h3>
        <?php if (!empty($quiz_results)) : ?>
            <table>
                <tr>
                    <!-- <th>Quiz Name</th> -->
                    <th>Total Score</th>
                    <th>Category Scores</th>
                    <th>Date</th>
                </tr>
                <?php foreach ($quiz_results as $result) : ?>
                    <?php
                    // Ensure category_scores is not NULL
                    $category_scores = !empty($result->category_scores) ? json_decode($result->category_scores, true) : [];

                    // If decoding fails, set a default empty array
                    if (!is_array($category_scores)) {
                        $category_scores = [];
                    }

                    $formatted_scores = [];
                    foreach ($category_scores as $category_id => $score) {
                        $category_name = $wpdb->get_var(
                            $wpdb->prepare("SELECT category_name FROM $table_categories WHERE id = %d", $category_id)
                        );
                        $formatted_scores[] = esc_html($category_name) . ": " . esc_html($score);
                    }
                    ?>
                    <tr>
                        <!-- <td><?php //echo esc_html($result->quiz_name); ?></td> -->
                        <td><?php echo esc_html($result->total_score); ?></td>
                        <td><?php echo (!empty($formatted_scores) ? implode(', ', $formatted_scores) : 'No category data'); ?></td>
                        <td><?php echo esc_html(date('Y-m-d', strtotime($result->submission_date))); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else : ?> 
            <p>No quiz attempts found.</p>
        <?php endif; ?>

        <p><a href="<?php echo wp_logout_url(home_url()); ?>">Logout</a></p>
    </div>

    <style>
        .silverscore-dashboard { max-width: 600px; margin: 0 auto; padding: 20px; background: #f9f9f9; border-radius: 5px; }
        .silverscore-dashboard h2, h3 { text-align: center; }
        .silverscore-dashboard form { display: flex; flex-direction: column; gap: 10px; }
        .silverscore-dashboard input { padding: 8px; }
        .silverscore-dashboard table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .silverscore-dashboard th, td { padding: 8px; border: 1px solid #ddd; text-align: center; }
    </style>

    <?php
    return ob_get_clean();
}
add_shortcode('silverscore_user_dashboard', 'silverscore_user_dashboard_shortcode');
