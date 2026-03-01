<?php
/**
 * Gauri Collections - Checkout Page
 */
require_once __DIR__ . '/../config/database.php';

// Require login
if (!isLoggedIn()) {
    setFlash('error', 'Please login to proceed with checkout.');
    $_SESSION['redirect_after_login'] = SITE_URL . '/pages/checkout.php';
    redirect(SITE_URL . '/pages/login.php');
}

$userId = $_SESSION['user_id'];

// Fetch cart items
$stmt = $pdo->prepare("SELECT c.*, p.name, p.price, p.sale_price, p.stock_quantity, p.image
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ? AND p.is_active = 1
    ORDER BY c.created_at DESC");
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll();

if (empty($cartItems)) {
    setFlash('error', 'Your cart is empty.');
    redirect(SITE_URL . '/pages/cart.php');
}

// Calculate totals
$subtotal = 0;
foreach ($cartItems as $item) {
    $itemPrice = $item['sale_price'] ?: $item['price'];
    $subtotal += $itemPrice * $item['quantity'];
}

$taxRate = (float)getSetting('tax_rate', '18') / 100;
$shippingCharge = (float)getSetting('shipping_charge', '500');
$freeShippingAbove = (float)getSetting('free_shipping_above', '5000');
$shipping = ($subtotal >= $freeShippingAbove) ? 0 : $shippingCharge;
$tax = round($subtotal * $taxRate, 2);
$discount = 0;

$couponCode = $_SESSION['coupon_code'] ?? '';
if ($couponCode) {
    $cStmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1 AND (expires_at IS NULL OR expires_at > NOW()) AND (max_uses IS NULL OR used_count < max_uses)");
    $cStmt->execute([$couponCode]);
    $coupon = $cStmt->fetch();
    if ($coupon && $subtotal >= $coupon['min_order_amount']) {
        $discount = $coupon['discount_type'] === 'percentage'
            ? round($subtotal * ($coupon['discount_value'] / 100), 2)
            : (float)$coupon['discount_value'];
    } else {
        unset($_SESSION['coupon_code']);
        $couponCode = '';
    }
}

$total = $subtotal + $tax + $shipping - $discount;

// Fetch user info for pre-fill
$userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->execute([$userId]);
$user = $userStmt->fetch();

// Process order
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        $shippingName = trim($_POST['shipping_name'] ?? '');
        $shippingEmail = trim($_POST['shipping_email'] ?? '');
        $shippingPhone = trim($_POST['shipping_phone'] ?? '');
        $shippingAddress = trim($_POST['shipping_address'] ?? '');
        $shippingCity = trim($_POST['shipping_city'] ?? '');
        $shippingState = trim($_POST['shipping_state'] ?? '');
        $shippingPincode = trim($_POST['shipping_pincode'] ?? '');
        $paymentMethod = $_POST['payment_method'] ?? 'cod';
        $notes = trim($_POST['notes'] ?? '');

        // Validate
        if ($shippingName === '') $errors[] = 'Full name is required.';
        if ($shippingEmail === '' || !filter_var($shippingEmail, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
        if ($shippingPhone === '') $errors[] = 'Phone number is required.';
        if ($shippingAddress === '') $errors[] = 'Address is required.';
        if ($shippingCity === '') $errors[] = 'City is required.';
        if ($shippingState === '') $errors[] = 'State is required.';
        if ($shippingPincode === '') $errors[] = 'Pincode is required.';

        // Verify stock
        foreach ($cartItems as $item) {
            if ($item['quantity'] > $item['stock_quantity']) {
                $errors[] = e($item['name']) . ' has only ' . $item['stock_quantity'] . ' in stock.';
            }
        }

        if (empty($errors)) {
            try {
                $pdo->beginTransaction();

                $orderNumber = generateOrderNumber();

                // Create order
                $orderStmt = $pdo->prepare("INSERT INTO orders (user_id, order_number, subtotal, tax_amount, shipping_amount, discount_amount, total_amount, status, payment_method, payment_status, shipping_name, shipping_email, shipping_phone, shipping_address, shipping_city, shipping_state, shipping_pincode, notes) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?, 'pending', ?, ?, ?, ?, ?, ?, ?, ?)");
                $orderStmt->execute([
                    $userId, $orderNumber, $subtotal, $tax, $shipping, $discount, $total,
                    $paymentMethod, $shippingName, $shippingEmail, $shippingPhone,
                    $shippingAddress, $shippingCity, $shippingState, $shippingPincode, $notes
                ]);
                $orderId = $pdo->lastInsertId();

                // Create order items and update stock
                $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price, total) VALUES (?, ?, ?, ?, ?, ?)");
                $stockStmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ? AND stock_quantity >= ?");

                foreach ($cartItems as $item) {
                    $itemPrice = $item['sale_price'] ?: $item['price'];
                    $itemTotal = $itemPrice * $item['quantity'];
                    $itemStmt->execute([$orderId, $item['product_id'], $item['name'], $item['quantity'], $itemPrice, $itemTotal]);
                    $stockStmt->execute([$item['quantity'], $item['product_id'], $item['quantity']]);
                }

                // Update coupon usage
                if ($couponCode && $discount > 0) {
                    $pdo->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE code = ?")->execute([$couponCode]);
                    unset($_SESSION['coupon_code']);
                }

                // Clear cart
                $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$userId]);

                $pdo->commit();

                setFlash('success', 'Order placed successfully!');
                redirect(SITE_URL . '/pages/order_confirmation.php?order=' . urlencode($orderNumber));

            } catch (PDOException $e) {
                $pdo->rollBack();
                $errors[] = 'An error occurred while placing your order. Please try again.';
            }
        }
    }
}

