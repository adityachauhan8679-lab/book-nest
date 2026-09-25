<?php
/**
 * BookNest - User Authentication Middleware
 * Enforces user login before accessing protected pages
 */
require_once __DIR__ . '/functions.php';

if (!isLoggedIn()) {
    setFlash('error', 'Please log in to continue.');
    $redirect = urlencode($_SERVER['REQUEST_URI'] ?? 'index.php');
    header("Location: login.php?redirect={$redirect}");
    exit;
}
