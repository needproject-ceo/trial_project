<?php
/**
 * Gauri Collections - Admin Coupons Management
 */
$pageTitle = 'Coupons';
require_once __DIR__ . '/header.php';

$errors = [];
$editCoupon = null;

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM coupons WHERE id = ?");
            $stmt->execute([(int)$_POST['delete_id']]);
            setFlash('success', 'Coupon deleted.');
        } catch (PDOException $ex) {
            setFlash('error', 'Could not delete coupon.');
        }
    }
    redirect(SITE_URL . '/admin/coupons.php');
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_coupon'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token.';
    }

    $couponId = (int)($_POST['coupon_id'] ?? 0);
    $code = strtoupper(trim($_POST['code'] ?? ''));
    $description = trim($_POST['description'] ?? '');
    $discountType = $_POST['discount_type'] ?? 'percentage';
    $discountValue = (float)($_POST['discount_value'] ?? 0);
    $minOrderAmount = (float)($_POST['min_order_amount'] ?? 0);
    $maxUses = ($_POST['max_uses'] ?? '') !== '' ? (int)$_POST['max_uses'] : null;
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $expiresAt = trim($_POST['expires_at'] ?? '') ?: null;

    if ($code === '') $errors[] = 'Coupon code is required.';
    if ($discountValue <= 0) $errors[] = 'Discount value must be greater than zero.';
    if (!in_array($discountType, ['percentage', 'fixed'])) $errors[] = 'Invalid discount type.';

    if (empty($errors)) {
        try {
            if ($couponId > 0) {
                $stmt = $pdo->prepare("UPDATE coupons SET code=?, description=?, discount_type=?, discount_value=?, min_order_amount=?, max_uses=?, is_active=?, expires_at=? WHERE id=?");
                $stmt->execute([$code, $description, $discountType, $discountValue, $minOrderAmount, $maxUses, $isActive, $expiresAt, $couponId]);
                setFlash('success', 'Coupon updated successfully.');
            } else {
                $stmt = $pdo->prepare("INSERT INTO coupons (code, description, discount_type, discount_value, min_order_amount, max_uses, is_active, expires_at) VALUES (?,?,?,?,?,?,?,?)");
                $stmt->execute([$code, $description, $discountType, $discountValue, $minOrderAmount, $maxUses, $isActive, $expiresAt]);
                setFlash('success', 'Coupon created successfully.');
            }
            redirect(SITE_URL . '/admin/coupons.php');
        } catch (PDOException $ex) {
            if ($ex->getCode() == 23000) {
                $errors[] = 'A coupon with this code already exists.';
            } else {
                $errors[] = 'Database error. Please try again.';
            }
        }
    }
}

// Load coupon for editing
$editId = (int)($_GET['edit'] ?? 0);
if ($editId > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM coupons WHERE id = ?");
        $stmt->execute([$editId]);
        $editCoupon = $stmt->fetch();
    } catch (PDOException $ex) {
        $editCoupon = null;
    }
}

// Load all coupons
try {
    $allCoupons = $pdo->query("SELECT * FROM coupons ORDER BY created_at DESC")->fetchAll();
} catch (PDOException $ex) {
    $allCoupons = [];
}
?>

