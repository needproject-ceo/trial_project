<?php
/**
 * Gauri Collections - Home Page
 */
require_once __DIR__ . '/config/database.php';
$pageTitle = 'Gauri Collections - Exquisite Antique Statues of Indian Gods';

// Fetch featured products
$featuredProducts = $pdo->query("SELECT * FROM products WHERE is_featured = 1 AND is_active = 1 ORDER BY created_at DESC LIMIT 8")->fetchAll();

// Fetch categories
$categories = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order LIMIT 8")->fetchAll();

// Fetch latest products
$latestProducts = $pdo->query("SELECT * FROM products WHERE is_active = 1 ORDER BY created_at DESC LIMIT 4")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-overlay"></div>
    <div class="container">
        <div class="hero-content">
            <span class="hero-badge animate-on-scroll">✦ Since 1985 ✦</span>
            <h1 class="animate-on-scroll">Discover Divine<br>Antique Artistry</h1>
            <p class="animate-on-scroll">Curating exquisite antique statues of Indian Gods &amp; Goddesses — each piece a timeless masterpiece carrying centuries of heritage and spiritual significance.</p>
            <div class="hero-buttons animate-on-scroll">
                <a href="<?php echo SITE_URL; ?>/pages/products.php" class="btn btn-primary">Explore Collection</a>
                <a href="<?php echo SITE_URL; ?>/pages/about.php" class="btn btn-outline">Our Story</a>
            </div>
        </div>
    </div>
</section>

