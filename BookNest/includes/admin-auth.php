<?php
/**
 * BookNest - Administrator Authorization Middleware
 * Verifies admin privilege; rejects non-admins with 403 Forbidden
 */
require_once __DIR__ . '/functions.php';

if (!isAdmin()) {
    http_response_code(403);
    setFlash('error', 'Access denied. Administrator privileges required.');
    header("Location: ../login.php");
    exit;
}