<?php if (!empty($errors)): ?>
<div class="flash-message flash-error">
    <ul style="margin:0;padding-left:1.2rem;">
        <?php foreach ($errors as $err): ?>
        <li><?php echo e($err); ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="admin-grid-two">
    <!-- Coupon Form -->
    <div class="admin-section">
        <h3 class="admin-section-title"><?php echo $editCoupon ? 'Edit Coupon' : 'Add New Coupon'; ?></h3>
        <form method="post" class="admin-form">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            <input type="hidden" name="coupon_id" value="<?php echo (int)($editCoupon['id'] ?? 0); ?>">
            <input type="hidden" name="save_coupon" value="1">

            <div class="form-group">
                <label for="code">Coupon Code *</label>
                <input type="text" name="code" id="code" class="form-control" value="<?php echo e($editCoupon['code'] ?? ''); ?>" required style="text-transform:uppercase;">
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <input type="text" name="description" id="description" class="form-control" value="<?php echo e($editCoupon['description'] ?? ''); ?>">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="discount_type">Discount Type</label>
                    <select name="discount_type" id="discount_type" class="form-control">
                        <option value="percentage" <?php echo ($editCoupon['discount_type'] ?? '') === 'percentage' ? 'selected' : ''; ?>>Percentage (%)</option>
                        <option value="fixed" <?php echo ($editCoupon['discount_type'] ?? '') === 'fixed' ? 'selected' : ''; ?>>Fixed Amount (₹)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="discount_value">Discount Value *</label>
                    <input type="number" name="discount_value" id="discount_value" class="form-control" step="0.01" min="0" value="<?php echo e($editCoupon['discount_value'] ?? ''); ?>" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="min_order_amount">Min Order Amount (₹)</label>
                    <input type="number" name="min_order_amount" id="min_order_amount" class="form-control" step="0.01" min="0" value="<?php echo e($editCoupon['min_order_amount'] ?? 0); ?>">
                </div>
                <div class="form-group">
                    <label for="max_uses">Max Uses (blank = unlimited)</label>
                    <input type="number" name="max_uses" id="max_uses" class="form-control" min="0" value="<?php echo e($editCoupon['max_uses'] ?? ''); ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="expires_at">Expires At</label>
                <input type="datetime-local" name="expires_at" id="expires_at" class="form-control" value="<?php echo $editCoupon && $editCoupon['expires_at'] ? date('Y-m-d\TH:i', strtotime($editCoupon['expires_at'])) : ''; ?>">
            </div>
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" value="1" <?php echo ($editCoupon === null || !empty($editCoupon['is_active'])) ? 'checked' : ''; ?>>
                    Active
                </label>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?php echo $editCoupon ? 'Update' : 'Add'; ?> Coupon</button>
                <?php if ($editCoupon): ?>
                <a href="<?php echo SITE_URL; ?>/admin/coupons.php" class="btn btn-outline">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Coupons List -->
    <div class="admin-section">
        <h3 class="admin-section-title">All Coupons</h3>
        <div class="admin-table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Discount</th>
                        <th>Min Order</th>
                        <th>Uses</th>
                        <th>Expires</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($allCoupons)): ?>
                    <tr><td colspan="7" class="text-center">No coupons found.</td></tr>
                    <?php else: ?>
                    <?php foreach ($allCoupons as $coupon): ?>
                    <tr>
                        <td><strong><?php echo e($coupon['code']); ?></strong></td>
                        <td>
                            <?php if ($coupon['discount_type'] === 'percentage'): ?>
                            <?php echo e($coupon['discount_value']); ?>%
                            <?php else: ?>
                            <?php echo formatPrice($coupon['discount_value']); ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo formatPrice($coupon['min_order_amount']); ?></td>
                        <td><?php echo (int)$coupon['used_count']; ?>/<?php echo $coupon['max_uses'] !== null ? (int)$coupon['max_uses'] : '∞'; ?></td>
                        <td><?php echo $coupon['expires_at'] ? date('d M Y', strtotime($coupon['expires_at'])) : 'Never'; ?></td>
                        <td>
                            <?php if ($coupon['is_active']): ?>
                            <span class="status-badge status-delivered">Active</span>
                            <?php else: ?>
                            <span class="status-badge status-cancelled">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="admin-actions">
                            <a href="?edit=<?php echo (int)$coupon['id']; ?>" class="btn btn-sm btn-outline" title="Edit"><i class="fas fa-edit"></i></a>
                            <form method="post" style="display:inline" onsubmit="return confirm('Delete this coupon?');">
                                <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
                                <input type="hidden" name="delete_id" value="<?php echo (int)$coupon['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
