<?php
/**
 * Gauri Collections - Header Include
 */
$cartCount = getCartCount();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo e(getSetting('meta_description')); ?>">
    <title><?php echo e($pageTitle ?? 'Gauri Collections - Antique Indian God Statues'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&family=Cinzel:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="top-bar-content">
                <div class="top-bar-left">
                    <span><i class="fas fa-phone-alt"></i> <?php echo e(getSetting('site_phone')); ?></span>
                    <span><i class="fas fa-envelope"></i> <?php echo e(getSetting('site_email')); ?></span>
                </div>
                <div class="top-bar-right">
                    <?php if (isLoggedIn()): ?>
                        <a href="<?php echo SITE_URL; ?>/pages/account.php"><i class="fas fa-user"></i> My Account</a>
                        <?php if (isAdmin()): ?>
                            <a href="<?php echo SITE_URL; ?>/admin/"><i class="fas fa-cog"></i> Admin Panel</a>
                        <?php endif; ?>
                        <a href="<?php echo SITE_URL; ?>/pages/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    <?php else: ?>
                        <a href="<?php echo SITE_URL; ?>/pages/login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                        <a href="<?php echo SITE_URL; ?>/pages/register.php"><i class="fas fa-user-plus"></i> Register</a>
                    <?php endif; ?>
                    <button class="theme-toggle" id="themeToggle" title="Toggle Dark/Light Mode">
                        <i class="fas fa-moon"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation -->
    <header class="main-header" id="mainHeader">
        <div class="container">
            <div class="header-content">
                <a href="<?php echo SITE_URL; ?>/index.php" class="logo">
                    <span class="logo-icon"><i class="fas fa-om"></i></span>
                    <div class="logo-text">
                        <span class="logo-name">Gauri Collections</span>
                        <span class="logo-tagline">Divine Antique Artistry</span>
                    </div>
                </a>
                <nav class="main-nav" id="mainNav">
                    <a href="<?php echo SITE_URL; ?>/index.php" class="<?php echo $currentPage === 'index' ? 'active' : ''; ?>">Home</a>
                    <a href="<?php echo SITE_URL; ?>/pages/products.php" class="<?php echo $currentPage === 'products' ? 'active' : ''; ?>">Shop</a>
                    <div class="nav-dropdown">
                        <a href="#" class="dropdown-toggle">Categories <i class="fas fa-chevron-down"></i></a>
                        <div class="dropdown-menu">
                            <?php
                            try {
                                $catStmt = $pdo->query("SELECT id, name, slug FROM categories WHERE is_active = 1 ORDER BY sort_order");
                                while ($cat = $catStmt->fetch()) {
                                    echo '<a href="' . SITE_URL . '/pages/products.php?category=' . (int)$cat['id'] . '">' . e($cat['name']) . '</a>';
                                }
                            } catch (PDOException $e) {}
                            ?>
                        </div>
                    </div>
                    <a href="<?php echo SITE_URL; ?>/pages/about.php" class="<?php echo $currentPage === 'about' ? 'active' : ''; ?>">About</a>
                    <a href="<?php echo SITE_URL; ?>/pages/contact.php" class="<?php echo $currentPage === 'contact' ? 'active' : ''; ?>">Contact</a>
                </nav>
                <div class="header-actions">
                    <a href="<?php echo SITE_URL; ?>/pages/cart.php" class="cart-icon">
                        <i class="fas fa-shopping-bag"></i>
                        <span class="cart-count" id="cartCount"><?php echo $cartCount; ?></span>
                    </a>
                    <?php if (isLoggedIn()): ?>
                        <a href="<?php echo SITE_URL; ?>/pages/wishlist.php" class="wishlist-icon" title="Wishlist">
                            <i class="fas fa-heart"></i>
                        </a>
                    <?php endif; ?>
                    <button class="mobile-menu-toggle" id="mobileMenuToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Flash Messages -->
    <?php $flash = getFlash(); ?>
    <?php if ($flash): ?>
    <div class="flash-message flash-<?php echo e($flash['type']); ?>" id="flashMessage">
        <div class="container">
            <span><?php echo e($flash['message']); ?></span>
            <button class="flash-close" onclick="this.parentElement.parentElement.remove()"><i class="fas fa-times"></i></button>
        </div>
    </div>
    <?php endif; ?>

    <main class="main-content">
