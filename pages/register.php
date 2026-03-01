<?php
/**
 * Gauri Collections - Registration Page
 */
require_once __DIR__ . '/../config/database.php';

if (isLoggedIn()) {
    redirect(SITE_URL . '/pages/account.php');
}

$errors = [];
$formData = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        $formData['first_name'] = trim($_POST['first_name'] ?? '');
        $formData['last_name'] = trim($_POST['last_name'] ?? '');
        $formData['email'] = trim($_POST['email'] ?? '');
        $formData['phone'] = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validate
        if ($formData['first_name'] === '') $errors[] = 'First name is required.';
        if ($formData['last_name'] === '') $errors[] = 'Last name is required.';
        if ($formData['email'] === '' || !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email address is required.';
        }
        if ($formData['phone'] === '') $errors[] = 'Phone number is required.';
        if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
        if ($password !== $confirmPassword) $errors[] = 'Passwords do not match.';

        // Check email unique
        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$formData['email']]);
            if ($stmt->fetch()) {
                $errors[] = 'An account with this email already exists.';
            }
        }

        if (empty($errors)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, phone, password) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $formData['first_name'],
                $formData['last_name'],
                $formData['email'],
                $formData['phone'],
                $hashedPassword
            ]);
            $newUserId = $pdo->lastInsertId();

            // Auto-login
            $_SESSION['user_id'] = $newUserId;
            $_SESSION['user_name'] = $formData['first_name'] . ' ' . $formData['last_name'];
            $_SESSION['user_email'] = $formData['email'];
            $_SESSION['user_role'] = 'customer';

            // Migrate guest cart
            $sessionId = session_id();
            $pdo->prepare("UPDATE cart SET user_id = ? WHERE session_id = ? AND user_id IS NULL")
                ->execute([$newUserId, $sessionId]);

            setFlash('success', 'Account created successfully! Welcome, ' . e($formData['first_name']) . '!');
            redirect(SITE_URL . '/pages/account.php');
        }
    }
}

$pageTitle = 'Register - Gauri Collections';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section">
    <div class="container">
        <div class="auth-container">
            <div class="auth-form-wrapper">
                <h1>Create Account</h1>
                <p>Join Gauri Collections for an exclusive collection experience.</p>

                <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $err): ?>
                    <p><?php echo e($err); ?></p>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <form method="POST" id="registerForm" class="auth-form">
                    <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="first_name">First Name *</label>
                            <input type="text" id="first_name" name="first_name" required value="<?php echo e($formData['first_name']); ?>" placeholder="First name">
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name *</label>
                            <input type="text" id="last_name" name="last_name" required value="<?php echo e($formData['last_name']); ?>" placeholder="Last name">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required value="<?php echo e($formData['email']); ?>" placeholder="your@email.com">
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="tel" id="phone" name="phone" required value="<?php echo e($formData['phone']); ?>" placeholder="+91 98765 43210">
                    </div>

                    <div class="form-group">
                        <label for="password">Password * (min 6 characters)</label>
                        <input type="password" id="password" name="password" required minlength="6" placeholder="Create a password">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="6" placeholder="Confirm your password">
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>
                </form>

                <p class="auth-link">
                    Already have an account? <a href="<?php echo SITE_URL; ?>/pages/login.php">Login here</a>
                </p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
