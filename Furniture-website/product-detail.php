<?php
require_once 'includes/db.php';

$slug = isset($_GET['slug']) ? sanitize($conn, $_GET['slug']) : '';

if (!$slug) {
    header("Location: products.php");
    exit();
}

// Get product
$stmt = $conn->prepare("
    SELECT p.*, c.name as cat_name, c.slug as cat_slug
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.slug = ? AND p.is_active = 1
");
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();


if (!$product) {
    header("Location: products.php");
    exit();
}

$page_title = $product['name'];

// Get all images
$img_result = db_query($conn, "
    SELECT * FROM product_images 
    WHERE product_id = {$product['id']} 
    ORDER BY is_primary DESC, sort_order ASC
");
$images = mysqli_fetch_all($img_result, MYSQLI_ASSOC);

// Get product variants (color, size, material)
$variants_result = db_query($conn, "
    SELECT * FROM product_variants 
    WHERE product_id = {$product['id']} AND is_active = 1 
    ORDER BY variant_type, sort_order ASC
");
$variants_raw = mysqli_fetch_all($variants_result, MYSQLI_ASSOC);

// Group variants by type
$variants = [];
foreach ($variants_raw as $v) {
    $variants[$v['variant_type']][] = $v;
}

// Related products
$related_result = db_query($conn, "
    SELECT p.*,
    (SELECT image_path FROM product_images 
     WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as main_image
    FROM products p
    WHERE p.category_id = {$product['category_id']} 
      AND p.id != {$product['id']} 
      AND p.is_active = 1
    ORDER BY RAND()
    LIMIT 4
");
$related = mysqli_fetch_all($related_result, MYSQLI_ASSOC);

$final_price = $product['discount_price'] ?: $product['price'];
$main_image = !empty($images) ? 'uploads/products/'.$images[0]['image_path'] : 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=600';

require_once 'includes/header.php';
?>

<!-- Breadcrumb -->
<div class="breadcrumb-section">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="products.php">Products</a></li>
                <li class="breadcrumb-item">
                    <a href="category.php?slug=<?= $product['cat_slug'] ?>">
                        <?= htmlspecialchars($product['cat_name']) ?>
                    </a>
                </li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($product['name']) ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="container pb-5">

    <!-- PRODUCT DETAIL -->
    <div class="row g-5 mb-5">

        <!-- Images -->
        <div class="col-lg-6">
            <img src="<?= $main_image ?>" 
                 alt="<?= htmlspecialchars($product['name']) ?>"
                 class="product-detail-img"
                 id="main-product-img"
                 onerror="this.src='https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=600'">

            <!-- Thumbnails -->
            <?php if(count($images) > 1): ?>
            <div class="thumbnail-strip mt-3">
                <?php foreach($images as $i => $img): ?>
                <img src="uploads/products/<?= $img['image_path'] ?>"
                     alt="View <?= $i+1 ?>"
                     class="<?= $i == 0 ? 'active' : '' ?>"
                     onclick="changeMainImage('uploads/products/<?= $img['image_path'] ?>')"
                     onerror="this.style.display='none'">
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Info -->
        <div class="col-lg-6">
            <!-- Category Tag -->
            <a href="category.php?slug=<?= $product['cat_slug'] ?>"
               style="display:inline-block; background:var(--bg-cream); color:var(--primary); 
                      padding:6px 16px; border-radius:50px; font-size:13px; font-weight:600; 
                      margin-bottom:16px; text-decoration:none;">
                <i class="fas fa-tag me-1"></i> <?= htmlspecialchars($product['cat_name']) ?>
            </a>

            <h1 class="product-detail-info" style="font-size:34px; margin-bottom:10px;">
                <?= htmlspecialchars($product['name']) ?>
            </h1>

            <!-- Rating Placeholder -->
            <div class="mb-3" style="color:#F1C40F;">
                ★★★★★ <span class="text-muted" style="font-size:14px;">Premium Quality</span>
            </div>

            <!-- Price -->
            <div class="product-detail-price">
                ₹<?= number_format($final_price, 0, '.', ',') ?>
                <?php if($product['discount_price']): ?>
                <span class="price-original ms-2" style="font-size:18px;">
                    ₹<?= number_format($product['price'], 0, '.', ',') ?>
                </span>
                <span class="badge ms-2" 
                      style="background:#E74C3C; color:white; font-size:14px; padding:6px 12px; border-radius:50px;">
                    <?= round((($product['price'] - $product['discount_price']) / $product['price']) * 100) ?>% OFF
                </span>
                <?php endif; ?>
            </div>

            <!-- Description -->
            <?php if($product['description']): ?>
            <p style="color:var(--text-muted); font-size:15px; line-height:1.8; margin-bottom:20px;">
                <?= nl2br(htmlspecialchars($product['description'])) ?>
            </p>
            <?php endif; ?>

            <!-- Product Meta -->
            <div class="product-meta">
                <?php if($product['material']): ?>
                <p><strong>🪵 Material:</strong> <?= htmlspecialchars($product['material']) ?></p>
                <?php endif; ?>
                <p><strong>📦 Category:</strong> <?= htmlspecialchars($product['cat_name']) ?></p>
                <p><strong>✅ Availability:</strong> 
                    <span style="color:#27AE60; font-weight:600;">In Stock</span>
                </p>
                <p class="mb-0"><strong>🚚 Delivery:</strong> Free delivery within city</p>
            </div>

            <!-- ── VARIANTS SELECTOR ───────────────────────── -->
            <?php if (!empty($variants)): ?>
            <div class="variant-section mb-4" id="variant-section">

                <?php if (!empty($variants['color'])): ?>
                <!-- Color Variants -->
                <div class="mb-3">
                    <p class="mb-2 fw-semibold" style="font-size:14px; color:#555;">
                        🎨 Rang (Color): <span id="selected-color-label" style="color:#8B4513; font-weight:700;">
                            <?= htmlspecialchars($variants['color'][0]['variant_value']) ?>
                        </span>
                    </p>
                    <div class="d-flex flex-wrap gap-2" id="color-options">
                        <?php foreach ($variants['color'] as $i => $cv): ?>
                        <button 
                            class="color-swatch <?= $i===0 ? 'selected' : '' ?>"
                            data-color="<?= htmlspecialchars($cv['variant_value']) ?>"
                            data-modifier="<?= $cv['price_modifier'] ?>"
                            data-variant-id="<?= $cv['id'] ?>"
                            onclick="selectVariant('color', this)"
                            title="<?= htmlspecialchars($cv['variant_value']) ?>"
                            style="
                                width:34px; height:34px; border-radius:50%; cursor:pointer;
                                background:<?= !empty($cv['color_code']) ? htmlspecialchars($cv['color_code']) : '#ccc' ?>;
                                border: <?= $i===0 ? '3px solid #8B4513' : '3px solid #ddd' ?>;
                                box-shadow: <?= $i===0 ? '0 0 0 2px white inset' : 'none' ?>;
                                transition: all 0.2s;
                            ">
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($variants['size'])): ?>
                <!-- Size Variants -->
                <div class="mb-3">
                    <p class="mb-2 fw-semibold" style="font-size:14px; color:#555;">
                        📐 Size: <span id="selected-size-label" style="color:#8B4513; font-weight:700;">
                            <?= htmlspecialchars($variants['size'][0]['variant_value']) ?>
                        </span>
                    </p>
                    <div class="d-flex flex-wrap gap-2" id="size-options">
                        <?php foreach ($variants['size'] as $i => $sv): ?>
                        <button
                            class="size-btn <?= $i===0 ? 'selected' : '' ?>"
                            data-size="<?= htmlspecialchars($sv['variant_value']) ?>"
                            data-modifier="<?= $sv['price_modifier'] ?>"
                            data-variant-id="<?= $sv['id'] ?>"
                            onclick="selectVariant('size', this)"
                            style="
                                padding:7px 18px; border-radius:8px; font-size:13px; font-weight:600;
                                cursor:pointer; transition:all 0.2s;
                                background:<?= $i===0 ? '#8B4513' : '#FFF8F0' ?>;
                                color:<?= $i===0 ? 'white' : '#8B4513' ?>;
                                border:2px solid <?= $i===0 ? '#8B4513' : '#e8d5c4' ?>;
                            ">
                            <?= htmlspecialchars($sv['variant_value']) ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($variants['material'])): ?>
                <!-- Material Variants -->
                <div class="mb-3">
                    <p class="mb-2 fw-semibold" style="font-size:14px; color:#555;">
                        🪵 Material: <span id="selected-material-label" style="color:#8B4513; font-weight:700;">
                            <?= htmlspecialchars($variants['material'][0]['variant_value']) ?>
                        </span>
                    </p>
                    <div class="d-flex flex-wrap gap-2" id="material-options">
                        <?php foreach ($variants['material'] as $i => $mv): ?>
                        <button
                            class="material-btn <?= $i===0 ? 'selected' : '' ?>"
                            data-material="<?= htmlspecialchars($mv['variant_value']) ?>"
                            data-modifier="<?= $mv['price_modifier'] ?>"
                            data-variant-id="<?= $mv['id'] ?>"
                            onclick="selectVariant('material', this)"
                            style="
                                padding:7px 18px; border-radius:8px; font-size:13px; font-weight:600;
                                cursor:pointer; transition:all 0.2s;
                                background:<?= $i===0 ? '#8B4513' : '#FFF8F0' ?>;
                                color:<?= $i===0 ? 'white' : '#8B4513' ?>;
                                border:2px solid <?= $i===0 ? '#8B4513' : '#e8d5c4' ?>;
                            ">
                            <?= htmlspecialchars($mv['variant_value']) ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Variant Price Note -->
                <div id="variant-price-note" style="display:none; background:#FFF8F0; border:1px solid #e8d5c4; 
                     border-radius:8px; padding:10px 14px; font-size:13px; margin-bottom:8px;">
                    <i class="fas fa-info-circle me-1" style="color:#8B4513;"></i>
                    <span id="variant-note-text"></span>
                </div>
            </div>
            <?php endif; ?>
            <!-- ── END VARIANTS ──────────────────────────────── -->

            <!-- Quantity + Cart -->
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="d-flex align-items-center gap-2" 
                     style="background:var(--bg-cream); border-radius:50px; padding:8px 16px;">
                    <button onclick="changeDetailQty(-1)" 
                            style="background:none; border:none; font-size:20px; color:var(--primary); 
                                   cursor:pointer; width:28px; font-weight:700;">−</button>
                    <span id="detail-qty" style="font-size:18px; font-weight:700; min-width:30px; 
                                                  text-align:center;">1</span>
                    <button onclick="changeDetailQty(1)" 
                            style="background:none; border:none; font-size:20px; color:var(--primary); 
                                   cursor:pointer; width:28px; font-weight:700;">+</button>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="product-detail-actions">
                <button class="btn-primary-wood" style="flex:1; text-align:center;"
                    onclick="addToCartWithQty(
                        <?= $product['id'] ?>,
                        '<?= addslashes($product['name']) ?>',
                        <?= $final_price ?>,
                        '<?= !empty($images) ? 'uploads/products/'.$images[0]['image_path'] : '' ?>'
                    )">
                    <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                </button>

                <?php if($product['whatsapp_number']): ?>
                <button class="btn-whatsapp" style="flex:1; text-align:center;"
                    onclick="openWhatsApp('<?= $product['whatsapp_number'] ?>', '<?= addslashes($product['name']) ?>')">
                    <i class="fab fa-whatsapp me-2"></i>WhatsApp Inquiry
                </button>
                <?php endif; ?>

                <button onclick="goToQuote('/furniture-website/quote.php?product_id=<?= $product['id'] ?>')"
                   class="btn-outline-wood" style="flex:1; text-align:center; cursor:pointer;">
                    <i class="fas fa-file-alt me-2"></i>Get Quote
                </button>
            </div>

            <!-- Share -->
            <div class="mt-4 pt-3" style="border-top:2px solid var(--bg-cream);">
                <span class="text-muted me-3" style="font-size:14px;">Share:</span>
                <a href="https://wa.me/?text=Check this furniture: <?= urlencode($product['name']) ?> - <?= urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']) ?>"
                   target="_blank" 
                   style="color:#25D366; font-size:22px; margin-right:12px;">
                    <i class="fab fa-whatsapp"></i>
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']) ?>"
                   target="_blank"
                   style="color:#3B5998; font-size:22px; margin-right:12px;">
                    <i class="fab fa-facebook"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- RELATED PRODUCTS -->
    <?php if(!empty($related)): ?>
    <div class="mt-4">
        <div class="section-heading text-start mb-4">
            <h3 style="font-family:'Playfair Display',serif;">Related Products</h3>
            <div class="section-divider" style="margin:10px 0 0;"></div>
        </div>
        <div class="row g-4">
            <?php foreach($related as $rel): ?>
            <div class="col-sm-6 col-lg-3">
                <div class="product-card">
                    <div class="product-card-img">
                        <img src="<?= $rel['main_image'] ? 'uploads/products/'.$rel['main_image'] : 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=400' ?>"
                             alt="<?= htmlspecialchars($rel['name']) ?>"
                             onerror="this.src='https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=400'">
                    </div>
                    <div class="product-card-body">
                        <h5><?= htmlspecialchars($rel['name']) ?></h5>
                        <div class="product-price">
                            <span class="price-current">
                                ₹<?= number_format($rel['discount_price'] ?: $rel['price'], 0, '.', ',') ?>
                            </span>
                        </div>
                        <a href="product-detail.php?slug=<?= $rel['slug'] ?>" 
                           class="btn-outline-wood d-block text-center">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Quantity Script -->
<script>
const BASE_PRICE = <?= (float)$final_price ?>;
let variantModifier = 0;
let selectedVariants = {};

function selectVariant(type, btn) {
    // Remove selected state from siblings
    btn.parentElement.querySelectorAll('button').forEach(b => {
        if (type === 'color') {
            b.style.border = '3px solid #ddd';
            b.style.boxShadow = 'none';
        } else {
            b.style.background = '#FFF8F0';
            b.style.color = '#8B4513';
            b.style.border = '2px solid #e8d5c4';
        }
    });

    // Apply selected state
    if (type === 'color') {
        btn.style.border = '3px solid #8B4513';
        btn.style.boxShadow = '0 0 0 2px white inset';
        const lbl = document.getElementById('selected-color-label');
        if (lbl) lbl.textContent = btn.dataset.color;
        selectedVariants['color'] = btn.dataset.color;
    } else if (type === 'size') {
        btn.style.background = '#8B4513';
        btn.style.color = 'white';
        btn.style.border = '2px solid #8B4513';
        const lbl = document.getElementById('selected-size-label');
        if (lbl) lbl.textContent = btn.dataset.size;
        selectedVariants['size'] = btn.dataset.size;
    } else if (type === 'material') {
        btn.style.background = '#8B4513';
        btn.style.color = 'white';
        btn.style.border = '2px solid #8B4513';
        const lbl = document.getElementById('selected-material-label');
        if (lbl) lbl.textContent = btn.dataset.material;
        selectedVariants['material'] = btn.dataset.material;
    }

    // Recalculate price modifier (use highest modifier among selected variants)
    let totalMod = 0;
    document.querySelectorAll('#variant-section button.selected, #variant-section button[style*="background:#8B4513"], #variant-section button[style*="border: 3px solid #8B4513"]').forEach(b => {
        if (b.dataset.modifier) totalMod += parseFloat(b.dataset.modifier);
    });

    const modifier = parseFloat(btn.dataset.modifier) || 0;
    variantModifier = modifier;

    // Show price note if modifier exists
    const note = document.getElementById('variant-price-note');
    const noteText = document.getElementById('variant-note-text');
    if (modifier !== 0 && note && noteText) {
        const sign = modifier > 0 ? '+' : '';
        noteText.textContent = `Is variant ke liye price: ₹${(BASE_PRICE + modifier).toLocaleString('en-IN')} (${sign}₹${modifier.toLocaleString('en-IN')})`;
        note.style.display = 'block';
    } else if (note) {
        note.style.display = 'none';
    }
}

function getVariantInfo() {
    const parts = [];
    if (selectedVariants['color']) parts.push('Color: ' + selectedVariants['color']);
    if (selectedVariants['size']) parts.push('Size: ' + selectedVariants['size']);
    if (selectedVariants['material']) parts.push('Material: ' + selectedVariants['material']);
    return parts.join(' | ');
}

function changeDetailQty(change) {
    const el = document.getElementById('detail-qty');
    let qty = parseInt(el.textContent) + change;
    if (qty < 1) qty = 1;
    el.textContent = qty;
}

function addToCartWithQty(id, name, price, image) {
    const qty = parseInt(document.getElementById('detail-qty').textContent);
    const variantInfo = getVariantInfo();
    const finalPrice = BASE_PRICE + variantModifier;
    const displayName = variantInfo ? name + ' (' + variantInfo + ')' : name;
    for (let i = 0; i < qty; i++) {
        addToCart(id, displayName, finalPrice, image);
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>