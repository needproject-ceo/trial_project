<?php
/**
 * Gauri Collections - Admin Reviews Management
 */
$pageTitle = 'Reviews';
require_once __DIR__ . '/header.php';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
    } else {
        if (isset($_POST['approve_id'])) {
            $stmt = $pdo->prepare("UPDATE reviews SET is_approved = 1 WHERE id = ?");
            $stmt->execute([(int)$_POST['approve_id']]);
            setFlash('success', 'Review approved.');
        } elseif (isset($_POST['reject_id'])) {
            $stmt = $pdo->prepare("UPDATE reviews SET is_approved = 0 WHERE id = ?");
            $stmt->execute([(int)$_POST['reject_id']]);
            setFlash('success', 'Review rejected.');
        } elseif (isset($_POST['delete_id'])) {
            $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
            $stmt->execute([(int)$_POST['delete_id']]);
            setFlash('success', 'Review deleted.');
        }
    }
    redirect(SITE_URL . '/admin/reviews.php');
}

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

try {
    $totalReviews = (int)$pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
    $totalPages = max(1, ceil($totalReviews / $perPage));

    $stmt = $pdo->prepare("SELECT r.*, p.name AS product_name, u.first_name, u.last_name FROM reviews r LEFT JOIN products p ON r.product_id = p.id LEFT JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC LIMIT $perPage OFFSET $offset");
    $stmt->execute();
    $reviews = $stmt->fetchAll();
} catch (PDOException $ex) {
    $reviews = [];
    $totalPages = 1;
}
?>

<div class="admin-table-responsive">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>User</th>
                <th>Rating</th>
                <th>Comment</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($reviews)): ?>
            <tr><td colspan="7" class="text-center">No reviews found.</td></tr>
            <?php else: ?>
            <?php foreach ($reviews as $review): ?>
            <tr>
                <td><?php echo e($review['product_name'] ?? 'Deleted Product'); ?></td>
                <td><?php echo e(($review['first_name'] ?? '') . ' ' . ($review['last_name'] ?? '')); ?></td>
                <td>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="fas fa-star" style="color:<?php echo $i <= $review['rating'] ? '#f5a623' : '#ddd'; ?>;font-size:0.85rem;"></i>
                    <?php endfor; ?>
                </td>
                <td>
                    <?php if ($review['title']): ?><strong><?php echo e($review['title']); ?></strong><br><?php endif; ?>
                    <?php echo e(mb_strimwidth($review['comment'] ?? '', 0, 100, '...')); ?>
                </td>
                <td>
                    <?php if ($review['is_approved']): ?>
                    <span class="status-badge status-delivered">Approved</span>
                    <?php else: ?>
                    <span class="status-badge status-pending">Pending</span>
                    <?php endif; ?>
                </td>
                <td><?php echo date('d M Y', strtotime($review['created_at'])); ?></td>
                <td class="admin-actions">
                    <?php if (!$review['is_approved']): ?>
                    <form method="post" style="display:inline">
                        <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
                        <input type="hidden" name="approve_id" value="<?php echo (int)$review['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-primary" title="Approve"><i class="fas fa-check"></i></button>
                    </form>
                    <?php else: ?>
                    <form method="post" style="display:inline">
                        <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
                        <input type="hidden" name="reject_id" value="<?php echo (int)$review['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-outline" title="Reject"><i class="fas fa-times"></i></button>
                    </form>
                    <?php endif; ?>
                    <form method="post" style="display:inline" onsubmit="return confirm('Delete this review?');">
                        <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
                        <input type="hidden" name="delete_id" value="<?php echo (int)$review['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                    </form>
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
