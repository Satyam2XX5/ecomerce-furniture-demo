<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireAdmin();

$page_title = "Categories";
$errors     = [];
$success    = '';
$edit_cat   = null;

// ── EDIT MODE ──────────────────────────────────────────
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_cat = mysqli_fetch_assoc(
        db_query($conn, "SELECT * FROM categories WHERE id = $edit_id")
    );
}

// ── DELETE ─────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];

    // Check if products exist in this category
    $prod_count = mysqli_fetch_assoc(
        db_query($conn, "SELECT COUNT(*) as c FROM products WHERE category_id = $del_id")
    )['c'];

    if ($prod_count > 0) {
        $success = "error:Pehle is category ke $prod_count products delete ya move karo!";
    } else {
        // Delete category image
        $cat_row = mysqli_fetch_assoc(
            db_query($conn, "SELECT image FROM categories WHERE id = $del_id")
        );
        if ($cat_row && $cat_row['image']) {
            $file = '../uploads/categories/' . $cat_row['image'];
            if (file_exists($file)) unlink($file);
        }
        db_query($conn, "DELETE FROM categories WHERE id = $del_id");
        $success = "success:Category delete ho gayi!";
    }
}

// ── ADD / UPDATE ───────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cat_id      = (int)($_POST['cat_id'] ?? 0);
    $name        = sanitize($conn, $_POST['name'] ?? '');
    $description = sanitize($conn, $_POST['description'] ?? '');
    $is_active   = isset($_POST['is_active']) ? 1 : 0;

    // Slug generate
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    $slug = rtrim($slug, '-');

    if (empty($name)) $errors[] = "Category naam zaroori hai";

    // Check slug unique (exclude self on edit)
    $slug_check = db_query($conn, "
        SELECT id FROM categories 
        WHERE slug = '$slug' AND id != $cat_id
    ");
    if (mysqli_num_rows($slug_check) > 0) {
        $slug = $slug . '-' . time();
    }

    if (empty($errors)) {
        // Handle image upload
        $image_name = '';
        if (!empty($_FILES['image']['name'])) {
            $upload_dir = '../uploads/categories/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            $ext      = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed  = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($ext, $allowed) && $_FILES['image']['error'] === 0) {
                $image_name = 'cat_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_name);
            }
        }

        if ($cat_id > 0) {
            // UPDATE
            $img_sql = $image_name ? ", image = '$image_name'" : '';
            db_query($conn, "
                UPDATE categories SET
                    name        = '$name',
                    slug        = '$slug',
                    description = '$description',
                    is_active   = $is_active
                    $img_sql
                WHERE id = $cat_id
            ");
            $success = "success:Category update ho gayi!";
        } else {
            // INSERT
            db_query($conn, "
                INSERT INTO categories (name, slug, description, image, is_active)
                VALUES ('$name', '$slug', '$description', '$image_name', $is_active)
            ");
            $success = "success:Nayi category add ho gayi!";
        }

        $edit_cat = null;
        header("Location: categories.php?msg=" . urlencode(explode(':', $success)[1]));
        exit();
    }
}

// ── FETCH ALL CATEGORIES ───────────────────────────────
$categories = mysqli_fetch_all(db_query($conn, "
    SELECT c.*, COUNT(p.id) as product_count
    FROM categories c
    LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1
    GROUP BY c.id
    ORDER BY c.created_at DESC
"), MYSQLI_ASSOC);

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
$err = strpos($success, 'error:') === 0 ? substr($success, 6) : '';
$ok  = strpos($success, 'success:') === 0 ? substr($success, 8) : $msg;

require_once 'includes/admin-sidebar.php';
?>

<!-- Messages -->
<?php if($ok): ?>
<div class="alert-wood mb-4">
    <i class="fas fa-check-circle me-2" style="color:var(--primary);"></i>
    <?= htmlspecialchars($ok) ?>
</div>
<?php endif; ?>

<?php if($err): ?>
<div class="alert alert-danger mb-4" style="border-radius:12px;">
    <i class="fas fa-exclamation-circle me-2"></i>
    <?= htmlspecialchars($err) ?>
</div>
<?php endif; ?>

<?php if(!empty($errors)): ?>
<div class="alert alert-danger mb-4" style="border-radius:12px;">
    <?php foreach($errors as $e): ?>
    <div><i class="fas fa-times me-2"></i><?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="row g-4">

    <!-- ── ADD / EDIT FORM ── -->
    <div class="col-lg-4">
        <div class="admin-table-card">
            <div class="table-header">
                <h5>
                    <i class="fas fa-<?= $edit_cat ? 'edit' : 'plus-circle' ?> me-2"
                       style="color:var(--primary);"></i>
                    <?= $edit_cat ? 'Edit Category' : 'Add New Category' ?>
                </h5>
                <?php if($edit_cat): ?>
                <a href="categories.php" class="btn-outline-wood"
                   style="padding:6px 14px; font-size:12px;">
                    + New
                </a>
                <?php endif; ?>
            </div>
            <div class="p-4">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="cat_id"
                           value="<?= $edit_cat ? $edit_cat['id'] : 0 ?>">

                    <!-- Name -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            Category Name <span style="color:#E74C3C;">*</span>
                        </label>
                        <input type="text" name="name" class="form-control"
                               placeholder="e.g. Sofa & Couch"
                               value="<?= $edit_cat ? htmlspecialchars($edit_cat['name']) : (isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '') ?>"
                               required maxlength="100">
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control" rows="3"
                                  placeholder="Is category ke baare mein likhlo..."
                                  maxlength="500"><?= $edit_cat ? htmlspecialchars($edit_cat['description']) : '' ?></textarea>
                    </div>

                    <!-- Image -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Category Image</label>

                        <?php if($edit_cat && $edit_cat['image']): ?>
                        <div class="mb-2">
                            <img src="../uploads/categories/<?= $edit_cat['image'] ?>"
                                 style="width:100%; height:120px; object-fit:cover; 
                                        border-radius:10px; border:2px solid var(--primary-light);"
                                 onerror="this.style.display='none'">
                            <small class="text-muted d-block mt-1">Current image</small>
                        </div>
                        <?php endif; ?>

                        <div style="border:2px dashed var(--primary-light); border-radius:10px;
                                    padding:20px; text-align:center; cursor:pointer; 
                                    background:var(--bg-light);"
                             onclick="document.getElementById('cat-image').click()">
                            <i class="fas fa-image" style="color:var(--primary-light); font-size:28px;"></i>
                            <p class="mb-0 mt-2" style="font-size:13px; color:var(--primary); font-weight:600;">
                                <?= $edit_cat ? 'Change Image' : 'Upload Image' ?>
                            </p>
                            <small class="text-muted">JPG, PNG, WEBP</small>
                        </div>
                        <input type="file" id="cat-image" name="image"
                               accept="image/*" style="display:none;"
                               onchange="previewCatImage(this)">
                        <div id="cat-img-preview" class="mt-2"></div>
                    </div>

                    <!-- Active -->
                    <div class="d-flex justify-content-between align-items-center mb-4 p-3"
                         style="background:var(--bg-light); border-radius:10px;">
                        <div>
                            <div class="fw-bold" style="font-size:14px;">Active</div>
                            <small class="text-muted">Navbar mein dikhao</small>
                        </div>
                        <label style="cursor:pointer;">
                            <input type="checkbox" name="is_active" id="cat_active"
                                   style="display:none;"
                                   <?= (!$edit_cat || $edit_cat['is_active']) ? 'checked' : '' ?>
                                   onchange="updateToggle(this, 'toggle-cat-active')">
                            <div id="toggle-cat-active"
                                 style="width:48px; height:26px; border-radius:13px; 
                                        transition:all 0.3s; position:relative;
                                        background:<?= (!$edit_cat || $edit_cat['is_active']) ? 'var(--primary)' : '#ccc' ?>;">
                                <div style="width:20px; height:20px; background:white; border-radius:50%;
                                            position:absolute; top:3px; transition:all 0.3s;
                                            <?= (!$edit_cat || $edit_cat['is_active']) ? 'right:3px;' : 'left:3px;' ?>">
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn-primary-wood w-100"
                            style="padding:13px; font-size:15px;">
                        <i class="fas fa-save me-2"></i>
                        <?= $edit_cat ? 'Update Category' : 'Add Category' ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ── CATEGORIES TABLE ── -->
    <div class="col-lg-8">
        <div class="admin-table-card">
            <div class="table-header">
                <h5>All Categories (<?= count($categories) ?>)</h5>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width:60px;">Image</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Products</th>
                            <th>Status</th>
                            <th style="width:100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($categories)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="empty-state" style="padding:30px;">
                                    <i class="fas fa-tags" style="font-size:40px;"></i>
                                    <h5 class="mt-3">Koi category nahi hai</h5>
                                    <p>Pehli category add karo!</p>
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach($categories as $cat): ?>
                        <tr>
                            <td>
                                <img src="<?= $cat['image'] ? '../uploads/categories/'.$cat['image'] : 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=80' ?>"
                                     style="width:48px; height:48px; object-fit:cover; border-radius:10px;"
                                     onerror="this.src='https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=80'">
                            </td>
                            <td>
                                <div style="font-weight:600; font-size:14px;">
                                    <?= htmlspecialchars($cat['name']) ?>
                                </div>
                                <?php if($cat['description']): ?>
                                <div style="font-size:12px; color:var(--text-muted); 
                                            max-width:200px; white-space:nowrap; 
                                            overflow:hidden; text-overflow:ellipsis;">
                                    <?= htmlspecialchars($cat['description']) ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <code style="background:var(--bg-cream); padding:3px 8px; 
                                             border-radius:6px; font-size:12px; color:var(--primary);">
                                    <?= $cat['slug'] ?>
                                </code>
                            </td>
                            <td>
                                <span style="font-weight:700; color:var(--primary);">
                                    <?= $cat['product_count'] ?>
                                </span>
                                <span style="font-size:12px; color:var(--text-muted);"> products</span>
                            </td>
                            <td>
                                <span class="status-badge <?= $cat['is_active'] ? 'status-active' : 'status-inactive' ?>">
                                    <?= $cat['is_active'] ? 'Active' : 'Hidden' ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="categories.php?edit=<?= $cat['id'] ?>"
                                       title="Edit"
                                       style="width:34px; height:34px; background:var(--bg-cream);
                                              border-radius:8px; display:flex; align-items:center;
                                              justify-content:center; color:var(--primary); font-size:14px;">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="categories.php?delete=<?= $cat['id'] ?>"
                                       title="Delete"
                                       onclick="return confirm('\'<?= addslashes($cat['name']) ?>\' delete karna chahte ho?')"
                                       style="width:34px; height:34px; background:#FADBD8;
                                              border-radius:8px; display:flex; align-items:center;
                                              justify-content:center; color:#E74C3C; font-size:14px;">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function previewCatImage(input) {
    const preview = document.getElementById('cat-img-preview');
    if (!input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        preview.innerHTML = `
            <img src="${e.target.result}"
                 style="width:100%; height:100px; object-fit:cover; 
                        border-radius:10px; border:2px solid var(--primary);">`;
    };
    reader.readAsDataURL(input.files[0]);
}

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
</script>

<?php require_once 'includes/admin-footer.php'; ?>