<?php
$page_title = "Get Free Quote";
require_once 'includes/header.php';

// Login gate — agar user logged in nahi hai toh redirect
if (!isset($_SESSION['user_id'])) {
    $current = urlencode('quote.php' . (!empty($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : ''));
    header("Location: login.php?redirect=" . $current . "&msg=Quote%20lene%20ke%20liye%20pehle%20login%20karo!");
    exit();
}

// Pre-select product if coming from product page
$pre_product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

// All active products for dropdown
$prod_result = db_query($conn, "
    SELECT p.id, p.name, c.name as cat_name 
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.is_active = 1
    ORDER BY c.name ASC, p.name ASC
");
$all_products = mysqli_fetch_all($prod_result, MYSQLI_ASSOC);

// Success/Error message
$success = isset($_GET['success']) ? $_GET['success'] : '';
$error   = isset($_GET['error']) ? $_GET['error'] : '';
?>

<!-- Breadcrumb -->
<div class="breadcrumb-section">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active">Get Free Quote</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <!-- Success Message -->
            <?php if($success): ?>
            <div class="alert-wood mb-4 text-center p-4" style="border-radius:16px;">
                <i class="fas fa-check-circle fa-2x mb-3" style="color:var(--primary); display:block;"></i>
                <h5 style="color:var(--primary-dark);">Quote Request Bhej Diya Gaya! 🎉</h5>
                <p class="mb-0">Hamari team 24 ghante mein aapko contact karegi.</p>
            </div>
            <?php endif; ?>

            <?php if($error): ?>
            <div class="alert alert-danger mb-4 text-center">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <div class="quote-section">
                <!-- Header -->
                <div class="text-center mb-5">
                    <p style="color:var(--primary); font-weight:700; text-transform:uppercase; 
                               letter-spacing:2px; font-size:13px;">Free Service</p>
                    <h2>Get Your Free Quote</h2>
                    <div class="section-divider"></div>
                    <p class="text-muted">
                        Fill in your details and our team will get back to you with the best price!
                    </p>
                </div>

                <form action="quote-submit.php" method="POST">

                    <!-- Name + Phone -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fas fa-user me-1" style="color:var(--primary);"></i>
                                Your Name *
                            </label>
                            <input type="text" name="name" class="form-control" 
                                   placeholder="Aapka naam" required
                                   minlength="2" maxlength="100">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fas fa-phone me-1" style="color:var(--primary);"></i>
                                Phone Number *
                            </label>
                            <input type="tel" name="phone" class="form-control" 
                                   placeholder="+91 XXXXX XXXXX" required
                                   pattern="[0-9+\-\s]+" minlength="10" maxlength="20">
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="mb-4">
                        <label class="form-label">
                            <i class="fas fa-envelope me-1" style="color:var(--primary);"></i>
                            Email Address (Optional)
                        </label>
                        <input type="email" name="email" class="form-control" 
                               placeholder="aap@email.com" maxlength="100">
                    </div>

                    <!-- Product -->
                    <div class="mb-4">
                        <label class="form-label">
                            <i class="fas fa-couch me-1" style="color:var(--primary);"></i>
                            Product Select Karo (Optional)
                        </label>
                        <select name="product_id" class="form-select">
                            <option value="">-- Koi specific product hai? --</option>
                            <?php 
                            $current_cat = '';
                            foreach($all_products as $prod): 
                                if($current_cat != $prod['cat_name']):
                                    if($current_cat != '') echo '</optgroup>';
                                    $current_cat = $prod['cat_name'];
                                    echo '<optgroup label="'.$prod['cat_name'].'">';
                                endif;
                            ?>
                            <option value="<?= $prod['id'] ?>" 
                                    <?= $pre_product_id == $prod['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($prod['name']) ?>
                            </option>
                            <?php endforeach; ?>
                            <?php if($current_cat) echo '</optgroup>'; ?>
                        </select>
                    </div>

                    <!-- Message -->
                    <div class="mb-4">
                        <label class="form-label">
                            <i class="fas fa-comment me-1" style="color:var(--primary);"></i>
                            Aapki Requirements *
                        </label>
                        <textarea name="message" class="form-control" rows="5" 
                                  placeholder="Apni zaroorat batao — size, color, material, budget, ya koi bhi sawaal..." 
                                  required minlength="10" maxlength="1000"></textarea>
                        <small class="text-muted">Jitna detail doge, utna better quote milega!</small>
                    </div>

                    <!-- Features -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="text-center p-3" 
                                 style="background:var(--bg-light); border-radius:12px;">
                                <i class="fas fa-clock" style="color:var(--primary); font-size:24px;"></i>
                                <p class="mb-0 mt-2" style="font-size:13px; font-weight:600;">
                                    24hr Response
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3" 
                                 style="background:var(--bg-light); border-radius:12px;">
                                <i class="fas fa-rupee-sign" style="color:var(--primary); font-size:24px;"></i>
                                <p class="mb-0 mt-2" style="font-size:13px; font-weight:600;">
                                    Best Price
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3" 
                                 style="background:var(--bg-light); border-radius:12px;">
                                <i class="fas fa-shield-alt" style="color:var(--primary); font-size:24px;"></i>
                                <p class="mb-0 mt-2" style="font-size:13px; font-weight:600;">
                                    No Obligation
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn-primary-wood w-100" 
                            style="font-size:17px; padding:15px;">
                        <i class="fas fa-paper-plane me-2"></i>
                        Send Quote Request
                    </button>

                    <p class="text-center text-muted mt-3" style="font-size:13px;">
                        Ya seedha WhatsApp karo: 
                        <a href="https://wa.me/919876543210" target="_blank" 
                           style="color:#25D366; font-weight:600;">
                            <i class="fab fa-whatsapp me-1"></i>+91 98765 43210
                        </a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>