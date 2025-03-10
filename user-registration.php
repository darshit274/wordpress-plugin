<?php
function silverscore_register_form() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['silverscore_register'])) {
        $username = sanitize_text_field($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $password = sanitize_text_field($_POST['password']);

        if (username_exists($username) || email_exists($email)) {
            echo '<p style="color:red;">User already exists!</p>';
        } else {
            $user_id = wp_create_user($username, $password, $email);
            if (!is_wp_error($user_id)) {
                global $wpdb;
                $wpdb->insert("{$wpdb->prefix}silverscore_users", [
                    'user_id' => $user_id,
                    'total_score' => 0
                ]);

                // Auto-login after successful registration
                $user = get_user_by('id', $user_id);
                wp_set_current_user($user_id, $user->user_login);
                wp_set_auth_cookie($user_id);
                do_action('wp_login', $user->user_login, $user);

                // Redirect to quiz page
                wp_redirect(home_url('/silverscore_quiz'));
                exit;
            } else {
                echo '<p style="color:red;">Error creating user.</p>';
            }
        }
    }

    return '<form method="post">
                <input type="text" name="username" placeholder="Username" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="submit" name="silverscore_register" value="Register">
            </form>';
}

add_shortcode('silverscore_register', 'silverscore_register_form');

function silverscore_login_form() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['silverscore_login'])) {
        $username = sanitize_text_field($_POST['username']);
        $password = sanitize_text_field($_POST['password']);
        $creds = [
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => true
        ];

        $user = wp_signon($creds, false);
        if (is_wp_error($user)) {
            echo '<p style="color:red;">Invalid login credentials.</p>';
        } else {
            wp_redirect(home_url('/silverscore_quiz'));
            exit;
        }
    }

    return '<form method="post">
                <input type="text" name="username" placeholder="Username or Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="submit" name="silverscore_login" value="Login">
            </form>';
}

add_shortcode('silverscore_login', 'silverscore_login_form');
