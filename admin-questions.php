<?php
function silverscore_questions_page() {
    global $wpdb;
    
    // Handle Delete
    if (isset($_GET['delete_question'])) {
        $question_id = intval($_GET['delete_question']);
        $wpdb->delete("{$wpdb->prefix}silverscore_options", ['question_id' => $question_id]);
        $wpdb->delete("{$wpdb->prefix}silverscore_questions", ['id' => $question_id]);
        echo '<div class="updated"><p>Question deleted successfully!</p></div>';
    }

    // Handle Edit (Load Existing Data)
    $edit_data = null;
    if (isset($_GET['edit_question'])) {
        $question_id = intval($_GET['edit_question']);
        $edit_data = $wpdb->get_row(
            $wpdb->prepare("SELECT q.id, c.category_name, s.subcategory_name, q.question_text
                            FROM {$wpdb->prefix}silverscore_questions q
                            JOIN {$wpdb->prefix}silverscore_subcategories s ON q.subcategory_id = s.id
                            JOIN {$wpdb->prefix}silverscore_categories c ON s.category_id = c.id
                            WHERE q.id = %d", $question_id)
        );
        
        // Load options
        $edit_options = $wpdb->get_results(
            $wpdb->prepare("SELECT id, option_text, score FROM {$wpdb->prefix}silverscore_options WHERE question_id = %d", $question_id)
        );
    }

    // Handle Add/Edit Form Submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['silverscore_submit'])) {
        $category = sanitize_text_field($_POST['category']);
        $subcategory = sanitize_text_field($_POST['subcategory']);
        $question = sanitize_text_field($_POST['question']);
        $options = $_POST['options'];
        $scores = $_POST['scores'];
        $question_id = isset($_POST['question_id']) ? intval($_POST['question_id']) : null;

        // Insert or Update Category
        $existing_category = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}silverscore_categories WHERE category_name = %s", $category));
        if (!$existing_category) {
            $wpdb->insert("{$wpdb->prefix}silverscore_categories", ['category_name' => $category]);
            $category_id = $wpdb->insert_id;
        } else {
            $category_id = $existing_category;
        }

        // Insert or Update Subcategory
        $existing_subcategory = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}silverscore_subcategories WHERE subcategory_name = %s AND category_id = %d", $subcategory, $category_id));
        if (!$existing_subcategory) {
            $wpdb->insert("{$wpdb->prefix}silverscore_subcategories", ['category_id' => $category_id, 'subcategory_name' => $subcategory]);
            $subcategory_id = $wpdb->insert_id;
        } else {
            $subcategory_id = $existing_subcategory;
        }

        if ($question_id) {
            // Update existing question
            $wpdb->update("{$wpdb->prefix}silverscore_questions", 
                ['subcategory_id' => $subcategory_id, 'question_text' => $question, 'updated_at' => current_time('mysql')], 
                ['id' => $question_id]
            );
            // Delete old options
            $wpdb->delete("{$wpdb->prefix}silverscore_options", ['question_id' => $question_id]);
        } else {
            // Insert new question
            $wpdb->insert("{$wpdb->prefix}silverscore_questions", 
            ['subcategory_id' => $subcategory_id, 'question_text' => $question, 'created_at' => current_time('mysql')]
            );
            $question_id = $wpdb->insert_id;
        }

        // Insert new options
        foreach ($options as $index => $option) {
            $score = intval($scores[$index]);
            $wpdb->insert("{$wpdb->prefix}silverscore_options", [
                'question_id' => $question_id,
                'option_text' => sanitize_text_field($option),
                'score' => $score
            ]);
        }

        echo '<div class="updated"><p>Question saved successfully!</p></div>';
        echo '<script>window.location.href="?page=silverscore_questions";</script>';
        exit;
    }

    ?>
    <div class="wrap">
        <h1>Manage Questions</h1>

        <form class="questionaddform" method="POST">
            <input type="hidden" name="question_id" value="<?= $edit_data ? esc_attr($edit_data->id) : ''; ?>">
            <div class="cate">
                <div class="cat">
                    <label>Category: </label> <input type="text" name="category" value="<?= $edit_data ? esc_attr($edit_data->category_name) : ''; ?>" placeholder="Category" required>
                </div>
                <div class="cat">
                <label>Subcategory: </label><input type="text"  name="subcategory" value="<?= $edit_data ? esc_attr($edit_data->subcategory_name) : ''; ?>"  placeholder="Subcategory" required>
                </div>
            </div>
            <div class="questionop-box">
                <h3>Question</h3>
                <input type="text" name="question" value="<?= $edit_data ? esc_attr($edit_data->question_text) : ''; ?>"  placeholder="Question" required>

                <h3>Options</h3>
                <div id="options-container">
                    <?php if ($edit_data && $edit_options): ?>
                        <?php foreach ($edit_options as $option): ?>
                            <div class="option-group">
                                <input type="text" name="options[]" value="<?= esc_attr($option->option_text); ?>" placeholder="Option" required>
                                <input type="number" name="scores[]" value="<?= esc_attr($option->score); ?>" placeholder="Score" required>
                                <button type="button" class="remove-option">Remove</button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="option-group">
                            <input type="text" name="options[]" placeholder="Option" required>
                            <input type="number" name="scores[]" placeholder="Score" required>
                            <button type="button" class="remove-option">Remove</button>
                        </div>
                    <?php endif; ?>
                </div>
                <button type="button" id="add-option">Add Option</button>
            </div>

            <input type="submit" class="updatesub" name="silverscore_submit" value="<?= $edit_data ? 'Update' : 'Submit'; ?>">
        </form>

        <form method="GET" id="filter-form" class="filter-form">
            <input type="hidden" name="page" value="silverscore_questions">
            
            <label for="filter_date" class="filter-label">Filter by Date: </label>
            
            <div class="custom-date-wrapper">
                <input type="date" name="filter_date" id="filter_date" class="filter-input" 
                    value="<?= isset($_GET['filter_date']) ? esc_attr($_GET['filter_date']) : ''; ?>">
                <span class="calendar-icon">📅</span>
            </div>
            
            <input type="submit" value="Filter" class="filter-button">
        </form>

        <?php
            $filter_date = isset($_GET['filter_date']) ? sanitize_text_field($_GET['filter_date']) : '';

            $query = "SELECT q.id, c.category_name, s.subcategory_name, q.question_text, q.created_at 
                      FROM {$wpdb->prefix}silverscore_questions q
                      JOIN {$wpdb->prefix}silverscore_subcategories s ON q.subcategory_id = s.id
                      JOIN {$wpdb->prefix}silverscore_categories c ON s.category_id = c.id";
            
            if ($filter_date) {
                $query .= $wpdb->prepare(" WHERE DATE(q.created_at) = %s", $filter_date);
            }
            
            $query .= " ORDER BY q.created_at DESC"; // Sort by date
            
            $questions = $wpdb->get_results($query);            
        ?>
        <h2>All Questions</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Number</th>
                    <th>Category</th>
                    <th>Subcategory</th>
                    <th>Question</th>
                    <th>Options</th>
                    <th>Scores</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                
                <?php
                $filter_date = isset($_GET['filter_date']) ? sanitize_text_field($_GET['filter_date']) : '';

                $query = "SELECT q.id, c.category_name, s.subcategory_name, q.question_text, q.created_at 
                          FROM {$wpdb->prefix}silverscore_questions q
                          JOIN {$wpdb->prefix}silverscore_subcategories s ON q.subcategory_id = s.id
                          JOIN {$wpdb->prefix}silverscore_categories c ON s.category_id = c.id";
                
                if ($filter_date) {
                    $query .= $wpdb->prepare(" WHERE DATE(q.created_at) = %s", $filter_date);
                }
                
                $query .= " ORDER BY q.created_at DESC"; // Sort by date
                
                $questions = $wpdb->get_results($query);    

                $count = 1; // Start numbering
                foreach ($questions as $q) {
                    $options = $wpdb->get_results("SELECT option_text, score FROM {$wpdb->prefix}silverscore_options WHERE question_id = {$q->id}");
                    $option_texts = array_column($options, 'option_text');
                    $option_scores = array_column($options, 'score');
            
                    echo "<tr>
                            <td>{$count}</td>
                            <td>{$q->category_name}</td>
                            <td>{$q->subcategory_name}</td>
                            <td>{$q->question_text}</td>
                            <td>" . implode(', ', $option_texts) . "</td>
                            <td>" . implode(', ', $option_scores) . "</td>
                            <td>" . date('Y-m-d', strtotime($q->created_at)) . "</td>
                            <td>
                                <a href='?page=silverscore_questions&edit_question={$q->id}' class='button'>Edit</a>
                                <a href='?page=silverscore_questions&delete_question={$q->id}' class='button' onclick='return confirm(\"Are you sure?\")'>Delete</a>
                            </td>
                          </tr>";
                    $count++;
                }
                ?>
            </tbody>
        </table>
    </div>
    <script>
        document.getElementById('add-option').addEventListener('click', function() {
            let container = document.getElementById('options-container');
            let newOption = document.createElement('div');
            newOption.classList.add('option-group');
            newOption.innerHTML = '<input type="text" name="options[]" placeholder="Option" required>' +
                                  '<input type="number" name="scores[]" placeholder="Score" required>' +
                                  '<button type="button" class="remove-option">Remove</button>';
            container.appendChild(newOption);
        });

        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('remove-option')) {
                event.target.parentElement.remove();      
            }
        });
    </script>
<?php
}
