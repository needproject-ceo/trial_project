<?php
/**
 * Gauri Collections - User Account Page
 */
require_once __DIR__ . '/../config/database.php';

if (!isLoggedIn()) {
    setFlash('error', 'Please login to access your account.');
    redirect(SITE_URL . '/pages/login.php');
}

$userId = $_SESSION['user_id'];

// Fetch user
$userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->execute([$userId]);
$user = $userStmt->fetch();

if (!$user) {
    session_destroy();
    redirect(SITE_URL . '/pages/login.php');
}

$errors = [];
$successMsg = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token.';
    } else {
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $pincode = trim($_POST['pincode'] ?? '');

        if ($firstName === '') $errors[] = 'First name is required.';
        if ($lastName === '') $errors[] = 'Last name is required.';

        if (empty($errors)) {
            $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ?, address = ?, city = ?, state = ?, pincode = ? WHERE id = ?");
            $stmt->execute([$firstName, $lastName, $phone, $address, $city, $state, $pincode, $userId]);

            $_SESSION['user_name'] = $firstName . ' ' . $lastName;
            setFlash('success', 'Profile updated successfully!');
            redirect(SITE_URL . '/pages/account.php');
        }
    }
}

// Handle password update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token.';
    } else {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!password_verify($currentPassword, $user['password'])) {
            $errors[] = 'Current password is incorrect.';
        }
        if (strlen($newPassword) < 6) {
            $errors[] = 'New password must be at least 6 characters.';
        }
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'New passwords do not match.';
        }

        if (empty($errors)) {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hashed, $userId]);
            setFlash('success', 'Password updated successfully!');
            redirect(SITE_URL . '/pages/account.php');
        }
    }
}

// Fetch orders
$orderStmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$orderStmt->execute([$userId]);
$orders = $orderStmt->fetchAll();

$pageTitle = 'My Account - Gauri Collections';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="page-title">My Account</h1>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $err): ?>
            <p><?php echo e($err); ?></p>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="account-layout">
            <!-- Profile Section -->
            <div class="account-section">
                <h3><i class="fas fa-user"></i> Profile Information</h3>
                <form method="POST" class="account-form">
                    <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="first_name">First Name *</label>
                            <input type="text" id="first_name" name="first_name" required value="<?php echo e($user['first_name']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name *</label>
                            <input type="text" id="last_name" name="last_name" required value="<?php echo e($user['last_name']); ?>">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" value="<?php echo e($user['email']); ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo e($user['phone']); ?>">
                        </div>
                        <div class="form-group form-group-full">
                            <label for="address">Address</label>
                            <textarea id="address" name="address" rows="2"><?php echo e($user['address']); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" value="<?php echo e($user['city']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="state">State</label>
                            <input type="text" id="state" name="state" value="<?php echo e($user['state']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="pincode">Pincode</label>
                            <input type="text" id="pincode" name="pincode" value="<?php echo e($user['pincode']); ?>">
                        </div>
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </form>
            </div>

            <!-- Password Section -->
            <div class="account-section">
                <h3><i class="fas fa-lock"></i> Change Password</h3>
                <form method="POST" class="account-form">
                    <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">

                    <div class="form-group">
                        <label for="current_password">Current Password *</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password * (min 6 characters)</label>
                        <input type="password" id="new_password" name="new_password" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                    </div>
                    <button type="submit" name="update_password" class="btn btn-primary">
                        <i class="fas fa-key"></i> Update Password
                    </button>
                </form>
            </div>

            <!-- Orders Section -->
            <div class="account-section account-section-full">
                <h3><i class="fas fa-shopping-bag"></i> Order History</h3>
                <?php if (empty($orders)): ?>
                    <div class="empty-state">
                        <p>You haven't placed any orders yet.</p>
                        <a href="<?php echo SITE_URL; ?>/pages/products.php" class="btn btn-primary">Start Shopping</a>
                    </div>
                <?php else: ?>
                    <div class="orders-table-wrapper">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><strong><?php echo e($order['order_number']); ?></strong></td>
                                    <td><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                                    <td><?php echo formatPrice($order['total_amount']); ?></td>
                                    <td><span class="badge badge-<?php echo e($order['status']); ?>"><?php echo ucfirst(e($order['status'])); ?></span></td>
                                    <td><?php echo ucfirst(e($order['payment_status'])); ?></td>
                                    <td><a href="<?php echo SITE_URL; ?>/pages/order_confirmation.php?order=<?php echo urlencode($order['order_number']); ?>" class="btn btn-sm btn-outline">View</a></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
