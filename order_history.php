<?php
require_once 'config.php';
requireLogin();

$conn = getConnection();

// Get user orders
$orders_query = "SELECT o.*, COUNT(oi.id) as item_count 
                 FROM orders o 
                 LEFT JOIN order_items oi ON o.id = oi.order_id 
                 WHERE o.user_id = ? 
                 GROUP BY o.id 
                 ORDER BY o.created_at DESC";
$orders_stmt = mysqli_prepare($conn, $orders_query);
mysqli_stmt_bind_param($orders_stmt, 'i', $_SESSION['user_id']);
mysqli_stmt_execute($orders_stmt);
$orders_result = mysqli_stmt_get_result($orders_stmt);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - ShopFlow</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
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
                    <?php if (isAdmin()): ?>
                        <li><a href="admin/dashboard.php">Admin</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container" style="padding-top: 2rem;">
        <div style="text-align: center; margin-bottom: 3rem;">
            <h1 style="font-size: 3rem; color: var(--primary-dark); margin-bottom: 1rem;">Order History</h1>
            <p style="font-size: 1.2rem; color: var(--text-light);">Track all your purchases and order status</p>
        </div>

        <?php if (mysqli_num_rows($orders_result) > 0): ?>
            <div class="orders-container">
                <?php while ($order = mysqli_fetch_assoc($orders_result)): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div class="order-info">
                                <h3>Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></h3>
                                <p style="margin-bottom: 0.5rem;">
                                    <strong>Date:</strong> <?php echo date('M j, Y \a\t g:i A', strtotime($order['created_at'])); ?>
                                </p>
                                <p>
                                    <strong>Items:</strong> <?php echo $order['item_count']; ?> item<?php echo $order['item_count'] != 1 ? 's' : ''; ?>
                                </p>
                            </div>
                            <div>
                                <div class="order-status status-<?php echo strtolower($order['status']); ?>" style="margin-bottom: 1rem;">
                                    <?php echo $order['status']; ?>
                                </div>
                                <div style="font-size: 1.4rem; font-weight: 700; color: var(--primary-blue);">
                                    <?php echo formatPrice($order['total_amount']); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="order-body">
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
                                <div>
                                    <h4 style="color: var(--text-dark); margin-bottom: 0.5rem;">Payment Method:</h4>
                                    <p style="color: var(--text-light);"><?php echo htmlspecialchars($order['payment_method']); ?></p>
                                </div>
                                <div>
                                    <h4 style="color: var(--text-dark); margin-bottom: 0.5rem;">Shipping Address:</h4>
                                    <p style="color: var(--text-light); line-height: 1.5;">
                                        <?php echo nl2br(htmlspecialchars(substr($order['shipping_address'], 0, 100))); ?>
                                        <?php echo strlen($order['shipping_address']) > 100 ? '...' : ''; ?>
                                    </p>
                                </div>
                            </div>
                            
                            <div style="display: flex; gap: 1rem; justify-content: flex-end; flex-wrap: wrap;">
                                <a href="order_details.php?id=<?php echo $order['id']; ?>" 
                                   class="btn btn-primary">View Details</a>
                                <?php if ($order['status'] == 'Delivered'): ?>
                                    <a href="index.php" class="btn" style="background: var(--success); color: white;">
                                        Reorder Items
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 4rem; background: var(--white); border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div style="font-size: 6rem; color: var(--pale-blue); margin-bottom: 2rem;">📦</div>
                <h2 style="color: var(--text-light); font-size: 2.5rem; margin-bottom: 1rem;">No Orders Yet</h2>
                <p style="color: var(--text-light); font-size: 1.2rem; margin-bottom: 2rem; max-width: 500px; margin-left: auto; margin-right: auto;">
                    You haven't placed any orders yet. Start shopping to see your purchase history here.
                </p>
                <a href="index.php" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.2rem;">
                    Start Shopping
                </a>
            </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 3rem; padding-top: 2rem; border-top: 1px solid var(--pale-blue);">
            <a href="profile.php" class="btn" style="margin-right: 1rem;">Back to Profile</a>
            <a href="index.php" class="btn btn-primary">Continue Shopping</a>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 ShopFlow. All rights reserved. | Premium E-commerce Experience</p>
        </div>
    </footer>
</body>
</html>