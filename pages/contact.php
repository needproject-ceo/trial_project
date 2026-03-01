<?php
/**
 * Gauri Collections - Contact Page
 */
require_once __DIR__ . '/../config/database.php';

$errors = [];
$success = false;
$formData = ['name' => '', 'email' => '', 'phone' => '', 'subject' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        $formData['name'] = trim($_POST['name'] ?? '');
        $formData['email'] = trim($_POST['email'] ?? '');
        $formData['phone'] = trim($_POST['phone'] ?? '');
        $formData['subject'] = trim($_POST['subject'] ?? '');
        $formData['message'] = trim($_POST['message'] ?? '');

        if ($formData['name'] === '') $errors[] = 'Name is required.';
        if ($formData['email'] === '' || !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email address is required.';
        }
        if ($formData['subject'] === '') $errors[] = 'Subject is required.';
        if ($formData['message'] === '') $errors[] = 'Message is required.';

        if (empty($errors)) {
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $formData['name'],
                $formData['email'],
                $formData['phone'],
                $formData['subject'],
                $formData['message']
            ]);
            $success = true;
            $formData = ['name' => '', 'email' => '', 'phone' => '', 'subject' => '', 'message' => ''];
            setFlash('success', 'Thank you! Your message has been sent successfully.');
            redirect(SITE_URL . '/pages/contact.php');
        }
    }
}

$pageTitle = 'Contact Us - Gauri Collections';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumbs -->
<section class="breadcrumbs">
    <div class="container">
        <a href="<?php echo SITE_URL; ?>/index.php">Home</a>
        <span>/</span>
        <span>Contact Us</span>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-title animate-on-scroll">
            <span class="section-subtitle">Get in Touch</span>
            <h2>Contact Us</h2>
            <p>Have a question about our collection or need expert guidance? We'd love to hear from you.</p>
        </div>

        <div class="contact-layout">
            <!-- Contact Form -->
            <div class="contact-form-wrapper animate-on-scroll">
                <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $err): ?>
                    <p><?php echo e($err); ?></p>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <form method="POST" id="contactForm" class="contact-form">
                    <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="name">Full Name *</label>
                            <input type="text" id="name" name="name" required value="<?php echo e($formData['name']); ?>" placeholder="Your full name">
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" required value="<?php echo e($formData['email']); ?>" placeholder="your@email.com">
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo e($formData['phone']); ?>" placeholder="+91 98765 43210">
                        </div>
                        <div class="form-group">
                            <label for="subject">Subject *</label>
                            <input type="text" id="subject" name="subject" required value="<?php echo e($formData['subject']); ?>" placeholder="How can we help?">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="message">Message *</label>
                        <textarea id="message" name="message" rows="6" required placeholder="Write your message here..."><?php echo e($formData['message']); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </form>
            </div>

            <!-- Contact Info Sidebar -->
            <div class="contact-sidebar animate-on-scroll">
                <div class="contact-info-card">
                    <h3>Contact Information</h3>

                    <div class="contact-info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <strong>Visit Us</strong>
                            <p><?php echo e(getSetting('site_address')); ?></p>
                        </div>
                    </div>

                    <div class="contact-info-item">
                        <i class="fas fa-phone-alt"></i>
                        <div>
                            <strong>Call Us</strong>
                            <p><?php echo e(getSetting('site_phone')); ?></p>
                        </div>
                    </div>

                    <div class="contact-info-item">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <strong>Email Us</strong>
                            <p><?php echo e(getSetting('site_email')); ?></p>
                        </div>
                    </div>

                    <div class="contact-info-item">
                        <i class="fas fa-clock"></i>
                        <div>
                            <strong>Business Hours</strong>
                            <p>Mon - Sat: 10:00 AM - 7:00 PM<br>Sunday: By Appointment Only</p>
                        </div>
                    </div>
                </div>

                <!-- Map Placeholder -->
                <div class="map-placeholder">
                    <div class="map-overlay">
                        <i class="fas fa-map-marked-alt"></i>
                        <p>42 Heritage Lane, Jaipur, Rajasthan</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
