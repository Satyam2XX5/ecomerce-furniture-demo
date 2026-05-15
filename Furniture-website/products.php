<?php
$page_title = "All Products";
require_once 'includes/header.php';

// Filters
$selected_cat = isset($_GET['category']) ? sanitize($conn, $_GET['category']) : '';
$search       = isset($_GET['search']) ? sanitize($conn, $_GET['search']) : '';
$sort         = isset($_GET['sort']) ? sanitize($conn, $_GET['sort']) : 'newest';

// Base WHERE
$where  = "WHERE p.is_active = 1";
$params = [];
$types  = "";

// Category Filter
if ($selected_cat) {
    $where .= " AND c.slug = ?";
    $params[] = $selected_cat;
    $types .= "s";
}

// Search Filter
if ($search) {
    $where .= " AND (
        p.name LIKE ?
        OR p.description LIKE ?
        OR p.material LIKE ?
    )";

    $searchTerm = "%$search%";

    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;

    $types .= "sss";
}

// Sorting
$order = match($sort) {
    'price_low'  => "ORDER BY COALESCE(p.discount_price, p.price) ASC",
    'price_high' => "ORDER BY COALESCE(p.discount_price, p.price) DESC",
    'name'       => "ORDER BY p.name ASC",
    default      => "ORDER BY p.created_at DESC"
};

// Pagination
$per_page = 12;
$page     = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset   = ($page - 1) * $per_page;

/* =========================================================
   TOTAL COUNT QUERY
========================================================= */

$count_sql = "
    SELECT COUNT(*) as total
    FROM products p
    JOIN categories c ON p.category_id = c.id
    $where
";

$count_stmt = $conn->prepare($count_sql);

if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}

$count_stmt->execute();

$count_result = $count_stmt->get_result();
$total = $count_result->fetch_assoc()['total'];

$total_pages = ceil($total / $per_page);

/* =========================================================
   PRODUCTS QUERY
========================================================= */

$product_sql = "
    SELECT
        p.*,
        c.name as cat_name,
        c.slug as cat_slug,

        (
            SELECT image_path
            FROM product_images
            WHERE product_id = p.id
            AND is_primary = 1
            LIMIT 1
        ) as main_image

    FROM products p
    JOIN categories c ON p.category_id = c.id

    $where
    $order

    LIMIT ? OFFSET ?
";

$product_stmt = $conn->prepare($product_sql);

// Add LIMIT + OFFSET params
$product_params = $params;
$product_params[] = $per_page;
$product_params[] = $offset;

$product_types = $types . "ii";

$product_stmt->bind_param($product_types, ...$product_params);

$product_stmt->execute();

$product_result = $product_stmt->get_result();

$products = $product_result->fetch_all(MYSQLI_ASSOC);

/* =========================================================
   ALL CATEGORIES
========================================================= */

$cat_sql = "
    SELECT
        c.*,
        COUNT(p.id) as product_count

    FROM categories c

    LEFT JOIN products p
        ON p.category_id = c.id
        AND p.is_active = 1

    WHERE c.is_active = 1

    GROUP BY c.id

    ORDER BY c.name ASC
";

$cat_result = $conn->query($cat_sql);

$all_categories = $cat_result->fetch_all(MYSQLI_ASSOC);

/* =========================================================
   PAGINATION QUERY STRING
========================================================= */

