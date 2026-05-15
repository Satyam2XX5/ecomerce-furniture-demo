<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireAdmin();

$product_id = (int)($_GET['product_id'] ?? 0);
if (!$product_id) { header("Location: products.php"); exit(); }

$product = mysqli_fetch_assoc(db_query($conn, "SELECT * FROM products WHERE id=$product_id"));
if (!$product) { header("Location: products.php"); exit(); }

$page_title = "Variants: " . $product['name'];

// Add variant
if (isset($_POST['add_variant'])) {
    $type  = sanitize($conn, $_POST['variant_type']);
    $value = sanitize($conn, $_POST['variant_value']);
    $color = sanitize($conn, $_POST['color_code'] ?? '');
    $price_mod = (float)($_POST['price_modifier'] ?? 0);
    $stock = (int)($_POST['stock_qty'] ?? 0);

    if (!empty($type) && !empty($value)) {
        db_query($conn, "INSERT INTO product_variants (product_id, variant_type, variant_value, color_code, price_modifier, stock_qty) 
                         VALUES ($product_id, '$type', '$value', '$color', $price_mod, $stock)");
        header("Location: product-variants.php?product_id=$product_id&msg=Variant+add+ho+gaya!");
        exit();
    }
}

// Delete variant
if (isset($_GET['delete'])) {
    $del = (int)$_GET['delete'];
    db_query($conn, "DELETE FROM product_variants WHERE id=$del AND product_id=$product_id");
    header("Location: product-variants.php?product_id=$product_id&msg=Variant+delete+ho+gaya!");
    exit();
}

// Toggle active
if (isset($_GET['toggle'])) {
    $tid = (int)$_GET['toggle'];
    db_query($conn, "UPDATE product_variants SET is_active = !is_active WHERE id=$tid AND product_id=$product_id");
    header("Location: product-variants.php?product_id=$product_id");
    exit();
}

$variants = mysqli_fetch_all(
    db_query($conn, "SELECT * FROM product_variants WHERE product_id=$product_id ORDER BY variant_type, sort_order"),
    MYSQLI_ASSOC
);

// Group by type
$grouped = [];
foreach ($variants as $v) {
    $grouped[$v['variant_type']][] = $v;
}

require_once 'includes/admin-sidebar.php';
?>

<div class="mb-3">
    <a href="products.php" style="color:#8B4513; text-decoration:none;">
        <i class="fas fa-arrow-left me-1"></i>Wapas Products
    </a>
</div>

<?php if(isset($_GET['msg'])): ?>
<div class="alert alert-success mb-4" style="border-radius:12px;">
    <i class="fas fa-check-circle me-1"></i><?= htmlspecialchars($_GET['msg']) ?>
</div>
<?php endif; ?>

<div class="row g-4">
    <!-- Add Variant Form -->
    <div class="col-lg-4">
        <div class="admin-table-card">
            <div class="p-4 border-bottom">
                <h6 style="font-family:'Playfair Display',serif;margin:0;">Naya Variant Add Karo</h6>
                <small class="text-muted"><?= htmlspecialchars($product['name']) ?></small>
            </div>
            <div class="p-4">
                <form method="POST">
                    <input type="hidden" name="add_variant" value="1">
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Variant Type *</label>
                        <select name="variant_type" class="form-select" style="border-radius:10px;" required>
                            <option value="">-- Chuniye --</option>
                            <option value="color">Color (Rang)</option>
                            <option value="size">Size (Aakaar)</option>
                            <option value="material">Material (Samagri)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Variant Value *</label>
                        <input type="text" name="variant_value" class="form-control" style="border-radius:10px;"
                               placeholder="e.g. Brown, Large, Teak Wood" required>
                        <small class="text-muted">Color ke liye: Brown, White, Black<br>Size ke liye: Small, Medium, Large, Custom</small>
                    </div>

                    <div class="mb-3" id="color_code_div">
                        <label class="form-label fw-semibold">Color Code (hex)</label>
                        <div class="input-group">
                            <input type="color" name="color_code" class="form-control form-control-color" 
                                   value="#8B4513" style="width:50px;">
                            <input type="text" id="color_hex_display" class="form-control" 
                                   value="#8B4513" style="border-radius:0 10px 10px 0;" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Price Modifier (₹)</label>
                        <input type="number" name="price_modifier" class="form-control" 
                               style="border-radius:10px;" value="0" step="0.01"
                               placeholder="0 = same price, +500 = zyada, -200 = kam">
                        <small class="text-muted">Base price se kitna plus/minus karna hai</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Stock Quantity</label>
                        <input type="number" name="stock_qty" class="form-control" 
                               style="border-radius:10px;" value="0" min="0">
                    </div>

                    <button type="submit" class="btn w-100" 
                            style="background:#8B4513;color:white;border-radius:10px;padding:12px;">
                        <i class="fas fa-plus me-1"></i>Variant Add Karo
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Variants List -->
    <div class="col-lg-8">
        <?php if (empty($variants)): ?>
        <div class="admin-table-card p-5 text-center text-muted">
            <i class="fas fa-palette fa-3x mb-3 opacity-50"></i>
            <h5>Koi variant nahi</h5>
            <p>Left form se pehla variant add karo!</p>
        </div>
        <?php else: ?>

        <?php foreach ($grouped as $type => $type_variants): ?>
        <div class="admin-table-card mb-4">
            <div class="p-4 border-bottom">
                <h6 style="font-family:'Playfair Display',serif;margin:0;text-transform:capitalize;">
                    <?php
                    $icons = ['color'=>'fa-palette','size'=>'fa-ruler-combined','material'=>'fa-tree'];
                    $labels = ['color'=>'Colors (Rang)','size'=>'Sizes (Aakaar)','material'=>'Materials'];
                    ?>
                    <i class="fas <?= $icons[$type] ?? 'fa-tag' ?> me-2" style="color:#8B4513;"></i>
                    <?= $labels[$type] ?? ucfirst($type) ?>
                    <span class="badge ms-2" style="background:#8B4513;"><?= count($type_variants) ?></span>
                </h6>
            </div>
            <div class="p-4">
                <div class="row g-3">
                    <?php foreach ($type_variants as $v): ?>
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 <?= !$v['is_active'] ? 'opacity-50' : '' ?>"
                             style="border-color:#f0e6da !important; background:<?= $v['is_active'] ? '#FFF8F0' : '#f9f9f9' ?>;">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <?php if ($type === 'color' && $v['color_code']): ?>
                                    <div style="width:24px;height:24px;border-radius:50%;background:<?= htmlspecialchars($v['color_code']) ?>;border:2px solid #ddd;flex-shrink:0;"></div>
                                    <?php endif; ?>
                                    <strong><?= htmlspecialchars($v['variant_value']) ?></strong>
                                </div>
                                <div class="d-flex gap-1">
                                    <a href="product-variants.php?product_id=<?= $product_id ?>&toggle=<?= $v['id'] ?>"
                                       class="btn btn-sm <?= $v['is_active'] ? 'btn-outline-warning' : 'btn-outline-success' ?>"
                                       style="border-radius:6px; font-size:11px; padding:3px 8px;">
                                        <?= $v['is_active'] ? 'Hide' : 'Show' ?>
                                    </a>
                                    <a href="product-variants.php?product_id=<?= $product_id ?>&delete=<?= $v['id'] ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       style="border-radius:6px; font-size:11px; padding:3px 8px;"
                                       onclick="return confirm('Delete karna chahte ho?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="d-flex gap-3" style="font-size:12px;">
                                <span class="text-muted">
                                    Price: <?= $v['price_modifier'] >= 0 ? '+' : '' ?>₹<?= number_format($v['price_modifier'], 0) ?>
                                </span>
                                <span class="text-muted">Stock: <?= $v['stock_qty'] ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelector('input[type="color"]').addEventListener('input', function() {
    document.getElementById('color_hex_display').value = this.value;
});
document.querySelector('select[name="variant_type"]').addEventListener('change', function() {
    const colorDiv = document.getElementById('color_code_div');
    colorDiv.style.display = this.value === 'color' ? 'block' : 'none';
});
// Initially hide color code if not color type
document.getElementById('color_code_div').style.display = 'none';
</script>

<?php require_once 'includes/admin-footer.php'; ?>
