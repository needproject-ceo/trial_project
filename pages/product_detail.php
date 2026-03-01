<?php
/**
 * Gauri Collections - Product Detail Page
 */
require_once __DIR__ . '/../config/database.php';

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($productId <= 0) {
    setFlash('error', 'Product not found.');
    redirect(SITE_URL . '/pages/products.php');
}

// Fetch product
$stmt = $pdo->prepare("SELECT p.*, c.name AS category_name, c.id AS cat_id
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.id = ? AND p.is_active = 1");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    setFlash('error', 'Product not found.');
    redirect(SITE_URL . '/pages/products.php');
}

// Increment views
$pdo->prepare("UPDATE products SET views = views + 1 WHERE id = ?")->execute([$productId]);

// Related products (same category, exclude current)
$relatedStmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? AND is_active = 1 ORDER BY RAND() LIMIT 4");
$relatedStmt->execute([$product['cat_id'], $productId]);
$relatedProducts = $relatedStmt->fetchAll();

// Check if in wishlist
$inWishlist = false;
if (isLoggedIn()) {
    $wStmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $wStmt->execute([$_SESSION['user_id'], $productId]);
    $inWishlist = (bool)$wStmt->fetch();
}

$effectivePrice = $product['sale_price'] ?: $product['price'];
$pageTitle = e($product['name']) . ' - Gauri Collections';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumbs -->
<section class="breadcrumbs">
    <div class="container">
        <a href="<?php echo SITE_URL; ?>/index.php">Home</a>
        <span>/</span>
        <a href="<?php echo SITE_URL; ?>/pages/products.php">Shop</a>
        <span>/</span>
        <?php if ($product['category_name']): ?>
            <a href="<?php echo SITE_URL; ?>/pages/products.php?category=<?php echo (int)$product['cat_id']; ?>"><?php echo e($product['category_name']); ?></a>
            <span>/</span>
        <?php endif; ?>
        <span><?php echo e($product['name']); ?></span>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="product-detail">
            <!-- Images -->
            <div class="product-gallery">
                <div class="product-main-image-wrapper">
                    <img src="<?php echo SITE_URL; ?>/assets/images/placeholder-statue.svg" alt="<?php echo e($product['name']); ?>" class="product-main-image" id="mainProductImage">
                </div>
                <?php if ($product['image_2'] || $product['image_3']): ?>
                <div class="product-thumbnails">
                    <img src="<?php echo SITE_URL; ?>/assets/images/placeholder-statue.svg" alt="<?php echo e($product['name']); ?>" class="active" data-full="<?php echo SITE_URL; ?>/assets/images/placeholder-statue.svg">
                    <?php if ($product['image_2']): ?>
                    <img src="<?php echo SITE_URL; ?>/assets/images/placeholder-statue.svg" alt="<?php echo e($product['name']); ?> - 2" data-full="<?php echo SITE_URL; ?>/assets/images/placeholder-statue.svg">
                    <?php endif; ?>
                    <?php if ($product['image_3']): ?>
                    <img src="<?php echo SITE_URL; ?>/assets/images/placeholder-statue.svg" alt="<?php echo e($product['name']); ?> - 3" data-full="<?php echo SITE_URL; ?>/assets/images/placeholder-statue.svg">
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Details -->
            <div class="product-detail-info">
                <?php if ($product['category_name']): ?>
                    <span class="product-category"><?php echo e($product['category_name']); ?></span>
                <?php endif; ?>

                <h1><?php echo e($product['name']); ?></h1>

                <?php if ($product['sku']): ?>
                    <p class="product-sku">SKU: <?php echo e($product['sku']); ?></p>
                <?php endif; ?>

                <div class="product-price product-price-large">
                    <?php if ($product['sale_price']): ?>
                        <span class="price-current"><?php echo formatPrice($product['sale_price']); ?></span>
                        <span class="price-original"><?php echo formatPrice($product['price']); ?></span>
                        <?php
                        $discount = round((($product['price'] - $product['sale_price']) / $product['price']) * 100);
                        ?>
                        <span class="badge badge-sale"><?php echo $discount; ?>% OFF</span>
                    <?php else: ?>
                        <span class="price-current"><?php echo formatPrice($product['price']); ?></span>
                    <?php endif; ?>
                </div>

                <p class="product-short-desc"><?php echo e($product['short_description']); ?></p>

                <div class="product-stock">
                    <?php if ($product['stock_quantity'] > 0): ?>
                        <span class="in-stock"><i class="fas fa-check-circle"></i> In Stock (<?php echo (int)$product['stock_quantity']; ?> available)</span>
                    <?php else: ?>
                        <span class="out-of-stock"><i class="fas fa-times-circle"></i> Out of Stock</span>
                    <?php endif; ?>
                </div>

                <!-- Add to Cart -->
                <?php if ($product['stock_quantity'] > 0): ?>
                <form class="add-to-cart-form" id="addToCartForm">
                    <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
                    <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
                    <input type="hidden" name="action" value="add">
                    <div class="qty-wrapper">
                        <button type="button" class="qty-btn" data-action="decrease">−</button>
                        <input type="number" name="quantity" value="1" min="1" max="<?php echo (int)$product['stock_quantity']; ?>" class="qty-input">
                        <button type="button" class="qty-btn" data-action="increase">+</button>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg add-to-cart-submit">
                        <i class="fas fa-shopping-bag"></i> Add to Cart
                    </button>
                    <?php if (isLoggedIn()): ?>
                    <button type="button" class="btn btn-outline wishlist-toggle <?php echo $inWishlist ? 'active' : ''; ?>" data-product-id="<?php echo (int)$product['id']; ?>">
                        <i class="fas fa-heart"></i> <?php echo $inWishlist ? 'In Wishlist' : 'Add to Wishlist'; ?>
                    </button>
                    <?php endif; ?>
                </form>
                <?php endif; ?>

                <!-- Specifications -->
                <div class="product-specs">
                    <h3>Specifications</h3>
                    <table class="specs-table">
                        <?php if ($product['material']): ?>
                        <tr><th>Material</th><td><?php echo e($product['material']); ?></td></tr>
                        <?php endif; ?>
                        <?php if ($product['height']): ?>
                        <tr><th>Height</th><td><?php echo e($product['height']); ?></td></tr>
                        <?php endif; ?>
                        <?php if ($product['weight']): ?>
                        <tr><th>Weight</th><td><?php echo e($product['weight']); ?></td></tr>
                        <?php endif; ?>
                        <?php if ($product['origin']): ?>
                        <tr><th>Origin</th><td><?php echo e($product['origin']); ?></td></tr>
                        <?php endif; ?>
                        <?php if ($product['era']): ?>
                        <tr><th>Era / Period</th><td><?php echo e($product['era']); ?></td></tr>
                        <?php endif; ?>
                        <?php if ($product['condition_note']): ?>
                        <tr><th>Condition</th><td><?php echo e($product['condition_note']); ?></td></tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>

        <!-- Full Description -->
        <?php if ($product['description']): ?>
        <div class="product-full-description">
            <h3>Description</h3>
            <p><?php echo nl2br(e($product['description'])); ?></p>
        </div>
        <?php endif; ?>

        <!-- Related Products -->
        <?php if (!empty($relatedProducts)): ?>
        <div class="related-products">
            <div class="section-title">
                <h2>Related Products</h2>
            </div>
            <div class="products-grid">
                <?php foreach ($relatedProducts as $rp): ?>
                <div class="product-card animate-on-scroll">
                    <?php if ($rp['sale_price']): ?>
                        <span class="badge badge-sale">Sale</span>
                    <?php endif; ?>
                    <div class="product-image">
                        <img src="<?php echo SITE_URL; ?>/assets/images/placeholder-statue.svg" alt="<?php echo e($rp['name']); ?>" loading="lazy">
                        <div class="product-actions">
                            <a href="<?php echo SITE_URL; ?>/pages/product_detail.php?id=<?php echo (int)$rp['id']; ?>" class="btn-icon" title="View Details"><i class="fas fa-eye"></i></a>
                            <button class="btn-icon add-to-cart" data-product-id="<?php echo (int)$rp['id']; ?>" title="Add to Cart"><i class="fas fa-shopping-bag"></i></button>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3><a href="<?php echo SITE_URL; ?>/pages/product_detail.php?id=<?php echo (int)$rp['id']; ?>"><?php echo e($rp['name']); ?></a></h3>
                        <div class="product-price">
                            <?php if ($rp['sale_price']): ?>
                                <span class="price-current"><?php echo formatPrice($rp['sale_price']); ?></span>
                                <span class="price-original"><?php echo formatPrice($rp['price']); ?></span>
                            <?php else: ?>
                                <span class="price-current"><?php echo formatPrice($rp['price']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('addToCartForm');
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = form.querySelector('.add-to-cart-submit');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            try {
                const res = await fetch('<?php echo SITE_URL; ?>/pages/cart_actions.php', {
                    method: 'POST',
                    body: new FormData(form)
                });
                const data = await res.json();
                if (data.success) {
                    window.showToast(data.message || 'Added to cart!', 'success');
                    const el = document.getElementById('cartCount');
                    if (el && data.count !== undefined) el.textContent = data.count;
                } else {
                    window.showToast(data.message || 'Could not add to cart.', 'error');
                }
            } catch {
                window.showToast('Network error. Please try again.', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-shopping-bag"></i> Add to Cart';
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