function buildQuery($extra = [])
{
    $params = array_merge($_GET, $extra);

    unset($params['page']);

    return http_build_query(array_filter($params));
}
?>
<!-- Breadcrumb -->
<div class="breadcrumb-section">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active">All Products</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container pb-5">
    <div class="row g-4">

        <!-- SIDEBAR -->
        <div class="col-lg-3">
            <!-- Search -->
            <div class="admin-table-card mb-4">
                <div class="p-4">
                    <h6 class="mb-3" style="font-family:'Playfair Display',serif;">Search</h6>
                    <form method="GET" action="products.php">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Search products..." 
                                   value="<?= htmlspecialchars($search) ?>">
                            <button class="btn btn-primary-wood" type="submit" 
                                    style="border-radius:0 10px 10px 0; background:var(--primary); 
                                           color:white; border:none; padding:10px 14px;">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <?php if($selected_cat): ?>
                        <input type="hidden" name="category" value="<?= $selected_cat ?>">
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Categories -->
            <div class="admin-table-card mb-4">
                <div class="p-4">
                    <h6 class="mb-3" style="font-family:'Playfair Display',serif;">Categories</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <a href="products.php<?= $search ? '?search='.$search : '' ?>" 
                               class="d-flex justify-content-between align-items-center py-2 px-3 rounded"
                               style="<?= !$selected_cat ? 'background:var(--bg-cream); color:var(--primary); font-weight:700;' : 'color:var(--text-dark);' ?>">
                                <span>All Products</span>
                                <span class="badge" style="background:var(--primary-light); color:white;">
                                    <?= $total ?>
                                </span>
                            </a>
                        </li>
                        <?php foreach($all_categories as $cat): ?>
                        <li class="mb-2">
                            <a href="products.php?category=<?= $cat['slug'] ?><?= $search ? '&search='.$search : '' ?>"
                               class="d-flex justify-content-between align-items-center py-2 px-3 rounded"
                               style="<?= $selected_cat == $cat['slug'] ? 'background:var(--bg-cream); color:var(--primary); font-weight:700;' : 'color:var(--text-dark);' ?>">
                                <span><?= htmlspecialchars($cat['name']) ?></span>
                                <span class="badge" style="background:var(--bg-cream); color:var(--text-muted);">
                                    <?= $cat['product_count'] ?>
                                </span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <!-- WhatsApp CTA -->
            <div class="p-4 text-center rounded" 
                 style="background:linear-gradient(135deg, var(--primary-dark), var(--primary)); color:white;">
                <i class="fab fa-whatsapp" style="font-size:36px; margin-bottom:12px; display:block;"></i>
                <h6 style="color:white;">Need Help Choosing?</h6>
                <p style="font-size:13px; opacity:0.85;">Talk to our furniture expert</p>
                <a href="https://wa.me/919876543210" target="_blank" class="btn-whatsapp d-block text-center mt-2"
                   style="font-size:14px; padding:10px;">
                    Chat on WhatsApp
                </a>
            </div>
        </div>

        <!-- PRODUCTS GRID -->
        <div class="col-lg-9">
            <!-- Header Row -->
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <div>
                    <h4 style="font-family:'Playfair Display',serif; margin-bottom:4px;">
                        <?= $selected_cat ? htmlspecialchars(ucwords(str_replace('-', ' ', $selected_cat))) : 'All Products' ?>
                    </h4>
                    <p class="text-muted mb-0" style="font-size:14px;">
                        <?= $total ?> products found
                        <?= $search ? ' for "<strong>'.$search.'</strong>"' : '' ?>
                    </p>
                </div>

                <!-- Sort -->
                <form method="GET" id="sort-form">
                    <?php if($selected_cat): ?>
                    <input type="hidden" name="category" value="<?= $selected_cat ?>">
                    <?php endif; ?>
                    <?php if($search): ?>
                    <input type="hidden" name="search" value="<?= $search ?>">
                    <?php endif; ?>
                    <select name="sort" class="form-select" style="width:auto;" 
                            onchange="document.getElementById('sort-form').submit()">
                        <option value="newest" <?= $sort=='newest'?'selected':'' ?>>Newest First</option>
                        <option value="price_low" <?= $sort=='price_low'?'selected':'' ?>>Price: Low to High</option>
                        <option value="price_high" <?= $sort=='price_high'?'selected':'' ?>>Price: High to Low</option>
                        <option value="name" <?= $sort=='name'?'selected':'' ?>>Name A-Z</option>
                    </select>
                </form>
            </div>

            <!-- Active Filters -->
            <?php if($selected_cat || $search): ?>
            <div class="mb-4 d-flex gap-2 flex-wrap align-items-center">
                <span class="text-muted" style="font-size:14px;">Active filters:</span>
                <?php if($selected_cat): ?>
                <a href="products.php<?= $search?'?search='.$search:'' ?>" 
                   class="badge text-decoration-none"
                   style="background:var(--bg-cream); color:var(--primary); padding:8px 14px; 
                          border-radius:50px; font-size:13px;">
                    <?= htmlspecialchars(ucwords(str_replace('-', ' ', $selected_cat))) ?> ✕
                </a>
                <?php endif; ?>
                <?php if($search): ?>
                <a href="products.php<?= $selected_cat?'?category='.$selected_cat:'' ?>" 
                   class="badge text-decoration-none"
                   style="background:var(--bg-cream); color:var(--primary); padding:8px 14px; 
                          border-radius:50px; font-size:13px;">
                    "<?= htmlspecialchars($search) ?>" ✕
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Products -->
            <?php if(empty($products)): ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h4>Koi product nahi mila!</h4>
                <p>Search ya filter change karke try karo</p>
                <a href="products.php" class="btn-primary-wood mt-3">All Products Dekho</a>
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
                            <p class="product-material mb-1">
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
                    <?php if($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= buildQuery(['page' => $page-1]) ?>"
                           style="border-radius:10px; border-color:var(--primary-light); color:var(--primary);">
                            ← Prev
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= buildQuery(['page' => $i]) ?>"
                           style="border-radius:10px; <?= $i==$page ? 'background:var(--primary); border-color:var(--primary);' : 'border-color:var(--primary-light); color:var(--primary);' ?>">
                            <?= $i ?>
                        </a>
                    </li>
                    <?php endfor; ?>

                    <?php if($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= buildQuery(['page' => $page+1]) ?>"
                           style="border-radius:10px; border-color:var(--primary-light); color:var(--primary);">
                            Next →
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>