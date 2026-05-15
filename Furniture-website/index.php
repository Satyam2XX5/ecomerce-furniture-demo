<?php
$page_title = "Home";
require_once 'includes/header.php';

// Featured Products
$featured = db_query($conn, "
    SELECT p.*, c.name as cat_name,
    (SELECT image_path FROM product_images 
     WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as main_image
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.is_featured = 1 AND p.is_active = 1
    LIMIT 8
");
$featured_products = mysqli_fetch_all($featured, MYSQLI_ASSOC);

// All Categories
$cats = db_query($conn, "SELECT * FROM categories WHERE is_active = 1 LIMIT 6");
$all_categories = mysqli_fetch_all($cats, MYSQLI_ASSOC);
?>

<!-- HERO SECTION -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <p class="text-muted fw-bold mb-2" 
                   style="letter-spacing:2px; text-transform:uppercase; color:var(--primary) !important;">
                    Premium Quality
                </p>
                <h1 class="hero-title">
                    Furniture That <span>Feels Like</span> Home
                </h1>
                <p class="hero-subtitle">
                    Handcrafted with the finest wood, designed for your comfort. 
                    Every piece tells a story of quality and craftsmanship.
                </p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="products.php" class="btn-primary-wood">
                        <i class="fas fa-couch me-2"></i>Shop Now
                    </a>
                    <a onclick="goToQuote('/furniture-website/quote.php'); return false;" href="#" class="btn-outline-wood">
                        Get Free Quote
                    </a>
                </div>

                <!-- Stats -->
                <div class="row mt-5 g-3">
                    <div class="col-4">
                        <h3 style="color:var(--primary); font-family:'Playfair Display',serif;">
                            500+
                        </h3>
                        <small class="text-muted">Products</small>
                    </div>
                    <div class="col-4">
                        <h3 style="color:var(--primary); font-family:'Playfair Display',serif;">
                            2K+
                        </h3>
                        <small class="text-muted">Happy Customers</small>
                    </div>
                    <div class="col-4">
                        <h3 style="color:var(--primary); font-family:'Playfair Display',serif;">
                            10+
                        </h3>
                        <small class="text-muted">Years Experience</small>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mt-5 mt-lg-0 text-center">
                <img src="assets/images/hero-furniture.jpg" 
                     alt="Premium Furniture" 
                     class="hero-image w-100"
                     onerror="this.src='https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=600'">
            </div>
        </div>
    </div>
</section>


<!-- CATEGORIES SECTION -->


<section class="py-5 mt-3">
    <div class="container">
        <div class="section-heading">
            <p style="color:var(--primary); font-weight:700; text-transform:uppercase; letter-spacing:2px;">
                Browse By
            </p>
            <h2>Our Categories</h2>
            <div class="section-divider"></div>
            <p>Find exactly what you're looking for</p>
        </div>

        <div class="row g-4">
            <?php foreach($all_categories as $cat): ?>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="category.php?slug=<?= $cat['slug'] ?>" class="text-decoration-none">
                    <div class="category-card">
                        <img src="<?= $cat['image'] ? 'uploads/categories/'.$cat['image'] : 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=300' ?>"
                             alt="<?= htmlspecialchars($cat['name']) ?>"
                             onerror="this.src='https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=300'">
                        <div class="category-card-body">
                            <h6><?= htmlspecialchars($cat['name']) ?></h6>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>



<!-- FEATURED PRODUCTS -->


<section class="py-5" style="background: var(--bg-cream);">
    <div class="container">
        <div class="section-heading">
            <p style="color:var(--primary); font-weight:700; text-transform:uppercase; letter-spacing:2px;">
                Hand Picked
            </p>
            <h2>Featured Products</h2>
            <div class="section-divider"></div>
            <p>Our most loved furniture pieces</p>
        </div>

        <?php if(empty($featured_products)): ?>
        <div class="empty-state">
            <i class="fas fa-couch"></i>
            <h4>Products Coming Soon!</h4>
            <p>Admin panel se products add karo</p>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach($featured_products as $product): ?>
            <div class="col-sm-6 col-lg-3">
                <div class="product-card">
                    <div class="product-card-img">
                        <img src="<?= $product['main_image'] ? 'uploads/products/'.$product['main_image'] : 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=400' ?>"
                             alt="<?= htmlspecialchars($product['name']) ?>"
                             onerror="this.src='https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=400'">
                        <span class="badge-featured">⭐ Featured</span>
                        <?php if($product['discount_price']): ?>
                        <span class="badge-discount">
                            <?= round((($product['price'] - $product['discount_price']) / $product['price']) * 100) ?>% OFF
                        </span>
                        <?php endif; ?>
                    </div>
                    <div class="product-card-body">
                        <p class="product-material">
                            <i class="fas fa-tag"></i> <?= htmlspecialchars($product['cat_name']) ?>
                        </p>
                        <h5><?= htmlspecialchars($product['name']) ?></h5>
                        <?php if($product['material']): ?>
                        <p class="product-material">
                            <i class="fas fa-tree"></i> <?= htmlspecialchars($product['material']) ?>
                        </p>
                        <?php endif; ?>
                        <div class="product-price">
                            <span class="price-current">
                                ₹<?= number_format($product['discount_price'] ?: $product['price'], 0, '.', ',') ?>
                            </span>
                            <?php if($product['discount_price']): ?>
                            <span class="price-original">
                                ₹<?= number_format($product['price'], 0, '.', ',') ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        <div class="product-card-actions">
                            <a href="product-detail.php?slug=<?= $product['slug'] ?>" 
                               class="btn-outline-wood">
                                <i class="fas fa-eye me-1"></i> View
                            </a>
                            <button class="btn-primary-wood" 
                                onclick="addToCart(
                                    <?= $product['id'] ?>, 
                                    '<?= addslashes($product['name']) ?>', 
                                    <?= $product['discount_price'] ?: $product['price'] ?>,
                                    '<?= $product['main_image'] ? 'uploads/products/'.$product['main_image'] : '' ?>'
                                )">
                                <i class="fas fa-cart-plus me-1"></i> Add
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-5">
            <a href="products.php" class="btn-primary-wood">
                View All Products <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>



<!-- WHY CHOOSE US -->


<section class="py-5">
    <div class="container">
        <div class="section-heading">
            <p style="color:var(--primary); font-weight:700; text-transform:uppercase; letter-spacing:2px;">
                Why Us
            </p>
            <h2>Why Choose WoodCraft?</h2>
            <div class="section-divider"></div>
        </div>
        <div class="row g-4 text-center">
            <div class="col-md-3">
                <div class="p-4">
                    <div class="mb-3" style="font-size:48px;">🪵</div>
                    <h5>Premium Wood</h5>
                    <p class="text-muted">Only the finest quality wood sourced sustainably</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-4">
                    <div class="mb-3" style="font-size:48px;">🔨</div>
                    <h5>Handcrafted</h5>
                    <p class="text-muted">Every piece crafted by skilled artisans</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-4">
                    <div class="mb-3" style="font-size:48px;">🚚</div>
                    <h5>Free Delivery</h5>
                    <p class="text-muted">Free delivery within the city on all orders</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-4">
                    <div class="mb-3" style="font-size:48px;">🛡️</div>
                    <h5>5 Year Warranty</h5>
                    <p class="text-muted">Quality guaranteed with 5 year warranty</p>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- CTA SECTION -->


<section class="py-5" style="background: linear-gradient(135deg, var(--primary-dark), var(--primary));">
    <div class="container text-center text-white">
        <h2 style="color:white; font-size:36px;">Ready to Transform Your Home?</h2>
        <p class="mt-3 mb-4" style="font-size:18px; opacity:0.85;">
            Get a free quote today and our team will help you choose the perfect furniture
        </p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a onclick="goToQuote('/furniture-website/quote.php'); return false;" href="#" class="btn-primary-wood" 
               style="background:white; color:var(--primary-dark);">
                Get Free Quote
            </a>
            <a href="https://wa.me/918210187952" target="_blank" class="btn-whatsapp">
                <i class="fab fa-whatsapp me-2"></i>WhatsApp Us
            </a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>