<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireAdmin();

$order_id = (int)($_GET['id'] ?? 0);
$order = mysqli_fetch_assoc(db_query($conn, "SELECT o.*, u.name as uname, u.email as uemail FROM orders o LEFT JOIN users u ON o.user_id=u.id WHERE o.id=$order_id"));
if (!$order) { header("Location: orders.php"); exit(); }

$page_title = "Order Detail #".$order['order_number'];

$items = mysqli_fetch_all(db_query($conn, "SELECT oi.*, pi.image_path as img FROM order_items oi 
    LEFT JOIN product_images pi ON pi.product_id=oi.product_id AND pi.is_primary=1 
    WHERE oi.order_id=$order_id"), MYSQLI_ASSOC);

$history = mysqli_fetch_all(db_query($conn, "SELECT * FROM order_status_history WHERE order_id=$order_id ORDER BY created_at ASC"), MYSQLI_ASSOC);

$status_map = [
    'pending'    => ['label'=>'Pending',    'color'=>'#FFA500', 'icon'=>'fa-clock'],
    'confirmed'  => ['label'=>'Confirmed',  'color'=>'#3498DB', 'icon'=>'fa-check-circle'],
    'processing' => ['label'=>'Processing', 'color'=>'#9B59B6', 'icon'=>'fa-cog'],
    'shipped'    => ['label'=>'Shipped',    'color'=>'#1ABC9C', 'icon'=>'fa-truck'],
    'delivered'  => ['label'=>'Delivered',  'color'=>'#27AE60', 'icon'=>'fa-box-open'],
    'cancelled'  => ['label'=>'Cancelled',  'color'=>'#E74C3C', 'icon'=>'fa-times-circle'],
];
$s = $status_map[$order['status']] ?? ['label'=>ucfirst($order['status']),'color'=>'#666','icon'=>'fa-circle'];

require_once 'includes/admin-sidebar.php';
?>

<div class="mb-3">
    <a href="orders.php" style="color:#8B4513; text-decoration:none;">
        <i class="fas fa-arrow-left me-1"></i>Wapas Orders par
    </a>
</div>

<div class="row g-4">
    <!-- Order Items -->
    <div class="col-lg-8">
        <div class="admin-table-card mb-4">
            <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                <h6 style="font-family:'Playfair Display',serif; margin:0;">
                    Order #<?= $order['order_number'] ?>
                </h6>
                <span class="badge px-3 py-2"
                      style="background:<?= $s['color'] ?>22; color:<?= $s['color'] ?>; border-radius:15px; font-size:13px;">
                    <i class="fas <?= $s['icon'] ?> me-1"></i><?= $s['label'] ?>
                </span>
            </div>
            <div class="p-4">
                <p class="text-muted mb-3"><i class="fas fa-calendar me-2"></i><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></p>
                
                <?php foreach ($items as $item): ?>
                <div class="d-flex gap-3 align-items-center py-3 border-bottom">
                    <?php if ($item['img']): ?>
                    <img src="../uploads/products/<?= $item['img'] ?>" 
                         style="width:60px; height:60px; object-fit:cover; border-radius:10px;">
                    <?php else: ?>
                    <div style="width:60px; height:60px; background:#f5ece3; border-radius:10px; display:flex; align-items:center; justify-content:center; color:#8B4513; font-size:24px;">
                        <i class="fas fa-couch"></i>
                    </div>
                    <?php endif; ?>
                    <div class="flex-grow-1">
                        <div class="fw-semibold"><?= htmlspecialchars($item['product_name']) ?></div>
                        <?php if ($item['variant_info']): ?>
                        <small class="text-muted"><?= htmlspecialchars($item['variant_info']) ?></small>
                        <?php endif; ?>
                        <small class="text-muted d-block">Qty: <?= $item['quantity'] ?> × ₹<?= number_format($item['price'],0) ?></small>
                    </div>
                    <div class="fw-bold" style="color:#8B4513;">₹<?= number_format($item['subtotal'],0) ?></div>
                </div>
                <?php endforeach; ?>

                <div class="d-flex justify-content-between mt-3 pt-2 fs-5 fw-bold">
                    <span>Total</span>
                    <span style="color:#8B4513;">₹<?= number_format($order['total_amount'],0) ?></span>
                </div>

                <?php if ($order['notes']): ?>
                <div class="mt-3 p-3" style="background:#FFF8F0; border-radius:10px;">
                    <strong>Note:</strong> <?= htmlspecialchars($order['notes']) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Status History -->
        <?php if (!empty($history)): ?>
        <div class="admin-table-card">
            <div class="p-4 border-bottom"><h6 style="font-family:'Playfair Display',serif; margin:0;">Status History</h6></div>
            <div class="p-4">
                <?php foreach ($history as $h):
                    $hs = $status_map[$h['status']] ?? ['label'=>$h['status'],'color'=>'#666','icon'=>'fa-circle'];
                ?>
                <div class="d-flex gap-3 mb-3">
                    <div class="rounded-circle flex-shrink-0"
                         style="width:40px;height:40px;background:<?= $hs['color'] ?>22;display:flex;align-items:center;justify-content:center;color:<?= $hs['color'] ?>;">
                        <i class="fas <?= $hs['icon'] ?>"></i>
                    </div>
                    <div>
                        <div class="fw-semibold" style="color:<?= $hs['color'] ?>;"><?= $hs['label'] ?></div>
                        <?php if($h['note']): ?><small class="text-muted"><?= htmlspecialchars($h['note']) ?></small><?php endif; ?>
                        <small class="text-muted d-block"><?= date('d M Y, h:i A', strtotime($h['created_at'])) ?></small>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Customer Info + Actions -->
    <div class="col-lg-4">
        <div class="admin-table-card mb-4">
            <div class="p-4 border-bottom"><h6 style="font-family:'Playfair Display',serif;margin:0;">Customer Info</h6></div>
            <div class="p-4">
                <p><i class="fas fa-user me-2 text-muted"></i><strong><?= htmlspecialchars($order['customer_name']) ?></strong></p>
                <p><i class="fas fa-phone me-2 text-muted"></i><?= htmlspecialchars($order['customer_phone']) ?></p>
                <?php if($order['customer_email']): ?>
                <p><i class="fas fa-envelope me-2 text-muted"></i><?= htmlspecialchars($order['customer_email']) ?></p>
                <?php endif; ?>
                <?php if($order['customer_address']): ?>
                <p><i class="fas fa-map-marker-alt me-2 text-muted"></i><?= htmlspecialchars($order['customer_address']) ?></p>
                <?php endif; ?>
                <?php if($order['uname']): ?>
                <hr>
                <small class="text-muted">Registered User: <?= htmlspecialchars($order['uname']) ?></small>
                <?php endif; ?>
            </div>
        </div>

        <!-- Update Status -->
        <div class="admin-table-card">
            <div class="p-4 border-bottom"><h6 style="font-family:'Playfair Display',serif;margin:0;">Status Update</h6></div>
            <div class="p-4">
                <form method="POST" action="orders.php">
                    <input type="hidden" name="update_status" value="1">
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select" style="border-radius:10px;">
                            <?php foreach($status_map as $k=>$v): ?>
                            <option value="<?= $k ?>" <?= $order['status']===$k?'selected':'' ?>><?= $v['label'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Note</label>
                        <textarea name="note" class="form-control" rows="2" style="border-radius:10px;" placeholder="Customer ke liye..."></textarea>
                    </div>
                    <button type="submit" class="btn w-100" style="background:#8B4513;color:white;border-radius:10px;">
                        <i class="fas fa-save me-1"></i>Update + Email Bhejo
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
