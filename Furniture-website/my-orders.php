<?php
require_once 'includes/db.php';
session_start();

$page_title = "Mere Orders";

// Login check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=my-orders.php&msg=Pehle login karo");
    exit();
}

$user_id   = (int)$_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Order detail view
$view_order = null;
$order_items_list = [];
$status_history = [];

if (isset($_GET['order'])) {
    $order_num = sanitize($conn, $_GET['order']);
    $stmt = $conn->prepare("SELECT * FROM orders WHERE order_number = ? AND user_id = ?");
    $stmt->bind_param("si", $order_num, $user_id);
    $stmt->execute();
    $view_order = $stmt->get_result()->fetch_assoc();

    if ($view_order) {
        $order_items_list = mysqli_fetch_all(
            db_query($conn, "SELECT * FROM order_items WHERE order_id = {$view_order['id']}"),
            MYSQLI_ASSOC
        );
        $status_history = mysqli_fetch_all(
            db_query($conn, "SELECT * FROM order_status_history WHERE order_id = {$view_order['id']} ORDER BY created_at ASC"),
            MYSQLI_ASSOC
        );
    }
}

// All orders list
$orders = mysqli_fetch_all(
    db_query($conn, "SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC"),
    MYSQLI_ASSOC
);

// Status labels & colors
$status_map = [
    'pending'    => ['label' => 'Pending',    'color' => '#FFA500', 'icon' => 'fa-clock'],
    'confirmed'  => ['label' => 'Confirmed',  'color' => '#3498DB', 'icon' => 'fa-check-circle'],
    'processing' => ['label' => 'Processing', 'color' => '#9B59B6', 'icon' => 'fa-cog'],
    'shipped'    => ['label' => 'Shipped',    'color' => '#1ABC9C', 'icon' => 'fa-truck'],
    'delivered'  => ['label' => 'Delivered',  'color' => '#27AE60', 'icon' => 'fa-box-open'],
    'cancelled'  => ['label' => 'Cancelled',  'color' => '#E74C3C', 'icon' => 'fa-times-circle'],
];

$all_statuses = ['pending','confirmed','processing','shipped','delivered'];

require_once 'includes/header.php';
?>

