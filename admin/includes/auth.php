<?php
/**
 * Seychelles International Cargo LLC - Admin Authentication Helper
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

function is_admin_logged_in() {
    return isset($_SESSION['admin_user_id']) && !empty($_SESSION['admin_user_id']);
}

function require_admin_login() {
    if (!is_admin_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function get_logged_admin_username() {
    return $_SESSION['admin_username'] ?? 'Admin';
}
