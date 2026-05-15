<?php
require_once __DIR__ . '/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Fetch all active categories for navbar
$cat_result = db_query($conn, "SELECT * FROM categories WHERE is_active = 1 ORDER BY name ASC");
$categories = mysqli_fetch_all($cat_result, MYSQLI_ASSOC);

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' | WoodCraft Furniture' : 'WoodCraft Furniture' ?></title>
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="/furniture-website/assets/css/style.css" rel="stylesheet">
</head>
<body>

<!-- Top Bar -->
<div class="top-bar py-1">
    <div class="container d-flex justify-content-between align-items-center">
        <small><i class="fas fa-phone me-1"></i> +91 8210187952 </small>
        <small><i class="fas fa-envelope me-1"></i> Santoshfurniture@gmail.com</small>
    </div>
</div>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light sticky-top">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand" href="/furniture-website/index.php">
            <span class="brand-name">🪑 Santosh-furniture</span>
        </a>

        <!-- Mobile Toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'index.php' ? 'active' : '' ?>" href="/furniture-website/index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'products.php' ? 'active' : '' ?>" href="/furniture-website/products.php">All Products</a>
                </li>

                <!-- Categories Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Categories</a>
                    <ul class="dropdown-menu">
                        <?php foreach($categories as $cat): ?>
                        <li>
                            <a class="dropdown-item" href="/furniture-website/category.php?slug=<?= $cat['slug'] ?>">
                                <?= htmlspecialchars($cat['name']) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'quote.php' ? 'active' : '' ?>" 
                       href="#" onclick="goToQuote('/furniture-website/quote.php'); return false;">Get Quote</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'store-location.php' ? 'active' : '' ?>" href="/furniture-website/store-location.php">
                        <i class="fas fa-map-marker-alt me-1"></i>Our Store
                    </a>
                </li>

                <!-- Language Switcher -->
                <li class="nav-item dropdown ms-1">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-1" href="#" data-bs-toggle="dropdown" title="Language / भाषा" style="font-size:13px; padding:6px 10px;">
                        <i class="fas fa-language" style="font-size:16px; color:#8B4513;"></i>
                        <span class="d-none d-lg-inline">Lang</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" style="min-width:200px; border-radius:14px; padding:12px; box-shadow:0 10px 40px rgba(0,0,0,0.12);">
                        <li>
                            <p class="mb-2 px-1 fw-bold" style="font-size:12px; color:#8B4513; text-transform:uppercase; letter-spacing:0.5px;">
                                <i class="fas fa-language me-1"></i> Language / भाषा
                            </p>
                        </li>
                        <li>
                            <button class="lang-option dropdown-item d-flex align-items-center gap-2 mb-1" 
                                    data-lang="hinglish"
                                    onclick="setLanguage('hinglish')"
                                    style="border-radius:10px; padding:8px 12px; font-weight:600; background:#FFF8F0; color:#8B4513; border:none; width:100%; text-align:left;">
                                🤝 Hinglish
                                <span style="font-size:11px; color:#aaa; font-weight:400; margin-left:auto;">Default</span>
                            </button>
                        </li>
                        <li>
                            <button class="lang-option dropdown-item d-flex align-items-center gap-2 mb-1" 
                                    data-lang="hi"
                                    onclick="setLanguage('hi')"
                                    style="border-radius:10px; padding:8px 12px; font-weight:600; background:#FFF8F0; color:#8B4513; border:none; width:100%; text-align:left;">
                                🇮🇳 हिंदी
                            </button>
                        </li>
                        <li>
                            <button class="lang-option dropdown-item d-flex align-items-center gap-2" 
                                    data-lang="en"
                                    onclick="setLanguage('en')"
                                    style="border-radius:10px; padding:8px 12px; font-weight:600; background:#FFF8F0; color:#8B4513; border:none; width:100%; text-align:left;">
                                🇬🇧 English
                            </button>
                        </li>
                    </ul>
                </li>

                <!-- Cart Icon -->
                <li class="nav-item ms-2">
                    <a href="/furniture-website/cart.php" class="btn btn-cart position-relative">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-badge" id="cart-count">0</span>
                    </a>
                </li>

                <!-- User Auth -->
                <?php if (isset($_SESSION['user_id'])): ?>
                <li class="nav-item dropdown ms-2">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-1" href="#" data-bs-toggle="dropdown">
                        <span style="width:32px;height:32px;background:#8B4513;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:14px;">
                            <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
                        </span>
                        <span class="d-none d-lg-inline" style="font-size:14px;"><?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/furniture-website/my-orders.php"><i class="fas fa-box me-2" style="color:#8B4513;"></i>Mere Orders</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="/furniture-website/user-logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </li>
                <?php else: ?>
                <li class="nav-item ms-2">
                    <a href="/furniture-website/login.php" class="btn btn-sm" style="background:#8B4513;color:white;border-radius:8px;padding:6px 14px;font-size:13px;">
                        <i class="fas fa-user me-1"></i>Login
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>