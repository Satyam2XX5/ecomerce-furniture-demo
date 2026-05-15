<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request");
}

if (session_status() === PHP_SESSION_NONE) session_start();

$product_id = isset($_POST['product_id']) && $_POST['product_id'] !== '' 
    ? (int)$_POST['product_id'] : null;

$name    = trim($_POST['name'] ?? '');
$phone   = trim($_POST['phone'] ?? '');
$email   = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

if (empty($name) || empty($phone))  die("Name aur phone zaroori hain");
if (!preg_match('/^[0-9+\-\s]{7,15}$/', $phone)) die("Invalid phone number");
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) die("Invalid email format");

$stmt = $conn->prepare("INSERT INTO inquiries (product_id, name, phone, email, message, status) VALUES (?, ?, ?, ?, ?, 'new')");
if (!$stmt) die("Prepare failed: " . $conn->error);
$stmt->bind_param("issss", $product_id, $name, $phone, $email, $message);

if ($stmt->execute()) {
    
    // Settings
    $admin_email = 'Santoshfurniture@gmail.com';
    $store_name  = 'Santosh Furniture';
    $res = db_query($conn, "SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('admin_email','store_name','email_notifications')");
    $settings = [];
    while ($row = mysqli_fetch_assoc($res)) $settings[$row['setting_key']] = $row['setting_value'];
    if (!empty($settings['admin_email'])) $admin_email = $settings['admin_email'];
    if (!empty($settings['store_name']))  $store_name  = $settings['store_name'];
    $email_notif = $settings['email_notifications'] ?? '1';

    $product_name = 'General Inquiry';
    if ($product_id) {
        $p = mysqli_fetch_assoc(db_query($conn, "SELECT name FROM products WHERE id=$product_id"));
        if ($p) $product_name = $p['name'];
    }

    $inquiry_id = $conn->insert_id;
    $date = date('d M Y, h:i A');
    $wa_phone = preg_replace('/[^0-9]/', '', $phone);

    // ── Admin email
    if ($email_notif === '1') {
        $subject = "Naya Inquiry #$inquiry_id - $store_name";
        $body = "<html><body style='font-family:Arial;max-width:580px;margin:0 auto;'>
        <div style='background:#8B4513;padding:22px;text-align:center;'><h2 style='color:white;margin:0;'>🪑 $store_name — Naya Inquiry!</h2></div>
        <div style='padding:28px;background:#fff;'>
        <p style='background:#FFF3CD;padding:12px;border-radius:8px;'><strong>⚡ Inquiry #$inquiry_id — $date</strong></p>
        <table style='width:100%;'>
        <tr><td style='padding:8px;color:#888;'>Naam:</td><td style='padding:8px;font-weight:bold;'>$name</td></tr>
        <tr><td style='padding:8px;color:#888;'>Phone:</td><td style='padding:8px;'><a href='tel:$phone' style='color:#8B4513;font-weight:bold;'>$phone</a></td></tr>
        " . (!empty($email) ? "<tr><td style='padding:8px;color:#888;'>Email:</td><td style='padding:8px;'>$email</td></tr>" : "") . "
        <tr><td style='padding:8px;color:#888;'>Product:</td><td style='padding:8px;color:#8B4513;font-weight:bold;'>$product_name</td></tr>
        " . (!empty($message) ? "<tr><td style='padding:8px;color:#888;vertical-align:top;'>Message:</td><td style='padding:8px;'>".nl2br(htmlspecialchars($message))."</td></tr>" : "") . "
        </table>
        <div style='margin-top:22px;'>
        <a href='https://wa.me/91$wa_phone' style='background:#25D366;color:white;padding:11px 20px;text-decoration:none;border-radius:8px;margin-right:10px;display:inline-block;'>💬 WhatsApp</a>
        <a href='http://".$_SERVER['HTTP_HOST']."/furniture-website/admin/inquiries.php' style='background:#8B4513;color:white;padding:11px 20px;text-decoration:none;border-radius:8px;display:inline-block;'>Admin Panel</a>
        </div>
        </div></body></html>";
        $headers = "MIME-Version: 1.0\r\nContent-type: text/html; charset=UTF-8\r\nFrom: $store_name Website <no-reply@santoshfurniture.com>\r\n";
        @mail($admin_email, $subject, $body, $headers);
    }

    // ── Customer email
    if (!empty($email)) {
        $csubject = "Inquiry Confirm | $store_name";
        $cbody = "<html><body style='font-family:Arial;max-width:580px;margin:0 auto;'>
        <div style='background:#8B4513;padding:25px;text-align:center;'><h1 style='color:white;margin:0;'>🪑 $store_name</h1></div>
        <div style='padding:28px;background:#fff;'>
        <h3 style='color:#8B4513;'>Namaskar $name ji! 🙏</h3>
        <p>Aapka inquiry (#$inquiry_id) hamare paas pahunch gaya. Hum 24 ghante mein contact karenge!</p>
        <div style='background:#FFF8F0;border:1px solid #f0e6da;padding:18px;border-radius:10px;margin:18px 0;'>
        <strong>Product:</strong> $product_name<br><strong>Date:</strong> $date</div>
        <a href='tel:+918210187952' style='background:#8B4513;color:white;padding:12px 25px;text-decoration:none;border-radius:8px;display:inline-block;'>📞 +91 82101 87952</a>
        <p style='margin-top:20px;color:#999;font-size:12px;'>$store_name, Gopalganj, Bihar</p>
        </div></body></html>";
        $cheaders = "MIME-Version: 1.0\r\nContent-type: text/html; charset=UTF-8\r\nFrom: $store_name <$admin_email>\r\n";
        @mail($email, $csubject, $cbody, $cheaders);
    }

    $redirect = $product_id ? "product-detail.php?id=$product_id" : "index.php";
    $email_msg = !empty($email) ? "\\nConfirmation email bhi bhej diya!" : "";
    echo "<script>alert('Aapka inquiry submit ho gaya!$email_msg\\n\\nHum jald contact karenge.'); window.location.href='$redirect';</script>";

} else {
    die("Execute failed: " . $stmt->error);
}
$stmt->close();
$conn->close();
?>
