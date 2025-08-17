<?php
require_once 'config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = (int)$_POST['product_id'];
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);
    $user_id = $_SESSION['user_id'];
    
    if ($product_id && $rating >= 1 && $rating <= 5) {
        $conn = getConnection();
        
        // Check if user already reviewed this product
        $check_query = "SELECT id FROM reviews WHERE product_id = ? AND user_id = ?";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, 'ii', $product_id, $user_id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            // Update existing review
            $update_query = "UPDATE reviews SET rating = ?, comment = ? WHERE product_id = ? AND user_id = ?";
            $update_stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($update_stmt, 'isii', $rating, $comment, $product_id, $user_id);
            mysqli_stmt_execute($update_stmt);
        } else {
            // Insert new review
            $insert_query = "INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)";
            $insert_stmt = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($insert_stmt, 'iiis', $product_id, $user_id, $rating, $comment);
            mysqli_stmt_execute($insert_stmt);
        }
        
        mysqli_close($conn);
    }
}

// Redirect back to product page
header("Location: product_details.php?id=" . $product_id);
exit();
?>