<?php
/**
 * Gauri Collections - Admin Settings
 */
require_once __DIR__ . '/../config/database.php';

if (!isAdmin()) {
    redirect(SITE_URL . '/pages/login.php');
}

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
    } else {
        $settingKeys = [
            'site_name', 'site_tagline', 'site_email', 'site_phone', 'site_address',
            'currency_symbol', 'shipping_charge', 'free_shipping_above', 'tax_rate',
            'footer_text', 'about_text', 'meta_description'
        ];

        try {
            $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
            foreach ($settingKeys as $key) {
                if (isset($_POST[$key])) {
                    $stmt->execute([trim($_POST[$key]), $key]);
                }
            }
            setFlash('success', 'Settings saved successfully.');
        } catch (PDOException $ex) {
            setFlash('error', 'Could not save settings.');
        }
    }
    redirect(SITE_URL . '/admin/settings.php');
}

// Reload settings for display
try {
    $allSettings = [];
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    while ($row = $stmt->fetch()) {
        $allSettings[$row['setting_key']] = $row['setting_value'];
    }
} catch (PDOException $ex) {
    $allSettings = [];
}

$pageTitle = 'Settings';
require_once __DIR__ . '/header.php';
?>

<div class="admin-form-container">
    <form method="post" class="admin-form">
        <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">

        <h3 class="admin-section-title">General</h3>
        <div class="form-row">
            <div class="form-group">
                <label for="site_name">Site Name</label>
                <input type="text" name="site_name" id="site_name" class="form-control" value="<?php echo e($allSettings['site_name'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="site_tagline">Tagline</label>
                <input type="text" name="site_tagline" id="site_tagline" class="form-control" value="<?php echo e($allSettings['site_tagline'] ?? ''); ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="site_email">Email</label>
                <input type="email" name="site_email" id="site_email" class="form-control" value="<?php echo e($allSettings['site_email'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="site_phone">Phone</label>
                <input type="text" name="site_phone" id="site_phone" class="form-control" value="<?php echo e($allSettings['site_phone'] ?? ''); ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="site_address">Address</label>
            <textarea name="site_address" id="site_address" class="form-control" rows="2"><?php echo e($allSettings['site_address'] ?? ''); ?></textarea>
        </div>

        <h3 class="admin-section-title" style="margin-top:2rem;">Commerce</h3>
        <div class="form-row">
            <div class="form-group">
                <label for="currency_symbol">Currency Symbol</label>
                <input type="text" name="currency_symbol" id="currency_symbol" class="form-control" value="<?php echo e($allSettings['currency_symbol'] ?? '₹'); ?>">
            </div>
            <div class="form-group">
                <label for="tax_rate">Tax Rate (%)</label>
                <input type="number" name="tax_rate" id="tax_rate" class="form-control" step="0.01" min="0" value="<?php echo e($allSettings['tax_rate'] ?? ''); ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="shipping_charge">Shipping Charge (₹)</label>
                <input type="number" name="shipping_charge" id="shipping_charge" class="form-control" step="0.01" min="0" value="<?php echo e($allSettings['shipping_charge'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="free_shipping_above">Free Shipping Above (₹)</label>
                <input type="number" name="free_shipping_above" id="free_shipping_above" class="form-control" step="0.01" min="0" value="<?php echo e($allSettings['free_shipping_above'] ?? ''); ?>">
            </div>
        </div>

        <h3 class="admin-section-title" style="margin-top:2rem;">Content</h3>
        <div class="form-group">
            <label for="about_text">About Text</label>
            <textarea name="about_text" id="about_text" class="form-control" rows="4"><?php echo e($allSettings['about_text'] ?? ''); ?></textarea>
        </div>
        <div class="form-group">
            <label for="footer_text">Footer Text</label>
            <input type="text" name="footer_text" id="footer_text" class="form-control" value="<?php echo e($allSettings['footer_text'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="meta_description">Meta Description</label>
            <textarea name="meta_description" id="meta_description" class="form-control" rows="3"><?php echo e($allSettings['meta_description'] ?? ''); ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Settings</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
