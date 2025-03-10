<?php
function silverscore_quiz_display() {

    if (!is_user_logged_in()) {
        return do_shortcode('[silverscore_register]') . '<br>OR<br>' . do_shortcode('[silverscore_login]');
    }

    global $wpdb;
    $total_score = 0;
    $submitted = false;
    $category_scores = []; // Array to store category-wise scores

    // Score Interpretation Ranges
    $overall_score_ranges = [
        'needs_attention' => ['min' => 15, 'max' => 30, 'feedback' => 'Needs immediate attention'],
        'needs_improvement' => ['min' => 31, 'max' => 45, 'feedback' => 'Some aspects need improvement'],
        'fairly_prepared' => ['min' => 46, 'max' => 60, 'feedback' => 'Fairly prepared, with some room to grow'],
        'very_well_prepared' => ['min' => 61, 'max' => 75, 'feedback' => 'Very well prepared for retirement'],
    ];

    $subcategory_score_ranges = [
        'low' => ['min' => 3, 'max' => 6, 'feedback' => 'Low'],
        'moderate' => ['min' => 7, 'max' => 9, 'feedback' => 'Moderate'],
        'good' => ['min' => 10, 'max' => 12, 'feedback' => 'Good'],
        'excellent' => ['min' => 13, 'max' => 15, 'feedback' => 'Excellent'],
    ];

    // Function to get feedback based on score and ranges
    function get_score_feedback($score, $ranges) {
        foreach ($ranges as $range) {
            if ($score >= $range['min'] && $score <= $range['max']) {
                return $range['feedback'];
            }
        }
        return 'No feedback available'; // Default case
    }

    // Handle form submission
    // if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["answer"])) {
    //     $submitted = true;
    //     $answers = $_POST["answer"];

    //     // Initialize category scores
    //     $categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}silverscore_categories");
    //     foreach ($categories as $category) {
    //         $category_scores[$category->id] = 0;
    //     }

    //     foreach ($answers as $question_id => $option_id) {
    //         // Fetch option details from the database including category and subcategory IDs
    //         $option_details = $wpdb->get_row($wpdb->prepare("
    //             SELECT o.score, q.subcategory_id, sc.category_id
    //             FROM {$wpdb->prefix}silverscore_options o
    //             JOIN {$wpdb->prefix}silverscore_questions q ON o.question_id = q.id
    //             JOIN {$wpdb->prefix}silverscore_subcategories sc ON q.subcategory_id = sc.id
    //             WHERE o.id = %d
    //         ", $option_id));

    //         if ($option_details) {
    //             $total_score += intval($option_details->score);
    //             $category_scores[$option_details->category_id] += intval($option_details->score); // Accumulate category score
    //         }
    //     }
    // }
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["answer"])) {
        $submitted = true;
        $answers = $_POST["answer"];
        $user_id = get_current_user_id(); // Get logged-in user ID
        $total_score = 0;
        $category_scores = [];
    
        // Get categories dynamically
        $categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}silverscore_categories");
        foreach ($categories as $category) {
            $category_scores[$category->id] = 0;
        }
    
        // Calculate scores
        foreach ($answers as $question_id => $option_id) {
            $option_details = $wpdb->get_row($wpdb->prepare("
                SELECT o.score, q.subcategory_id, sc.category_id
                FROM {$wpdb->prefix}silverscore_options o
                JOIN {$wpdb->prefix}silverscore_questions q ON o.question_id = q.id
                JOIN {$wpdb->prefix}silverscore_subcategories sc ON q.subcategory_id = sc.id
                WHERE o.id = %d
            ", $option_id));
    
            if ($option_details) {
                $total_score += intval($option_details->score);
                $category_scores[$option_details->category_id] += intval($option_details->score);
            }
        }
    
        // Convert category scores to JSON
        $category_scores_json = json_encode($category_scores);
    
        // Store results in the database
        $wpdb->insert(
            "{$wpdb->prefix}silverscore_results",
            [
                'user_id' => $user_id,
                'total_score' => $total_score,
                'category_scores' => $category_scores_json,
                'submission_date' => current_time('mysql')
            ],
            ['%d', '%d', '%s', '%s']
        );
    }
    

    // Fetch Categories
    $categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}silverscore_categories");

    if (!$categories) {
        return "<p>No categories available.</p>";
    }

    ob_start(); // Start buffering output
    ?>

    <form id="silverscore_quiz_form" method="post">
        <h2>SilverScore Quiz</h2>

        <?php foreach ($categories as $catIndex => $category) : ?>
            <div class="category-block">
                <h3><?= ($catIndex + 1) . ". " . esc_html($category->category_name); ?></h3>

                <?php
                $subcategories = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}silverscore_subcategories WHERE category_id = %d", $category->id));
                if ($subcategories) :
                    foreach ($subcategories as $subcatIndex => $subcategory) : ?>
                        <div class="subcategory-block">
                            <h4><?= ($catIndex + 1) . "." . ($subcatIndex + 1) . " " . esc_html($subcategory->subcategory_name); ?></h4>

                            <?php
                            // Fetch Questions
                            $questions = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}silverscore_questions WHERE subcategory_id = %d", $subcategory->id));

                            if ($questions) :
                                foreach ($questions as $qIndex => $question) : ?>
                                    <div class="question-block">
                                        <p><strong><?= ($catIndex + 1) . "." . ($subcatIndex + 1) . "." . ($qIndex + 1) . " " . esc_html($question->question_text); ?></strong></p>

                                        <?php
                                        // Fetch Options
                                        $options = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}silverscore_options WHERE question_id = %d", $question->id));

                                        if ($options) :
                                            foreach ($options as $optIndex => $option) : ?>
                                                <label>
                                                    <input type="radio" name="answer[<?= $question->id; ?>]" value="<?= esc_attr($option->id); ?>" required>
                                                    <?= esc_html($option->option_text); ?>
                                                </label><br>
                                            <?php endforeach;
                                        else :
                                            echo "<p>No options available.</p>";
                                        endif;
                                        ?>
                                    </div>
                                <?php endforeach;
                            else :
                                echo "<p>No questions available.</p>";
                            endif;
                            ?>
                        </div>
                    <?php endforeach;
                endif;
                ?>
            </div>
        <?php endforeach; ?>

        <button type="submit" class="submit-btn">Submit Quiz</button>
    </form>

    <?php if ($submitted) : ?>
        <div id="quiz-results" class="quiz-result">
            <h3>Your SilverScore: <?= esc_html($total_score); ?></h3>
            <p><strong>Overall Feedback:</strong> <?= esc_html(get_score_feedback($total_score, $overall_score_ranges)); ?></p>

            <h4>Category Breakdown:</h4>
            <ul>
                <?php foreach ($categories as $category) : ?>
                    <?php if (isset($category_scores[$category->id])) : ?>
                        <li>
                            <strong><?= esc_html($category->category_name); ?>:</strong>
                            <?= esc_html($category_scores[$category->id]); ?> 
                            (<?= esc_html(get_score_feedback($category_scores[$category->id], $subcategory_score_ranges)); ?>)
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                document.getElementById("quiz-results").scrollIntoView({ behavior: "smooth" });
            });
        </script>
    <?php endif; ?>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9f9f9;
        }
        .category-block {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }
        .subcategory-block {
            margin-left: 20px;
            padding: 10px;
            border-left: 3px solid #0073aa;
            font-weight: bold;
        }
        .question-block {
            margin-left: 30px;
            padding: 5px;
            background: #f4f4f4;
            border-radius: 5px;
        }
        .option-label {
            display: block;
            padding: 5px;
            cursor: pointer;
        }
        .submit-btn {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            border: none;
            background: #4CAF50;
            color: white;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
        }
        .quiz-result {
            margin-top: 20px;
            padding: 15px;
            background: #eaf7e4;
            border: 1px solid #4CAF50;
            color: #4CAF50;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
        }
        .quiz-result ul {
            list-style: none;
            text-align: left;
        }
    </style>

    <?php
    return ob_get_clean(); // Return buffered content
}

add_shortcode('silverscore_quiz', 'silverscore_quiz_display');
?>
