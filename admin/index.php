<?php
/**
 * Gauri Collections - Admin Dashboard
 */
$pageTitle = 'Dashboard';
require_once __DIR__ . '/header.php';

// Gather stats
try {
    $totalProducts = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $totalOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $totalRevenue = (float)$pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE payment_status = 'paid'")->fetchColumn();
    $totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
    $pendingOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
    $lowStock = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= 2 AND is_active = 1")->fetchColumn();
    $unreadMessages = (int)$pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0")->fetchColumn();

    $recentOrders = $pdo->query("SELECT o.*, u.first_name, u.last_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 10")->fetchAll();
    $recentMessages = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5")->fetchAll();
} catch (PDOException $ex) {
    $totalProducts = $totalOrders = $totalRevenue = $totalUsers = $pendingOrders = $lowStock = $unreadMessages = 0;
    $recentOrders = $recentMessages = [];
}
?>

<!-- Stats Cards -->
<div class="admin-stats-grid">
    <div class="admin-stat-card">
        <div class="admin-stat-icon"><i class="fas fa-box-open"></i></div>
        <div class="admin-stat-info">
            <h3><?php echo $totalProducts; ?></h3>
            <p>Total Products</p>
        </div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-icon"><i class="fas fa-shopping-cart"></i></div>
        <div class="admin-stat-info">
            <h3><?php echo $totalOrders; ?></h3>
            <p>Total Orders</p>
        </div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-icon"><i class="fas fa-rupee-sign"></i></div>
        <div class="admin-stat-info">
            <h3><?php echo formatPrice($totalRevenue); ?></h3>
            <p>Total Revenue</p>
        </div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-icon"><i class="fas fa-users"></i></div>
        <div class="admin-stat-info">
            <h3><?php echo $totalUsers; ?></h3>
            <p>Total Users</p>
        </div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-icon"><i class="fas fa-clock"></i></div>
        <div class="admin-stat-info">
            <h3><?php echo $pendingOrders; ?></h3>
            <p>Pending Orders</p>
        </div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="admin-stat-info">
            <h3><?php echo $lowStock; ?></h3>
            <p>Low Stock Products</p>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="admin-section">
    <h3 class="admin-section-title">Quick Actions</h3>
    <div class="admin-quick-actions">
        <a href="<?php echo SITE_URL; ?>/admin/product_form.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Product</a>
        <a href="<?php echo SITE_URL; ?>/admin/orders.php" class="btn btn-outline"><i class="fas fa-list"></i> View Orders</a>
        <a href="<?php echo SITE_URL; ?>/admin/messages.php" class="btn btn-outline"><i class="fas fa-envelope"></i> Messages <?php if ($unreadMessages > 0): ?><span class="badge"><?php echo $unreadMessages; ?></span><?php endif; ?></a>
        <a href="<?php echo SITE_URL; ?>/admin/categories.php" class="btn btn-outline"><i class="fas fa-tags"></i> Categories</a>
    </div>
</div>

<!-- Recent Orders -->
<div class="admin-section">
    <h3 class="admin-section-title">Recent Orders</h3>
    <div class="admin-table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentOrders)): ?>
                <tr><td colspan="7" class="text-center">No orders yet.</td></tr>
                <?php else: ?>
                <?php foreach ($recentOrders as $order): ?>
                <tr>
                    <td><?php echo e($order['order_number']); ?></td>
                    <td><?php echo e(($order['first_name'] ?? 'Guest') . ' ' . ($order['last_name'] ?? '')); ?></td>
                    <td><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                    <td><?php echo formatPrice($order['total_amount']); ?></td>
                    <td><span class="status-badge status-<?php echo e($order['status']); ?>"><?php echo ucfirst(e($order['status'])); ?></span></td>
                    <td><span class="status-badge status-<?php echo e($order['payment_status']); ?>"><?php echo ucfirst(e($order['payment_status'])); ?></span></td>
                    <td><a href="<?php echo SITE_URL; ?>/admin/order_detail.php?id=<?php echo (int)$order['id']; ?>" class="btn btn-sm btn-outline">View</a></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Recent Messages -->
<div class="admin-section">
    <h3 class="admin-section-title">Recent Messages <span class="badge"><?php echo $unreadMessages; ?> unread</span></h3>
    <div class="admin-table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Subject</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentMessages)): ?>
                <tr><td colspan="4" class="text-center">No messages.</td></tr>
                <?php else: ?>
                <?php foreach ($recentMessages as $msg): ?>
                <tr>
                    <td><?php echo e($msg['name']); ?></td>
                    <td><?php echo e($msg['subject']); ?></td>
                    <td><?php echo date('d M Y', strtotime($msg['created_at'])); ?></td>
                    <td><?php echo $msg['is_read'] ? '<span class="status-badge status-delivered">Read</span>' : '<span class="status-badge status-pending">Unread</span>'; ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
