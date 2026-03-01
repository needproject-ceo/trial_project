<?php
/**
 * Gauri Collections - Newsletter AJAX Handler
 * Returns JSON responses only.
 */
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$email = trim($_POST['email'] ?? '');

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

try {
    // Check if already subscribed
    $stmt = $pdo->prepare("SELECT id, is_active FROM newsletter WHERE email = ?");
    $stmt->execute([$email]);
    $existing = $stmt->fetch();

    if ($existing) {
        if ($existing['is_active']) {
            echo json_encode(['success' => true, 'message' => 'You are already subscribed!']);
        } else {
            // Re-activate
            $pdo->prepare("UPDATE newsletter SET is_active = 1 WHERE id = ?")->execute([$existing['id']]);
            echo json_encode(['success' => true, 'message' => 'Welcome back! Your subscription has been reactivated.']);
        }
    } else {
        $pdo->prepare("INSERT INTO newsletter (email) VALUES (?)")->execute([$email]);
        echo json_encode(['success' => true, 'message' => 'Thank you for subscribing to our newsletter!']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later.']);
}
