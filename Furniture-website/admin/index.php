<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireAdmin();

$page_title = "Dashboard";
require_once 'includes/admin-sidebar.php';

// ── STATS ──────────────────────────────────────────────────────
$total_products   = mysqli_fetch_assoc(db_query($conn, "SELECT COUNT(*) as c FROM products WHERE is_active = 1"))['c'];
$total_categories = mysqli_fetch_assoc(db_query($conn, "SELECT COUNT(*) as c FROM categories WHERE is_active = 1"))['c'];
$total_inquiries  = mysqli_fetch_assoc(db_query($conn, "SELECT COUNT(*) as c FROM inquiries"))['c'];
$new_inquiries    = mysqli_fetch_assoc(db_query($conn, "SELECT COUNT(*) as c FROM inquiries WHERE status = 'new'"))['c'];
$total_users      = mysqli_fetch_assoc(db_query($conn, "SELECT COUNT(*) as c FROM users WHERE is_active = 1"))['c'];
$total_orders     = mysqli_fetch_assoc(db_query($conn, "SELECT COUNT(*) as c FROM orders"))['c'];
$pending_orders   = mysqli_fetch_assoc(db_query($conn, "SELECT COUNT(*) as c FROM orders WHERE status='pending'"))['c'];
$total_revenue    = mysqli_fetch_assoc(db_query($conn, "SELECT COALESCE(SUM(total_amount),0) as r FROM orders WHERE status NOT IN ('cancelled')"))['r'];

// ── MONTHLY INQUIRIES CHART (last 6 months) ───────────────────
$monthly_data = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $label = date('M Y', strtotime("-$i months"));
    $count = mysqli_fetch_assoc(db_query($conn, "SELECT COUNT(*) as c FROM inquiries WHERE DATE_FORMAT(created_at,'%Y-%m')='$month'"))['c'];
    $monthly_data[] = ['label' => $label, 'count' => $count];
}

// ── ORDER STATUS BREAKDOWN ─────────────────────────────────────
$order_statuses = [];
$statuses = ['pending','confirmed','processing','shipped','delivered','cancelled'];
foreach ($statuses as $st) {
    $order_statuses[$st] = mysqli_fetch_assoc(db_query($conn, "SELECT COUNT(*) as c FROM orders WHERE status='$st'"))['c'];
}

