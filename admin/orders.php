<?php
require_once '../config.php';
requireAdmin();

$conn = getConnection();
$message = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['status'];
    
    $update_query = "UPDATE orders SET status = ? WHERE id = ?";
    $update_stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($update_stmt, 'si', $new_status, $order_id);
    
    if (mysqli_stmt_execute($update_stmt)) {
        $message = '<div class="alert alert-success">Order status updated successfully!</div>';
    } else {
        $message = '<div class="alert alert-error">Error updating order status.</div>';
    }
}

// Get all orders with customer info
$orders_query = "SELECT o.*, u.name as customer_name, u.email as customer_email,
                 COUNT(oi.id) as item_count
                 FROM orders o
                 JOIN users u ON o.user_id = u.id
                 LEFT JOIN order_items oi ON o.id = oi.order_id
                 GROUP BY o.id
                 ORDER BY o.created_at DESC";
$orders_result = mysqli_query($conn, $orders_query);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management - ShopFlow Admin</title>
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
                    <li><a href="../profile.php">Profile</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="admin-container">
        <div class="admin-header">
            <div class="admin-header-content">
                <h1 class="admin-title">Orders Management</h1>
                <p class="admin-subtitle">Process and manage customer orders</p>
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

            <?php echo $message; ?>

            <!-- Orders List -->
            <?php if (mysqli_num_rows($orders_result) > 0): ?>
                <div class="orders-container">
                    <?php while ($order = mysqli_fetch_assoc($orders_result)): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div class="order-info">
                                    <h3>Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></h3>
                                    <p style="margin-bottom: 0.5rem;">
                                        <strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?>
                                    </p>
                                    <p style="margin-bottom: 0.5rem;">
                                        <strong>Email:</strong> <?php echo htmlspecialchars($order['customer_email']); ?>
                                    </p>
                                    <p style="margin-bottom: 0.5rem;">
                                        <strong>Date:</strong> <?php echo date('M j, Y \a\t g:i A', strtotime($order['created_at'])); ?>
                                    </p>
                                    <p>
                                        <strong>Items:</strong> <?php echo $order['item_count']; ?> item<?php echo $order['item_count'] != 1 ? 's' : ''; ?>
                                    </p>
                                </div>
                                <div>
                                    <div style="font-size: 1.4rem; font-weight: 700; color: var(--primary-blue); margin-bottom: 1rem;">
                                        <?php echo formatPrice($order['total_amount']); ?>
                                    </div>
                                    <form method="POST" style="display: inline-block;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="status" class="form-control" style="margin-bottom: 1rem; min-width: 150px;" onchange="this.form.submit()">
                                            <option value="Pending" <?php echo $order['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="Delivered" <?php echo $order['status'] == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                            <option value="Cancelled" <?php echo $order['status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                    <div class="order-status status-<?php echo strtolower($order['status']); ?>">
                                        <?php echo $order['status']; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="order-body">
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
                                    <div>
                                        <h4 style="color: var(--text-dark); margin-bottom: 0.5rem;">Payment Method:</h4>
                                        <p style="color: var(--text-light);"><?php echo htmlspecialchars($order['payment_method']); ?></p>
                                    </div>
                                    <div>
                                        <h4 style="color: var(--text-dark); margin-bottom: 0.5rem;">Shipping Address:</h4>
                                        <p style="color: var(--text-light); line-height: 1.5;">
                                            <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                                        </p>
                                    </div>
                                </div>
                                
                                <div style="display: flex; gap: 1rem; justify-content: flex-end; flex-wrap: wrap;">
                                    <a href="order_details.php?id=<?php echo $order['id']; ?>" 
                                       class="btn btn-primary">View Full Details</a>
                                    <?php if ($order['status'] == 'Pending'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <input type="hidden" name="status" value="Delivered">
                                            <input type="hidden" name="update_status" value="1">
                                            <button type="submit" class="btn btn-success">Mark as Delivered</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 4rem; background: var(--white); border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                    <div style="font-size: 6rem; color: var(--pale-blue); margin-bottom: 2rem;">🛒</div>
                    <h2 style="color: var(--text-light); font-size: 2.5rem; margin-bottom: 1rem;">No Orders Yet</h2>
                    <p style="color: var(--text-light); font-size: 1.2rem; margin-bottom: 2rem;">
                        Orders will appear here once customers start making purchases.
                    </p>
                    <a href="products.php" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.2rem;">
                        Manage Products
                    </a>
                </div>
            <?php endif; ?>

            <!-- Order Statistics -->
            <div style="margin-top: 3rem;">
                <h2 style="text-align: center; margin-bottom: 2rem; color: var(--primary-dark); font-size: 2.2rem;">Order Statistics</h2>
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo mysqli_num_rows($orders_result); ?></div>
                        <div class="stat-label">Total Orders</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-number">
                            <?php 
                            mysqli_data_seek($orders_result, 0);
                            $pending = 0;
                            while ($row = mysqli_fetch_assoc($orders_result)) {
                                if ($row['status'] == 'Pending') $pending++;
                            }
                            echo $pending;
                            ?>
                        </div>
                        <div class="stat-label">Pending Orders</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-number">
                            <?php 
                            mysqli_data_seek($orders_result, 0);
                            $delivered = 0;
                            while ($row = mysqli_fetch_assoc($orders_result)) {
                                if ($row['status'] == 'Delivered') $delivered++;
                            }
                            echo $delivered;
                            ?>
                        </div>
                        <div class="stat-label">Delivered Orders</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-number">
                            <?php 
                            mysqli_data_seek($orders_result, 0);
                            $total_revenue = 0;
                            while ($row = mysqli_fetch_assoc($orders_result)) {
                                if ($row['status'] != 'Cancelled') {
                                    $total_revenue += $row['total_amount'];
                                }
                            }
                            echo formatPrice($total_revenue);
                            ?>
                        </div>
                        <div class="stat-label">Total Revenue</div>
                    </div>
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