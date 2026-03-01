<?php
/**
 * Gauri Collections - Admin Header
 */
require_once __DIR__ . '/../config/database.php';

if (!isAdmin()) {
    redirect(SITE_URL . '/pages/login.php');
}

$adminPage = basename($_SERVER['PHP_SELF'], '.php');
$adminName = e(($_SESSION['user_first_name'] ?? 'Admin') . ' ' . ($_SESSION['user_last_name'] ?? ''));
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle ?? 'Admin Panel'); ?> - <?php echo e(getSetting('site_name', 'Gauri Collections')); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&family=Cinzel:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="admin-sidebar-header">
                <a href="<?php echo SITE_URL; ?>/admin/" class="logo">
                    <span class="logo-icon"><i class="fas fa-om"></i></span>
                    <div class="logo-text">
                        <span class="logo-name">Gauri Collections</span>
                        <span class="logo-tagline">Admin Panel</span>
                    </div>
                </a>
            </div>
            <nav class="admin-sidebar-nav">
                <a href="<?php echo SITE_URL; ?>/admin/" class="<?php echo $adminPage === 'index' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/products.php" class="<?php echo in_array($adminPage, ['products', 'product_form']) ? 'active' : ''; ?>">
                    <i class="fas fa-box-open"></i> <span>Products</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/categories.php" class="<?php echo $adminPage === 'categories' ? 'active' : ''; ?>">
                    <i class="fas fa-tags"></i> <span>Categories</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/orders.php" class="<?php echo in_array($adminPage, ['orders', 'order_detail']) ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart"></i> <span>Orders</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/users.php" class="<?php echo $adminPage === 'users' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> <span>Users</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/reviews.php" class="<?php echo $adminPage === 'reviews' ? 'active' : ''; ?>">
                    <i class="fas fa-star"></i> <span>Reviews</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/messages.php" class="<?php echo $adminPage === 'messages' ? 'active' : ''; ?>">
                    <i class="fas fa-envelope"></i> <span>Messages</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/coupons.php" class="<?php echo $adminPage === 'coupons' ? 'active' : ''; ?>">
                    <i class="fas fa-ticket-alt"></i> <span>Coupons</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/settings.php" class="<?php echo $adminPage === 'settings' ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i> <span>Settings</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/pages/logout.php">
                    <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <div class="admin-content">
            <!-- Top Header Bar -->
            <header class="admin-header">
                <div class="admin-header-left">
                    <button class="admin-sidebar-toggle" id="sidebarToggle" title="Toggle Sidebar">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h2><?php echo e($pageTitle ?? 'Dashboard'); ?></h2>
                </div>
                <div class="admin-header-right">
                    <a href="<?php echo SITE_URL; ?>/index.php" class="btn btn-sm btn-outline" target="_blank">
                        <i class="fas fa-external-link-alt"></i> View Site
                    </a>
                    <button class="theme-toggle" id="themeToggle" title="Toggle Dark/Light Mode">
                        <i class="fas fa-moon"></i>
                    </button>
                    <span class="admin-user-name">
                        <i class="fas fa-user-shield"></i> <?php echo $adminName; ?>
                    </span>
                </div>
            </header>

            <!-- Flash Messages -->
            <?php $flash = getFlash(); ?>
            <?php if ($flash): ?>
            <div class="flash-message flash-<?php echo e($flash['type']); ?>" id="flashMessage">
                <span><?php echo e($flash['message']); ?></span>
                <button class="flash-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
            </div>
            <?php endif; ?>

            <!-- Page Content -->
            <div class="admin-page-content">
