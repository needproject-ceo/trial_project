<?php
/**
 * Gauri Collections - Order Confirmation Page
 */
require_once __DIR__ . '/../config/database.php';

if (!isLoggedIn()) {
    redirect(SITE_URL . '/pages/login.php');
}

$orderNumber = trim($_GET['order'] ?? '');
if ($orderNumber === '') {
    redirect(SITE_URL . '/pages/account.php');
}

// Fetch order
$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ? AND user_id = ?");
$stmt->execute([$orderNumber, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    setFlash('error', 'Order not found.');
    redirect(SITE_URL . '/pages/account.php');
}

// Fetch order items
$itemStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$itemStmt->execute([$order['id']]);
$orderItems = $itemStmt->fetchAll();

$pageTitle = 'Order Confirmation - Gauri Collections';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section">
    <div class="container">
        <div class="order-confirmation">
            <div class="confirmation-header">
                <i class="fas fa-check-circle" style="font-size:64px;color:#28a745;"></i>
                <h1>Thank You for Your Order!</h1>
                <p>Your order has been placed successfully. We will process it shortly.</p>
            </div>

            <div class="order-details-card">
                <h3>Order Details</h3>
                <div class="order-meta">
                    <div class="meta-item">
                        <span class="meta-label">Order Number</span>
                        <span class="meta-value"><?php echo e($order['order_number']); ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Date</span>
                        <span class="meta-value"><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Status</span>
                        <span class="meta-value badge badge-status"><?php echo ucfirst(e($order['status'])); ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Payment</span>
                        <span class="meta-value"><?php echo $order['payment_method'] === 'cod' ? 'Cash on Delivery' : e($order['payment_method']); ?></span>
                    </div>
                </div>

                <h4>Items Ordered</h4>
                <table class="order-items-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orderItems as $item): ?>
                        <tr>
                            <td><?php echo e($item['product_name']); ?></td>
                            <td><?php echo (int)$item['quantity']; ?></td>
                            <td><?php echo formatPrice($item['price']); ?></td>
                            <td><?php echo formatPrice($item['total']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="order-totals">
                    <div class="summary-row"><span>Subtotal</span><span><?php echo formatPrice($order['subtotal']); ?></span></div>
                    <div class="summary-row"><span>Tax</span><span><?php echo formatPrice($order['tax_amount']); ?></span></div>
                    <div class="summary-row"><span>Shipping</span><span><?php echo $order['shipping_amount'] > 0 ? formatPrice($order['shipping_amount']) : 'Free'; ?></span></div>
                    <?php if ($order['discount_amount'] > 0): ?>
                    <div class="summary-row text-success"><span>Discount</span><span>-<?php echo formatPrice($order['discount_amount']); ?></span></div>
                    <?php endif; ?>
                    <hr>
                    <div class="summary-row summary-total"><strong>Total</strong><strong><?php echo formatPrice($order['total_amount']); ?></strong></div>
                </div>

                <h4>Shipping Address</h4>
                <p>
                    <?php echo e($order['shipping_name']); ?><br>
                    <?php echo e($order['shipping_address']); ?><br>
                    <?php echo e($order['shipping_city']); ?>, <?php echo e($order['shipping_state']); ?> - <?php echo e($order['shipping_pincode']); ?><br>
                    Phone: <?php echo e($order['shipping_phone']); ?><br>
                    Email: <?php echo e($order['shipping_email']); ?>
                </p>
            </div>

            <div class="confirmation-actions" style="text-align:center;margin-top:30px;">
                <a href="<?php echo SITE_URL; ?>/pages/account.php" class="btn btn-primary">View My Orders</a>
                <a href="<?php echo SITE_URL; ?>/pages/products.php" class="btn btn-outline">Continue Shopping</a>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
