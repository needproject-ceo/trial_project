<?php
/**
 * Gauri Collections - About Us Page
 */
require_once __DIR__ . '/../config/database.php';
$pageTitle = 'About Us - Gauri Collections';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumbs -->
<section class="breadcrumbs">
    <div class="container">
        <a href="<?php echo SITE_URL; ?>/index.php">Home</a>
        <span>/</span>
        <span>About Us</span>
    </div>
</section>

<!-- Hero -->
<section class="page-hero">
    <div class="container">
        <h1>Our Story</h1>
        <p>Four decades of preserving and sharing India's magnificent spiritual heritage.</p>
    </div>
</section>

<!-- Company Story -->
<section class="section">
    <div class="container">
        <div class="about-story">
            <div class="about-story-content animate-on-scroll">
                <span class="section-subtitle">Since 1985</span>
                <h2>A Legacy of Divine Artistry</h2>
                <p>Gauri Collections was founded in 1985 by a passionate connoisseur of Indian art and spirituality. What began as a small collection of antique statues discovered in the temples and markets of Rajasthan has grown into one of India's most respected destinations for authentic antique religious sculptures.</p>
                <p>Our founder's vision was simple yet profound — to preserve the divine craftsmanship of ancient Indian artisans and share these treasures with collectors, devotees, and art enthusiasts worldwide. Each piece in our collection is carefully sourced, authenticated, and restored with the utmost respect for its historical and spiritual significance.</p>
                <p>Today, Gauri Collections houses over 500 unique pieces spanning centuries of Indian artistic tradition, from Chola bronzes to Rajasthani marble carvings, from Gandhara stone sculptures to Kerala wood masterpieces.</p>
            </div>
            <div class="about-story-image animate-on-scroll">
                <div class="about-image-placeholder">
                    <i class="fas fa-om"></i>
                    <span>Est. 1985</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mission & Values -->
<section class="section section-alt">
    <div class="container">
        <div class="section-title animate-on-scroll">
            <span class="section-subtitle">What Drives Us</span>
            <h2>Our Mission &amp; Values</h2>
        </div>
        <div class="values-grid">
            <div class="value-card animate-on-scroll">
                <div class="value-icon"><i class="fas fa-certificate"></i></div>
                <h3>Authenticity</h3>
                <p>Every piece is meticulously verified by our team of experts. We provide certificates of authenticity and detailed provenance documentation for each statue.</p>
            </div>
            <div class="value-card animate-on-scroll">
                <div class="value-icon"><i class="fas fa-hand-holding-heart"></i></div>
                <h3>Preservation</h3>
                <p>We are committed to preserving India's artistic heritage. Our restoration processes use traditional techniques to maintain the integrity of each piece.</p>
            </div>
            <div class="value-card animate-on-scroll">
                <div class="value-icon"><i class="fas fa-gem"></i></div>
                <h3>Quality</h3>
                <p>We curate only the finest pieces that demonstrate exceptional craftsmanship, historical significance, and spiritual beauty.</p>
            </div>
            <div class="value-card animate-on-scroll">
                <div class="value-icon"><i class="fas fa-users"></i></div>
                <h3>Community</h3>
                <p>We foster a community of collectors, scholars, and devotees who share a deep appreciation for Indian religious art and culture.</p>
            </div>
        </div>
    </div>
</section>

<!-- Expertise -->
<section class="section">
    <div class="container">
        <div class="section-title animate-on-scroll">
            <span class="section-subtitle">Why Choose Us</span>
            <h2>Our Expertise</h2>
        </div>
        <div class="expertise-grid">
            <div class="expertise-item animate-on-scroll">
                <i class="fas fa-search"></i>
                <h4>Expert Sourcing</h4>
                <p>Our network spans across India's heritage sites, ensuring access to the rarest and most authentic pieces available.</p>
            </div>
            <div class="expertise-item animate-on-scroll">
                <i class="fas fa-microscope"></i>
                <h4>Scientific Authentication</h4>
                <p>We use both traditional expertise and modern scientific methods to verify the age and authenticity of every piece.</p>
            </div>
            <div class="expertise-item animate-on-scroll">
                <i class="fas fa-tools"></i>
                <h4>Professional Restoration</h4>
                <p>Our skilled craftsmen use traditional techniques to carefully restore pieces while preserving their original character.</p>
            </div>
            <div class="expertise-item animate-on-scroll">
                <i class="fas fa-shipping-fast"></i>
                <h4>Secure Delivery</h4>
                <p>Custom packaging and insured shipping ensure your precious acquisition arrives in perfect condition.</p>
            </div>
        </div>
    </div>
</section>

<!-- Statistics -->
<section class="section section-alt">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item animate-on-scroll">
                <div class="stat-number">40+</div>
                <div class="stat-label">Years of Excellence</div>
            </div>
            <div class="stat-item animate-on-scroll">
                <div class="stat-number">500+</div>
                <div class="stat-label">Unique Pieces</div>
            </div>
            <div class="stat-item animate-on-scroll">
                <div class="stat-number">10,000+</div>
                <div class="stat-label">Happy Collectors</div>
            </div>
            <div class="stat-item animate-on-scroll">
                <div class="stat-number">25+</div>
                <div class="stat-label">Countries Served</div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="section">
    <div class="container" style="text-align:center;">
        <div class="animate-on-scroll">
            <h2>Start Your Collection Today</h2>
            <p style="max-width:600px;margin:15px auto;">Discover the perfect divine sculpture that resonates with your spirit and enriches your space.</p>
            <a href="<?php echo SITE_URL; ?>/pages/products.php" class="btn btn-primary btn-lg">Explore Our Collection</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
