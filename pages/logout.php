<?php
/**
 * Gauri Collections - Logout Handler
 */
require_once __DIR__ . '/../config/database.php';

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

session_destroy();

// Start a new session for flash message
session_start();
setFlash('success', 'You have been logged out successfully.');
redirect(SITE_URL . '/index.php');
