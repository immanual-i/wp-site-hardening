<?php
function wpsh_dictionary()
{
    static $words = null;

    if ($words === null) {
        $file = plugin_dir_path(__FILE__) . 'assets/password-dictionary.txt';
        if (file_exists($file)) {
            $words = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        } else {
            $words = [];
        }
    }

    return $words;
}

function wpsh_password_policy($password, $user = null)
{
    $errors = new WP_Error();

    // Require a baseline mix of length and character classes.
    if (strlen($password) < 12) {
        $errors->add('password_length', __('Password must be at least 12 characters long.'));
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors->add('password_uppercase', __('Password must include at least one uppercase letter.'));
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors->add('password_lowercase', __('Password must include at least one lowercase letter.'));
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors->add('password_number', __('Password must include at least one number.'));
    }
    if (!preg_match('/[\W]/', $password)) {
        $errors->add('password_special', __('Password must include at least one special character.'));
    }

    // Prevent passwords that include the username or email prefix.
    if ($user instanceof WP_User) {
        $username = $user->user_login;
        $email_name = explode('@', $user->user_email)[0];

        if (stripos($password, $username) !== false || stripos($password, $email_name) !== false) {
            $errors->add('password_personal', __('Password cannot contain your username or email.'));
        }
    }

    // Reject common passwords and obvious password patterns.
    foreach (wpsh_dictionary() as $word) {
        // Skip short words to avoid excessive false positives.
        if (strlen($word) < 4) continue;
        if (stripos($password, $word) !== false) {
            $errors->add(
                'password_dictionary',
                __('Password cannot contain common words or common password patterns.')
            );
            break;
        }
    }

    return $errors;
}

// Validate passwords during user registration.
add_filter('registration_errors', function ($errors, $sanitized_user_login, $user_email) {
    if (isset($_POST['password'])) {
        $password = $_POST['password'];

        $dummy_user = new stdClass();
        $dummy_user->user_login = $sanitized_user_login;
        $dummy_user->user_email = $user_email;

        $password_errors = wpsh_password_policy($password, $dummy_user);

        if ($password_errors->has_errors()) {
            foreach ($password_errors->get_error_messages() as $message) {
                $errors->add('password_error', $message);
            }
        }
    }

    return $errors;
}, 10, 3);

// Validate passwords during password resets.
add_action('validate_password_reset', function ($errors, $user) {
    if (isset($_POST['pass1'])) {
        $password = $_POST['pass1'];

        $password_errors = wpsh_password_policy($password, $user);

        if ($password_errors->has_errors()) {
            foreach ($password_errors->get_error_messages() as $message) {
                $errors->add('password_error', $message);
            }
        }
    }
}, 10, 2);


// Validate passwords changed from user profile screens.
add_action('user_profile_update_errors', function ($errors, $update, $user) {

    if (isset($_POST['pass1']) && !empty($_POST['pass1'])) {
        $password = $_POST['pass1'];

        $password_errors = wpsh_password_policy($password, $user);

        if ($password_errors->has_errors()) {
            foreach ($password_errors->get_error_messages() as $message) {
                $errors->add('password_error', $message);
            }
        }
    }
}, 10, 3);
