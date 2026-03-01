<?php
/**
 * Gauri Collections - Login Page
 */
require_once __DIR__ . '/../config/database.php';

if (isLoggedIn()) {
    redirect(SITE_URL . '/pages/account.php');
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if ($password === '') {
            $errors[] = 'Please enter your password.';
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];

                // Migrate guest cart to user
                $sessionId = session_id();
                $pdo->prepare("UPDATE cart SET user_id = ? WHERE session_id = ? AND user_id IS NULL")
                    ->execute([$user['id'], $sessionId]);

                setFlash('success', 'Welcome back, ' . e($user['first_name']) . '!');

                // Redirect
                $redirectUrl = $_SESSION['redirect_after_login'] ?? null;
                unset($_SESSION['redirect_after_login']);

                if ($user['role'] === 'admin') {
                    redirect($redirectUrl ?: SITE_URL . '/admin/');
                } else {
                    redirect($redirectUrl ?: SITE_URL . '/pages/account.php');
                }
            } else {
                $errors[] = 'Invalid email or password.';
            }
        }
    }
}

$pageTitle = 'Login - Gauri Collections';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section">
    <div class="container">
        <div class="auth-container">
            <div class="auth-form-wrapper">
                <h1>Welcome Back</h1>
                <p>Login to your account to continue shopping.</p>

                <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $err): ?>
                    <p><?php echo e($err); ?></p>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <form method="POST" id="loginForm" class="auth-form">
                    <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required value="<?php echo e($email); ?>" placeholder="your@email.com">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required placeholder="Enter your password">
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>

                <p class="auth-link">
                    Don't have an account? <a href="<?php echo SITE_URL; ?>/pages/register.php">Create one here</a>
                </p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
