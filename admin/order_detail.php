<?php
/**
 * Gauri Collections - Admin Order Detail
 */
require_once __DIR__ . '/../config/database.php';

if (!isAdmin()) {
    redirect(SITE_URL . '/pages/login.php');
}

$orderId = (int)($_GET['id'] ?? 0);
if ($orderId <= 0) {
    setFlash('error', 'Invalid order ID.');
    redirect(SITE_URL . '/admin/orders.php');
}

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
    } else {
        $validStatuses = ['pending','confirmed','processing','shipped','delivered','cancelled','refunded'];
        $validPayment = ['pending','paid','failed','refunded'];

        if (isset($_POST['update_status'])) {
            $newStatus = $_POST['status'] ?? '';
            if (in_array($newStatus, $validStatuses)) {
                $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
                $stmt->execute([$newStatus, $orderId]);
                setFlash('success', 'Order status updated to ' . ucfirst($newStatus) . '.');
            }
        }
        if (isset($_POST['update_payment'])) {
            $newPayment = $_POST['payment_status'] ?? '';
            if (in_array($newPayment, $validPayment)) {
                $stmt = $pdo->prepare("UPDATE orders SET payment_status = ? WHERE id = ?");
                $stmt->execute([$newPayment, $orderId]);
                setFlash('success', 'Payment status updated.');
            }
        }
    }
    redirect(SITE_URL . '/admin/order_detail.php?id=' . $orderId);
}

// Load order
try {
    $stmt = $pdo->prepare("SELECT o.*, u.first_name, u.last_name, u.email, u.phone AS user_phone FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    if (!$order) {
        setFlash('error', 'Order not found.');
        redirect(SITE_URL . '/admin/orders.php');
    }

    $itemStmt = $pdo->prepare("SELECT oi.*, p.image FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
    $itemStmt->execute([$orderId]);
    $items = $itemStmt->fetchAll();
} catch (PDOException $ex) {
    setFlash('error', 'Database error.');
    redirect(SITE_URL . '/admin/orders.php');
}

$pageTitle = 'Order #' . e($order['order_number']);
require_once __DIR__ . '/header.php';

$statuses = ['pending','confirmed','processing','shipped','delivered','cancelled','refunded'];
$paymentStatuses = ['pending','paid','failed','refunded'];
?>

<a href="<?php echo SITE_URL; ?>/admin/orders.php" class="btn btn-outline btn-sm" style="margin-bottom:1rem;"><i class="fas fa-arrow-left"></i> Back to Orders</a>

<div class="admin-grid-two">
    <!-- Order Info -->
    <div class="admin-section">
        <h3 class="admin-section-title">Order Information</h3>
        <table class="admin-detail-table">
            <tr><th>Order Number</th><td><?php echo e($order['order_number']); ?></td></tr>
            <tr><th>Date</th><td><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></td></tr>
            <tr><th>Status</th><td><span class="status-badge status-<?php echo e($order['status']); ?>"><?php echo ucfirst(e($order['status'])); ?></span></td></tr>
            <tr><th>Payment Method</th><td><?php echo e($order['payment_method'] ?? '—'); ?></td></tr>
            <tr><th>Payment Status</th><td><span class="status-badge status-<?php echo e($order['payment_status']); ?>"><?php echo ucfirst(e($order['payment_status'])); ?></span></td></tr>
            <tr><th>Transaction ID</th><td><?php echo e($order['transaction_id'] ?? '—'); ?></td></tr>
            <?php if ($order['notes']): ?>
            <tr><th>Notes</th><td><?php echo e($order['notes']); ?></td></tr>
            <?php endif; ?>
        </table>

        <!-- Update Status -->
        <div style="margin-top:1rem;display:flex;gap:1rem;flex-wrap:wrap;">
            <form method="post" style="display:flex;gap:0.5rem;align-items:center;">
                <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
                <input type="hidden" name="update_status" value="1">
                <select name="status" class="form-control">
                    <?php foreach ($statuses as $s): ?>
                    <option value="<?php echo $s; ?>" <?php echo $order['status'] === $s ? 'selected' : ''; ?>><?php echo ucfirst($s); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-sm btn-primary">Update Status</button>
            </form>
            <form method="post" style="display:flex;gap:0.5rem;align-items:center;">
                <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
                <input type="hidden" name="update_payment" value="1">
                <select name="payment_status" class="form-control">
                    <?php foreach ($paymentStatuses as $ps): ?>
                    <option value="<?php echo $ps; ?>" <?php echo $order['payment_status'] === $ps ? 'selected' : ''; ?>><?php echo ucfirst($ps); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-sm btn-primary">Update Payment</button>
            </form>
        </div>
    </div>

    <!-- Shipping Info -->
    <div class="admin-section">
        <h3 class="admin-section-title">Shipping Details</h3>
        <table class="admin-detail-table">
            <tr><th>Name</th><td><?php echo e($order['shipping_name']); ?></td></tr>
            <tr><th>Email</th><td><?php echo e($order['shipping_email']); ?></td></tr>
            <tr><th>Phone</th><td><?php echo e($order['shipping_phone']); ?></td></tr>
            <tr><th>Address</th><td><?php echo e($order['shipping_address']); ?></td></tr>
            <tr><th>City</th><td><?php echo e($order['shipping_city']); ?></td></tr>
            <tr><th>State</th><td><?php echo e($order['shipping_state']); ?></td></tr>
            <tr><th>Pincode</th><td><?php echo e($order['shipping_pincode']); ?></td></tr>
        </table>
        <?php if ($order['first_name']): ?>
        <h4 style="margin-top:1rem;">Customer Account</h4>
        <p><?php echo e($order['first_name'] . ' ' . $order['last_name']); ?> — <?php echo e($order['email']); ?></p>
        <?php endif; ?>
    </div>
</div>

<!-- Order Items -->
<div class="admin-section" style="margin-top:1.5rem;">
    <h3 class="admin-section-title">Order Items</h3>
    <div class="admin-table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td>
                        <?php if ($item['image']): ?>
                        <img src="<?php echo UPLOAD_URL . e($item['image']); ?>" alt="" class="admin-thumb">
                        <?php else: ?>
                        <span class="admin-no-image"><i class="fas fa-image"></i></span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo e($item['product_name']); ?></td>
                    <td><?php echo formatPrice($item['price']); ?></td>
                    <td><?php echo (int)$item['quantity']; ?></td>
                    <td><?php echo formatPrice($item['total']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr><th colspan="4" style="text-align:right;">Subtotal</th><td><?php echo formatPrice($order['subtotal']); ?></td></tr>
                <tr><th colspan="4" style="text-align:right;">Tax</th><td><?php echo formatPrice($order['tax_amount']); ?></td></tr>
                <tr><th colspan="4" style="text-align:right;">Shipping</th><td><?php echo formatPrice($order['shipping_amount']); ?></td></tr>
                <?php if ($order['discount_amount'] > 0): ?>
                <tr><th colspan="4" style="text-align:right;">Discount</th><td>-<?php echo formatPrice($order['discount_amount']); ?></td></tr>
                <?php endif; ?>
                <tr><th colspan="4" style="text-align:right;"><strong>Total</strong></th><td><strong><?php echo formatPrice($order['total_amount']); ?></strong></td></tr>
            </tfoot>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
