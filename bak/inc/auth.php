<?php
// Session management is handled by the main index.php file.
// This file only contains functions.

// Correct path to the users file. It is now relative to this file.
define('USERS_FILE', __DIR__ . '/../data/users.json');

function get_all_users(): array {
    if (!file_exists(USERS_FILE)) {
        error_log("Users file not found: " . USERS_FILE);
        return [];
    }
    $json_content = file_get_contents(USERS_FILE);
    if ($json_content === false) {
        error_log("Failed to read users.json");
        return [];
    }
    $users = json_decode($json_content, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Error decoding users.json: " . json_last_error_msg());
        return [];
    }
    return is_array($users) ? $users : [];
}

function get_user_by_email(string $email): ?array {
    $users = get_all_users();
    return $users[$email] ?? null;
}

function authenticate_user(string $email, string $password): bool {
    $user = get_user_by_email($email);
    if ($user && password_verify($password, $user['password'])) {
        return true;
    }
    return false;
}

function is_logged_in(): bool {
    return isset($_SESSION['email']);
}

function is_admin(): bool {
    if (!is_logged_in()) {
        return false;
    }
    $email = $_SESSION['email'];
    $user = get_user_by_email($email);
    return ($user && ($user['admin'] ?? false));
}