<!-- Features Bar -->
<section class="features-bar">
    <div class="container">
        <div class="features-grid">
            <div class="feature-item animate-on-scroll">
                <i class="fas fa-certificate"></i>
                <div>
                    <h4>Authenticated Antiques</h4>
                    <p>Every piece verified for authenticity</p>
                </div>
            </div>
            <div class="feature-item animate-on-scroll">
                <i class="fas fa-truck"></i>
                <div>
                    <h4>Secure Shipping</h4>
                    <p>Insured & carefully packaged delivery</p>
                </div>
            </div>
            <div class="feature-item animate-on-scroll">
                <i class="fas fa-shield-alt"></i>
                <div>
                    <h4>100% Secure</h4>
                    <p>Safe & encrypted transactions</p>
                </div>
            </div>
            <div class="feature-item animate-on-scroll">
                <i class="fas fa-headset"></i>
                <div>
                    <h4>Expert Support</h4>
                    <p>Guidance from antique specialists</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="section">
    <div class="container">
        <div class="section-title animate-on-scroll">
            <span class="section-subtitle">Handpicked Treasures</span>
            <h2>Featured Collection</h2>
            <p>Explore our most sought-after divine masterpieces, each carefully selected for their exceptional artistry and historical significance.</p>
        </div>
        <div class="products-grid">
            <?php foreach ($featuredProducts as $product): ?>
            <div class="product-card animate-on-scroll">
                <?php if ($product['sale_price']): ?>
                    <span class="badge badge-sale">Sale</span>
                <?php endif; ?>
                <div class="product-image">
                    <img src="<?php echo SITE_URL; ?>/assets/images/placeholder-statue.svg" alt="<?php echo e($product['name']); ?>" loading="lazy">
                    <div class="product-actions">
                        <a href="<?php echo SITE_URL; ?>/pages/product_detail.php?id=<?php echo $product['id']; ?>" class="btn-icon" title="View Details"><i class="fas fa-eye"></i></a>
                        <button class="btn-icon add-to-cart" data-product-id="<?php echo $product['id']; ?>" title="Add to Cart"><i class="fas fa-shopping-bag"></i></button>
                        <?php if (isLoggedIn()): ?>
                        <button class="btn-icon wishlist-btn" data-product-id="<?php echo $product['id']; ?>" title="Add to Wishlist"><i class="fas fa-heart"></i></button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="product-info">
                    <span class="product-category"><?php
                        $catName = '';
                        foreach ($categories as $c) { if ($c['id'] == $product['category_id']) $catName = $c['name']; }
                        echo e($catName);
                    ?></span>
                    <h3><a href="<?php echo SITE_URL; ?>/pages/product_detail.php?id=<?php echo $product['id']; ?>"><?php echo e($product['name']); ?></a></h3>
                    <p class="product-excerpt"><?php echo e($product['short_description']); ?></p>
                    <div class="product-price">
                        <?php if ($product['sale_price']): ?>
                            <span class="price-current"><?php echo formatPrice($product['sale_price']); ?></span>
                            <span class="price-original"><?php echo formatPrice($product['price']); ?></span>
                        <?php else: ?>
                            <span class="price-current"><?php echo formatPrice($product['price']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="<?php echo SITE_URL; ?>/pages/products.php" class="btn btn-primary">View All Collection</a>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="section section-alt">
    <div class="container">
        <div class="section-title animate-on-scroll">
            <span class="section-subtitle">Browse By Deity</span>
            <h2>Our Categories</h2>
            <p>Find the perfect divine sculpture from our curated categories spanning the rich pantheon of Indian mythology.</p>
        </div>
        <div class="categories-grid">
            <?php foreach ($categories as $category): ?>
            <a href="<?php echo SITE_URL; ?>/pages/products.php?category=<?php echo $category['id']; ?>" class="category-card animate-on-scroll">
                <div class="category-icon"><i class="fas fa-om"></i></div>
                <h3><?php echo e($category['name']); ?></h3>
                <p><?php echo e(substr($category['description'], 0, 80)); ?>...</p>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- About / Story Section -->
<section class="section">
    <div class="container">
        <div class="about-home">
            <div class="about-home-content animate-on-scroll">
                <span class="section-subtitle">Our Legacy</span>
                <h2>Four Decades of<br>Divine Artistry</h2>
                <p>Since 1985, Gauri Collections has been the trusted name for connoisseurs of antique Indian religious art. Our founder, inspired by the divine craftsmanship of ancient Indian artisans, began curating rare and authentic statues from across the subcontinent.</p>
                <p>Each piece in our collection tells a story — of devotion, of artistry, and of a civilization that saw the divine in every chisel stroke. We don't just sell statues; we preserve and share India's magnificent spiritual heritage.</p>
                <a href="<?php echo SITE_URL; ?>/pages/about.php" class="btn btn-primary">Read Our Story</a>
            </div>
            <div class="about-home-image animate-on-scroll">
                <div class="about-image-placeholder">
                    <i class="fas fa-om"></i>
                    <span>40+ Years of Excellence</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Latest Products -->
<section class="section section-alt">
    <div class="container">
        <div class="section-title animate-on-scroll">
            <span class="section-subtitle">Just Arrived</span>
            <h2>Latest Additions</h2>
            <p>Newly acquired pieces added to our growing collection of divine antiquities.</p>
        </div>
        <div class="products-grid">
            <?php foreach ($latestProducts as $product): ?>
            <div class="product-card animate-on-scroll">
                <span class="badge badge-new">New</span>
                <?php if ($product['sale_price']): ?>
                    <span class="badge badge-sale" style="top:45px;">Sale</span>
                <?php endif; ?>
                <div class="product-image">
                    <img src="<?php echo SITE_URL; ?>/assets/images/placeholder-statue.svg" alt="<?php echo e($product['name']); ?>" loading="lazy">
                    <div class="product-actions">
                        <a href="<?php echo SITE_URL; ?>/pages/product_detail.php?id=<?php echo $product['id']; ?>" class="btn-icon" title="View Details"><i class="fas fa-eye"></i></a>
                        <button class="btn-icon add-to-cart" data-product-id="<?php echo $product['id']; ?>" title="Add to Cart"><i class="fas fa-shopping-bag"></i></button>
                    </div>
                </div>
                <div class="product-info">
                    <h3><a href="<?php echo SITE_URL; ?>/pages/product_detail.php?id=<?php echo $product['id']; ?>"><?php echo e($product['name']); ?></a></h3>
                    <div class="product-price">
                        <?php if ($product['sale_price']): ?>
                            <span class="price-current"><?php echo formatPrice($product['sale_price']); ?></span>
                            <span class="price-original"><?php echo formatPrice($product['price']); ?></span>
                        <?php else: ?>
                            <span class="price-current"><?php echo formatPrice($product['price']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="section">
    <div class="container">
        <div class="section-title animate-on-scroll">
            <span class="section-subtitle">Client Voices</span>
            <h2>What Our Collectors Say</h2>
        </div>
        <div class="testimonials-grid">
            <div class="testimonial-card animate-on-scroll">
                <div class="testimonial-stars">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p>"The Nataraja I purchased from Gauri Collections is absolutely breathtaking. The patina, the detail, the presence — it's a museum-quality piece that now graces our family shrine."</p>
                <div class="testimonial-author">
                    <strong>Rajesh Sharma</strong>
                    <span>Art Collector, Mumbai</span>
                </div>
            </div>
            <div class="testimonial-card animate-on-scroll">
                <div class="testimonial-stars">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p>"I've been collecting antique Indian statues for 20 years, and Gauri Collections stands out for their authenticity and expertise. Every piece comes with proper documentation."</p>
                <div class="testimonial-author">
                    <strong>Dr. Priya Mehta</strong>
                    <span>Heritage Enthusiast, Delhi</span>
                </div>
            </div>
            <div class="testimonial-card animate-on-scroll">
                <div class="testimonial-stars">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p>"The Ganesha statue was packaged with incredible care and arrived in perfect condition. The team's knowledge and customer service is exceptional. Highly recommended!"</p>
                <div class="testimonial-author">
                    <strong>Anita Desai</strong>
                    <span>Interior Designer, Bangalore</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="newsletter-section">
    <div class="container">
        <div class="newsletter-content animate-on-scroll">
            <h2>Stay Connected with Divine Art</h2>
            <p>Subscribe to our newsletter for exclusive previews of new arrivals, special offers, and stories behind our antique treasures.</p>
            <form class="newsletter-form-large" id="newsletterFormHome">
                <input type="email" name="email" placeholder="Enter your email address" required>
                <button type="submit" class="btn btn-primary">Subscribe</button>
            </form>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
