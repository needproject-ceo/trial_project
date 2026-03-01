<?php
/**
 * Gauri Collections - Cart AJAX Handler
 * Returns JSON responses only, no HTML.
 */
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$action = $_POST['action'] ?? '';

// get_count doesn't require CSRF
if ($action === 'get_count') {
    echo json_encode(['success' => true, 'count' => getCartCount()]);
    exit;
}

// Verify CSRF for all other actions
if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token. Please refresh the page.']);
    exit;
}

$sessionId = session_id();
$userId = $_SESSION['user_id'] ?? null;

switch ($action) {
    case 'add':
        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));

        if ($productId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product.']);
            exit;
        }

        // Check product exists and in stock
        $stmt = $pdo->prepare("SELECT id, stock_quantity, name FROM products WHERE id = ? AND is_active = 1");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();

        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found.']);
            exit;
        }
        if ($product['stock_quantity'] <= 0) {
            echo json_encode(['success' => false, 'message' => 'Product is out of stock.']);
            exit;
        }

        // Check if already in cart
        if ($userId) {
            $existStmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
            $existStmt->execute([$userId, $productId]);
        } else {
            $existStmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE session_id = ? AND user_id IS NULL AND product_id = ?");
            $existStmt->execute([$sessionId, $productId]);
        }
        $existing = $existStmt->fetch();

        if ($existing) {
            $newQty = min($existing['quantity'] + $quantity, $product['stock_quantity']);
            $pdo->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?")->execute([$newQty, $existing['id']]);
        } else {
            $qty = min($quantity, $product['stock_quantity']);
            $pdo->prepare("INSERT INTO cart (session_id, user_id, product_id, quantity) VALUES (?, ?, ?, ?)")
                ->execute([$sessionId, $userId, $productId, $qty]);
        }

        echo json_encode([
            'success' => true,
            'message' => e($product['name']) . ' added to cart!',
            'count' => getCartCount()
        ]);
        break;

    case 'update':
        $cartId = (int)($_POST['cart_id'] ?? 0);
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));

        if ($cartId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid cart item.']);
            exit;
        }

        // Verify ownership
        if ($userId) {
            $stmt = $pdo->prepare("SELECT c.id, p.stock_quantity FROM cart c JOIN products p ON c.product_id = p.id WHERE c.id = ? AND c.user_id = ?");
            $stmt->execute([$cartId, $userId]);
        } else {
            $stmt = $pdo->prepare("SELECT c.id, p.stock_quantity FROM cart c JOIN products p ON c.product_id = p.id WHERE c.id = ? AND c.session_id = ? AND c.user_id IS NULL");
            $stmt->execute([$cartId, $sessionId]);
        }
        $cartItem = $stmt->fetch();

        if (!$cartItem) {
            echo json_encode(['success' => false, 'message' => 'Cart item not found.']);
            exit;
        }

        $qty = min($quantity, $cartItem['stock_quantity']);
        $pdo->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?")->execute([$qty, $cartId]);

        echo json_encode([
            'success' => true,
            'message' => 'Cart updated.',
            'count' => getCartCount()
        ]);
        break;

    case 'remove':
        $cartId = (int)($_POST['cart_id'] ?? 0);

        if ($cartId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid cart item.']);
            exit;
        }

        if ($userId) {
            $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?")->execute([$cartId, $userId]);
        } else {
            $pdo->prepare("DELETE FROM cart WHERE id = ? AND session_id = ? AND user_id IS NULL")->execute([$cartId, $sessionId]);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Item removed from cart.',
            'count' => getCartCount()
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        break;
}
