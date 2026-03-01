<?php
/**
 * Gauri Collections - Admin Users Management
 */
$pageTitle = 'Users';
require_once __DIR__ . '/header.php';

// Handle toggle active
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_user_id'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
    } else {
        $uid = (int)$_POST['toggle_user_id'];
        try {
            $stmt = $pdo->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ? AND role != 'admin'");
            $stmt->execute([$uid]);
            setFlash('success', 'User status updated.');
        } catch (PDOException $ex) {
            setFlash('error', 'Could not update user.');
        }
    }
    redirect(SITE_URL . '/admin/users.php');
}

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

try {
    $totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalPages = max(1, ceil($totalUsers / $perPage));

    $stmt = $pdo->prepare("SELECT u.*, (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) AS order_count FROM users u ORDER BY u.created_at DESC LIMIT $perPage OFFSET $offset");
    $stmt->execute();
    $users = $stmt->fetchAll();
} catch (PDOException $ex) {
    $users = [];
    $totalPages = 1;
}
?>

<div class="admin-table-responsive">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Orders</th>
                <th>Joined</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
            <tr><td colspan="7" class="text-center">No users found.</td></tr>
            <?php else: ?>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo e($user['first_name'] . ' ' . $user['last_name']); ?></td>
                <td><?php echo e($user['email']); ?></td>
                <td><span class="status-badge status-<?php echo $user['role'] === 'admin' ? 'confirmed' : 'processing'; ?>"><?php echo ucfirst(e($user['role'])); ?></span></td>
                <td><?php echo (int)$user['order_count']; ?></td>
                <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                <td>
                    <?php if ($user['is_active']): ?>
                    <span class="status-badge status-delivered">Active</span>
                    <?php else: ?>
                    <span class="status-badge status-cancelled">Inactive</span>
                    <?php endif; ?>
                </td>
                <td class="admin-actions">
                    <?php if ($user['role'] !== 'admin'): ?>
                    <form method="post" style="display:inline" onsubmit="return confirm('Toggle active status for this user?');">
                        <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
                        <input type="hidden" name="toggle_user_id" value="<?php echo (int)$user['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-outline" title="Toggle Active">
                            <i class="fas fa-<?php echo $user['is_active'] ? 'ban' : 'check'; ?>"></i>
                        </button>
                    </form>
                    <?php else: ?>
                    <span class="text-muted"><small>Admin</small></span>
                    <?php endif; ?>
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
    <a href="?page=<?php echo $i; ?>" class="btn btn-sm <?php echo $i === $page ? 'btn-primary' : 'btn-outline'; ?>"><?php echo $i; ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>
