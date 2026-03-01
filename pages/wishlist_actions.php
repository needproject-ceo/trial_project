<?php
/**
 * Gauri Collections - Wishlist AJAX Handler
 * Returns JSON responses only.
 */
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to manage your wishlist.']);
    exit;
}

if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token. Please refresh the page.']);
    exit;
}

$userId = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';
$productId = (int)($_POST['product_id'] ?? 0);

if ($productId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product.']);
    exit;
}

// Verify product exists
$stmt = $pdo->prepare("SELECT id, name FROM products WHERE id = ? AND is_active = 1");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found.']);
    exit;
}

switch ($action) {
    case 'add':
        // Check if already in wishlist
        $check = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
        $check->execute([$userId, $productId]);
        if ($check->fetch()) {
            echo json_encode(['success' => true, 'message' => 'Already in your wishlist.']);
        } else {
            $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)")
                ->execute([$userId, $productId]);
            echo json_encode(['success' => true, 'message' => e($product['name']) . ' added to wishlist!']);
        }
        break;

    case 'remove':
        $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?")
            ->execute([$userId, $productId]);
        echo json_encode(['success' => true, 'message' => 'Removed from wishlist.']);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        break;
}
