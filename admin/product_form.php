<?php
/**
 * Gauri Collections - Add/Edit Product Form
 */
require_once __DIR__ . '/../config/database.php';

if (!isAdmin()) {
    redirect(SITE_URL . '/pages/login.php');
}

$editId = (int)($_GET['id'] ?? 0);
$product = null;
$errors = [];

// Load product for edit mode
if ($editId > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$editId]);
        $product = $stmt->fetch();
        if (!$product) {
            setFlash('error', 'Product not found.');
            redirect(SITE_URL . '/admin/products.php');
        }
    } catch (PDOException $ex) {
        setFlash('error', 'Database error.');
        redirect(SITE_URL . '/admin/products.php');
    }
}

$pageTitle = $product ? 'Edit Product' : 'Add New Product';

// Load categories
try {
    $categories = $pdo->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY sort_order")->fetchAll();
} catch (PDOException $ex) {
    $categories = [];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token.';
    }

    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $shortDescription = trim($_POST['short_description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $salePrice = ($_POST['sale_price'] ?? '') !== '' ? (float)$_POST['sale_price'] : null;
    $sku = trim($_POST['sku'] ?? '');
    $stockQuantity = (int)($_POST['stock_quantity'] ?? 0);
    $categoryId = (int)($_POST['category_id'] ?? 0) ?: null;
    $material = trim($_POST['material'] ?? '');
    $height = trim($_POST['height'] ?? '');
    $weight = trim($_POST['weight'] ?? '');
    $origin = trim($_POST['origin'] ?? '');
    $era = trim($_POST['era'] ?? '');
    $conditionNote = trim($_POST['condition_note'] ?? '');
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if ($slug === '') {
        $slug = createSlug($name);
    } else {
        $slug = createSlug($slug);
    }

    // Validation
    if ($name === '') $errors[] = 'Product name is required.';
    if ($price <= 0) $errors[] = 'Price must be greater than zero.';
    if ($sku === '') $errors[] = 'SKU is required.';

    // Image upload
    $imageName = $product['image'] ?? null;
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        $fileType = $_FILES['image']['type'];
        $fileSize = $_FILES['image']['size'];

        if (!in_array($fileType, $allowedTypes)) {
            $errors[] = 'Invalid image type. Allowed: JPG, PNG, GIF, WebP.';
        } elseif ($fileSize > $maxSize) {
            $errors[] = 'Image size must not exceed 5MB.';
        } else {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = $slug . '-' . time() . '.' . $ext;
            $uploadDir = UPLOAD_PATH;
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName)) {
                $errors[] = 'Failed to upload image.';
                $imageName = $product['image'] ?? null;
            }
        }
    }

    if (empty($errors)) {
        try {
            if ($product) {
                // Update
                $stmt = $pdo->prepare("UPDATE products SET name=?, slug=?, description=?, short_description=?, price=?, sale_price=?, sku=?, stock_quantity=?, category_id=?, material=?, height=?, weight=?, origin=?, era=?, condition_note=?, is_featured=?, is_active=?, image=? WHERE id=?");
                $stmt->execute([$name, $slug, $description, $shortDescription, $price, $salePrice, $sku, $stockQuantity, $categoryId, $material, $height, $weight, $origin, $era, $conditionNote, $isFeatured, $isActive, $imageName, $editId]);
                setFlash('success', 'Product updated successfully.');
            } else {
                // Insert
                $stmt = $pdo->prepare("INSERT INTO products (name, slug, description, short_description, price, sale_price, sku, stock_quantity, category_id, material, height, weight, origin, era, condition_note, is_featured, is_active, image) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute([$name, $slug, $description, $shortDescription, $price, $salePrice, $sku, $stockQuantity, $categoryId, $material, $height, $weight, $origin, $era, $conditionNote, $isFeatured, $isActive, $imageName]);
                setFlash('success', 'Product created successfully.');
            }
            redirect(SITE_URL . '/admin/products.php');
        } catch (PDOException $ex) {
            if ($ex->getCode() == 23000) {
                $errors[] = 'A product with this SKU or slug already exists.';
            } else {
                $errors[] = 'Database error. Please try again.';
            }
        }
    }
    // If errors, repopulate $product from POST for form re-display
    if (!empty($errors)) {
        $product = $product ?? [];
        $product = array_merge(is_array($product) ? $product : [], [
            'name' => $name, 'slug' => $slug, 'description' => $description,
            'short_description' => $shortDescription, 'price' => $price, 'sale_price' => $salePrice,
            'sku' => $sku, 'stock_quantity' => $stockQuantity, 'category_id' => $categoryId,
            'material' => $material, 'height' => $height, 'weight' => $weight,
            'origin' => $origin, 'era' => $era, 'condition_note' => $conditionNote,
            'is_featured' => $isFeatured, 'is_active' => $isActive,
        ]);
    }
}

