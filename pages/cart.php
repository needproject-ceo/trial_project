<?php
/**
 * Gauri Collections - Shopping Cart
 */
require_once __DIR__ . '/../config/database.php';

$sessionId = session_id();
$userId = $_SESSION['user_id'] ?? null;

// Fetch cart items
if ($userId) {
    $stmt = $pdo->prepare("SELECT c.*, p.name, p.price, p.sale_price, p.image, p.stock_quantity, p.slug
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ? AND p.is_active = 1
        ORDER BY c.created_at DESC");
    $stmt->execute([$userId]);
} else {
    $stmt = $pdo->prepare("SELECT c.*, p.name, p.price, p.sale_price, p.image, p.stock_quantity, p.slug
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.session_id = ? AND c.user_id IS NULL AND p.is_active = 1
        ORDER BY c.created_at DESC");
    $stmt->execute([$sessionId]);
}
$cartItems = $stmt->fetchAll();

// Calculate totals
$subtotal = 0;
foreach ($cartItems as $item) {
    $itemPrice = $item['sale_price'] ?: $item['price'];
    $subtotal += $itemPrice * $item['quantity'];
}

$taxRate = (float)getSetting('tax_rate', '18') / 100;
$shippingCharge = (float)getSetting('shipping_charge', '500');
$freeShippingAbove = (float)getSetting('free_shipping_above', '5000');
$shipping = ($subtotal >= $freeShippingAbove && $subtotal > 0) ? 0 : ($subtotal > 0 ? $shippingCharge : 0);
$tax = round($subtotal * $taxRate, 2);
$total = $subtotal + $tax + $shipping;

// Check for coupon in session
$discount = 0;
$couponCode = $_SESSION['coupon_code'] ?? '';
if ($couponCode && $subtotal > 0) {
    $cStmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1 AND (expires_at IS NULL OR expires_at > NOW()) AND (max_uses IS NULL OR used_count < max_uses)");
    $cStmt->execute([$couponCode]);
    $coupon = $cStmt->fetch();
    if ($coupon && $subtotal >= $coupon['min_order_amount']) {
        if ($coupon['discount_type'] === 'percentage') {
            $discount = round($subtotal * ($coupon['discount_value'] / 100), 2);
        } else {
            $discount = (float)$coupon['discount_value'];
        }
        $total = $subtotal + $tax + $shipping - $discount;
    } else {
        unset($_SESSION['coupon_code']);
        $couponCode = '';
    }
}

// Handle coupon apply/remove via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_coupon'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid request.');
    } else {
        $code = trim($_POST['coupon_code'] ?? '');
        if ($code === '') {
            setFlash('error', 'Please enter a coupon code.');
        } else {
            $cStmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1 AND (expires_at IS NULL OR expires_at > NOW()) AND (max_uses IS NULL OR used_count < max_uses)");
            $cStmt->execute([$code]);
            $coupon = $cStmt->fetch();
            if ($coupon && $subtotal >= $coupon['min_order_amount']) {
                $_SESSION['coupon_code'] = $code;
                setFlash('success', 'Coupon applied successfully!');
            } else {
                setFlash('error', 'Invalid or expired coupon code.');
            }
        }
    }
    redirect(SITE_URL . '/pages/cart.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_coupon'])) {
    if (verifyCsrf($_POST['csrf_token'] ?? '')) {
        unset($_SESSION['coupon_code']);
        setFlash('success', 'Coupon removed.');
    }
    redirect(SITE_URL . '/pages/cart.php');
}

$pageTitle = 'Shopping Cart - Gauri Collections';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumbs -->
<section class="breadcrumbs">
    <div class="container">
        <a href="<?php echo SITE_URL; ?>/index.php">Home</a>
        <span>/</span>
        <span>Shopping Cart</span>
    </div>
</section>

