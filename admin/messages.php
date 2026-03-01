<?php
/**
 * Gauri Collections - Admin Contact Messages
 */
$pageTitle = 'Messages';
require_once __DIR__ . '/header.php';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
    } else {
        if (isset($_POST['mark_read_id'])) {
            $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
            $stmt->execute([(int)$_POST['mark_read_id']]);
            setFlash('success', 'Message marked as read.');
        } elseif (isset($_POST['delete_id'])) {
            $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
            $stmt->execute([(int)$_POST['delete_id']]);
            setFlash('success', 'Message deleted.');
        }
    }
    redirect(SITE_URL . '/admin/messages.php');
}

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

try {
    $totalMessages = (int)$pdo->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();
    $totalPages = max(1, ceil($totalMessages / $perPage));

    $stmt = $pdo->prepare("SELECT * FROM contact_messages ORDER BY is_read ASC, created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$perPage, $offset]);
    $messages = $stmt->fetchAll();
} catch (PDOException $ex) {
    $messages = [];
    $totalPages = 1;
}

// Viewing a specific message?
$viewId = (int)($_GET['view'] ?? 0);
$viewMsg = null;
if ($viewId > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id = ?");
        $stmt->execute([$viewId]);
        $viewMsg = $stmt->fetch();
        if ($viewMsg && !$viewMsg['is_read']) {
            $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?")->execute([$viewId]);
            $viewMsg['is_read'] = 1;
        }
    } catch (PDOException $ex) {
        $viewMsg = null;
    }
}
?>

<?php if ($viewMsg): ?>
<!-- Message Detail View -->
<a href="<?php echo SITE_URL; ?>/admin/messages.php" class="btn btn-outline btn-sm" style="margin-bottom:1rem;"><i class="fas fa-arrow-left"></i> Back to Messages</a>
<div class="admin-section">
    <h3 class="admin-section-title"><?php echo e($viewMsg['subject'] ?: 'No Subject'); ?></h3>
    <table class="admin-detail-table">
        <tr><th>From</th><td><?php echo e($viewMsg['name']); ?></td></tr>
        <tr><th>Email</th><td><a href="mailto:<?php echo e($viewMsg['email']); ?>"><?php echo e($viewMsg['email']); ?></a></td></tr>
        <?php if ($viewMsg['phone']): ?>
        <tr><th>Phone</th><td><?php echo e($viewMsg['phone']); ?></td></tr>
        <?php endif; ?>
        <tr><th>Date</th><td><?php echo date('d M Y, h:i A', strtotime($viewMsg['created_at'])); ?></td></tr>
        <tr><th>Message</th><td style="white-space:pre-wrap;"><?php echo e($viewMsg['message']); ?></td></tr>
    </table>
</div>

<?php else: ?>
<!-- Messages List -->
<div class="admin-table-responsive">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Subject</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($messages)): ?>
            <tr><td colspan="6" class="text-center">No messages found.</td></tr>
            <?php else: ?>
            <?php foreach ($messages as $msg): ?>
            <tr style="<?php echo !$msg['is_read'] ? 'font-weight:600;' : ''; ?>">
                <td><?php echo e($msg['name']); ?></td>
                <td><?php echo e($msg['email']); ?></td>
                <td><?php echo e($msg['subject'] ?: '—'); ?></td>
                <td><?php echo date('d M Y', strtotime($msg['created_at'])); ?></td>
                <td>
                    <?php if ($msg['is_read']): ?>
                    <span class="status-badge status-delivered">Read</span>
                    <?php else: ?>
                    <span class="status-badge status-pending">Unread</span>
                    <?php endif; ?>
                </td>
                <td class="admin-actions">
                    <a href="?view=<?php echo (int)$msg['id']; ?>" class="btn btn-sm btn-outline" title="View"><i class="fas fa-eye"></i></a>
                    <?php if (!$msg['is_read']): ?>
                    <form method="post" style="display:inline">
                        <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
                        <input type="hidden" name="mark_read_id" value="<?php echo (int)$msg['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-primary" title="Mark Read"><i class="fas fa-check"></i></button>
                    </form>
                    <?php endif; ?>
                    <form method="post" style="display:inline" onsubmit="return confirm('Delete this message?');">
                        <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
                        <input type="hidden" name="delete_id" value="<?php echo (int)$msg['id']; ?>">
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

<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>
