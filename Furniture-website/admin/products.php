<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireAdmin();

$page_title = "Products";

// Search + Filter
$search     = isset($_GET['search']) ? sanitize($conn, $_GET['search']) : '';
$filter_cat = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$where      = "WHERE 1=1";
if ($search)     $where .= " AND (p.name LIKE '%$search%' OR p.material LIKE '%$search%')";
if ($filter_cat) $where .= " AND p.category_id = $filter_cat";

// Pagination
$per_page = 15;
$page     = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset   = ($page - 1) * $per_page;

$total       = mysqli_fetch_assoc(db_query($conn, "SELECT COUNT(*) as c FROM products p $where"))['c'];
$total_pages = ceil($total / $per_page);

$products = mysqli_fetch_all(db_query($conn, "
    SELECT p.*, c.name as cat_name,
    (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as main_image
    FROM products p
    JOIN categories c ON p.category_id = c.id
    $where
    ORDER BY p.created_at DESC
    LIMIT $per_page OFFSET $offset
"), MYSQLI_ASSOC);

// All categories for filter
$all_cats = mysqli_fetch_all(db_query($conn, "SELECT * FROM categories WHERE is_active = 1 ORDER BY name"), MYSQLI_ASSOC);

// Success/Error messages
$success = isset($_GET['success']) ? $_GET['success'] : '';
$error   = isset($_GET['error']) ? $_GET['error'] : '';

require_once 'includes/admin-sidebar.php';
?>

<!-- Messages -->
<?php if($success): ?>
<div class="alert-wood mb-4">
    <i class="fas fa-check-circle me-2" style="color:var(--primary);"></i>
    <?= htmlspecialchars($success) ?>
</div>
<?php endif; ?>

<?php if($error): ?>
<div class="alert alert-danger mb-4" style="border-radius:12px;">
    <i class="fas fa-exclamation-circle me-2"></i>
    <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<!-- Top Actions -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <p class="text-muted mb-0" style="font-size:14px;">
            Total <strong><?= $total ?></strong> products
        </p>
    </div>
    <a href="product-add.php" class="btn-primary-wood" style="font-size:14px; padding:10px 20px;">
        <i class="fas fa-plus me-2"></i>Add New Product
    </a>
</div>

<!-- Search + Filter -->
<div class="admin-table-card mb-4 p-4">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-5">
            <label class="form-label fw-bold" style="font-size:13px;">Search</label>
            <input type="text" name="search" class="form-control"
                   placeholder="Product naam ya material..." 
                   value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-bold" style="font-size:13px;">Category</label>
            <select name="category" class="form-select">
                <option value="">All Categories</option>
                <?php foreach($all_cats as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $filter_cat == $cat['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn-primary-wood flex-grow-1" 
                    style="padding:11px; font-size:14px;">
                <i class="fas fa-search me-1"></i> Search
            </button>
            <a href="products.php" class="btn-outline-wood" style="padding:11px 16px; font-size:14px;">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </form>
</div>

<!-- Products Table -->
<div class="admin-table-card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width:60px;">#</th>
                    <th style="width:70px;">Image</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Featured</th>
                    <th>Status</th>
                    <th style="width:120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($products)): ?>
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <div class="empty-state" style="padding:30px;">
                            <i class="fas fa-box-open" style="font-size:40px;"></i>
                            <h5 class="mt-3">Koi product nahi mila</h5>
                            <a href="product-add.php" class="btn-primary-wood mt-3" 
                               style="font-size:13px; padding:10px 20px;">
                                Add First Product
                            </a>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach($products as $i => $p): ?>
                <tr>
                    <td style="color:var(--text-muted); font-size:13px;">
                        <?= $offset + $i + 1 ?>
                    </td>
                    <td>
                        <img src="<?= $p['main_image'] ? '../uploads/products/'.$p['main_image'] : 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=100' ?>"
                             alt="<?= htmlspecialchars($p['name']) ?>"
                             style="width:52px; height:52px; object-fit:cover; border-radius:10px;"
                             onerror="this.src='https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=100'">
                    </td>
                    <td>
                        <div style="font-weight:600; font-size:14px; max-width:200px;">
                            <?= htmlspecialchars($p['name']) ?>
                        </div>
                        <?php if($p['material']): ?>
                        <div style="font-size:12px; color:var(--text-muted);">
                            <i class="fas fa-tree me-1"></i><?= htmlspecialchars($p['material']) ?>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span style="background:var(--bg-cream); color:var(--primary); 
                                     padding:4px 12px; border-radius:50px; font-size:12px; font-weight:600;">
                            <?= htmlspecialchars($p['cat_name']) ?>
                        </span>
                    </td>
                    <td>
                        <div style="font-weight:700; color:var(--primary); font-size:15px;">
                            ₹<?= number_format($p['discount_price'] ?: $p['price'], 0, '.', ',') ?>
                        </div>
                        <?php if($p['discount_price']): ?>
                        <div style="font-size:12px; color:var(--text-muted); text-decoration:line-through;">
                            ₹<?= number_format($p['price'], 0, '.', ',') ?>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($p['is_featured']): ?>
                        <span style="color:var(--accent); font-size:18px;" title="Featured">⭐</span>
                        <?php else: ?>
                        <span style="color:#ccc; font-size:18px;">☆</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="status-badge <?= $p['is_active'] ? 'status-active' : 'status-inactive' ?>">
                            <?= $p['is_active'] ? 'Active' : 'Hidden' ?>
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <!-- Edit -->
                            <a href="product-edit.php?id=<?= $p['id'] ?>"
                               title="Edit"
                               style="width:34px; height:34px; background:var(--bg-cream); 
                                      border-radius:8px; display:flex; align-items:center; 
                                      justify-content:center; color:var(--primary); font-size:14px;">
                                <i class="fas fa-edit"></i>
                            </a>

                            <!-- View -->
                            <a href="../product-detail.php?slug=<?= $p['slug'] ?>" target="_blank"
                               title="View"
                               style="width:34px; height:34px; background:#EBF5FB; 
                                      border-radius:8px; display:flex; align-items:center; 
                                      justify-content:center; color:#2980B9; font-size:14px;">
                                <i class="fas fa-eye"></i>
                            </a>

                            <!-- Delete -->
                            <button onclick="confirmDelete('product-delete.php?id=<?= $p['id'] ?>', '<?= addslashes($p['name']) ?>')"
                                    title="Delete"
                                    style="width:34px; height:34px; background:#FADBD8; 
                                           border-radius:8px; display:flex; align-items:center; 
                                           justify-content:center; color:#E74C3C; font-size:14px;
                                           border:none; cursor:pointer;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if($total_pages > 1): ?>
    <div class="p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <small class="text-muted">
            Page <?= $page ?> of <?= $total_pages ?>
        </small>
        <nav>
            <ul class="pagination mb-0 gap-2">
                <?php if($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page-1 ?>&search=<?= $search ?>&category=<?= $filter_cat ?>"
                       style="border-radius:8px; border-color:var(--primary-light); color:var(--primary);">
                        ← Prev
                    </a>
                </li>
                <?php endif; ?>

                <?php for($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
                <li class="page-item <?= $i==$page?'active':'' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&search=<?= $search ?>&category=<?= $filter_cat ?>"
                       style="border-radius:8px; <?= $i==$page ? 'background:var(--primary); border-color:var(--primary);' : 'border-color:var(--primary-light); color:var(--primary);' ?>">
                        <?= $i ?>
                    </a>
                </li>
                <?php endfor; ?>

                <?php if($page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page+1 ?>&search=<?= $search ?>&category=<?= $filter_cat ?>"
                       style="border-radius:8px; border-color:var(--primary-light); color:var(--primary);">
                        Next →
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