// ── TOP PRODUCTS BY INQUIRIES ──────────────────────────────────
$top_products = mysqli_fetch_all(db_query($conn, "
    SELECT p.name, COUNT(i.id) as inquiry_count
    FROM products p
    LEFT JOIN inquiries i ON i.product_id = p.id
    WHERE p.is_active = 1
    GROUP BY p.id, p.name
    ORDER BY inquiry_count DESC
    LIMIT 5
"), MYSQLI_ASSOC);

// ── RECENT DATA ────────────────────────────────────────────────
$recent_products = mysqli_fetch_all(db_query($conn, "
    SELECT p.*, c.name as cat_name 
    FROM products p JOIN categories c ON p.category_id = c.id 
    ORDER BY p.created_at DESC LIMIT 5
"), MYSQLI_ASSOC);

$recent_inquiries = mysqli_fetch_all(db_query($conn, "
    SELECT i.*, p.name as product_name 
    FROM inquiries i LEFT JOIN products p ON i.product_id = p.id 
    ORDER BY i.created_at DESC LIMIT 5
"), MYSQLI_ASSOC);

$recent_orders = mysqli_fetch_all(db_query($conn, "
    SELECT * FROM orders ORDER BY created_at DESC LIMIT 5
"), MYSQLI_ASSOC);
?>

<!-- ══ STAT CARDS ══════════════════════════════════════════════ -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon brown"><i class="fas fa-couch"></i></div>
            <div class="stat-info"><h3><?= $total_products ?></h3><p>Total Products</p></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-envelope"></i></div>
            <div class="stat-info"><h3><?= $total_inquiries ?></h3>
                <p>Total Inquiries 
                    <?php if($new_inquiries): ?>
                    <span class="badge ms-1" style="background:#E74C3C;font-size:10px;"><?= $new_inquiries ?> new</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-users"></i></div>
            <div class="stat-info"><h3><?= $total_users ?></h3><p>Registered Users</p></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon gold"><i class="fas fa-shopping-bag"></i></div>
            <div class="stat-info"><h3><?= $total_orders ?></h3>
                <p>Total Orders
                    <?php if($pending_orders): ?>
                    <span class="badge ms-1" style="background:#FFA500;font-size:10px;"><?= $pending_orders ?> pending</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Revenue + Quick Actions -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="admin-table-card p-4 text-center" style="background:linear-gradient(135deg,#8B4513,#A0522D);">
            <i class="fas fa-rupee-sign fa-2x text-white mb-2 opacity-75"></i>
            <h3 class="text-white fw-bold mb-1">₹<?= number_format($total_revenue, 0) ?></h3>
            <p class="text-white mb-0 opacity-75">Total Revenue (Orders)</p>
        </div>
    </div>
    <div class="col-md-8">
        <div class="admin-table-card p-4">
            <h6 class="mb-3" style="font-family:'Playfair Display',serif;">Quick Actions</h6>
            <div class="d-flex gap-2 flex-wrap">
                <a href="product-add.php" class="btn-primary-wood" style="font-size:13px;padding:9px 16px;">
                    <i class="fas fa-plus me-1"></i>New Product</a>
                <a href="orders.php" class="btn-outline-wood" style="font-size:13px;padding:9px 16px;">
                    <i class="fas fa-shopping-bag me-1"></i>Orders
                    <?php if($pending_orders): ?>
                    <span class="badge ms-1" style="background:#FFA500;color:white;font-size:10px;"><?= $pending_orders ?></span>
                    <?php endif; ?>
                </a>
                <a href="inquiries.php" class="btn-outline-wood" style="font-size:13px;padding:9px 16px;">
                    <i class="fas fa-envelope me-1"></i>Inquiries
                    <?php if($new_inquiries): ?>
                    <span class="badge ms-1" style="background:#E74C3C;color:white;font-size:10px;"><?= $new_inquiries ?></span>
                    <?php endif; ?>
                </a>
                <a href="categories.php" class="btn-outline-wood" style="font-size:13px;padding:9px 16px;">
                    <i class="fas fa-tags me-1"></i>Categories</a>
                <a href="../index.php" target="_blank" class="btn-outline-wood" style="font-size:13px;padding:9px 16px;">
                    <i class="fas fa-external-link-alt me-1"></i>Website</a>
            </div>
        </div>
    </div>
</div>

<!-- ══ CHARTS ROW ══════════════════════════════════════════════ -->
<div class="row g-4 mb-4">

    <!-- Monthly Inquiries Chart -->
    <div class="col-lg-7">
        <div class="admin-table-card p-4">
            <h6 class="mb-4" style="font-family:'Playfair Display',serif;">
                <i class="fas fa-chart-bar me-2" style="color:#8B4513;"></i>Monthly Inquiries (Last 6 Months)
            </h6>
            <canvas id="inquiryChart" height="120"></canvas>
        </div>
    </div>

    <!-- Order Status Pie -->
    <div class="col-lg-5">
        <div class="admin-table-card p-4">
            <h6 class="mb-4" style="font-family:'Playfair Display',serif;">
                <i class="fas fa-chart-pie me-2" style="color:#8B4513;"></i>Order Status
            </h6>
            <?php if ($total_orders > 0): ?>
            <canvas id="orderChart" height="160"></canvas>
            <?php else: ?>
            <div class="text-center py-4 text-muted">
                <i class="fas fa-shopping-bag fa-3x mb-3 opacity-25"></i>
                <p>Abhi koi order nahi hai</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ══ TOP PRODUCTS + RECENT ORDERS ══ -->
<div class="row g-4 mb-4">
    <!-- Top Products -->
    <div class="col-lg-5">
        <div class="admin-table-card">
            <div class="table-header">
                <h5>Top Products (Inquiries se)</h5>
            </div>
            <?php foreach ($top_products as $i => $tp): 
                $bar_pct = $top_products[0]['inquiry_count'] > 0 
                    ? round(($tp['inquiry_count'] / $top_products[0]['inquiry_count']) * 100) : 0;
            ?>
            <div class="px-4 py-2 border-bottom">
                <div class="d-flex justify-content-between mb-1">
                    <small class="fw-semibold"><?= htmlspecialchars($tp['name']) ?></small>
                    <small style="color:#8B4513; font-weight:700;"><?= $tp['inquiry_count'] ?> inquiries</small>
                </div>
                <div style="height:6px; background:#f0e6da; border-radius:3px;">
                    <div style="height:100%; width:<?= $bar_pct ?>%; background:#8B4513; border-radius:3px;"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="col-lg-7">
        <div class="admin-table-card">
            <div class="table-header">
                <h5>Recent Orders</h5>
                <a href="orders.php" class="btn-outline-wood" style="padding:8px 16px;font-size:13px;">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table admin-table mb-0">
                    <thead>
                        <tr><th>Order No.</th><th>Customer</th><th>Amount</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_orders)): ?>
                        <tr><td colspan="4" class="text-center py-3 text-muted">Koi order nahi</td></tr>
                        <?php endif; ?>
                        <?php
                        $sc = ['pending'=>'#FFA500','confirmed'=>'#3498DB','processing'=>'#9B59B6','shipped'=>'#1ABC9C','delivered'=>'#27AE60','cancelled'=>'#E74C3C'];
                        foreach ($recent_orders as $ord): $c = $sc[$ord['status']] ?? '#666'; ?>
                        <tr>
                            <td><strong style="color:#8B4513;font-size:13px;">#<?= $ord['order_number'] ?></strong></td>
                            <td><?= htmlspecialchars($ord['customer_name']) ?></td>
                            <td><strong>₹<?= number_format($ord['total_amount'],0) ?></strong></td>
                            <td>
                                <span class="badge px-2 py-1" style="background:<?= $c ?>22;color:<?= $c ?>;border-radius:10px;font-size:11px;">
                                    <?= ucfirst($ord['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ══ EXISTING TABLES ══ -->
<div class="row g-4">
    <div class="col-lg-7">
        <div class="admin-table-card">
            <div class="table-header">
                <h5>Recent Products</h5>
                <a href="products.php" class="btn-outline-wood" style="padding:8px 16px;font-size:13px;">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Product</th><th>Category</th><th>Price</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php if(empty($recent_products)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">Koi product nahi</td></tr>
                        <?php else: ?>
                        <?php foreach($recent_products as $p): ?>
                        <tr>
                            <td>
                                <div style="font-weight:600;font-size:14px;"><?= htmlspecialchars($p['name']) ?></div>
                                <?php if($p['is_featured']): ?><span style="font-size:11px;color:var(--accent);">⭐ Featured</span><?php endif; ?>
                            </td>
                            <td style="font-size:13px;color:var(--text-muted);"><?= htmlspecialchars($p['cat_name']) ?></td>
                            <td style="font-weight:600;color:var(--primary);">₹<?= number_format($p['discount_price'] ?: $p['price'],0,'.', ',') ?></td>
                            <td><span class="status-badge status-<?= $p['is_active'] ? 'active' : 'inactive' ?>"><?= $p['is_active'] ? 'Active' : 'Hidden' ?></span></td>
                            <td>
                                <a href="product-edit.php?id=<?= $p['id'] ?>" style="color:var(--primary);font-size:16px;margin-right:8px;"><i class="fas fa-edit"></i></a>
                                <a href="product-variants.php?product_id=<?= $p['id'] ?>" style="color:#9B59B6;font-size:14px;" title="Variants"><i class="fas fa-palette"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="admin-table-card">
            <div class="table-header">
                <h5>Recent Inquiries</h5>
                <a href="inquiries.php" class="btn-outline-wood" style="padding:8px 16px;font-size:13px;">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Name</th><th>Phone</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php if(empty($recent_inquiries)): ?>
                        <tr><td colspan="3" class="text-center text-muted py-4">Koi inquiry nahi</td></tr>
                        <?php else: ?>
                        <?php foreach($recent_inquiries as $inq): ?>
                        <tr>
                            <td>
                                <div style="font-weight:600;font-size:14px;"><?= htmlspecialchars($inq['name']) ?></div>
                                <?php if($inq['product_name']): ?><div style="font-size:11px;color:var(--text-muted);"><?= htmlspecialchars($inq['product_name']) ?></div><?php endif; ?>
                            </td>
                            <td style="font-size:13px;"><?= htmlspecialchars($inq['phone']) ?></td>
                            <td><span class="status-badge status-<?= $inq['status'] ?>"><?= ucfirst($inq['status']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Monthly Inquiries Bar Chart
const mLabels = <?= json_encode(array_column($monthly_data, 'label')) ?>;
const mData   = <?= json_encode(array_column($monthly_data, 'count')) ?>;

new Chart(document.getElementById('inquiryChart'), {
    type: 'bar',
    data: {
        labels: mLabels,
        datasets: [{
            label: 'Inquiries',
            data: mData,
            backgroundColor: 'rgba(139,69,19,0.7)',
            borderColor: '#8B4513',
            borderWidth: 2,
            borderRadius: 8,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f0e6da' } },
            x: { grid: { display: false } }
        }
    }
});

// Orders Doughnut Chart
<?php if ($total_orders > 0): ?>
const oLabels = ['Pending','Confirmed','Processing','Shipped','Delivered','Cancelled'];
const oData   = <?= json_encode(array_values($order_statuses)) ?>;
const oColors = ['#FFA500','#3498DB','#9B59B6','#1ABC9C','#27AE60','#E74C3C'];

new Chart(document.getElementById('orderChart'), {
    type: 'doughnut',
    data: {
        labels: oLabels,
        datasets: [{ data: oData, backgroundColor: oColors, borderWidth: 2, borderColor: '#fff' }]
    },
    options: {
        responsive: true,
        cutout: '65%',
        plugins: {
            legend: { position: 'right', labels: { font: { size: 12 }, padding: 12 } }
        }
    }
});
<?php endif; ?>
</script>

<?php require_once 'includes/admin-footer.php'; ?>