<section class="section">
    <div class="container">
        <h1 class="page-title">Shopping Cart</h1>

        <?php if (empty($cartItems)): ?>
            <div class="empty-state">
                <i class="fas fa-shopping-bag" style="font-size:64px;color:var(--text-light);margin-bottom:20px;"></i>
                <h3>Your cart is empty</h3>
                <p>Looks like you haven't added any divine masterpieces yet.</p>
                <a href="<?php echo SITE_URL; ?>/pages/products.php" class="btn btn-primary">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-layout">
                <div class="cart-items">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($cartItems as $item):
                            $itemPrice = $item['sale_price'] ?: $item['price'];
                            $itemTotal = $itemPrice * $item['quantity'];
                        ?>
                            <tr data-cart-id="<?php echo (int)$item['id']; ?>">
                                <td class="cart-product">
                                    <img src="<?php echo SITE_URL; ?>/assets/images/placeholder-statue.svg" alt="<?php echo e($item['name']); ?>">
                                    <div>
                                        <a href="<?php echo SITE_URL; ?>/pages/product_detail.php?id=<?php echo (int)$item['product_id']; ?>"><?php echo e($item['name']); ?></a>
                                    </div>
                                </td>
                                <td class="cart-price"><?php echo formatPrice($itemPrice); ?></td>
                                <td class="cart-quantity">
                                    <div class="qty-wrapper">
                                        <button type="button" class="qty-btn cart-qty-btn" data-action="decrease" data-cart-id="<?php echo (int)$item['id']; ?>">−</button>
                                        <input type="number" value="<?php echo (int)$item['quantity']; ?>" min="1" max="<?php echo (int)$item['stock_quantity']; ?>" class="qty-input cart-qty-input" data-cart-id="<?php echo (int)$item['id']; ?>">
                                        <button type="button" class="qty-btn cart-qty-btn" data-action="increase" data-cart-id="<?php echo (int)$item['id']; ?>">+</button>
                                    </div>
                                </td>
                                <td class="cart-subtotal"><?php echo formatPrice($itemTotal); ?></td>
                                <td class="cart-remove">
                                    <button class="btn-icon cart-remove-btn" data-cart-id="<?php echo (int)$item['id']; ?>" title="Remove"><i class="fas fa-trash-alt"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="cart-summary">
                    <h3>Order Summary</h3>
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
                        <span>Discount</span>
                        <span>-<?php echo formatPrice($discount); ?></span>
                    </div>
                    <?php endif; ?>
                    <hr>
                    <div class="summary-row summary-total">
                        <strong>Total</strong>
                        <strong><?php echo formatPrice($total); ?></strong>
                    </div>

                    <!-- Coupon -->
                    <div class="coupon-section">
                        <?php if ($couponCode): ?>
                            <p class="coupon-applied"><i class="fas fa-tag"></i> Coupon: <strong><?php echo e($couponCode); ?></strong></p>
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
                                <button type="submit" name="remove_coupon" class="btn btn-sm btn-outline">Remove Coupon</button>
                            </form>
                        <?php else: ?>
                            <form method="POST" class="coupon-form">
                                <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
                                <input type="text" name="coupon_code" placeholder="Coupon code" required>
                                <button type="submit" name="apply_coupon" class="btn btn-sm btn-outline">Apply</button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <?php if ($subtotal < $freeShippingAbove && $shipping > 0): ?>
                    <p class="free-shipping-note"><i class="fas fa-truck"></i> Add <?php echo formatPrice($freeShippingAbove - $subtotal); ?> more for free shipping!</p>
                    <?php endif; ?>

                    <a href="<?php echo SITE_URL; ?>/pages/checkout.php" class="btn btn-primary btn-lg" style="width:100%;margin-top:15px;">
                        <i class="fas fa-lock"></i> Proceed to Checkout
                    </a>
                    <a href="<?php echo SITE_URL; ?>/pages/products.php" class="btn btn-outline" style="width:100%;margin-top:10px;">Continue Shopping</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const SITE_URL = '<?php echo SITE_URL; ?>';

    function getCSRFToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) return meta.getAttribute('content');
        const input = document.querySelector('input[name="csrf_token"]');
        return input ? input.value : '';
    }

    // Update quantity
    document.querySelectorAll('.cart-qty-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const cartId = this.dataset.cartId;
            const input = document.querySelector('.cart-qty-input[data-cart-id="' + cartId + '"]');
            if (!input) return;
            let val = parseInt(input.value, 10) || 1;
            const max = parseInt(input.max, 10) || 999;
            if (this.dataset.action === 'increase') val = Math.min(val + 1, max);
            else val = Math.max(val - 1, 1);
            input.value = val;
            updateCart(cartId, val);
        });
    });

    document.querySelectorAll('.cart-qty-input').forEach(function(input) {
        input.addEventListener('change', function() {
            const val = Math.max(1, parseInt(this.value, 10) || 1);
            this.value = val;
            updateCart(this.dataset.cartId, val);
        });
    });

    async function updateCart(cartId, quantity) {
        const formData = new FormData();
        formData.append('action', 'update');
        formData.append('cart_id', cartId);
        formData.append('quantity', quantity);
        formData.append('csrf_token', getCSRFToken());
        try {
            const res = await fetch(SITE_URL + '/pages/cart_actions.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                window.location.reload();
            } else {
                window.showToast(data.message || 'Error updating cart.', 'error');
            }
        } catch {
            window.showToast('Network error.', 'error');
        }
    }

    // Remove item
    document.querySelectorAll('.cart-remove-btn').forEach(function(btn) {
        btn.addEventListener('click', async function() {
            const cartId = this.dataset.cartId;
            const formData = new FormData();
            formData.append('action', 'remove');
            formData.append('cart_id', cartId);
            formData.append('csrf_token', getCSRFToken());
            try {
                const res = await fetch(SITE_URL + '/pages/cart_actions.php', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    window.showToast(data.message || 'Error removing item.', 'error');
                }
            } catch {
                window.showToast('Network error.', 'error');
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
