<?php
/**
 * Gauri Collections - Admin Products Listing
 */
$pageTitle = 'Products';
require_once __DIR__ . '/header.php';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
    } else {
        $delId = (int)$_POST['delete_id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$delId]);
            setFlash('success', 'Product deleted successfully.');
        } catch (PDOException $ex) {
            setFlash('error', 'Cannot delete product. It may be referenced by orders.');
        }
    }
    redirect(SITE_URL . '/admin/products.php');
}

// Filters
$search = trim($_GET['search'] ?? '');
$categoryFilter = (int)($_GET['category'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

$where = [];
$params = [];

if ($search !== '') {
    $where[] = "(p.name LIKE ? OR p.sku LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($categoryFilter > 0) {
    $where[] = "p.category_id = ?";
    $params[] = $categoryFilter;
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

try {
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM products p $whereSQL");
    $countStmt->execute($params);
    $totalProducts = (int)$countStmt->fetchColumn();
    $totalPages = max(1, ceil($totalProducts / $perPage));

    $params[] = $perPage;
    $params[] = $offset;
    $stmt = $pdo->prepare("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id $whereSQL ORDER BY p.created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    $categories = $pdo->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY sort_order")->fetchAll();
} catch (PDOException $ex) {
    $products = [];
    $categories = [];
    $totalPages = 1;
}
?>

<div class="admin-toolbar">
    <a href="<?php echo SITE_URL; ?>/admin/product_form.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Product</a>
    <form method="get" class="admin-filter-form">
        <input type="text" name="search" placeholder="Search products..." value="<?php echo e($search); ?>" class="form-control">
        <select name="category" class="form-control">
            <option value="0">All Categories</option>
            <?php foreach ($categories as $cat): ?>
            <option value="<?php echo (int)$cat['id']; ?>" <?php echo $categoryFilter === (int)$cat['id'] ? 'selected' : ''; ?>><?php echo e($cat['name']); ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-outline"><i class="fas fa-search"></i> Filter</button>
    </form>
</div>

<div class="admin-table-responsive">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>SKU</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Category</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($products)): ?>
            <tr><td colspan="8" class="text-center">No products found.</td></tr>
            <?php else: ?>
            <?php foreach ($products as $prod): ?>
            <tr>
                <td>
                    <?php if ($prod['image']): ?>
                    <img src="<?php echo UPLOAD_URL . e($prod['image']); ?>" alt="<?php echo e($prod['name']); ?>" class="admin-thumb">
                    <?php else: ?>
                    <span class="admin-no-image"><i class="fas fa-image"></i></span>
                    <?php endif; ?>
                </td>
                <td><?php echo e($prod['name']); ?></td>
                <td><?php echo e($prod['sku']); ?></td>
                <td>
                    <?php echo formatPrice($prod['price']); ?>
                    <?php if ($prod['sale_price']): ?>
                    <br><small class="text-success"><?php echo formatPrice($prod['sale_price']); ?></small>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="<?php echo $prod['stock_quantity'] <= 2 ? 'text-danger' : ''; ?>">
                        <?php echo (int)$prod['stock_quantity']; ?>
                    </span>
                </td>
                <td><?php echo e($prod['category_name'] ?? '—'); ?></td>
                <td>
                    <?php if ($prod['is_active']): ?>
                    <span class="status-badge status-delivered">Active</span>
                    <?php else: ?>
                    <span class="status-badge status-cancelled">Inactive</span>
                    <?php endif; ?>
                    <?php if ($prod['is_featured']): ?>
                    <span class="status-badge status-confirmed">Featured</span>
                    <?php endif; ?>
                </td>
                <td class="admin-actions">
                    <a href="<?php echo SITE_URL; ?>/admin/product_form.php?id=<?php echo (int)$prod['id']; ?>" class="btn btn-sm btn-outline" title="Edit"><i class="fas fa-edit"></i></a>
                    <form method="post" style="display:inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                        <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
                        <input type="hidden" name="delete_id" value="<?php echo (int)$prod['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($totalPages > 1): ?>
<div class="admin-pagination">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $categoryFilter; ?>" class="btn btn-sm <?php echo $i === $page ? 'btn-primary' : 'btn-outline'; ?>"><?php echo $i; ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>
