<?php
/**
 * Gauri Collections - Admin Categories
 */
$pageTitle = 'Categories';
require_once __DIR__ . '/header.php';

$errors = [];
$editCat = null;

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([(int)$_POST['delete_id']]);
            setFlash('success', 'Category deleted successfully.');
        } catch (PDOException $ex) {
            setFlash('error', 'Cannot delete category. It may have products assigned.');
        }
    }
    redirect(SITE_URL . '/admin/categories.php');
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_category'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token.';
    }

    $catId = (int)($_POST['cat_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if ($slug === '') $slug = createSlug($name);
    else $slug = createSlug($slug);

    if ($name === '') $errors[] = 'Category name is required.';

    if (empty($errors)) {
        try {
            if ($catId > 0) {
                $stmt = $pdo->prepare("UPDATE categories SET name=?, slug=?, description=?, sort_order=?, is_active=? WHERE id=?");
                $stmt->execute([$name, $slug, $description, $sortOrder, $isActive, $catId]);
                setFlash('success', 'Category updated successfully.');
            } else {
                $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description, sort_order, is_active) VALUES (?,?,?,?,?)");
                $stmt->execute([$name, $slug, $description, $sortOrder, $isActive]);
                setFlash('success', 'Category created successfully.');
            }
            redirect(SITE_URL . '/admin/categories.php');
        } catch (PDOException $ex) {
            if ($ex->getCode() == 23000) {
                $errors[] = 'A category with this slug already exists.';
            } else {
                $errors[] = 'Database error. Please try again.';
            }
        }
    }
}

// Load category for editing
$editId = (int)($_GET['edit'] ?? 0);
if ($editId > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$editId]);
        $editCat = $stmt->fetch();
    } catch (PDOException $ex) {
        $editCat = null;
    }
}

// Load all categories
try {
    $allCategories = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS product_count FROM categories c ORDER BY c.sort_order, c.name")->fetchAll();
} catch (PDOException $ex) {
    $allCategories = [];
}
?>

<?php if (!empty($errors)): ?>
<div class="flash-message flash-error">
    <ul style="margin:0;padding-left:1.2rem;">
        <?php foreach ($errors as $err): ?>
        <li><?php echo e($err); ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="admin-grid-two">
    <!-- Category Form -->
    <div class="admin-section">
        <h3 class="admin-section-title"><?php echo $editCat ? 'Edit Category' : 'Add New Category'; ?></h3>
        <form method="post" class="admin-form">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            <input type="hidden" name="cat_id" value="<?php echo (int)($editCat['id'] ?? 0); ?>">
            <input type="hidden" name="save_category" value="1">

            <div class="form-group">
                <label for="name">Name *</label>
                <input type="text" name="name" id="name" class="form-control" value="<?php echo e($editCat['name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="slug">Slug</label>
                <input type="text" name="slug" id="slug" class="form-control" value="<?php echo e($editCat['slug'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" id="description" class="form-control" rows="3"><?php echo e($editCat['description'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label for="sort_order">Sort Order</label>
                <input type="number" name="sort_order" id="sort_order" class="form-control" value="<?php echo (int)($editCat['sort_order'] ?? 0); ?>">
            </div>
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" value="1" <?php echo ($editCat === null || !empty($editCat['is_active'])) ? 'checked' : ''; ?>>
                    Active
                </label>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?php echo $editCat ? 'Update' : 'Add'; ?> Category</button>
                <?php if ($editCat): ?>
                <a href="<?php echo SITE_URL; ?>/admin/categories.php" class="btn btn-outline">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Categories List -->
    <div class="admin-section">
        <h3 class="admin-section-title">All Categories</h3>
        <div class="admin-table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Products</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($allCategories)): ?>
                    <tr><td colspan="6" class="text-center">No categories found.</td></tr>
                    <?php else: ?>
                    <?php foreach ($allCategories as $cat): ?>
                    <tr>
                        <td><?php echo e($cat['name']); ?></td>
                        <td><small><?php echo e($cat['slug']); ?></small></td>
                        <td><?php echo (int)$cat['product_count']; ?></td>
                        <td><?php echo (int)$cat['sort_order']; ?></td>
                        <td>
                            <?php if ($cat['is_active']): ?>
                            <span class="status-badge status-delivered">Active</span>
                            <?php else: ?>
                            <span class="status-badge status-cancelled">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="admin-actions">
                            <a href="?edit=<?php echo (int)$cat['id']; ?>" class="btn btn-sm btn-outline" title="Edit"><i class="fas fa-edit"></i></a>
                            <form method="post" style="display:inline" onsubmit="return confirm('Delete this category?');">
                                <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
                                <input type="hidden" name="delete_id" value="<?php echo (int)$cat['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
