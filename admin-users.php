<?php
function silverscore_users_page() {
    global $wpdb;
    $results = $wpdb->get_results("SELECT r.*, u.user_login FROM {$wpdb->prefix}silverscore_results r JOIN {$wpdb->prefix}users u ON r.user_id = u.ID ORDER BY r.submission_date DESC");

    echo '<div class="wrap"><h1>SilverScore Quiz Results</h1>';
    echo '<table class="widefat fixed" cellspacing="0">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Total Score</th>
                    <th>Category Scores</th>
                    <th>Submission Date</th>
                </tr>
            </thead>
            <tbody>';

    if (!empty($results)) {
        foreach ($results as $result) {
            // Ensure category_scores is not NULL
            $category_scores = !empty($result->category_scores) ? json_decode($result->category_scores, true) : [];

            // If decoding fails, set a default empty array
            if (!is_array($category_scores)) {
                $category_scores = [];
            }

            $formatted_scores = [];

            foreach ($category_scores as $category_id => $score) {
                $category_name = $wpdb->get_var($wpdb->prepare("SELECT category_name FROM {$wpdb->prefix}silverscore_categories WHERE id = %d", $category_id));
                $formatted_scores[] = "{$category_name}: $score";
            }

            echo "<tr>
                    <td>{$result->user_login}</td>
                    <td>{$result->total_score}</td>
                    <td>" . (!empty($formatted_scores) ? implode(', ', $formatted_scores) : 'No category data') . "</td>
                    <td>{$result->submission_date}</td>
                </tr>";
        }
    } else {
        echo '<tr><td colspan="4">No quiz results found.</td></tr>';
    }

    echo '</tbody></table></div>';
}
