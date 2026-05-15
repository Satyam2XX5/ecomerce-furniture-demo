<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header("Location: products.php");
    exit();
}

// Fetch product
$result  = db_query($conn, "SELECT * FROM products WHERE id = $id");
$product = mysqli_fetch_assoc($result);
if (!$product) {
    header("Location: products.php?error=Product nahi mila");
    exit();
}

// Fetch images
$img_result = db_query($conn, "
    SELECT * FROM product_images 
    WHERE product_id = $id 
    ORDER BY is_primary DESC, sort_order ASC
");
$existing_images = mysqli_fetch_all($img_result, MYSQLI_ASSOC);

// All categories
$all_cats = mysqli_fetch_all(
    db_query($conn, "SELECT * FROM categories WHERE is_active = 1 ORDER BY name ASC"),
    MYSQLI_ASSOC
);

$page_title = "Edit Product";
$errors     = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name           = sanitize($conn, $_POST['name'] ?? '');
    $category_id    = (int)($_POST['category_id'] ?? 0);
    $description    = sanitize($conn, $_POST['description'] ?? '');
    $material       = sanitize($conn, $_POST['material'] ?? '');
    $price          = (float)($_POST['price'] ?? 0);
    $discount_price = (isset($_POST['discount_price']) && $_POST['discount_price'] !== '') 
                      ? (float)$_POST['discount_price'] : null;
    $whatsapp       = sanitize($conn, $_POST['whatsapp_number'] ?? '');
    $is_featured    = isset($_POST['is_featured']) ? 1 : 0;
    $is_active      = isset($_POST['is_active']) ? 1 : 0;

    if (empty($name))      $errors[] = "Product naam zaroori hai";
    if ($category_id == 0) $errors[] = "Category select karo";
    if ($price <= 0)       $errors[] = "Valid price daalo";
    if ($discount_price !== null && $discount_price >= $price)
                           $errors[] = "Discount price, original price se kam honi chahiye";

    // Delete selected images
    if (!empty($_POST['delete_images'])) {
        foreach ($_POST['delete_images'] as $img_id) {
            $img_id  = (int)$img_id;
            $img_row = mysqli_fetch_assoc(
                db_query($conn, "SELECT image_path FROM product_images WHERE id = $img_id")
            );
            if ($img_row) {
                $file = '../uploads/products/' . $img_row['image_path'];
                if (file_exists($file)) unlink($file);
                db_query($conn, "DELETE FROM product_images WHERE id = $img_id");
            }
        }
    }

    if (empty($errors)) {
        $discount_val = $discount_price !== null ? $discount_price : 'NULL';

        db_query($conn, "
            UPDATE products SET
                category_id     = $category_id,
                name            = '$name',
                description     = '$description',
                material        = '$material',
                price           = $price,
                discount_price  = $discount_val,
                whatsapp_number = '$whatsapp',
                is_featured     = $is_featured,
                is_active       = $is_active
            WHERE id = $id
        ");

        // Upload new images
        if (!empty($_FILES['images']['name'][0])) {
            $upload_dir = '../uploads/products/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            $allowed     = ['jpg', 'jpeg', 'png', 'webp'];
            $has_primary = mysqli_num_rows(
                db_query($conn, "SELECT id FROM product_images WHERE product_id = $id AND is_primary = 1")
            ) > 0;

            foreach ($_FILES['images']['tmp_name'] as $i => $tmp) {
                if ($_FILES['images']['error'][$i] !== 0) continue;
                $ext = strtolower(pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed)) continue;

                $filename = 'product_' . $id . '_' . time() . '_' . $i . '.' . $ext;
                if (move_uploaded_file($tmp, $upload_dir . $filename)) {
                    $is_primary = (!$has_primary) ? 1 : 0;
                    if (!$has_primary) $has_primary = true;
                    db_query($conn, "
                        INSERT INTO product_images (product_id, image_path, is_primary, sort_order)
                        VALUES ($id, '$filename', $is_primary, $i)
                    ");
                }
            }
        }

        // Set primary image
        if (!empty($_POST['primary_image'])) {
            $primary_img_id = (int)$_POST['primary_image'];
            db_query($conn, "UPDATE product_images SET is_primary = 0 WHERE product_id = $id");
            db_query($conn, "UPDATE product_images SET is_primary = 1 WHERE id = $primary_img_id");
        }

        header("Location: products.php?success=Product successfully update ho gaya!");
        exit();
    }

    // Reload product data after failed POST
    $product['name']            = $_POST['name'];
    $product['category_id']     = $_POST['category_id'];
    $product['description']     = $_POST['description'];
    $product['material']        = $_POST['material'];
    $product['price']           = $_POST['price'];
    $product['discount_price']  = $_POST['discount_price'];
    $product['whatsapp_number'] = $_POST['whatsapp_number'];
    $product['is_featured']     = isset($_POST['is_featured']) ? 1 : 0;
    $product['is_active']       = isset($_POST['is_active']) ? 1 : 0;
}

require_once 'includes/admin-sidebar.php';
?>

<!-- Errors -->
<?php if(!empty($errors)): ?>
<div class="alert alert-danger mb-4" style="border-radius:12px;">
    <strong><i class="fas fa-exclamation-triangle me-2"></i>Yeh errors fix karo:</strong>
    <ul class="mb-0 mt-2">
        <?php foreach($errors as $e): ?>
        <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<!-- ✅ FIXED: form tag row se PEHLE shuru hota hai -->
<form method="POST" enctype="multipart/form-data">
<div class="row g-4">

    <!-- LEFT: Main Fields -->
    <div class="col-lg-8">

        <!-- Basic Info -->
        <div class="admin-table-card mb-4">
            <div class="table-header">
                <h5>
                    <i class="fas fa-info-circle me-2" style="color:var(--primary);"></i>
                    Basic Information
                </h5>
            </div>
            <div class="p-4">

                <!-- Name -->
                <div class="mb-4">
                    <label class="form-label fw-bold">
                        Product Name <span style="color:#E74C3C;">*</span>
                    </label>
                    <input type="text" name="name" class="form-control"
                           value="<?= htmlspecialchars($product['name']) ?>"
                           required maxlength="200">
                </div>

                <!-- Category -->
                <div class="mb-4">
                    <label class="form-label fw-bold">
                        Category <span style="color:#E74C3C;">*</span>
                    </label>
                    <select name="category_id" class="form-select" required>
                        <option value="">-- Category Select Karo --</option>
                        <?php foreach($all_cats as $cat): ?>
                        <option value="<?= $cat['id'] ?>"
                                <?= $product['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Description</label>
                    <textarea name="description" class="form-control" rows="5"
                              maxlength="2000"><?= htmlspecialchars($product['description']) ?></textarea>
                </div>

                <!-- Material -->
                <div>
                    <label class="form-label fw-bold">Material / Wood Type</label>
                    <input type="text" name="material" class="form-control"
                           value="<?= htmlspecialchars($product['material']) ?>"
                           maxlength="100">
                </div>
            </div>
        </div>

        <!-- Pricing -->
        <div class="admin-table-card mb-4">
            <div class="table-header">
                <h5>
                    <i class="fas fa-rupee-sign me-2" style="color:var(--primary);"></i>
                    Pricing
                </h5>
            </div>
            <div class="p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">
                            Original Price (₹) <span style="color:#E74C3C;">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"
                                  style="background:var(--bg-cream); border-color:#dee2e6;">₹</span>
                            <input type="number" name="price" class="form-control"
                                   value="<?= $product['price'] ?>"
                                   min="0" step="0.01" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Discount Price (₹)</label>
                        <div class="input-group">
                            <span class="input-group-text"
                                  style="background:var(--bg-cream); border-color:#dee2e6;">₹</span>
                            <input type="number" name="discount_price" class="form-control"
                                   value="<?= $product['discount_price'] ?>"
                                   min="0" step="0.01" placeholder="Optional">
                        </div>
                        <small class="text-muted">Khaali chhodo agar discount nahi hai</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- WhatsApp -->
        <div class="admin-table-card mb-4">
            <div class="table-header">
                <h5>
                    <i class="fab fa-whatsapp me-2" style="color:#25D366;"></i>
                    WhatsApp Contact
                </h5>
            </div>
            <div class="p-4">
                <label class="form-label fw-bold">WhatsApp Number</label>
                <div class="input-group">
                    <span class="input-group-text"
                          style="background:var(--bg-cream); border-color:#dee2e6;">+</span>
                    <input type="text" name="whatsapp_number" class="form-control"
                           placeholder="919876543210"
                           value="<?= htmlspecialchars($product['whatsapp_number']) ?>"
                           maxlength="20">
                </div>
                <small class="text-muted">Country code ke saath daalo, e.g. 919876543210</small>
            </div>
        </div>

        <!-- Existing Images -->
        <?php if(!empty($existing_images)): ?>
        <div class="admin-table-card mb-4">
            <div class="table-header">
                <h5>
                    <i class="fas fa-images me-2" style="color:var(--primary);"></i>
                    Current Images
                </h5>
            </div>
            <div class="p-4">
                <p class="text-muted mb-3" style="font-size:13px;">
                    <i class="fas fa-info-circle me-1"></i>
                    Image delete karne ke liye ❌ click karo.
                    Primary set karne ke liye "Main" radio select karo.
                </p>
                <div class="row g-3">
                    <?php foreach($existing_images as $img): ?>
                    <div class="col-4 col-md-3">
                        <div class="position-relative" id="img-wrap-<?= $img['id'] ?>">
                            <img src="../uploads/products/<?= $img['image_path'] ?>"
                                 style="width:100%; height:100px; object-fit:cover;
                                        border-radius:10px;
                                        border:2px solid <?= $img['is_primary'] ? 'var(--primary)' : '#eee' ?>;"
                                 onerror="this.src='https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=200'">

                            <!-- Delete checkbox -->
                            <label style="position:absolute; top:6px; right:6px; cursor:pointer;">
                                <input type="checkbox" name="delete_images[]"
                                       value="<?= $img['id'] ?>"
                                       style="display:none;"
                                       onchange="markDelete(this, <?= $img['id'] ?>)">
                                <div style="width:22px; height:22px; background:white; border-radius:50%;
                                            display:flex; align-items:center; justify-content:center;
                                            box-shadow:0 2px 6px rgba(0,0,0,0.2);">
                                    <i class="fas fa-times" style="font-size:11px; color:#E74C3C;"></i>
                                </div>
                            </label>

                            <!-- Primary radio -->
                            <div style="position:absolute; bottom:6px; left:6px;">
                                <label style="cursor:pointer; background:white; border-radius:50px;
                                              padding:2px 8px; font-size:11px; font-weight:600;
                                              color:var(--primary); box-shadow:0 2px 6px rgba(0,0,0,0.15);">
                                    <input type="radio" name="primary_image"
                                           value="<?= $img['id'] ?>"
                                           <?= $img['is_primary'] ? 'checked' : '' ?>
                                           style="margin-right:3px;">
                                    Main
                                </label>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Add New Images -->
        <div class="admin-table-card mb-4">
            <div class="table-header">
                <h5>
                    <i class="fas fa-plus me-2" style="color:var(--primary);"></i>
                    Add More Images
                </h5>
            </div>
            <div class="p-4">
                <div style="border:2px dashed var(--primary-light); border-radius:12px;
                            padding:24px; text-align:center; cursor:pointer; background:var(--bg-light);"
                     onclick="document.getElementById('new-images').click()">
                    <i class="fas fa-cloud-upload-alt fa-2x mb-2"
                       style="color:var(--primary-light);"></i>
                    <p class="mb-0 fw-bold" style="color:var(--primary); font-size:14px;">
                        Nayi images upload karo
                    </p>
                    <small class="text-muted">JPG, PNG, WEBP • Multiple select kar sakte ho</small>
                </div>
                <input type="file" name="images[]" id="new-images"
                       accept="image/*" multiple style="display:none;"
                       onchange="previewImages(this)">
                <div id="image-preview" class="d-flex flex-wrap gap-2 mt-3"></div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="d-flex gap-3 mb-4">
            <button type="submit" class="btn-primary-wood flex-grow-1"
                    style="padding:14px; font-size:16px;">
                <i class="fas fa-save me-2"></i>Update Product
            </button>
            <a href="products.php" class="btn-outline-wood"
               style="padding:14px 24px; font-size:16px;">
                Cancel
            </a>
        </div>

    </div> <!-- col-lg-8 end -->

    <!-- RIGHT: Visibility + Actions -->
    <!-- ✅ FIXED: Yeh ab form ke ANDAR hai -->
    <div class="col-lg-4">

        <!-- Visibility Toggles -->
        <div class="admin-table-card mb-4">
            <div class="table-header">
                <h5>
                    <i class="fas fa-eye me-2" style="color:var(--primary);"></i>
                    Visibility
                </h5>
            </div>
            <div class="p-4">

                <!-- Active Toggle -->
                <div class="d-flex justify-content-between align-items-center mb-3 p-3"
                     style="background:var(--bg-light); border-radius:10px;">
                    <div>
                        <div class="fw-bold" style="font-size:14px;">Active / Visible</div>
                        <small class="text-muted">Website pe show hoga</small>
                    </div>
                    <label style="cursor:pointer;">
                        <input type="checkbox" name="is_active" id="is_active"
                               style="display:none;"
                               <?= $product['is_active'] ? 'checked' : '' ?>
                               onchange="updateToggle(this, 'toggle-active')">
                        <div id="toggle-active"
                             style="width:48px; height:26px; border-radius:13px; transition:all 0.3s;
                                    background:<?= $product['is_active'] ? 'var(--primary)' : '#ccc' ?>;
                                    position:relative;">
                            <div style="width:20px; height:20px; background:white; border-radius:50%;
                                        position:absolute; top:3px; transition:all 0.3s;
                                        <?= $product['is_active'] ? 'right:3px;' : 'left:3px;' ?>">
                            </div>
                        </div>
                    </label>
                </div>

                <!-- Featured Toggle -->
                <div class="d-flex justify-content-between align-items-center p-3"
                     style="background:var(--bg-light); border-radius:10px;">
                    <div>
                        <div class="fw-bold" style="font-size:14px;">⭐ Featured</div>
                        <small class="text-muted">Homepage pe dikhao</small>
                    </div>
                    <label style="cursor:pointer;">
                        <input type="checkbox" name="is_featured" id="is_featured"
                               style="display:none;"
                               <?= $product['is_featured'] ? 'checked' : '' ?>
                               onchange="updateToggle(this, 'toggle-featured')">
                        <div id="toggle-featured"
                             style="width:48px; height:26px; border-radius:13px; transition:all 0.3s;
                                    background:<?= $product['is_featured'] ? 'var(--primary)' : '#ccc' ?>;
                                    position:relative;">
                            <div style="width:20px; height:20px; background:white; border-radius:50%;
                                        position:absolute; top:3px; transition:all 0.3s;
                                        <?= $product['is_featured'] ? 'right:3px;' : 'left:3px;' ?>">
                            </div>
                        </div>
                    </label>
                </div>

            </div>
        </div>

        <!-- Quick Actions -->
        <div class="admin-table-card p-4">
            <h6 class="mb-3" style="font-family:'Playfair Display',serif;">Quick Actions</h6>
            <div class="d-flex flex-column gap-2">
                <a href="../product-detail.php?slug=<?= $product['slug'] ?>" target="_blank"
                   class="btn-outline-wood text-center"
                   style="padding:10px; font-size:14px;">
                    <i class="fas fa-eye me-2"></i>Website pe Dekho
                </a>
                <button type="button"
                        onclick="confirmDelete('product-delete.php?id=<?= $product['id'] ?>', '<?= addslashes($product['name']) ?>')"
                        style="background:#FADBD8; color:#E74C3C; border:none; border-radius:50px;
                               padding:10px; font-size:14px; cursor:pointer; font-weight:600; width:100%;">
                    <i class="fas fa-trash me-2"></i>Delete Product
                </button>
            </div>
        </div>

        <!-- Tips -->
        <div class="p-4 mt-4 rounded"
             style="background:linear-gradient(135deg, var(--bg-cream), var(--bg-light));
                    border:2px solid var(--primary-light);">
            <h6 style="font-family:'Playfair Display',serif; color:var(--primary-dark);">
                💡 Tips
            </h6>
            <ul style="font-size:13px; color:var(--text-muted); padding-left:16px; margin:0;">
                <li class="mb-2">Active OFF karne se product website pe nahi dikhega</li>
                <li class="mb-2">Featured ON karne se product homepage pe aayega</li>
                <li class="mb-2">Primary image wahi dikhti hai jo "Main" select ho</li>
                <li>Images delete karne ke baad save zaroor karo</li>
            </ul>
        </div>

    </div> <!-- col-lg-4 end -->

</div> <!-- row end -->
</form> <!-- ✅ FIXED: form yahan band hota hai — sab kuch andar -->

<script>
// Toggle switch
function updateToggle(checkbox, toggleId) {
    const toggle = document.getElementById(toggleId);
    const dot    = toggle.querySelector('div');
    if (checkbox.checked) {
        toggle.style.background = 'var(--primary)';
        dot.style.left  = 'auto';
        dot.style.right = '3px';
    } else {
        toggle.style.background = '#ccc';
        dot.style.right = 'auto';
        dot.style.left  = '3px';
    }
}

// Mark image for deletion
function markDelete(checkbox, imgId) {
    const wrap = document.getElementById('img-wrap-' + imgId);
    const img  = wrap.querySelector('img');
    if (checkbox.checked) {
        img.style.opacity = '0.3';
        img.style.filter  = 'grayscale(100%)';
        wrap.style.outline = '2px solid #E74C3C';
        wrap.style.borderRadius = '10px';
    } else {
        img.style.opacity = '1';
        img.style.filter  = '';
        wrap.style.outline = '';
    }
}
</script>

<?php require_once 'includes/admin-footer.php'; ?>