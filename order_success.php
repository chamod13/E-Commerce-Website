<?php
require_once 'config.php';
requireLogin();

if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    header('Location: index.php');
    exit();
}

$order_id = (int)$_GET['order_id'];
$conn = getConnection();

// Get order details
$order_query = "SELECT o.*, u.name as customer_name, u.email as customer_email 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                WHERE o.id = ? AND o.user_id = ?";
$order_stmt = mysqli_prepare($conn, $order_query);
mysqli_stmt_bind_param($order_stmt, 'ii', $order_id, $_SESSION['user_id']);
mysqli_stmt_execute($order_stmt);
$order_result = mysqli_stmt_get_result($order_stmt);

if (!$order = mysqli_fetch_assoc($order_result)) {
    header('Location: index.php');
    exit();
}

// Get order items
$items_query = "SELECT oi.*, p.name as product_name, p.image_url 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = ?";
$items_stmt = mysqli_prepare($conn, $items_query);
mysqli_stmt_bind_param($items_stmt, 'i', $order_id);
mysqli_stmt_execute($items_stmt);
$items_result = mysqli_stmt_get_result($items_stmt);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed - ShopFlow</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/cart.css">
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <h1><a href="index.php" style="color: white; text-decoration: none;">ShopFlow</a></h1>
            </div>
            <nav>
                <ul class="nav-menu">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="order_history.php">Orders</a></li>
                    <li><a href="cart.php">Cart <span class="cart-badge"><?php echo getCartCount(); ?></span></a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="cart-container">
        <div class="cart-header" style="background: linear-gradient(135deg, var(--success) 0%, #27ae60 100%);">
            <div style="font-size: 4rem; margin-bottom: 1rem;">✅</div>
            <h1 class="cart-title">Order Confirmed!</h1>
            <p class="cart-subtitle">Thank you for your purchase</p>
        </div>

        <div class="container">
            <div style="max-width: 800px; margin: 0 auto;">
                <div class="card" style="margin-bottom: 2rem;">
                    <div class="card-header" style="background: linear-gradient(135deg, var(--success) 0%, #2ecc71 100%);">
                        <h2>Order Details</h2>
                    </div>
                    <div class="card-body">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
                            <div>
                                <h4 style="color: var(--text-dark); margin-bottom: 0.5rem;">Order Number:</h4>
                                <p style="font-size: 1.2rem; font-weight: 700; color: var(--primary-blue);">#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></p>
                            </div>
                            <div>
                                <h4 style="color: var(--text-dark); margin-bottom: 0.5rem;">Order Date:</h4>
                                <p style="font-weight: 600;"><?php echo date('M j, Y \a\t g:i A', strtotime($order['created_at'])); ?></p>
                            </div>
                            <div>
                                <h4 style="color: var(--text-dark); margin-bottom: 0.5rem;">Payment Method:</h4>
                                <p style="font-weight: 600;"><?php echo htmlspecialchars($order['payment_method']); ?></p>
                            </div>
                            <div>
                                <h4 style="color: var(--text-dark); margin-bottom: 0.5rem;">Total Amount:</h4>
                                <p style="font-size: 1.3rem; font-weight: 700; color: var(--success);"><?php echo formatPrice($order['total_amount']); ?></p>
                            </div>
                        </div>

                        <div style="background: var(--very-light-blue); padding: 2rem; border-radius: 12px; margin-bottom: 2rem;">
                            <h4 style="color: var(--text-dark); margin-bottom: 1rem;">Shipping Address:</h4>
                            <p style="color: var(--text-dark); line-height: 1.6;"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                        </div>

                        <h4 style="color: var(--text-dark); margin-bottom: 1.5rem;">Order Items:</h4>
                        <div class="order-items" style="background: #f8f9fa; padding: 1.5rem; border-radius: 12px;">
                            <?php while ($item = mysqli_fetch_assoc($items_result)): ?>
                                <div style="display: flex; align-items: center; gap: 1.5rem; padding: 1rem 0; border-bottom: 1px solid var(--pale-blue);">
                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                         style="width: 80px; height: 80px; object-fit: cover; border-radius: 10px;">
                                    <div style="flex: 1;">
                                        <h5 style="color: var(--text-dark); margin-bottom: 0.5rem;"><?php echo htmlspecialchars($item['product_name']); ?></h5>
                                        <p style="color: var(--text-light);">Quantity: <?php echo $item['quantity']; ?></p>
                                        <p style="color: var(--text-light);">Price: <?php echo formatPrice($item['price']); ?> each</p>
                                    </div>
                                    <div style="text-align: right;">
                                        <p style="font-size: 1.2rem; font-weight: 700; color: var(--primary-blue);">
                                            <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>

                <div style="background: linear-gradient(135deg, var(--light-blue) 0%, var(--lighter-blue) 100%); color: white; padding: 3rem; border-radius: 20px; text-align: center; margin-bottom: 2rem;">
                    <h3 style="font-size: 1.8rem; margin-bottom: 1.5rem;">What's Next?</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; text-align: left;">
                        <div>
                            <div style="font-size: 2rem; margin-bottom: 1rem;">📧</div>
                            <h4>Confirmation Email</h4>
                            <p style="opacity: 0.9; font-size: 0.95rem;">We'll send you an order confirmation email shortly.</p>
                        </div>
                        <div>
                            <div style="font-size: 2rem; margin-bottom: 1rem;">📦</div>
                            <h4>Processing</h4>
                            <p style="opacity: 0.9; font-size: 0.95rem;">Your order is being prepared for shipment.</p>
                        </div>
                        <div>
                            <div style="font-size: 2rem; margin-bottom: 1rem;">🚚</div>
                            <h4>Delivery</h4>
                            <p style="opacity: 0.9; font-size: 0.95rem;">We'll notify you when your order is on its way.</p>
                        </div>
                    </div>
                </div>

                <div style="text-align: center; margin-bottom: 3rem;">
                    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                        <a href="order_history.php" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.1rem;">
                            View All Orders
                        </a>
                        <a href="index.php" class="btn" style="padding: 1rem 2rem; font-size: 1.1rem; background: var(--secondary-blue); color: white;">
                            Continue Shopping
                        </a>
                    </div>
                </div>

                <div style="background: var(--very-light-blue); padding: 2rem; border-radius: 15px; text-align: center; border-left: 4px solid var(--accent-blue);">
                    <h4 style="color: var(--primary-dark); margin-bottom: 1rem;">Need Help?</h4>
                    <p style="color: var(--text-dark); margin-bottom: 1rem;">
                        If you have any questions about your order, please don't hesitate to contact us.
                    </p>
                    <a href="contact.php" class="btn" style="background: var(--accent-blue); color: white;">Contact Support</a>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; 2025 ShopFlow. All rights reserved. | Premium E-commerce Experience</p>
        </div>
    </footer>
</body>
</html>