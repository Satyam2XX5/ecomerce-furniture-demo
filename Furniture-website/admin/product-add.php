<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireAdmin();

$page_title = "Add New Product";
$errors = [];
$success = '';

// All categories
$all_cats = mysqli_fetch_all(
    db_query($conn, "SELECT * FROM categories WHERE is_active = 1 ORDER BY name ASC"),
    MYSQLI_ASSOC
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitize inputs
    $name           = sanitize($conn, $_POST['name'] ?? '');
    $category_id    = (int)($_POST['category_id'] ?? 0);
    $description    = sanitize($conn, $_POST['description'] ?? '');
    $material       = sanitize($conn, $_POST['material'] ?? '');
    $price          = (float)($_POST['price'] ?? 0);
    $discount_price = $_POST['discount_price'] != '' ? (float)$_POST['discount_price'] : null;
    $whatsapp       = sanitize($conn, $_POST['whatsapp_number'] ?? '');
    $is_featured    = isset($_POST['is_featured']) ? 1 : 0;
    $is_active      = isset($_POST['is_active']) ? 1 : 0;

    // Validate
    if (empty($name))         $errors[] = "Product naam zaroori hai";
    if ($category_id == 0)    $errors[] = "Category select karo";
    if ($price <= 0)          $errors[] = "Valid price daalo";
    if ($discount_price !== null && $discount_price >= $price)
                              $errors[] = "Discount price, original price se kam honi chahiye";

    // Generate slug
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    $slug = rtrim($slug, '-');

    // Check slug unique
    $slug_check = db_query($conn, "SELECT id FROM products WHERE slug = '$slug'");
    if (mysqli_num_rows($slug_check) > 0) {
        $slug = $slug . '-' . time();
    }

    if (empty($errors)) {
        $discount_val = $discount_price !== null ? $discount_price : 'NULL';

        $sql = "INSERT INTO products 
                (category_id, name, slug, description, material, price, discount_price, 
                 whatsapp_number, is_featured, is_active)
                VALUES 
                ($category_id, '$name', '$slug', '$description', '$material', 
                 $price, $discount_val, '$whatsapp', $is_featured, $is_active)";

        db_query($conn, $sql);
        $product_id = mysqli_insert_id($conn);

        // Handle image uploads
        if (!empty($_FILES['images']['name'][0])) {
            $upload_dir = '../uploads/products/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $primary_set = false;

            foreach ($_FILES['images']['tmp_name'] as $i => $tmp) {
                if ($_FILES['images']['error'][$i] !== 0) continue;

                $original  = $_FILES['images']['name'][$i];
                $ext       = strtolower(pathinfo($original, PATHINFO_EXTENSION));

                if (!in_array($ext, $allowed)) continue;

                $filename  = 'product_' . $product_id . '_' . time() . '_' . $i . '.' . $ext;
                $dest      = $upload_dir . $filename;

                if (move_uploaded_file($tmp, $dest)) {
                    $is_primary = (!$primary_set) ? 1 : 0;
                    if (!$primary_set) $primary_set = true;

                    db_query($conn, "
                        INSERT INTO product_images (product_id, image_path, is_primary, sort_order)
                        VALUES ($product_id, '$filename', $is_primary, $i)
                    ");
                }
            }
        }

        header("Location: products.php?success=Product successfully add ho gaya!");
        exit();
    }
}

require_once 'includes/admin-sidebar.php';
?>

<!-- Error Messages -->
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

<div class="row g-4">

    <!-- Main Form -->
    <div class="col-lg-8">
        <form method="POST" enctype="multipart/form-data" id="product-form">

            <!-- Basic Info -->
            <div class="admin-table-card mb-4">
                <div class="table-header">
                    <h5><i class="fas fa-info-circle me-2" style="color:var(--primary);"></i>Basic Information</h5>
                </div>
                <div class="p-4">

                    <!-- Product Name -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            Product Name <span style="color:#E74C3C;">*</span>
                        </label>
                        <input type="text" name="name" class="form-control"
                               placeholder="e.g. Royal 3 Seater Sofa"
                               value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>"
                               required maxlength="200">
                        <small class="text-muted">URL slug automatically generate hoga</small>
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
                                <?= (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control" rows="5"
                                  placeholder="Product ki detail likho — features, size, color options..."
                                  maxlength="2000"><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
                    </div>

                    <!-- Material -->
                    <div class="mb-0">
                        <label class="form-label fw-bold">Material / Wood Type</label>
                        <input type="text" name="material" class="form-control"
                               placeholder="e.g. Teak Wood, Sheesham, Engineered Wood"
                               value="<?= isset($_POST['material']) ? htmlspecialchars($_POST['material']) : '' ?>"
                               maxlength="100">
                    </div>
                </div>
            </div>

            <!-- Pricing -->
            <div class="admin-table-card mb-4">
                <div class="table-header">
                    <h5><i class="fas fa-rupee-sign me-2" style="color:var(--primary);"></i>Pricing</h5>
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
                                       placeholder="25000"
                                       value="<?= isset($_POST['price']) ? $_POST['price'] : '' ?>"
                                       min="0" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Discount Price (₹)</label>
                            <div class="input-group">
                                <span class="input-group-text" 
                                      style="background:var(--bg-cream); border-color:#dee2e6;">₹</span>
                                <input type="number" name="discount_price" class="form-control"
                                       placeholder="22000 (optional)"
                                       value="<?= isset($_POST['discount_price']) ? $_POST['discount_price'] : '' ?>"
                                       min="0" step="0.01">
                            </div>
                            <small class="text-muted">Khaali chhodo agar discount nahi hai</small>
                        </div>
                    </div>

                    <!-- Price Preview -->
                    <div id="price-preview" class="mt-4 p-3"
                         style="background:var(--bg-light); border-radius:12px; display:none;">
                        <span class="price-current" id="preview-final">₹0</span>
                        <span class="price-original ms-2" id="preview-original"></span>
                        <span class="badge ms-2" id="preview-badge"
                              style="background:#E74C3C; color:white; border-radius:50px;"></span>
                    </div>
                </div>
            </div>

            <!-- Contact -->
            <div class="admin-table-card mb-4">
                <div class="table-header">
                    <h5><i class="fab fa-whatsapp me-2" style="color:#25D366;"></i>WhatsApp Contact</h5>
                </div>
                <div class="p-4">
                    <label class="form-label fw-bold">WhatsApp Number</label>
                    <div class="input-group">
                        <span class="input-group-text"
                              style="background:var(--bg-cream); border-color:#dee2e6;">+</span>
                        <input type="text" name="whatsapp_number" class="form-control"
                               placeholder="919876543210 (country code + number)"
                               value="<?= isset($_POST['whatsapp_number']) ? htmlspecialchars($_POST['whatsapp_number']) : '' ?>"
                               maxlength="20">
                    </div>
                    <small class="text-muted">Country code ke saath daalo, e.g. 919876543210</small>
                </div>
            </div>

            <!-- Images -->
            <div class="admin-table-card mb-4">
                <div class="table-header">
                    <h5><i class="fas fa-images me-2" style="color:var(--primary);"></i>Product Images</h5>
                </div>
                <div class="p-4">
                    <div class="mb-3"
                         style="border:2px dashed var(--primary-light); border-radius:12px;
                                padding:30px; text-align:center; cursor:pointer;
                                background:var(--bg-light);"
                         onclick="document.getElementById('image-input').click()">
                        <i class="fas fa-cloud-upload-alt fa-2x mb-2"
                           style="color:var(--primary-light);"></i>
                        <p class="mb-1 fw-bold" style="color:var(--primary);">
                            Click karke images upload karo
                        </p>
                        <small class="text-muted">JPG, PNG, WEBP • Multiple images select kar sakte ho</small>
                    </div>
                    <input type="file" name="images[]" id="image-input"
                           accept="image/*" multiple style="display:none;"
                           onchange="previewImages(this)">

                    <div id="image-preview" class="d-flex flex-wrap gap-2 mt-3"></div>
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Pehli image automatically primary/main image ban jaegi
                    </small>
                </div>
            </div>

            <!-- Submit -->
            <div class="d-flex gap-3">
                <button type="submit" class="btn-primary-wood flex-grow-1"
                        style="padding:14px; font-size:16px;">
                    <i class="fas fa-save me-2"></i>Save Product
                </button>
                <a href="products.php" class="btn-outline-wood"
                   style="padding:14px 24px; font-size:16px;">
                    Cancel
                </a>
            </div>

        </form>
    </div>

    <!-- Right Sidebar Options -->
    <div class="col-lg-4">

        <!-- Visibility -->
        <div class="admin-table-card mb-4">
            <div class="table-header">
                <h5><i class="fas fa-eye me-2" style="color:var(--primary);"></i>Visibility</h5>
            </div>
            <div class="p-4">

                <!-- Active Toggle -->
                <div class="d-flex justify-content-between align-items-center mb-3 p-3"
                     style="background:var(--bg-light); border-radius:10px;">
                    <div>
                        <div class="fw-bold" style="font-size:14px;">Active / Visible</div>
                        <small class="text-muted">Website pe show hoga</small>
                    </div>
                    <label class="position-relative" style="cursor:pointer;">
                        <input type="checkbox" name="is_active" id="is_active"
                               style="display:none;"
                               <?= !isset($_POST['is_active']) || $_POST['is_active'] ? 'checked' : '' ?>
                               onchange="updateToggle(this, 'toggle-active')">
                        <div id="toggle-active"
                             style="width:48px; height:26px; border-radius:13px; 
                                    background:var(--primary); transition:all 0.3s; position:relative;">
                            <div style="width:20px; height:20px; background:white; border-radius:50%;
                                        position:absolute; top:3px; right:3px; transition:all 0.3s;"></div>
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
                    <label class="position-relative" style="cursor:pointer;">
                        <input type="checkbox" name="is_featured" id="is_featured"
                               style="display:none;"
                               <?= isset($_POST['is_featured']) ? 'checked' : '' ?>
                               onchange="updateToggle(this, 'toggle-featured')">
                        <div id="toggle-featured"
                             style="width:48px; height:26px; border-radius:13px;
                                    background:#ccc; transition:all 0.3s; position:relative;">
                            <div style="width:20px; height:20px; background:white; border-radius:50%;
                                        position:absolute; top:3px; left:3px; transition:all 0.3s;"></div>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Tips -->
        <div class="p-4 rounded"
             style="background:linear-gradient(135deg, var(--bg-cream), var(--bg-light));
                    border:2px solid var(--primary-light);">
            <h6 style="font-family:'Playfair Display',serif; color:var(--primary-dark);">
                💡 Tips
            </h6>
            <ul style="font-size:13px; color:var(--text-muted); padding-left:16px; margin:0;">
                <li class="mb-2">Clear aur detailed naam use karo</li>
                <li class="mb-2">Multiple images upload karo for better presentation</li>
                <li class="mb-2">Material type likhne se customers ko help milti hai</li>
                <li class="mb-2">WhatsApp number daalo for direct inquiries</li>
                <li>Featured products homepage pe show hote hain</li>
            </ul>
        </div>
    </div>
</div>

<script>
// Price preview
const priceInput    = document.querySelector('[name="price"]');
const discountInput = document.querySelector('[name="discount_price"]');

function updatePricePreview() {
    const price    = parseFloat(priceInput.value) || 0;
    const discount = parseFloat(discountInput.value) || 0;
    const preview  = document.getElementById('price-preview');

    if (price > 0) {
        preview.style.display = 'block';
        const final = discount > 0 && discount < price ? discount : price;
        document.getElementById('preview-final').textContent =
            '₹' + final.toLocaleString('en-IN');

        if (discount > 0 && discount < price) {
            document.getElementById('preview-original').textContent =
                '₹' + price.toLocaleString('en-IN');
            const pct = Math.round(((price - discount) / price) * 100);
            document.getElementById('preview-badge').textContent = pct + '% OFF';
        } else {
            document.getElementById('preview-original').textContent = '';
            document.getElementById('preview-badge').textContent = '';
        }
    } else {
        preview.style.display = 'none';
    }
}

priceInput.addEventListener('input', updatePricePreview);
discountInput.addEventListener('input', updatePricePreview);

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

// Init toggles on load
document.addEventListener('DOMContentLoaded', function () {
    ['is_active', 'is_featured'].forEach(name => {
        const cb = document.getElementById(name);
        const toggleId = 'toggle-' + name.replace('is_', '');
        if (cb) updateToggle(cb, toggleId);
    });
});
</script>

<?php require_once 'includes/admin-footer.php'; ?>