<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireAdmin();

$page_title = "Orders Management";

// Status update
if (isset($_POST['update_status'])) {
    $order_id  = (int)$_POST['order_id'];
    $new_status = sanitize($conn, $_POST['status']);
    $note = sanitize($conn, $_POST['note'] ?? '');

    $valid = ['pending','confirmed','processing','shipped','delivered','cancelled'];
    if (in_array($new_status, $valid)) {
        db_query($conn, "UPDATE orders SET status='$new_status' WHERE id=$order_id");
        db_query($conn, "INSERT INTO order_status_history (order_id, status, note) VALUES ($order_id, '$new_status', '$note')");

        // Get order info for email
        $ord = mysqli_fetch_assoc(db_query($conn, "SELECT * FROM orders WHERE id=$order_id"));
        if ($ord && !empty($ord['customer_email'])) {
            $status_labels = [
                'confirmed'  => 'Confirmed ✅',
                'processing' => 'Processing 🔧',
                'shipped'    => 'Shipped 🚚',
                'delivered'  => 'Delivered 📦',
                'cancelled'  => 'Cancelled ❌',
            ];
            $slabel = $status_labels[$new_status] ?? ucfirst($new_status);
            $cname = $ord['customer_name'];
            $onum  = $ord['order_number'];
            $to = $ord['customer_email'];
            $subject = "Aapka Order #$onum - Status Update: $slabel";
            $body = "
            <html><body style='font-family:Arial,sans-serif;'>
            <div style='background:#8B4513;padding:25px;text-align:center;'>
                <h2 style='color:white;margin:0;'>🪑 Santosh Furniture</h2>
            </div>
            <div style='padding:30px;background:#fff;'>
                <h3 style='color:#8B4513;'>Namaskar $cname ji!</h3>
                <p>Aapke order <strong>#$onum</strong> ka status update ho gaya hai.</p>
                <div style='background:#FFF8F0;border-left:4px solid #8B4513;padding:15px 20px;border-radius:0 10px 10px 0;margin:20px 0;'>
                    <strong>Naya Status: $slabel</strong>
                    ".($note ? "<br><small>Note: $note</small>" : "")."
                </div>
                <a href='http://".$_SERVER['HTTP_HOST']."/furniture-website/my-orders.php?order=$onum' 
                   style='background:#8B4513;color:white;padding:12px 25px;text-decoration:none;border-radius:8px;display:inline-block;'>
                    Order Track Karo
                </a>
                <p style='margin-top:25px;color:#666;'>Koi sawaal? Call karein: <strong>+91 8210187952</strong></p>
            </div>
            </body></html>";
            $headers = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\nFrom: Santosh Furniture <Santoshfurniture@gmail.com>\r\n";
            @mail($to, $subject, $body, $headers);
        }

        header("Location: orders.php?msg=Status+update+ho+gaya!");
        exit();
    }
}

// Delete
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    db_query($conn, "DELETE FROM orders WHERE id=$del_id");
    header("Location: orders.php?msg=Order+delete+ho+gaya!");
    exit();
}

// Filters
$filter  = sanitize($conn, $_GET['filter'] ?? '');
$search  = sanitize($conn, $_GET['search'] ?? '');
$where = "WHERE 1=1";
if ($filter) $where .= " AND o.status = '$filter'";
if ($search) $where .= " AND (o.order_number LIKE '%$search%' OR o.customer_name LIKE '%$search%' OR o.customer_phone LIKE '%$search%')";

// Pagination
$per_page = 15;
$page = max(1,(int)($_GET['page'] ?? 1));
$offset = ($page-1)*$per_page;
$total = mysqli_fetch_assoc(db_query($conn,"SELECT COUNT(*) as c FROM orders o $where"))['c'];
$total_pages = ceil($total/$per_page);

$orders = mysqli_fetch_all(db_query($conn,
    "SELECT o.*, u.name as user_name FROM orders o LEFT JOIN users u ON o.user_id=u.id 
     $where ORDER BY o.created_at DESC LIMIT $per_page OFFSET $offset"
), MYSQLI_ASSOC);

// Status counts for tabs
$status_counts = [];
foreach (['all','pending','confirmed','processing','shipped','delivered','cancelled'] as $st) {
    $w = $st === 'all' ? '' : "WHERE status='$st'";
    $status_counts[$st] = mysqli_fetch_assoc(db_query($conn,"SELECT COUNT(*) as c FROM orders $w"))['c'];
}

require_once 'includes/admin-sidebar.php';

$status_map = [
    'pending'    => ['label'=>'Pending',    'color'=>'#FFA500'],
    'confirmed'  => ['label'=>'Confirmed',  'color'=>'#3498DB'],
    'processing' => ['label'=>'Processing', 'color'=>'#9B59B6'],
    'shipped'    => ['label'=>'Shipped',    'color'=>'#1ABC9C'],
    'delivered'  => ['label'=>'Delivered',  'color'=>'#27AE60'],
    'cancelled'  => ['label'=>'Cancelled',  'color'=>'#E74C3C'],
];
?>

