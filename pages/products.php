<?php
/**
 * Gauri Collections - Product Listing Page
 */
require_once __DIR__ . '/../config/database.php';

// Filters
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 0;
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Build query
$where = ['p.is_active = 1'];
$params = [];

if ($categoryId > 0) {
    $where[] = 'p.category_id = ?';
    $params[] = $categoryId;
}
if ($search !== '') {
    $where[] = '(p.name LIKE ? OR p.short_description LIKE ? OR p.material LIKE ?)';
    $searchTerm = '%' . $search . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}
if ($minPrice > 0) {
    $where[] = 'COALESCE(p.sale_price, p.price) >= ?';
    $params[] = $minPrice;
}
if ($maxPrice > 0) {
    $where[] = 'COALESCE(p.sale_price, p.price) <= ?';
    $params[] = $maxPrice;
}

$whereClause = implode(' AND ', $where);

$orderBy = match ($sort) {
    'price_asc' => 'COALESCE(p.sale_price, p.price) ASC',
    'price_desc' => 'COALESCE(p.sale_price, p.price) DESC',
    'name' => 'p.name ASC',
    default => 'p.created_at DESC',
};

// Count total
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM products p WHERE $whereClause");
$countStmt->execute($params);
$totalProducts = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalProducts / $perPage));

// Fetch products
$sql = "SELECT p.*, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE $whereClause
        ORDER BY $orderBy
        LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Fetch categories for sidebar