<div class="breadcrumb-section">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active">Mere Orders</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-5">

    <?php if (isset($_GET['welcome'])): ?>
    <div class="alert alert-success mb-4" style="border-radius:15px;">
        <i class="fas fa-party-horn me-2"></i>
        <strong>Swagat hai <?= htmlspecialchars($user_name) ?> ji!</strong> 
        Aapka account successfully ban gaya. 🎉
    </div>
    <?php endif; ?>

    <!-- User Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h3 style="font-family:'Playfair Display',serif; color:#8B4513;">
                <i class="fas fa-user-circle me-2"></i><?= htmlspecialchars($user_name) ?> ke Orders
            </h3>
            <p class="text-muted mb-0"><?= count($orders) ?> order(s) mile</p>
        </div>
        <a href="user-logout.php" class="btn btn-outline-danger" style="border-radius:10px;">
            <i class="fas fa-sign-out-alt me-1"></i>Logout
        </a>
    </div>

    <!-- ── LANGUAGE SETTINGS CARD ──────────────────────────── -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius:18px; overflow:hidden;">
        <div style="background:linear-gradient(135deg,#8B4513,#C17B4A); padding:16px 22px;">
            <h6 style="color:white; margin:0; font-weight:700;">
                <i class="fas fa-language me-2"></i>Language / भाषा Settings
            </h6>
        </div>
        <div class="card-body p-4">
            <p class="text-muted mb-3" style="font-size:14px;">
                Website ki bhasha chunno — aapki choice save rahegi! 
            </p>
            <div class="d-flex flex-wrap gap-3">

                <button class="lang-option" data-lang="hinglish"
                        onclick="setLanguage('hinglish')"
                        style="padding:12px 22px; border-radius:12px; font-weight:700; font-size:14px;
                               background:#FFF8F0; color:#8B4513; border:2px solid #8B4513;
                               display:flex; align-items:center; gap:8px;">
                    🤝 Hinglish
                    <span style="font-size:11px; background:#8B4513; color:white; padding:2px 8px; border-radius:50px;">Default</span>
                </button>

                <button class="lang-option" data-lang="hi"
                        onclick="setLanguage('hi')"
                        style="padding:12px 22px; border-radius:12px; font-weight:700; font-size:14px;
                               background:#FFF8F0; color:#8B4513; border:2px solid #8B4513;">
                    🇮🇳 हिंदी
                </button>

                <button class="lang-option" data-lang="en"
                        onclick="setLanguage('en')"
                        style="padding:12px 22px; border-radius:12px; font-weight:700; font-size:14px;
                               background:#FFF8F0; color:#8B4513; border:2px solid #8B4513;">
                    🇬🇧 English
                </button>
            </div>

            <!-- Live Preview -->
            <div class="mt-3 p-3" style="background:#FFF8F0; border-radius:12px; border:1px solid #e8d5c4;">
                <small class="text-muted d-block mb-1">Preview — cart button text:</small>
                <span style="font-size:15px; font-weight:600; color:#8B4513;" data-i18n="addToCart">
                    Cart mein add ho gaya! 🛒
                </span>
            </div>
        </div>
    </div>
    <!-- ── END LANGUAGE CARD ─────────────────────────────────── -->

    <?php if ($view_order): ?>
    <!-- ====== ORDER DETAIL VIEW ====== -->
    <div class="mb-3">
        <a href="my-orders.php" style="color:#8B4513; text-decoration:none;">
            <i class="fas fa-arrow-left me-1"></i>Wapas Orders par
        </a>
    </div>

    <div class="row g-4">
        <!-- Order Info -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                        <h5 style="font-family:'Playfair Display',serif;">
                            Order #<?= htmlspecialchars($view_order['order_number']) ?>
                        </h5>
                        <?php $s = $status_map[$view_order['status']] ?? $status_map['pending']; ?>
                        <span class="badge px-3 py-2" 
                              style="background:<?= $s['color'] ?>20; color:<?= $s['color'] ?>; 
                                     border-radius:20px; font-size:14px;">
                            <i class="fas <?= $s['icon'] ?> me-1"></i><?= $s['label'] ?>
                        </span>
                    </div>

                    <p class="text-muted mb-3">
                        <i class="fas fa-calendar me-1"></i>
                        <?= date('d M Y, h:i A', strtotime($view_order['created_at'])) ?>
                    </p>

                    <!-- Status Timeline -->
                    <h6 class="mb-3 fw-bold">Order Tracking</h6>
                    <div class="position-relative mb-4">
                        <div class="d-flex justify-content-between" style="position:relative;">
                            <!-- Progress line -->
                            <?php
                            $cur_idx = array_search($view_order['status'], $all_statuses);
                            $prog = $cur_idx === false ? 0 : (($cur_idx) / (count($all_statuses)-1)) * 100;
                            ?>
                            <div style="position:absolute; top:20px; left:0; right:0; height:4px; 
                                        background:#e9ecef; z-index:0;"></div>
                            <div style="position:absolute; top:20px; left:0; width:<?= $prog ?>%; 
                                        height:4px; background:#8B4513; z-index:1; transition:width .5s;"></div>

                            <?php foreach ($all_statuses as $i => $st): 
                                $info = $status_map[$st];
                                $is_done = $cur_idx !== false && $i <= $cur_idx;
                            ?>
                            <div class="text-center" style="z-index:2; flex:1;">
                                <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center"
                                     style="width:42px; height:42px; 
                                            background:<?= $is_done ? '#8B4513' : '#e9ecef' ?>; 
                                            color:<?= $is_done ? 'white' : '#aaa' ?>;
                                            border: 3px solid <?= $is_done ? '#8B4513' : '#e9ecef' ?>;
                                            font-size:16px;">
                                    <i class="fas <?= $info['icon'] ?>"></i>
                                </div>
                                <small class="d-block mt-1" 
                                       style="font-size:11px; color:<?= $is_done ? '#8B4513' : '#aaa' ?>; font-weight:<?= $is_done ? '700' : '400' ?>;">
                                    <?= $info['label'] ?>
                                </small>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <h6 class="fw-bold mb-3">Order Items</h6>
                    <?php foreach ($order_items_list as $item): ?>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div>
                            <div class="fw-semibold"><?= htmlspecialchars($item['product_name']) ?></div>
                            <?php if ($item['variant_info']): ?>
                            <small class="text-muted"><?= htmlspecialchars($item['variant_info']) ?></small>
                            <?php endif; ?>
                            <small class="text-muted">Qty: <?= $item['quantity'] ?></small>
                        </div>
                        <div class="fw-bold" style="color:#8B4513;">
                            ₹<?= number_format($item['subtotal'], 0) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <div class="d-flex justify-content-between mt-3 pt-2">
                        <span class="fw-bold fs-5">Total</span>
                        <span class="fw-bold fs-5" style="color:#8B4513;">
                            ₹<?= number_format($view_order['total_amount'], 0) ?>
                        </span>
                    </div>

                    <?php if ($view_order['notes']): ?>
                    <div class="mt-3 p-3" style="background:#FFF8F0; border-radius:10px;">
                        <small class="text-muted"><strong>Note:</strong> <?= htmlspecialchars($view_order['notes']) ?></small>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Customer Info + Status History -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">Delivery Details</h6>
                    <p class="mb-1"><i class="fas fa-user me-2 text-muted"></i><?= htmlspecialchars($view_order['customer_name']) ?></p>
                    <p class="mb-1"><i class="fas fa-phone me-2 text-muted"></i><?= htmlspecialchars($view_order['customer_phone']) ?></p>
                    <?php if ($view_order['customer_email']): ?>
                    <p class="mb-1"><i class="fas fa-envelope me-2 text-muted"></i><?= htmlspecialchars($view_order['customer_email']) ?></p>
                    <?php endif; ?>
                    <?php if ($view_order['customer_address']): ?>
                    <p class="mb-0"><i class="fas fa-map-marker-alt me-2 text-muted"></i><?= htmlspecialchars($view_order['customer_address']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($status_history)): ?>
            <div class="card border-0 shadow-sm" style="border-radius:16px;">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">Status History</h6>
                    <?php foreach (array_reverse($status_history) as $h): 
                        $hs = $status_map[$h['status']] ?? ['label' => $h['status'], 'color' => '#666', 'icon' => 'fa-circle'];
                    ?>
                    <div class="d-flex gap-3 mb-3">
                        <div class="rounded-circle flex-shrink-0" 
                             style="width:36px; height:36px; background:<?= $hs['color'] ?>20; 
                                    display:flex; align-items:center; justify-content:center; color:<?= $hs['color'] ?>;">
                            <i class="fas <?= $hs['icon'] ?>"></i>
                        </div>
                        <div>
                            <div class="fw-semibold" style="color:<?= $hs['color'] ?>;"><?= $hs['label'] ?></div>
                            <?php if ($h['note']): ?>
                            <small class="text-muted"><?= htmlspecialchars($h['note']) ?></small>
                            <?php endif; ?>
                            <small class="text-muted d-block"><?= date('d M, h:i A', strtotime($h['created_at'])) ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php elseif (empty($orders)): ?>
    <!-- No orders -->
    <div class="text-center py-5">
        <div style="font-size:80px; margin-bottom:20px;">📦</div>
        <h4 style="color:#8B4513; font-family:'Playfair Display',serif;">Abhi tak koi order nahi</h4>
        <p class="text-muted">Hamare products dekho aur apna pasandida furniture chunno!</p>
        <a href="products.php" class="btn btn-lg mt-2"
           style="background:#8B4513; color:white; border-radius:12px; padding:12px 30px;">
            <i class="fas fa-couch me-2"></i>Products Dekho
        </a>
    </div>

    <?php else: ?>
    <!-- Orders List -->
    <div class="row g-3">
        <?php foreach ($orders as $order): 
            $s = $status_map[$order['status']] ?? $status_map['pending'];
        ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius:16px; transition:transform .2s;"
                 onmouseenter="this.style.transform='translateY(-2px)'" 
                 onmouseleave="this.style.transform='translateY(0)'">
                <div class="card-body p-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-3">
                            <div class="fw-bold" style="color:#8B4513;">#<?= htmlspecialchars($order['order_number']) ?></div>
                            <small class="text-muted"><?= date('d M Y', strtotime($order['created_at'])) ?></small>
                        </div>
                        <div class="col-md-3">
                            <span class="badge px-3 py-2"
                                  style="background:<?= $s['color'] ?>20; color:<?= $s['color'] ?>; 
                                         border-radius:20px; font-size:13px;">
                                <i class="fas <?= $s['icon'] ?> me-1"></i><?= $s['label'] ?>
                            </span>
                        </div>
                        <div class="col-md-3">
                            <div class="fw-bold fs-5">₹<?= number_format($order['total_amount'], 0) ?></div>
                        </div>
                        <div class="col-md-3 text-md-end">
                            <a href="my-orders.php?order=<?= $order['order_number'] ?>"
                               class="btn btn-sm"
                               style="background:#8B4513; color:white; border-radius:8px; padding:8px 18px;">
                                <i class="fas fa-eye me-1"></i>Track Order
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