$pageTitle = 'Checkout - Gauri Collections';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumbs -->
<section class="breadcrumbs">
    <div class="container">
        <a href="<?php echo SITE_URL; ?>/index.php">Home</a>
        <span>/</span>
        <a href="<?php echo SITE_URL; ?>/pages/cart.php">Cart</a>
        <span>/</span>
        <span>Checkout</span>
    </div>
</section>

<section class="section">
    <div class="container">
        <h1 class="page-title">Checkout</h1>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $err): ?>
                <li><?php echo e($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form method="POST" id="checkoutForm" class="checkout-layout">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">

            <!-- Shipping Details -->
            <div class="checkout-form">
                <h3>Shipping Details</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="shipping_name">Full Name *</label>
                        <input type="text" id="shipping_name" name="shipping_name" required
                            value="<?php echo e($_POST['shipping_name'] ?? ($user['first_name'] . ' ' . $user['last_name'])); ?>">
                    </div>
                    <div class="form-group">
                        <label for="shipping_email">Email *</label>
                        <input type="email" id="shipping_email" name="shipping_email" required
                            value="<?php echo e($_POST['shipping_email'] ?? $user['email']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="shipping_phone">Phone *</label>
                        <input type="tel" id="shipping_phone" name="shipping_phone" required
                            value="<?php echo e($_POST['shipping_phone'] ?? $user['phone']); ?>">
                    </div>
                    <div class="form-group form-group-full">
                        <label for="shipping_address">Address *</label>
                        <textarea id="shipping_address" name="shipping_address" rows="3" required><?php echo e($_POST['shipping_address'] ?? $user['address']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="shipping_city">City *</label>
                        <input type="text" id="shipping_city" name="shipping_city" required
                            value="<?php echo e($_POST['shipping_city'] ?? $user['city']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="shipping_state">State *</label>
                        <input type="text" id="shipping_state" name="shipping_state" required
                            value="<?php echo e($_POST['shipping_state'] ?? $user['state']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="shipping_pincode">Pincode *</label>
                        <input type="text" id="shipping_pincode" name="shipping_pincode" required
                            value="<?php echo e($_POST['shipping_pincode'] ?? $user['pincode']); ?>">
                    </div>
                    <div class="form-group form-group-full">
                        <label for="notes">Order Notes (Optional)</label>
                        <textarea id="notes" name="notes" rows="2" placeholder="Any special instructions..."><?php echo e($_POST['notes'] ?? ''); ?></textarea>
                    </div>
                </div>

                <h3>Payment Method</h3>
                <div class="payment-methods">
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="cod" checked>
                        <span><i class="fas fa-money-bill-wave"></i> Cash on Delivery (COD)</span>
                    </label>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="cart-summary">
                <h3>Order Summary</h3>
                <?php foreach ($cartItems as $item):
                    $itemPrice = $item['sale_price'] ?: $item['price'];
                ?>
                <div class="summary-item">
                    <span><?php echo e($item['name']); ?> × <?php echo (int)$item['quantity']; ?></span>
                    <span><?php echo formatPrice($itemPrice * $item['quantity']); ?></span>
                </div>
                <?php endforeach; ?>
                <hr>
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span><?php echo formatPrice($subtotal); ?></span>
                </div>
                <div class="summary-row">
                    <span>Tax (<?php echo (int)($taxRate * 100); ?>%)</span>
                    <span><?php echo formatPrice($tax); ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span><?php echo $shipping > 0 ? formatPrice($shipping) : '<span class="text-success">Free</span>'; ?></span>
                </div>
                <?php if ($discount > 0): ?>
                <div class="summary-row text-success">
                    <span>Discount (<?php echo e($couponCode); ?>)</span>
                    <span>-<?php echo formatPrice($discount); ?></span>
                </div>
                <?php endif; ?>
                <hr>
                <div class="summary-row summary-total">
                    <strong>Total</strong>
                    <strong><?php echo formatPrice($total); ?></strong>
                </div>
                <button type="submit" class="btn btn-primary btn-lg" style="width:100%;margin-top:15px;">
                    <i class="fas fa-check"></i> Place Order
                </button>
            </div>
        </form>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
