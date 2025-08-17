<?php
require_once 'config.php';
requireLogin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: order_history.php');
    exit();
}

$order_id = (int)$_GET['id'];
$conn = getConnection();

// Get order details - ensure user can only see their own orders
$order_query = "SELECT o.* FROM orders o WHERE o.id = ? AND o.user_id = ?";
$order_stmt = mysqli_prepare($conn, $order_query);
mysqli_stmt_bind_param($order_stmt, 'ii', $order_id, $_SESSION['user_id']);
mysqli_stmt_execute($order_stmt);
$order_result = mysqli_stmt_get_result($order_stmt);

if (!$order = mysqli_fetch_assoc($order_result)) {
    header('Location: order_history.php');
    exit();
}

// Get order items
$items_query = "SELECT oi.*, p.name as product_name, p.description, p.image_url 
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
    <title>Order Details - ShopFlow</title>
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
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container" style="padding-top: 2rem;">
        <!-- Breadcrumb -->
        <nav style="margin-bottom: 2rem; padding: 1rem; background: var(--white); border-radius: 10px;">
            <a href="index.php" style="color: var(--secondary-blue); text-decoration: none;">Home</a> 
            <span style="color: var(--text-light);"> > </span>
            <a href="order_history.php" style="color: var(--secondary-blue); text-decoration: none;">Order History</a>
            <span style="color: var(--text-light);"> > </span>
            <span style="color: var(--text-dark);">Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></span>
        </nav>

        <div class="order-card">
            <div class="order-header">
                <div class="order-info">
                    <h1 style="font-size: 2.5rem; margin-bottom: 1rem;">Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></h1>
                    <p style="margin-bottom: 0.5rem; font-size: 1.1rem;">
                        <strong>Order Date:</strong> <?php echo date('M j, Y \a\t g:i A', strtotime($order['created_at'])); ?>
                    </p>
                    <p style="font-size: 1.1rem;">
                        <strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?>
                    </p>
                </div>
                <div>
                    <div class="order-status status-<?php echo strtolower($order['status']); ?>" style="margin-bottom: 1rem; font-size: 1.1rem; padding: 1rem 1.5rem;">
                        <?php echo $order['status']; ?>
                    </div>
                    <div style="font-size: 2rem; font-weight: 700; color: var(--primary-blue); text-align: center;">
                        <?php echo formatPrice($order['total_amount']); ?>
                    </div>
                </div>
            </div>
            
            <div class="order-body">
                <div style="background: var(--very-light-blue); padding: 2rem; border-radius: 15px; margin-bottom: 3rem;">
                    <h3 style="color: var(--primary-dark); margin-bottom: 1.5rem; font-size: 1.5rem;">Shipping Information</h3>
                    <div style="color: var(--text-dark); line-height: 1.7; font-size: 1.1rem;">
                        <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                    </div>
                </div>

                <h3 style="color: var(--primary-dark); margin-bottom: 2rem; font-size: 1.8rem;">Order Items</h3>
                <div class="order-items">
                    <?php while ($item = mysqli_fetch_assoc($items_result)): ?>
                        <div class="order-item" style="padding: 2rem 0; border-bottom: 2px solid var(--pale-blue);">
                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                 class="order-item-image" style="width: 120px; height: 120px; border-radius: 15px; border: 3px solid var(--pale-blue);">
                            
                            <div class="order-item-details" style="flex: 1;">
                                <h4 style="font-size: 1.4rem; color: var(--primary-dark); margin-bottom: 0.8rem;">
                                    <?php echo htmlspecialchars($item['product_name']); ?>
                                </h4>
                                <p style="color: var(--text-light); margin-bottom: 1rem; line-height: 1.6;">
                                    <?php echo htmlspecialchars(substr($item['description'], 0, 150)); ?>...
                                </p>
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 1rem; font-size: 1rem;">
                                    <div>
                                        <strong>Quantity:</strong><br>
                                        <span style="color: var(--secondary-blue); font-weight: 600;"><?php echo $item['quantity']; ?></span>
                                    </div>
                                    <div>
                                        <strong>Unit Price:</strong><br>
                                        <span style="color: var(--secondary-blue); font-weight: 600;"><?php echo formatPrice($item['price']); ?></span>
                                    </div>
                                    <div>
                                        <strong>Subtotal:</strong><br>
                                        <span style="color: var(--primary-blue); font-weight: 700; font-size: 1.2rem;">
                                            <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div style="text-align: center;">
                                <a href="product_details.php?id=<?php echo $item['product_id']; ?>" 
                                   class="btn btn-primary" style="margin-bottom: 1rem;">View Product</a>
                                <?php if ($order['status'] == 'Delivered'): ?>
                                    <br>
                                    <a href="product_details.php?id=<?php echo $item['product_id']; ?>#reviews" 
                                       class="btn" style="background: var(--warning); color: white; font-size: 0.9rem;">Write Review</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <div class="order-total" style="text-align: right; margin-top: 3rem; padding: 2rem; background: var(--very-light-blue); border-radius: 15px;">
                    <div style="display: grid; grid-template-columns: auto auto; gap: 1rem; justify-content: end; max-width: 300px; margin-left: auto;">
                        <div style="text-align: right; padding: 0.5rem 0; font-size: 1.1rem;">
                            <strong>Subtotal:</strong>
                        </div>
                        <div style="text-align: right; padding: 0.5rem 0; font-size: 1.1rem; font-weight: 600;">
                            <?php echo formatPrice($order['total_amount']); ?>
                        </div>
                        
                        <div style="text-align: right; padding: 0.5rem 0; font-size: 1.1rem;">
                            <strong>Shipping:</strong>
                        </div>
                        <div style="text-align: right; padding: 0.5rem 0; font-size: 1.1rem; font-weight: 600; color: var(--success);">
                            FREE
                        </div>
                        
                        <div style="text-align: right; padding: 0.5rem 0; font-size: 1.1rem;">
                            <strong>Tax:</strong>
                        </div>
                        <div style="text-align: right; padding: 0.5rem 0; font-size: 1.1rem; font-weight: 600;">
                            Included
                        </div>
                        
                        <div style="text-align: right; padding: 1rem 0; border-top: 2px solid var(--pale-blue); font-size: 1.4rem;">
                            <strong>Total:</strong>
                        </div>
                        <div style="text-align: right; padding: 1rem 0; border-top: 2px solid var(--pale-blue); font-size: 1.8rem; font-weight: 700; color: var(--primary-blue);">
                            <?php echo formatPrice($order['total_amount']); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($order['status'] == 'Pending'): ?>
            <div style="background: linear-gradient(135deg, var(--warning) 0%, #f39c12 100%); color: white; padding: 2rem; border-radius: 15px; text-align: center; margin: 2rem 0;">
                <h3 style="margin-bottom: 1rem; font-size: 1.5rem;">Order Status: Processing</h3>
                <p style="margin-bottom: 1.5rem; opacity: 0.9;">Your order is currently being processed and will be shipped soon. We'll notify you once it's on its way!</p>
                <div style="display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(255,255,255,0.2); padding: 1rem 1.5rem; border-radius: 25px;">
                    <span>📦</span>
                    <span>Processing Order...</span>
                </div>
            </div>
        <?php elseif ($order['status'] == 'Delivered'): ?>
            <div style="background: linear-gradient(135deg, var(--success) 0%, #27ae60 100%); color: white; padding: 2rem; border-radius: 15px; text-align: center; margin: 2rem 0;">
                <h3 style="margin-bottom: 1rem; font-size: 1.5rem;">Order Delivered Successfully!</h3>
                <p style="margin-bottom: 1.5rem; opacity: 0.9;">Your order has been delivered. We hope you enjoy your purchase!</p>
                <div style="display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(255,255,255,0.2); padding: 1rem 1.5rem; border-radius: 25px;">
                    <span>✅</span>
                    <span>Delivery Complete</span>
                </div>
            </div>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 3rem; padding-top: 2rem; border-top: 1px solid var(--pale-blue);">
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="order_history.php" class="btn" style="padding: 1rem 2rem;">← Back to Orders</a>
                <?php if ($order['status'] == 'Delivered'): ?>
                    <a href="index.php" class="btn btn-success" style="padding: 1rem 2rem;">Reorder Items</a>
                <?php endif; ?>
                <a href="contact.php" class="btn btn-primary" style="padding: 1rem 2rem;">Contact Support</a>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 ShopFlow. All rights reserved. | Premium E-commerce Experience</p>
        </div>
    </footer>
</body>
</html>