<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireAdmin();

$page_title = "Customers";

// Toggle active status
if (isset($_POST['toggle_status'])) {
    $uid = (int)$_POST['user_id'];
    $cur = sanitize($conn, $_POST['current_status']);
    $new = $cur == '1' ? '0' : '1';
    db_query($conn, "UPDATE users SET is_active=$new WHERE id=$uid");
    header("Location: users.php?msg=updated");
    exit();
}

// Search + filter
$search = sanitize($conn, $_GET['search'] ?? '');
$filter = sanitize($conn, $_GET['filter'] ?? 'all');

$where = "WHERE 1=1";
if ($search) $where .= " AND (u.name LIKE '%$search%' OR u.email LIKE '%$search%' OR u.phone LIKE '%$search%')";
if ($filter === 'active')   $where .= " AND u.is_active = 1";
if ($filter === 'inactive') $where .= " AND u.is_active = 0";

$users = mysqli_fetch_all(db_query($conn, "
    SELECT u.*, 
           COUNT(DISTINCT o.id) as order_count
    FROM users u
    LEFT JOIN orders o ON o.user_id = u.id
    $where
    GROUP BY u.id
    ORDER BY u.created_at DESC
"), MYSQLI_ASSOC);

$total_users   = mysqli_fetch_assoc(db_query($conn, "SELECT COUNT(*) as c FROM users"))['c'];
$active_users  = mysqli_fetch_assoc(db_query($conn, "SELECT COUNT(*) as c FROM users WHERE is_active=1"))['c'];

require_once 'includes/admin-sidebar.php';
?>

<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon brown"><i class="fas fa-users"></i></div>
            <div class="stat-info"><h3><?= $total_users ?></h3><p>Total Customers</p></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-user-check"></i></div>
            <div class="stat-info"><h3><?= $active_users ?></h3><p>Active Customers</p></div>
        </div>
    </div>
</div>

<?php if (isset($_GET['msg'])): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i> Customer status update ho gaya!
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Search + Filter Bar -->
<div class="admin-card mb-4">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-6">
            <label class="form-label fw-semibold">Search</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" name="search" class="form-control" 
                       placeholder="Naam, email ya phone se dhundo..." 
                       value="<?= htmlspecialchars($search) ?>">
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Filter</label>
            <select name="filter" class="form-select">
                <option value="all"      <?= $filter=='all'      ? 'selected':'' ?>>Sab</option>
                <option value="active"   <?= $filter=='active'   ? 'selected':'' ?>>Active</option>
                <option value="inactive" <?= $filter=='inactive' ? 'selected':'' ?>>Inactive</option>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-filter me-1"></i> Filter
            </button>
            <a href="users.php" class="btn btn-outline-secondary w-100">Reset</a>
        </div>
    </form>
</div>

<!-- Users Table -->
<div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 fw-bold"><i class="fas fa-users me-2" style="color:#8B4513;"></i>Customers List</h5>
        <span class="badge" style="background:#8B4513; font-size:13px; padding:6px 12px; border-radius:50px;">
            <?= count($users) ?> customers
        </span>
    </div>

    <?php if (empty($users)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-users fa-3x mb-3" style="color:#ddd;"></i>
        <p>Koi customer nahi mila</p>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead style="background:#FFF8F0;">
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Orders</th>
                    <th>Registered</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,#8B4513,#c17b4a);
                                    display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:16px;">
                            <?= strtoupper(substr($u['name'],0,1)) ?>
                        </div>
                        <div>
                            <div class="fw-semibold"><?= htmlspecialchars($u['name']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($u['email']) ?></small>
                        </div>
                    </div>
                </td>
                <td><?= htmlspecialchars($u['phone'] ?? '—') ?></td>
                <td>
                    <?php if ($u['order_count'] > 0): ?>
                    <a href="orders.php?user=<?= $u['id'] ?>" class="badge" 
                       style="background:#8B4513;color:white;text-decoration:none;padding:5px 10px;border-radius:50px;">
                        <?= $u['order_count'] ?> orders
                    </a>
                    <?php else: ?>
                    <span class="text-muted">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <small class="text-muted"><?= date('d M Y', strtotime($u['created_at'])) ?></small>
                </td>
                <td>
                    <?php if ($u['is_active']): ?>
                    <span class="badge" style="background:#27AE60;color:white;border-radius:50px;padding:5px 12px;">Active</span>
                    <?php else: ?>
                    <span class="badge" style="background:#E74C3C;color:white;border-radius:50px;padding:5px 12px;">Inactive</span>
                    <?php endif; ?>
                </td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                        <input type="hidden" name="current_status" value="<?= $u['is_active'] ?>">
                        <button type="submit" name="toggle_status"
                                class="btn btn-sm"
                                style="background:<?= $u['is_active'] ? '#E74C3C' : '#27AE60' ?>;color:white;border-radius:8px;"
                                onclick="return confirm('Status change karna chahte ho?')">
                            <i class="fas fa-<?= $u['is_active'] ? 'ban' : 'check' ?> me-1"></i>
                            <?= $u['is_active'] ? 'Block' : 'Activate' ?>
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