$categories = $pdo->query("SELECT c.*, COUNT(p.id) AS product_count
    FROM categories c
    LEFT JOIN products p ON c.id = p.category_id AND p.is_active = 1
    WHERE c.is_active = 1
    GROUP BY c.id
    ORDER BY c.sort_order")->fetchAll();

// Current category name for breadcrumb
$currentCategory = null;
if ($categoryId > 0) {
    $catStmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
    $catStmt->execute([$categoryId]);
    $currentCategory = $catStmt->fetchColumn();
}

$pageTitle = $currentCategory ? e($currentCategory) . ' - Gauri Collections' : 'Shop - Gauri Collections';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumbs -->
<section class="breadcrumbs">
    <div class="container">
        <a href="<?php echo SITE_URL; ?>/index.php">Home</a>
        <span>/</span>
        <?php if ($currentCategory): ?>
            <a href="<?php echo SITE_URL; ?>/pages/products.php">Shop</a>
            <span>/</span>
            <span><?php echo e($currentCategory); ?></span>
        <?php elseif ($search !== ''): ?>
            <a href="<?php echo SITE_URL; ?>/pages/products.php">Shop</a>
            <span>/</span>
            <span>Search: "<?php echo e($search); ?>"</span>
        <?php else: ?>
            <span>Shop</span>
        <?php endif; ?>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="shop-layout">
            <!-- Sidebar -->
            <aside class="shop-sidebar">
                <div class="sidebar-widget">
                    <h3>Categories</h3>
                    <ul class="category-list">
                        <li><a href="<?php echo SITE_URL; ?>/pages/products.php" class="<?php echo $categoryId === 0 ? 'active' : ''; ?>">All Products</a></li>
                        <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="<?php echo SITE_URL; ?>/pages/products.php?category=<?php echo (int)$cat['id']; ?>" class="<?php echo $categoryId === (int)$cat['id'] ? 'active' : ''; ?>">
                                <?php echo e($cat['name']); ?> <span>(<?php echo (int)$cat['product_count']; ?>)</span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="sidebar-widget">
                    <h3>Price Filter</h3>
                    <form method="GET" action="<?php echo SITE_URL; ?>/pages/products.php">
                        <?php if ($categoryId > 0): ?>
                            <input type="hidden" name="category" value="<?php echo $categoryId; ?>">
                        <?php endif; ?>
                        <?php if ($search !== ''): ?>
                            <input type="hidden" name="search" value="<?php echo e($search); ?>">
                        <?php endif; ?>
                        <?php if ($sort !== 'newest'): ?>
                            <input type="hidden" name="sort" value="<?php echo e($sort); ?>">
                        <?php endif; ?>
                        <div class="price-inputs">
                            <input type="number" name="min_price" placeholder="Min" value="<?php echo $minPrice > 0 ? (int)$minPrice : ''; ?>" min="0">
                            <span>—</span>
                            <input type="number" name="max_price" placeholder="Max" value="<?php echo $maxPrice > 0 ? (int)$maxPrice : ''; ?>" min="0">
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary" style="width:100%;margin-top:10px;">Apply Filter</button>
                    </form>
                </div>
            </aside>

            <!-- Products -->
            <div class="shop-products">
                <div class="shop-toolbar">
                    <p class="results-count">Showing <?php echo count($products); ?> of <?php echo $totalProducts; ?> products</p>
                    <div class="sort-options">
                        <label for="sortSelect">Sort by:</label>
                        <select id="sortSelect" onchange="window.location.href=this.value">
                            <?php
                            $buildUrl = function($s) use ($categoryId, $search, $minPrice, $maxPrice) {
                                $params = ['sort' => $s];
                                if ($categoryId > 0) $params['category'] = $categoryId;
                                if ($search !== '') $params['search'] = $search;
                                if ($minPrice > 0) $params['min_price'] = $minPrice;
                                if ($maxPrice > 0) $params['max_price'] = $maxPrice;
                                return SITE_URL . '/pages/products.php?' . http_build_query($params);
                            };
                            $sortOptions = [
                                'newest' => 'Newest First',
                                'price_asc' => 'Price: Low to High',
                                'price_desc' => 'Price: High to Low',
                                'name' => 'Name: A-Z',
                            ];
                            foreach ($sortOptions as $val => $label):
                            ?>
                            <option value="<?php echo e($buildUrl($val)); ?>" <?php echo $sort === $val ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <?php if (empty($products)): ?>
                    <div class="empty-state">
                        <i class="fas fa-search" style="font-size:48px;color:var(--text-light);margin-bottom:20px;"></i>
                        <h3>No products found</h3>
                        <p>Try adjusting your filters or search terms.</p>
                        <a href="<?php echo SITE_URL; ?>/pages/products.php" class="btn btn-primary">View All Products</a>
                    </div>
                <?php else: ?>
                    <div class="products-grid">
                        <?php foreach ($products as $product): ?>
                        <div class="product-card animate-on-scroll">
                            <?php if ($product['sale_price']): ?>
                                <span class="badge badge-sale">Sale</span>
                            <?php endif; ?>
                            <div class="product-image">
                                <img src="<?php echo SITE_URL; ?>/assets/images/placeholder-statue.svg" alt="<?php echo e($product['name']); ?>" loading="lazy">
                                <div class="product-actions">
                                    <a href="<?php echo SITE_URL; ?>/pages/product_detail.php?id=<?php echo (int)$product['id']; ?>" class="btn-icon" title="View Details"><i class="fas fa-eye"></i></a>
                                    <button class="btn-icon add-to-cart" data-product-id="<?php echo (int)$product['id']; ?>" title="Add to Cart"><i class="fas fa-shopping-bag"></i></button>
                                    <?php if (isLoggedIn()): ?>
                                    <button class="btn-icon wishlist-toggle" data-product-id="<?php echo (int)$product['id']; ?>" title="Add to Wishlist"><i class="fas fa-heart"></i></button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="product-info">
                                <span class="product-category"><?php echo e($product['category_name'] ?? ''); ?></span>
                                <h3><a href="<?php echo SITE_URL; ?>/pages/product_detail.php?id=<?php echo (int)$product['id']; ?>"><?php echo e($product['name']); ?></a></h3>
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

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php
                        $baseParams = [];
                        if ($categoryId > 0) $baseParams['category'] = $categoryId;
                        if ($search !== '') $baseParams['search'] = $search;
                        if ($sort !== 'newest') $baseParams['sort'] = $sort;
                        if ($minPrice > 0) $baseParams['min_price'] = $minPrice;
                        if ($maxPrice > 0) $baseParams['max_price'] = $maxPrice;

                        $pageUrl = function($p) use ($baseParams) {
                            $baseParams['page'] = $p;
                            return SITE_URL . '/pages/products.php?' . http_build_query($baseParams);
                        };
                        ?>
                        <?php if ($page > 1): ?>
                            <a href="<?php echo e($pageUrl($page - 1)); ?>" class="page-link">&laquo; Prev</a>
                        <?php endif; ?>
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <a href="<?php echo e($pageUrl($i)); ?>" class="page-link <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>
                        <?php if ($page < $totalPages): ?>
                            <a href="<?php echo e($pageUrl($page + 1)); ?>" class="page-link">Next &raquo;</a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
