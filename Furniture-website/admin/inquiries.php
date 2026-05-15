<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireAdmin();

$page_title = "Inquiries";

// ── STATUS UPDATE ──────────────────────────────────────
if (isset($_GET['status']) && isset($_GET['id'])) {
    $inq_id     = (int)$_GET['id'];
    $new_status = sanitize($conn, $_GET['status']);

    if (in_array($new_status, ['new', 'seen', 'replied'])) {
        db_query($conn, "UPDATE inquiries SET status = '$new_status' WHERE id = $inq_id");
    }

    header("Location: inquiries.php?msg=Status update ho gaya!");
    exit();
}

// ── DELETE ─────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    db_query($conn, "DELETE FROM inquiries WHERE id = $del_id");

    header("Location: inquiries.php?msg=Inquiry delete ho gayi!");
    exit();
}

// ── FILTERS ────────────────────────────────────────────
$filter_status = isset($_GET['filter']) ? sanitize($conn, $_GET['filter']) : '';
$search        = isset($_GET['search']) ? sanitize($conn, $_GET['search']) : '';

$where = "WHERE 1=1";

if ($filter_status) {
    $where .= " AND i.status = '$filter_status'";
}

if ($search) {
    $where .= " AND (
        i.name LIKE '%$search%' OR
        i.phone LIKE '%$search%' OR
        i.email LIKE '%$search%'
    )";
}

// ── PAGINATION ─────────────────────────────────────────
$per_page = 15;
$page     = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset   = ($page - 1) * $per_page;

$total = mysqli_fetch_assoc(
    db_query($conn, "SELECT COUNT(*) as c FROM inquiries i $where")
)['c'];

$total_pages = ceil($total / $per_page);

// ── FETCH DATA ─────────────────────────────────────────
$inquiries = mysqli_fetch_all(db_query($conn, "
    SELECT i.*, p.name as product_name, p.slug as product_slug
    FROM inquiries i
    LEFT JOIN products p ON i.product_id = p.id
    $where
    ORDER BY i.created_at DESC
    LIMIT $per_page OFFSET $offset
"), MYSQLI_ASSOC);

// ── COUNTS ─────────────────────────────────────────────
$count_new = mysqli_fetch_assoc(
    db_query($conn, "SELECT COUNT(*) as c FROM inquiries WHERE status = 'new'")
)['c'];

$count_seen = mysqli_fetch_assoc(
    db_query($conn, "SELECT COUNT(*) as c FROM inquiries WHERE status = 'seen'")
)['c'];

$count_replied = mysqli_fetch_assoc(
    db_query($conn, "SELECT COUNT(*) as c FROM inquiries WHERE status = 'replied'")
)['c'];

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

require_once 'includes/admin-sidebar.php';
?>

<!-- MESSAGE -->
<?php if ($msg): ?>
<div class="alert-wood mb-4">
    <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>

<!-- STATS -->
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <a href="?filter=new">
            <div class="stat-card <?= $filter_status=='new'?'active':'' ?>">
                <h3><?= $count_new ?></h3>
                <p>New</p>
            </div>
        </a>
    </div>

    <div class="col-sm-4">
        <a href="?filter=seen">
            <div class="stat-card <?= $filter_status=='seen'?'active':'' ?>">
                <h3><?= $count_seen ?></h3>
                <p>Seen</p>
            </div>
        </a>
    </div>

    <div class="col-sm-4">
        <a href="?filter=replied">
            <div class="stat-card <?= $filter_status=='replied'?'active':'' ?>">
                <h3><?= $count_replied ?></h3>
                <p>Replied</p>
            </div>
        </a>
    </div>
</div>

<!-- SEARCH -->
<form method="GET" class="mb-4 d-flex gap-2">
    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search..." class="form-control">
    
    <select name="filter" class="form-control">
        <option value="">All</option>
        <option value="new" <?= $filter_status=='new'?'selected':'' ?>>New</option>
        <option value="seen" <?= $filter_status=='seen'?'selected':'' ?>>Seen</option>
        <option value="replied" <?= $filter_status=='replied'?'selected':'' ?>>Replied</option>
    </select>

    <button class="btn btn-primary">Search</button>
</form>

<!-- LIST -->
<?php if (empty($inquiries)): ?>
<p>No inquiries found</p>
<?php else: ?>

<?php foreach ($inquiries as $inq): ?>
<div class="card mb-3 p-3 <?= $inq['status']=='new'?'border-primary':'' ?>">

    <strong><?= htmlspecialchars($inq['name']) ?></strong>
    <span>(<?= $inq['status'] ?>)</span>

    <div><?= htmlspecialchars($inq['phone']) ?></div>
    <div><?= htmlspecialchars($inq['email']) ?></div>

    <div><?= nl2br(htmlspecialchars($inq['message'])) ?></div>

    <?php if ($inq['product_name']): ?>
        <div>
            Product:
            <a href="../product-detail.php?slug=<?= $inq['product_slug'] ?>" target="_blank">
                <?= htmlspecialchars($inq['product_name']) ?>
            </a>
        </div>
    <?php endif; ?>

    <div class="mt-2">
        <a href="?id=<?= $inq['id'] ?>&status=new">New</a> |
        <a href="?id=<?= $inq['id'] ?>&status=seen">Seen</a> |
        <a href="?id=<?= $inq['id'] ?>&status=replied">Replied</a> |
        <a href="?delete=<?= $inq['id'] ?>" onclick="return confirm('Delete?')">Delete</a>
    </div>

</div>
<?php endforeach; ?>

<!-- PAGINATION -->
<?php if ($total_pages > 1): ?>
<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="?page=<?= $page-1 ?>&filter=<?= $filter_status ?>&search=<?= $search ?>">Prev</a>
    <?php endif; ?>

    <?php for ($i=1; $i<=$total_pages; $i++): ?>
        <a href="?page=<?= $i ?>&filter=<?= $filter_status ?>&search=<?= $search ?>"
           class="<?= $i==$page?'active':'' ?>">
           <?= $i ?>
        </a>
    <?php endfor; ?>

    <?php if ($page < $total_pages): ?>
        <a href="?page=<?= $page+1 ?>&filter=<?= $filter_status ?>&search=<?= $search ?>">Next</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php endif; ?>

<?php require_once 'includes/admin-footer.php'; ?>