require_once __DIR__ . '/header.php';
?>

<div class="admin-form-container">
    <a href="<?php echo SITE_URL; ?>/admin/products.php" class="btn btn-outline btn-sm" style="margin-bottom:1rem;"><i class="fas fa-arrow-left"></i> Back to Products</a>

    <?php if (!empty($errors)): ?>
    <div class="flash-message flash-error">
        <ul style="margin:0;padding-left:1.2rem;">
            <?php foreach ($errors as $err): ?>
            <li><?php echo e($err); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="admin-form">
        <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">

        <div class="form-group">
            <label for="name">Product Name *</label>
            <input type="text" name="name" id="name" class="form-control" value="<?php echo e($product['name'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="slug">Slug (auto-generated if empty)</label>
            <input type="text" name="slug" id="slug" class="form-control" value="<?php echo e($product['slug'] ?? ''); ?>">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="price">Price (₹) *</label>
                <input type="number" name="price" id="price" class="form-control" step="0.01" min="0" value="<?php echo e($product['price'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="sale_price">Sale Price (₹)</label>
                <input type="number" name="sale_price" id="sale_price" class="form-control" step="0.01" min="0" value="<?php echo e($product['sale_price'] ?? ''); ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="sku">SKU *</label>
                <input type="text" name="sku" id="sku" class="form-control" value="<?php echo e($product['sku'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="stock_quantity">Stock Quantity</label>
                <input type="number" name="stock_quantity" id="stock_quantity" class="form-control" min="0" value="<?php echo e($product['stock_quantity'] ?? 0); ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="category_id">Category</label>
            <select name="category_id" id="category_id" class="form-control">
                <option value="">— Select Category —</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?php echo (int)$cat['id']; ?>" <?php echo ((int)($product['category_id'] ?? 0)) === (int)$cat['id'] ? 'selected' : ''; ?>><?php echo e($cat['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="short_description">Short Description</label>
            <input type="text" name="short_description" id="short_description" class="form-control" value="<?php echo e($product['short_description'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="description">Full Description</label>
            <textarea name="description" id="description" class="form-control" rows="6"><?php echo e($product['description'] ?? ''); ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="material">Material</label>
                <input type="text" name="material" id="material" class="form-control" value="<?php echo e($product['material'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="height">Height</label>
                <input type="text" name="height" id="height" class="form-control" value="<?php echo e($product['height'] ?? ''); ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="weight">Weight</label>
                <input type="text" name="weight" id="weight" class="form-control" value="<?php echo e($product['weight'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="origin">Origin</label>
                <input type="text" name="origin" id="origin" class="form-control" value="<?php echo e($product['origin'] ?? ''); ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="era">Era</label>
                <input type="text" name="era" id="era" class="form-control" value="<?php echo e($product['era'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="condition_note">Condition Note</label>
                <input type="text" name="condition_note" id="condition_note" class="form-control" value="<?php echo e($product['condition_note'] ?? ''); ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="image">Product Image</label>
            <?php if (!empty($product['image'])): ?>
            <div style="margin-bottom:0.5rem;">
                <img src="<?php echo UPLOAD_URL . e($product['image']); ?>" alt="Current" class="admin-thumb-lg">
                <small>Current: <?php echo e($product['image']); ?></small>
            </div>
            <?php endif; ?>
            <input type="file" name="image" id="image" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp">
            <small>Max 5MB. Allowed: JPG, PNG, GIF, WebP.</small>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_featured" value="1" <?php echo !empty($product['is_featured']) ? 'checked' : ''; ?>>
                    Featured Product
                </label>
            </div>
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" value="1" <?php echo ($product === null || !empty($product['is_active'])) ? 'checked' : ''; ?>>
                    Active
                </label>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?php echo $editId ? 'Update Product' : 'Create Product'; ?></button>
            <a href="<?php echo SITE_URL; ?>/admin/products.php" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
