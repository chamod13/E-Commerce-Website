<?php
require_once '../config.php';
requireAdmin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: products.php');
    exit();
}

$product_id = (int)$_GET['id'];
$conn = getConnection();

// Check if product exists and get its name
$product_query = "SELECT name FROM products WHERE id = ?";
$product_stmt = mysqli_prepare($conn, $product_query);
mysqli_stmt_bind_param($product_stmt, 'i', $product_id);
mysqli_stmt_execute($product_stmt);
$product_result = mysqli_stmt_get_result($product_stmt);

if (!$product = mysqli_fetch_assoc($product_result)) {
    header('Location: products.php');
    exit();
}

// Check if product has any orders
$orders_check = "SELECT COUNT(*) as order_count FROM order_items WHERE product_id = ?";
$orders_stmt = mysqli_prepare($conn, $orders_check);
mysqli_stmt_bind_param($orders_stmt, 'i', $product_id);
mysqli_stmt_execute($orders_stmt);
$orders_result = mysqli_stmt_get_result($orders_stmt);
$orders_data = mysqli_fetch_assoc($orders_result);

if ($orders_data['order_count'] > 0) {
    // Product has orders, cannot delete
    $_SESSION['admin_message'] = '<div class="alert alert-error">Cannot delete product "' . htmlspecialchars($product['name']) . '" because it has existing orders. You can set its stock to 0 instead.</div>';
    header('Location: products.php');
    exit();
}

// Delete the product
$delete_query = "DELETE FROM products WHERE id = ?";
$delete_stmt = mysqli_prepare($conn, $delete_query);
mysqli_stmt_bind_param($delete_stmt, 'i', $product_id);

if (mysqli_stmt_execute($delete_stmt)) {
    $_SESSION['admin_message'] = '<div class="alert alert-success">Product "' . htmlspecialchars($product['name']) . '" has been deleted successfully.</div>';
} else {
    $_SESSION['admin_message'] = '<div class="alert alert-error">Error deleting product. Please try again.</div>';
}

mysqli_close($conn);
header('Location: products.php');
exit();
?>