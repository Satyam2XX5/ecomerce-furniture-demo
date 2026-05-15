<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header("Location: products.php?error=Invalid request");
    exit();
}

// Get product
$result  = db_query($conn, "SELECT * FROM products WHERE id = $id");
$product = mysqli_fetch_assoc($result);

if (!$product) {
    header("Location: products.php?error=Product nahi mila");
    exit();
}

// Delete images from folder
$images = mysqli_fetch_all(
    db_query($conn, "SELECT * FROM product_images WHERE product_id = $id"),
    MYSQLI_ASSOC
);

foreach ($images as $img) {
    $file = '../uploads/products/' . $img['image_path'];
    if (file_exists($file)) unlink($file);
}

// Delete from DB (CASCADE will handle product_images)
db_query($conn, "DELETE FROM products WHERE id = $id");

header("Location: products.php?success=" . urlencode($product['name'] . " delete ho gaya!"));
exit();
?>