<?php if(isset($_GET['msg'])): ?>
<div class="alert alert-success mb-4" style="border-radius:12px;">
    <i class="fas fa-check-circle me-1"></i><?= htmlspecialchars($_GET['msg']) ?>
</div>
<?php endif; ?>

<!-- Status Tabs -->
<div class="d-flex gap-2 flex-wrap mb-4">
    <?php foreach (['all'=>'Sab','pending'=>'Pending','confirmed'=>'Confirmed','processing'=>'Processing','shipped'=>'Shipped','delivered'=>'Delivered','cancelled'=>'Cancelled'] as $k=>$v): ?>
    <a href="orders.php?filter=<?= $k === 'all' ? '' : $k ?>" 
       class="btn btn-sm <?= ($filter === ($k==='all'?'':$k)) ? 'btn-dark' : 'btn-outline-secondary' ?>"
       style="border-radius:20px;">
        <?= $v ?> <span class="badge ms-1" style="background:rgba(255,255,255,0.3);"><?= $status_counts[$k] ?></span>
    </a>
    <?php endforeach; ?>
</div>

<!-- Search -->
<form method="GET" class="d-flex gap-2 mb-4">
    <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
    <input type="text" name="search" class="form-control" placeholder="Order no., naam, phone..."
           value="<?= htmlspecialchars($search) ?>" style="border-radius:10px; max-width:300px;">
    <button class="btn" type="submit" style="background:#8B4513;color:white;border-radius:10px;">
        <i class="fas fa-search"></i>
    </button>
    <?php if($search): ?><a href="orders.php" class="btn btn-outline-secondary" style="border-radius:10px;">Clear</a><?php endif; ?>
</form>

<div class="admin-table-card">
    <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
        <h6 style="font-family:'Playfair Display',serif; margin:0;">
            Orders (<?= $total ?>)
        </h6>
    </div>

    <div class="table-responsive">
        <table class="table admin-table mb-0">
            <thead>
                <tr>
                    <th>Order No.</th>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                <tr><td colspan="7" class="text-center py-4 text-muted">Koi order nahi mila</td></tr>
                <?php endif; ?>
                <?php foreach ($orders as $order): 
                    $s = $status_map[$order['status']] ?? ['label'=>$order['status'],'color'=>'#666'];
                ?>
                <tr>
                    <td><strong style="color:#8B4513;">#<?= $order['order_number'] ?></strong></td>
                    <td>
                        <?= htmlspecialchars($order['customer_name']) ?>
                        <?php if($order['user_name']): ?>
                        <small class="d-block text-muted">User: <?= htmlspecialchars($order['user_name']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($order['customer_phone']) ?></td>
                    <td><strong>₹<?= number_format($order['total_amount'],0) ?></strong></td>
                    <td>
                        <span class="badge px-2 py-1"
                              style="background:<?= $s['color'] ?>22; color:<?= $s['color'] ?>; border-radius:15px;">
                            <?= $s['label'] ?>
                        </span>
                    </td>
                    <td><small><?= date('d M Y', strtotime($order['created_at'])) ?></small></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1" style="border-radius:8px;"
                                onclick="openUpdateModal(<?= $order['id'] ?>, '<?= $order['status'] ?>')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <a href="order-detail.php?id=<?= $order['id'] ?>" 
                           class="btn btn-sm btn-outline-secondary me-1" style="border-radius:8px;">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="orders.php?delete=<?= $order['id'] ?>" 
                           class="btn btn-sm btn-outline-danger" style="border-radius:8px;"
                           onclick="return confirm('Order delete karna chahte ho?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if($total_pages > 1): ?>
    <div class="p-4 d-flex justify-content-center">
        <nav><ul class="pagination mb-0">
            <?php for($i=1;$i<=$total_pages;$i++): ?>
            <li class="page-item <?= $i==$page?'active':'' ?>">
                <a class="page-link" href="?page=<?= $i ?>&filter=<?= $filter ?>&search=<?= $search ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>
        </ul></nav>
    </div>
    <?php endif; ?>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px; border:none;">
            <div class="modal-header" style="border-bottom:1px solid #f0e6da;">
                <h5 class="modal-title" style="font-family:'Playfair Display',serif;">Order Status Update</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="update_status" value="1">
                    <input type="hidden" name="order_id" id="modal_order_id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Naya Status</label>
                        <select name="status" id="modal_status" class="form-select" style="border-radius:10px;">
                            <?php foreach($status_map as $k=>$v): ?>
                            <option value="<?= $k ?>"><?= $v['label'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Note (optional)</label>
                        <textarea name="note" class="form-control" rows="2" style="border-radius:10px;"
                                  placeholder="Customer ke liye note..."></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #f0e6da;">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" style="background:#8B4513;color:white;border-radius:10px;">
                        <i class="fas fa-save me-1"></i>Update Karo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openUpdateModal(orderId, currentStatus) {
    document.getElementById('modal_order_id').value = orderId;
    document.getElementById('modal_status').value = currentStatus;
    new bootstrap.Modal(document.getElementById('statusModal')).show();
}
</script>

<?php require_once 'includes/admin-footer.php'; ?>
