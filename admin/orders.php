<?php
/**
 * Gauri Collections - Admin Orders Listing
 */
$pageTitle = 'Orders';
require_once __DIR__ . '/header.php';

// Filters
$statusFilter = $_GET['status'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

$where = [];
$params = [];

if ($statusFilter !== '') {
    $where[] = "o.status = ?";
    $params[] = $statusFilter;
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

try {
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM orders o $whereSQL");
    $countStmt->execute($params);
    $totalOrders = (int)$countStmt->fetchColumn();
    $totalPages = max(1, ceil($totalOrders / $perPage));

    $params[] = $perPage;
    $params[] = $offset;
    $stmt = $pdo->prepare("SELECT o.*, u.first_name, u.last_name, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.id $whereSQL ORDER BY o.created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
} catch (PDOException $ex) {
    $orders = [];
    $totalPages = 1;
}

$statuses = ['pending','confirmed','processing','shipped','delivered','cancelled','refunded'];
?>

<div class="admin-toolbar">
    <form method="get" class="admin-filter-form">
        <select name="status" class="form-control">
            <option value="">All Statuses</option>
            <?php foreach ($statuses as $s): ?>
            <option value="<?php echo $s; ?>" <?php echo $statusFilter === $s ? 'selected' : ''; ?>><?php echo ucfirst($s); ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-outline"><i class="fas fa-filter"></i> Filter</button>
    </form>
</div>

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
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($orders)): ?>
            <tr><td colspan="7" class="text-center">No orders found.</td></tr>
            <?php else: ?>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td><strong><?php echo e($order['order_number']); ?></strong></td>
                <td>
                    <?php echo e(($order['first_name'] ?? 'Guest') . ' ' . ($order['last_name'] ?? '')); ?>
                    <br><small><?php echo e($order['email'] ?? ''); ?></small>
                </td>
                <td><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></td>
                <td><?php echo formatPrice($order['total_amount']); ?></td>
                <td><span class="status-badge status-<?php echo e($order['status']); ?>"><?php echo ucfirst(e($order['status'])); ?></span></td>
                <td><span class="status-badge status-<?php echo e($order['payment_status']); ?>"><?php echo ucfirst(e($order['payment_status'])); ?></span></td>
                <td>
                    <a href="<?php echo SITE_URL; ?>/admin/order_detail.php?id=<?php echo (int)$order['id']; ?>" class="btn btn-sm btn-outline"><i class="fas fa-eye"></i> View</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($totalPages > 1): ?>
<div class="admin-pagination">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
    <a href="?page=<?php echo $i; ?>&status=<?php echo urlencode($statusFilter); ?>" class="btn btn-sm <?php echo $i === $page ? 'btn-primary' : 'btn-outline'; ?>"><?php echo $i; ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>
