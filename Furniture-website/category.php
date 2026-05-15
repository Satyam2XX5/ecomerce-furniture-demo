<?php
require_once 'includes/db.php';

$slug = $_GET['slug'] ?? '';

$stmt = $conn->prepare("SELECT * FROM categories WHERE slug = ? AND is_active = 1");
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();
$category = $result->fetch_assoc();

if (!$category) {
    header("Location: products.php");
    exit();
}

$page_title = $category['name'];
require_once 'includes/header.php';

// Sort
$sort = isset($_GET['sort']) ? sanitize($conn, $_GET['sort']) : 'newest';
$order = match($sort) {
    'price_low'  => "ORDER BY (COALESCE(p.discount_price, p.price)) ASC",
    'price_high' => "ORDER BY (COALESCE(p.discount_price, p.price)) DESC",
    'name'       => "ORDER BY p.name ASC",
    default      => "ORDER BY p.created_at DESC"
};

// Pagination
$per_page = 12;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $per_page;

// Count
$count_result = db_query($conn, "
    SELECT COUNT(*) as total FROM products p
    WHERE p.category_id = {$category['id']} AND p.is_active = 1
");
$total = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total / $per_page);

// Products
$result = db_query($conn, "
    SELECT p.*,
    (SELECT image_path FROM product_images 
     WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as main_image
    FROM products p
    WHERE p.category_id = {$category['id']} AND p.is_active = 1
    $order
    LIMIT $per_page OFFSET $offset
");
$products = mysqli_fetch_all($result, MYSQLI_ASSOC);

// All categories for sidebar
$all_cats = db_query($conn, "SELECT * FROM categories WHERE is_active = 1 ORDER BY name ASC");
$all_categories = mysqli_fetch_all($all_cats, MYSQLI_ASSOC);
?>

<!-- Breadcrumb -->
<div class="breadcrumb-section">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="products.php">Products</a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($category['name']) ?></li>
            </ol>
        </nav>
    </div>
</div>

<!-- Category Banner -->
<div style="background: linear-gradient(135deg, var(--bg-cream), var(--bg-light)); 
            padding: 40px 0; margin-bottom: 40px;">
    <div class="container text-center">
        <h1 style="font-family:'Playfair Display',serif; font-size:42px; color:var(--primary-dark);">
            <?= htmlspecialchars($category['name']) ?>
        </h1>
        <?php if($category['description']): ?>
        <p class="text-muted mt-2" style="font-size:16px; max-width:500px; margin:10px auto 0;">
            <?= htmlspecialchars($category['description']) ?>
        </p>
        <?php endif; ?>
        <p class="mt-3" style="color:var(--primary); font-weight:600;">
            <?= $total ?> Products Available
        </p>
    </div>
</div>

<div class="container pb-5">
    <div class="row g-4">

        <!-- SIDEBAR -->
        <div class="col-lg-3">
            <div class="admin-table-card mb-4">
                <div class="p-4">
                    <h6 class="mb-3" style="font-family:'Playfair Display',serif;">All Categories</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <a href="products.php" class="d-block py-2 px-3 rounded" 
                               style="color:var(--text-dark);">
                                All Products
                            </a>
                        </li>
                        <?php foreach($all_categories as $cat): ?>
                        <li class="mb-2">
                            <a href="category.php?slug=<?= $cat['slug'] ?>"
                               class="d-block py-2 px-3 rounded"
                               style="<?= $cat['id'] == $category['id'] ? 
                                    'background:var(--bg-cream); color:var(--primary); font-weight:700;' : 
                                    'color:var(--text-dark);' ?>">
                                <?= htmlspecialchars($cat['name']) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <!-- Quote CTA -->
            <div class="p-4 text-center rounded"
                 style="background:linear-gradient(135deg, var(--primary-dark), var(--primary)); color:white;">
                <i class="fas fa-tags" style="font-size:32px; margin-bottom:10px; display:block;"></i>
                <h6 style="color:white;">Want Custom Size?</h6>
                <p style="font-size:13px; opacity:0.85;">Get a custom quote for your requirements</p>
                <a href="quote.php" class="btn-primary-wood d-block text-center mt-2"
                   style="background:white; color:var(--primary-dark); font-size:14px; padding:10px;">
                    Get Free Quote
                </a>
            </div>
        </div>

        <!-- PRODUCTS -->
        <div class="col-lg-9">
            <!-- Sort Bar -->
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <p class="text-muted mb-0" style="font-size:14px;">
                    Showing <?= count($products) ?> of <?= $total ?> products
                </p>
                <form method="GET" id="sort-form-cat">
                    <input type="hidden" name="slug" value="<?= $slug ?>">
                    <select name="sort" class="form-select" style="width:auto;"
                            onchange="document.getElementById('sort-form-cat').submit()">
                        <option value="newest" <?= $sort=='newest'?'selected':'' ?>>Newest First</option>
                        <option value="price_low" <?= $sort=='price_low'?'selected':'' ?>>Price: Low to High</option>
                        <option value="price_high" <?= $sort=='price_high'?'selected':'' ?>>Price: High to Low</option>
                        <option value="name" <?= $sort=='name'?'selected':'' ?>>Name A-Z</option>
                    </select>
                </form>
            </div>

            <?php if(empty($products)): ?>
            <div class="empty-state">
                <i class="fas fa-couch"></i>
                <h4>Is category mein abhi products nahi hain</h4>
                <p>Jald hi add honge!</p>
                <a href="products.php" class="btn-primary-wood mt-3">Sab Products Dekho</a>
            </div>
            <?php else: ?>
            <div class="row g-4">
                <?php foreach($products as $product): ?>
                <div class="col-sm-6 col-xl-4">
                    <div class="product-card h-100">
                        <div class="product-card-img">
                            <img src="<?= $product['main_image'] ? 'uploads/products/'.$product['main_image'] : 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=400' ?>"
                                 alt="<?= htmlspecialchars($product['name']) ?>"
                                 onerror="this.src='https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=400'">
                            <?php if($product['is_featured']): ?>
                            <span class="badge-featured">⭐ Featured</span>
                            <?php endif; ?>
                            <?php if($product['discount_price']): ?>
                            <span class="badge-discount">
                                <?= round((($product['price'] - $product['discount_price']) / $product['price']) * 100) ?>% OFF
                            </span>
                            <?php endif; ?>
                        </div>
                        <div class="product-card-body">
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
                                   class="btn-outline-wood text-center">
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

            <!-- Pagination -->
            <?php if($total_pages > 1): ?>
            <nav class="mt-5">
                <ul class="pagination justify-content-center gap-2">
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" 
                           href="category.php?slug=<?= $slug ?>&page=<?= $i ?>&sort=<?= $sort ?>"
                           style="border-radius:10px; <?= $i==$page ? 'background:var(--primary); border-color:var(--primary);' : 'border-color:var(--primary-light); color:var(--primary);' ?>">
                            <?= $i ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>