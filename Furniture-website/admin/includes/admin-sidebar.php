<?php
$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title.' | Admin' : 'Admin Panel' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>

<!-- SIDEBAR -->
<div class="admin-sidebar" id="admin-sidebar">
    <a href="index.php" class="admin-sidebar-brand">
       🪑 WoodCraft Admin
    </a>

    <nav class="admin-nav">
        <a href="index.php" class="<?= $current == 'index.php' ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="products.php" class="<?= $current == 'products.php' ? 'active' : '' ?>">
            <i class="fas fa-couch"></i> Products
        </a>
        <a href="product-add.php" class="<?= $current == 'product-add.php' ? 'active' : '' ?>">
            <i class="fas fa-plus-circle"></i> Add Product
        </a>
        <a href="categories.php" class="<?= $current == 'categories.php' ? 'active' : '' ?>">
            <i class="fas fa-tags"></i> Categories
        </a>
        <a href="inquiries.php" class="<?= $current == 'inquiries.php' ? 'active' : '' ?>">
            <i class="fas fa-envelope"></i> Inquiries
            <?php
            // New inquiry count badge
            require_once '../includes/db.php';
            $new_count_result = db_query($conn, "SELECT COUNT(*) as cnt FROM inquiries WHERE status = 'new'");
            $new_count = mysqli_fetch_assoc($new_count_result)['cnt'];
            if($new_count > 0): ?>
            <span class="ms-auto badge" 
                  style="background:#E74C3C; color:white; border-radius:50px; font-size:11px;">
                <?= $new_count ?>
            </span>
            <?php endif; ?>
        </a>

        <a href="orders.php" class="<?= $current == 'orders.php' || $current == 'order-detail.php' ? 'active' : '' ?>">
            <i class="fas fa-shopping-bag"></i> Orders
            <?php
            $pend = mysqli_fetch_assoc(db_query($conn, "SELECT COUNT(*) as c FROM orders WHERE status='pending'"))['c'];
            if($pend > 0): ?>
            <span class="ms-auto badge" style="background:#F39C12;color:white;border-radius:50px;font-size:11px;"><?= $pend ?></span>
            <?php endif; ?>
        </a>

        <a href="users.php" class="<?= $current == 'users.php' ? 'active' : '' ?>">
            <i class="fas fa-users"></i> Customers
        </a>

        <a href="product-variants.php" class="<?= $current == 'product-variants.php' ? 'active' : '' ?>">
            <i class="fas fa-palette"></i> Variants
        </a>

        <div style="border-top:1px solid rgba(255,255,255,0.08); margin:16px 0;"></div>

        <a href="../index.php" target="_blank">
            <i class="fas fa-external-link-alt"></i> View Website
        </a>
        <a href="logout.php" 
           style="color:#E74C3C !important;"
           onclick="return confirm('Logout karna chahte ho?')">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </nav>
</div>

<!-- MOBILE TOGGLE -->
<button id="sidebar-toggle"
        style="display:none; position:fixed; top:16px; left:16px; z-index:2000;
               background:var(--primary); color:white; border:none; border-radius:10px;
               padding:10px 14px; font-size:18px; cursor:pointer;">
    <i class="fas fa-bars"></i>
</button>

<!-- Overlay for mobile -->
<div id="sidebar-overlay"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:999;"
     onclick="closeSidebar()"></div>

<!-- ADMIN MAIN WRAPPER -->
<div class="admin-main">

    <!-- Top Bar -->
    <div class="admin-topbar">
        <h4><?= isset($page_title) ? $page_title : 'Dashboard' ?></h4>
        <div class="d-flex align-items-center gap-3">
            <div style="text-align:right;">
                <div style="font-weight:700; font-size:15px;">
                    <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?>
                </div>
                <div style="font-size:12px; color:var(--text-muted);">Administrator</div>
            </div>
            <div style="width:42px; height:42px; background:linear-gradient(135deg, var(--primary), var(--primary-dark));
                        border-radius:50%; display:flex; align-items:center; justify-content:center;
                        color:white; font-size:18px; font-weight:700;">
                <?= strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1)) ?>
            </div>
        </div>
    </div>