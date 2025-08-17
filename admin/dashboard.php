<?php
require_once '../config.php';
requireAdmin();

$conn = getConnection();

// Get statistics
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM products) as total_products,
    (SELECT COUNT(*) FROM orders) as total_orders,
    (SELECT COUNT(*) FROM users WHERE is_admin = 0) as total_users,
    (SELECT COALESCE(SUM(total_amount), 0) FROM orders) as total_revenue";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Get recent orders
$recent_orders_query = "SELECT o.*, u.name as customer_name 
                        FROM orders o 
                        JOIN users u ON o.user_id = u.id 
                        ORDER BY o.created_at DESC 
                        LIMIT 10";
$recent_orders_result = mysqli_query($conn, $recent_orders_query);

// Get low stock products
$low_stock_query = "SELECT * FROM products WHERE stock_quantity < 10 ORDER BY stock_quantity ASC LIMIT 10";
$low_stock_result = mysqli_query($conn, $low_stock_query);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ShopFlow</title>
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
                <h1 class="admin-title">Admin Dashboard</h1>
                <p class="admin-subtitle">Manage your e-commerce store with powerful tools</p>
            </div>
        </div>

        <div class="container">
            <!-- Admin Navigation -->
            <nav class="admin-nav">
                <ul class="admin-nav-list">
                    <li class="admin-nav-item"><a href="dashboard.php" class="active">📊 Dashboard</a></li>
                    <li class="admin-nav-item"><a href="products.php">📦 Products</a></li>
                    <li class="admin-nav-item"><a href="orders.php">🛒 Orders</a></li>
                    <li class="admin-nav-item"><a href="users.php">👥 Users</a></li>
                    <li class="admin-nav-item"><a href="messages.php">💬 Messages</a></li>
                </ul>
            </nav>

            <!-- Statistics Cards -->
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon products">📦</div>
                    <div class="stat-number"><?php echo number_format($stats['total_products']); ?></div>
                    <div class="stat-label">Total Products</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon orders">🛒</div>
                    <div class="stat-number"><?php echo number_format($stats['total_orders']); ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon users">👥</div>
                    <div class="stat-number"><?php echo number_format($stats['total_users']); ?></div>
                    <div class="stat-label">Customers</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon revenue">💰</div>
                    <div class="stat-number"><?php echo formatPrice($stats['total_revenue']); ?></div>
                    <div class="stat-label">Total Revenue</div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <!-- Recent Orders -->
                <div class="products-table-container">
                    <div class="table-header">
                        <h2>Recent Orders</h2>
                    </div>
                    <div style="max-height: 400px; overflow-y: auto;">
                        <table class="products-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($recent_orders_result) > 0): ?>
                                    <?php while ($order = mysqli_fetch_assoc($recent_orders_result)): ?>
                                        <tr>
                                            <td><strong>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                            <td><?php echo formatPrice($order['total_amount']); ?></td>
                                            <td>
                                                <span class="stock-status <?php echo $order['status'] == 'Pending' ? 'stock-low' : 'stock-in'; ?>">
                                                    <?php echo $order['status']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center; color: var(--text-light); padding: 2rem;">
                                            No orders yet
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div style="padding: 1rem; text-align: center; background: var(--very-light-blue);">
                        <a href="orders.php" class="btn btn-primary">View All Orders</a>
                    </div>
                </div>

                <!-- Low Stock Alert -->
                <div class="products-table-container">
                    <div class="table-header" style="background: linear-gradient(135deg, var(--warning) 0%, #f39c12 100%);">
                        <h2>⚠️ Low Stock Alert</h2>
                    </div>
                    <div style="max-height: 400px; overflow-y: auto;">
                        <table class="products-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Stock</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($low_stock_result) > 0): ?>
                                    <?php while ($product = mysqli_fetch_assoc($low_stock_result)): ?>
                                        <tr>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 1rem;">
                                                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                         class="product-image-admin">
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="stock-status <?php echo $product['stock_quantity'] == 0 ? 'stock-out' : 'stock-low'; ?>">
                                                    <?php echo $product['stock_quantity']; ?> left
                                                </span>
                                            </td>
                                            <td>
                                                <a href="edit_product.php?id=<?php echo $product['id']; ?>" 
                                                   class="action-btn action-btn-edit">Restock</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" style="text-align: center; color: var(--success); padding: 2rem;">
                                            ✅ All products are well stocked!
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div style="padding: 1rem; text-align: center; background: var(--very-light-blue);">
                        <a href="products.php" class="btn" style="background: var(--warning); color: white;">Manage Inventory</a>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div style="margin-top: 3rem;">
                <h2 style="text-align: center; margin-bottom: 2rem; color: var(--primary-dark); font-size: 2.2rem;">Quick Actions</h2>
                <div class="dashboard-stats">
                    <div class="stat-card" style="text-align: center; cursor: pointer;" onclick="location.href='add_product.php'">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">➕</div>
                        <h3 style="color: var(--primary-dark); margin-bottom: 1rem;">Add Product</h3>
                        <p style="color: var(--text-light);">Add new products to your store</p>
                    </div>
                    
                    <div class="stat-card" style="text-align: center; cursor: pointer;" onclick="location.href='orders.php'">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">📋</div>
                        <h3 style="color: var(--primary-dark); margin-bottom: 1rem;">Manage Orders</h3>
                        <p style="color: var(--text-light);">Process and update order status</p>
                    </div>
                    
                    <div class="stat-card" style="text-align: center; cursor: pointer;" onclick="location.href='users.php'">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">👨‍💼</div>
                        <h3 style="color: var(--primary-dark); margin-bottom: 1rem;">View Customers</h3>
                        <p style="color: var(--text-light);">Manage customer accounts</p>
                    </div>
                    
                    <div class="stat-card" style="text-align: center; cursor: pointer;" onclick="location.href='messages.php'">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">📧</div>
                        <h3 style="color: var(--primary-dark); margin-bottom: 1rem;">Customer Messages</h3>
                        <p style="color: var(--text-light);">Respond to customer inquiries</p>
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