<?php
// /includes/auth.php

function validate_session(array $session): bool {
    // Basic session validation - customize these checks
    $required_keys = ['user_id', 'username', 'user_type', 'ip_address', 'user_agent'];
    
    foreach ($required_keys as $key) {
        if (!isset($session[$key])) {
            return false;
        }
    }

    // Verify IP hasn't changed
    if ($session['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
        return false;
    }

    // Verify browser hasn't changed
    if ($session['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        return false;
    }

    // Verify session isn't too old (30 minutes)
    if (isset($session['last_activity']) && (time() - $session['last_activity'] > 1800)) {
        return false;
    }

    return true;
}

function initialize_user_session(array $user): array {
    return [
        'user_id' => $user['id'],
        'username' => $user['username'],
        'user_type' => $user['user_type'],
        'ip_address' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        'last_activity' => time()
    ];
}