<?php
/**
 * BookNest - User Registration API Handler
 * College Project: BookNest Online Bookstore
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    setFlash('error', 'Method not allowed.');
    header('Location: ../../register.php');
    exit;
}

// 1. Verify CSRF Token
$token = $_POST['csrf_token'] ?? '';
if (!verifyCSRFToken($token)) {
    setFlash('error', 'Invalid security token. Please try submitting the form again.');
    header('Location: ../../register.php');
    exit;
}

// 2. Extract and Sanitize Inputs
$name     = trim($_POST['name'] ?? '');
$email    = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$phone    = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$confirm  = $_POST['confirm_password'] ?? '';

// 3. Validation
$errors = [];

if (empty($name) || strlen($name) < 2) {
    $errors[] = 'Please enter your full name (at least 2 characters).';
}

if (!$email) {
    $errors[] = 'Please provide a valid email address.';
}

if (strlen($password) < 6) {
    $errors[] = 'Password must be at least 6 characters long.';
}

if (!empty($confirm) && $password !== $confirm) {
    $errors[] = 'Passwords do not match.';
}

if (!empty($errors)) {
    setFlash('error', implode(' ', $errors));
    header('Location: ../../register.php');
    exit;
}

// 4. Database Check & Insertion
try {
    $pdo = Database::getConnection();
    if (!$pdo) {
        throw new Exception("Database connection unavailable.");
    }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        setFlash('error', 'An account with this email address already exists. Please log in.');
        header('Location: ../../login.php');
        exit;
    }

    // Hash password with BCrypt
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

    // Insert user (default role: 'user', status: 'active')
    $insertStmt = $pdo->prepare("
        INSERT INTO users (name, email, password, role, phone, status, created_at, updated_at)
        VALUES (?, ?, ?, 'user', ?, 'active', NOW(), NOW())
    ");
    $insertStmt->execute([$name, $email, $hashedPassword, $phone ?: null]);
    $userId = (int)$pdo->lastInsertId();

    // Create shopping cart entry for the new user
    $cartStmt = $pdo->prepare("INSERT INTO cart (user_id, created_at, updated_at) VALUES (?, NOW(), NOW())");
    $cartStmt->execute([$userId]);

    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);

    // Set authenticated session
    $_SESSION['user'] = [
        'id'    => $userId,
        'name'  => $name,
        'email' => $email,
        'role'  => 'user',
        'phone' => $phone
    ];

    setFlash('success', "Welcome to BookNest, {$name}! Your account has been created.");
    header('Location: ../../index.php');
    exit;

} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    setFlash('error', 'Registration failed due to a server error. Please try again.');
    header('Location: ../../register.php');
    exit;
}
