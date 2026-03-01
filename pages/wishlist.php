<?php
/**
 * Gauri Collections - Wishlist Page
 */
require_once __DIR__ . '/../config/database.php';

if (!isLoggedIn()) {
    setFlash('error', 'Please login to view your wishlist.');
    redirect(SITE_URL . '/pages/login.php');
}

$userId = $_SESSION['user_id'];

// Fetch wishlist items
$stmt = $pdo->prepare("SELECT w.id AS wishlist_id, w.created_at AS added_at, p.*
    FROM wishlist w
    JOIN products p ON w.product_id = p.id
    WHERE w.user_id = ? AND p.is_active = 1
    ORDER BY w.created_at DESC");
$stmt->execute([$userId]);
$wishlistItems = $stmt->fetchAll();

$pageTitle = 'My Wishlist - Gauri Collections';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumbs -->
<section class="breadcrumbs">
    <div class="container">
        <a href="<?php echo SITE_URL; ?>/index.php">Home</a>
        <span>/</span>
        <span>My Wishlist</span>
    </div>
</section>

<section class="section">
    <div class="container">
        <h1 class="page-title">My Wishlist (<?php echo count($wishlistItems); ?>)</h1>

        <?php if (empty($wishlistItems)): ?>
            <div class="empty-state">
                <i class="fas fa-heart" style="font-size:64px;color:var(--text-light);margin-bottom:20px;"></i>
                <h3>Your wishlist is empty</h3>
                <p>Browse our collection and save your favorite divine masterpieces.</p>
                <a href="<?php echo SITE_URL; ?>/pages/products.php" class="btn btn-primary">Explore Collection</a>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($wishlistItems as $item): ?>
                <div class="product-card animate-on-scroll" id="wishlist-item-<?php echo (int)$item['wishlist_id']; ?>">
                    <?php if ($item['sale_price']): ?>
                        <span class="badge badge-sale">Sale</span>
                    <?php endif; ?>
                    <div class="product-image">
                        <img src="<?php echo SITE_URL; ?>/assets/images/placeholder-statue.svg" alt="<?php echo e($item['name']); ?>" loading="lazy">
                        <div class="product-actions">
                            <a href="<?php echo SITE_URL; ?>/pages/product_detail.php?id=<?php echo (int)$item['id']; ?>" class="btn-icon" title="View Details"><i class="fas fa-eye"></i></a>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3><a href="<?php echo SITE_URL; ?>/pages/product_detail.php?id=<?php echo (int)$item['id']; ?>"><?php echo e($item['name']); ?></a></h3>
                        <div class="product-price">
                            <?php if ($item['sale_price']): ?>
                                <span class="price-current"><?php echo formatPrice($item['sale_price']); ?></span>
                                <span class="price-original"><?php echo formatPrice($item['price']); ?></span>
                            <?php else: ?>
                                <span class="price-current"><?php echo formatPrice($item['price']); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="wishlist-actions" style="margin-top:10px;display:flex;gap:8px;">
                            <?php if ($item['stock_quantity'] > 0): ?>
                            <button class="btn btn-sm btn-primary add-to-cart" data-product-id="<?php echo (int)$item['id']; ?>">
                                <i class="fas fa-shopping-bag"></i> Add to Cart
                            </button>
                            <?php else: ?>
                            <span class="btn btn-sm btn-outline" disabled>Out of Stock</span>
                            <?php endif; ?>
                            <button class="btn btn-sm btn-outline wishlist-remove-btn" data-product-id="<?php echo (int)$item['id']; ?>" data-wishlist-id="<?php echo (int)$item['wishlist_id']; ?>">
                                <i class="fas fa-trash-alt"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const SITE_URL = '<?php echo SITE_URL; ?>';

    function getCSRFToken() {
        const input = document.querySelector('input[name="csrf_token"]');
        return input ? input.value : '';
    }

    document.querySelectorAll('.wishlist-remove-btn').forEach(function(btn) {
        btn.addEventListener('click', async function() {
            const productId = this.dataset.productId;
            const wishlistId = this.dataset.wishlistId;
            const formData = new FormData();
            formData.append('action', 'remove');
            formData.append('product_id', productId);
            formData.append('csrf_token', getCSRFToken());
            try {
                const res = await fetch(SITE_URL + '/pages/wishlist_actions.php', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.success) {
                    const card = document.getElementById('wishlist-item-' + wishlistId);
                    if (card) card.remove();
                    window.showToast(data.message || 'Removed from wishlist.', 'success');
                } else {
                    window.showToast(data.message || 'Error removing item.', 'error');
                }
            } catch {
                window.showToast('Network error.', 'error');
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
