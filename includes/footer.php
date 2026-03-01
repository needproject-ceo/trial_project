    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-top">
            <div class="container">
                <div class="footer-grid">
                    <div class="footer-col">
                        <div class="footer-logo">
                            <span class="logo-icon"><i class="fas fa-om"></i></span>
                            <span class="logo-name">Gauri Collections</span>
                        </div>
                        <p><?php echo e(getSetting('about_text', '')); ?></p>
                        <div class="social-links">
                            <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                            <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                            <a href="#" title="Pinterest"><i class="fab fa-pinterest"></i></a>
                        </div>
                    </div>
                    <div class="footer-col">
                        <h3>Quick Links</h3>
                        <ul>
                            <li><a href="<?php echo SITE_URL; ?>/index.php">Home</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/pages/products.php">Shop All</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/pages/about.php">About Us</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/pages/contact.php">Contact</a></li>
                        </ul>
                    </div>
                    <div class="footer-col">
                        <h3>Categories</h3>
                        <ul>
                            <?php
                            try {
                                $footerCats = $pdo->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY sort_order LIMIT 6");
                                while ($fc = $footerCats->fetch()) {
                                    echo '<li><a href="' . SITE_URL . '/pages/products.php?category=' . (int)$fc['id'] . '">' . e($fc['name']) . '</a></li>';
                                }
                            } catch (PDOException $e) {}
                            ?>
                        </ul>
                    </div>
                    <div class="footer-col">
                        <h3>Contact Info</h3>
                        <ul class="contact-info">
                            <li><i class="fas fa-map-marker-alt"></i> <?php echo e(getSetting('site_address')); ?></li>
                            <li><i class="fas fa-phone-alt"></i> <?php echo e(getSetting('site_phone')); ?></li>
                            <li><i class="fas fa-envelope"></i> <?php echo e(getSetting('site_email')); ?></li>
                        </ul>
                        <h3 class="mt-3">Newsletter</h3>
                        <form class="newsletter-form" id="newsletterForm">
                            <input type="email" name="email" placeholder="Your email address" required>
                            <button type="submit"><i class="fas fa-paper-plane"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <p><?php echo e(getSetting('footer_text')); ?></p>
                <div class="payment-icons">
                    <i class="fab fa-cc-visa"></i>
                    <i class="fab fa-cc-mastercard"></i>
                    <i class="fab fa-cc-amex"></i>
                    <i class="fab fa-google-pay"></i>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button class="back-to-top" id="backToTop" title="Back to Top">
        <i class="fas fa-chevron-up"></i>
    </button>

    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
