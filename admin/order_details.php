<?php
require_once '../config.php';
requireAdmin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: orders.php');
    exit();
}

$order_id = (int)$_GET['id'];
$conn = getConnection();

// Get order details with customer info
$order_query = "SELECT o.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                WHERE o.id = ?";
$order_stmt = mysqli_prepare($conn, $order_query);
mysqli_stmt_bind_param($order_stmt, 'i', $order_id);
mysqli_stmt_execute($order_stmt);
$order_result = mysqli_stmt_get_result($order_stmt);

if (!$order = mysqli_fetch_assoc($order_result)) {
    header('Location: orders.php');
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
    <title>Order Details - ShopFlow Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <h1><a href="../index.php" style="color: white; text-decoration: none;">ShopFlow</a></h1>
            </div>
            <nav>
                <ul class="nav-menu">
                    <li><a href="../index.php">Store</a></li>
                    <li><a href="orders.php">← Back to Orders</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="admin-container">
        <div class="admin-header">
            <div class="admin-header-content">
                <h1 class="admin-title">Order Details</h1>
                <p class="admin-subtitle">Complete order information and management</p>
            </div>
        </div>

        <div class="container">
            <!-- Admin Navigation -->
            <nav class="admin-nav">
                <ul class="admin-nav-list">
                    <li class="admin-nav-item"><a href="dashboard.php">📊 Dashboard</a></li>
                    <li class="admin-nav-item"><a href="products.php">📦 Products</a></li>
                    <li class="admin-nav-item"><a href="orders.php" class="active">🛒 Orders</a></li>
                    <li class="admin-nav-item"><a href="users.php">👥 Users</a></li>
                    <li class="admin-nav-item"><a href="messages.php">💬 Messages</a></li>
                </ul>
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
                    <!-- Customer Information -->
                    <div style="background: var(--very-light-blue); padding: 2rem; border-radius: 15px; margin-bottom: 3rem;">
                        <h3 style="color: var(--primary-dark); margin-bottom: 2rem; font-size: 1.8rem;">Customer Information</h3>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
                            <div>
                                <h4 style="color: var(--text-dark); margin-bottom: 0.8rem;">Name:</h4>
                                <p style="color: var(--text-dark); font-size: 1.1rem; font-weight: 600;">
                                    <?php echo htmlspecialchars($order['customer_name']); ?>
                                </p>
                            </div>
                            <div>
                                <h4 style="color: var(--text-dark); margin-bottom: 0.8rem;">Email:</h4>
                                <p style="color: var(--text-dark); font-size: 1.1rem;">
                                    <a href="mailto:<?php echo htmlspecialchars($order['customer_email']); ?>" 
                                       style="color: var(--secondary-blue); text-decoration: none;">
                                        <?php echo htmlspecialchars($order['customer_email']); ?>
                                    </a>
                                </p>
                            </div>
                            <?php if (!empty($order['customer_phone'])): ?>
                            <div>
                                <h4 style="color: var(--text-dark); margin-bottom: 0.8rem;">Phone:</h4>
                                <p style="color: var(--text-dark); font-size: 1.1rem;">
                                    <a href="tel:<?php echo htmlspecialchars($order['customer_phone']); ?>" 
                                       style="color: var(--secondary-blue); text-decoration: none;">
                                        <?php echo htmlspecialchars($order['customer_phone']); ?>
                                    </a>
                                </p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Shipping Information -->
                    <div style="background: var(--pale-blue); padding: 2rem; border-radius: 15px; margin-bottom: 3rem;">
                        <h3 style="color: var(--primary-dark); margin-bottom: 1.5rem; font-size: 1.5rem;">Shipping Address</h3>
                        <div style="color: var(--text-dark); line-height: 1.7; font-size: 1.1rem; background: white; padding: 1.5rem; border-radius: 10px;">
                            <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                        </div>
                    </div>

                    <!-- Order Items -->
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
                                            <strong>SKU/ID:</strong><br>
                                            <span style="color: var(--text-light); font-family: monospace;">#<?php echo $item['product_id']; ?></span>
                                        </div>
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
                                    <a href="../product_details.php?id=<?php echo $item['product_id']; ?>" 
                                       class="btn btn-primary" style="margin-bottom: 1rem;">View Product</a>
                                    <br>
                                    <a href="edit_product.php?id=<?php echo $item['product_id']; ?>" 
                                       class="btn" style="background: var(--warning); color: white; font-size: 0.9rem;">Edit Product</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <!-- Order Total -->
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

                    <!-- Order Actions -->
                    <div style="background: var(--lightest-blue); padding: 2rem; border-radius: 15px; margin-top: 3rem;">
                        <h3 style="color: var(--primary-dark); margin-bottom: 2rem; text-align: center;">Order Management</h3>
                        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                            <?php if ($order['status'] == 'Pending'): ?>
                                <form method="POST" action="orders.php" style="display: inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <input type="hidden" name="status" value="Delivered">
                                    <input type="hidden" name="update_status" value="1">
                                    <button type="submit" class="btn btn-success" style="padding: 1rem 2rem;">
                                        ✅ Mark as Delivered
                                    </button>
                                </form>
                                <form method="POST" action="orders.php" style="display: inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <input type="hidden" name="status" value="Cancelled">
                                    <input type="hidden" name="update_status" value="1">
                                    <button type="submit" class="btn btn-danger" style="padding: 1rem 2rem;" 
                                            onclick="return confirm('Are you sure you want to cancel this order?')">
                                        ❌ Cancel Order
                                    </button>
                                </form>
                            <?php elseif ($order['status'] == 'Delivered'): ?>
                                <div style="text-align: center; color: var(--success); font-size: 1.2rem; font-weight: 600;">
                                    ✅ This order has been delivered successfully
                                </div>
                            <?php else: ?>
                                <div style="text-align: center; color: var(--error); font-size: 1.2rem; font-weight: 600;">
                                    ❌ This order has been cancelled
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div style="text-align: center; margin-top: 3rem; padding-top: 2rem; border-top: 1px solid var(--pale-blue);">
                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <a href="orders.php" class="btn" style="padding: 1rem 2rem;">← Back to All Orders</a>
                    <a href="mailto:<?php echo htmlspecialchars($order['customer_email']); ?>" 
                       class="btn btn-primary" style="padding: 1rem 2rem;">📧 Email Customer</a>
                    <a href="dashboard.php" class="btn" style="padding: 1rem 2rem; background: var(--secondary-blue); color: white;">
                        📊 Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; 2025 ShopFlow Admin Panel. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>