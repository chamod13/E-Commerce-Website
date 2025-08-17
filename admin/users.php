<?php
require_once '../config.php';
requireAdmin();

$conn = getConnection();

// Get all users with their order statistics
$users_query = "SELECT u.*, 
                COUNT(DISTINCT o.id) as total_orders,
                COALESCE(SUM(o.total_amount), 0) as total_spent,
                MAX(o.created_at) as last_order_date
                FROM users u
                LEFT JOIN orders o ON u.id = o.user_id
                WHERE u.is_admin = 0
                GROUP BY u.id
                ORDER BY u.created_at DESC";
$users_result = mysqli_query($conn, $users_query);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management - ShopFlow Admin</title>
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
                <h1 class="admin-title">Users Management</h1>
                <p class="admin-subtitle">View and manage customer accounts</p>
            </div>
        </div>

        <div class="container">
            <!-- Admin Navigation -->
            <nav class="admin-nav">
                <ul class="admin-nav-list">
                    <li class="admin-nav-item"><a href="dashboard.php">📊 Dashboard</a></li>
                    <li class="admin-nav-item"><a href="products.php">📦 Products</a></li>
                    <li class="admin-nav-item"><a href="orders.php">🛒 Orders</a></li>
                    <li class="admin-nav-item"><a href="users.php" class="active">👥 Users</a></li>
                    <li class="admin-nav-item"><a href="messages.php">💬 Messages</a></li>
                </ul>
            </nav>

            <!-- Users Table -->
            <div class="products-table-container">
                <div class="table-header">
                    <h2>Customer Accounts (<?php echo mysqli_num_rows($users_result); ?>)</h2>
                </div>
                
                <?php if (mysqli_num_rows($users_result) > 0): ?>
                    <div style="overflow-x: auto;">
                        <table class="products-table">
                            <thead>
                                <tr>
                                    <th>Customer Info</th>
                                    <th>Contact Details</th>
                                    <th>Registration</th>
                                    <th>Orders</th>
                                    <th>Total Spent</th>
                                    <th>Last Order</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                                    <tr>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 1rem;">
                                                <div style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, var(--secondary-blue) 0%, var(--accent-blue) 100%); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; font-weight: 700;">
                                                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <h4 style="color: var(--primary-dark); margin-bottom: 0.3rem;">
                                                        <?php echo htmlspecialchars($user['name']); ?>
                                                    </h4>
                                                    <small style="color: var(--text-light);">
                                                        ID: <?php echo $user['id']; ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="min-width: 200px;">
                                                <p style="margin-bottom: 0.5rem;">
                                                    <strong>Email:</strong><br>
                                                    <a href="mailto:<?php echo htmlspecialchars($user['email']); ?>" 
                                                       style="color: var(--secondary-blue); text-decoration: none; font-size: 0.9rem;">
                                                        <?php echo htmlspecialchars($user['email']); ?>
                                                    </a>
                                                </p>
                                                <?php if (!empty($user['phone'])): ?>
                                                    <p style="margin-bottom: 0.5rem;">
                                                        <strong>Phone:</strong><br>
                                                        <a href="tel:<?php echo htmlspecialchars($user['phone']); ?>" 
                                                           style="color: var(--secondary-blue); text-decoration: none; font-size: 0.9rem;">
                                                            <?php echo htmlspecialchars($user['phone']); ?>
                                                        </a>
                                                    </p>
                                                <?php endif; ?>
                                                <?php if (!empty($user['address'])): ?>
                                                    <p>
                                                        <strong>Address:</strong><br>
                                                        <span style="color: var(--text-light); font-size: 0.85rem; line-height: 1.4;">
                                                            <?php echo htmlspecialchars(substr($user['address'], 0, 60)); ?>
                                                            <?php echo strlen($user['address']) > 60 ? '...' : ''; ?>
                                                        </span>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="text-align: center;">
                                                <div style="font-weight: 600; color: var(--primary-dark); margin-bottom: 0.3rem;">
                                                    <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                                                </div>
                                                <div style="font-size: 0.8rem; color: var(--text-light);">
                                                    <?php echo date('g:i A', strtotime($user['created_at'])); ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="text-align: center;">
                                                <div style="font-size: 1.5rem; font-weight: 700; color: var(--secondary-blue); margin-bottom: 0.3rem;">
                                                    <?php echo $user['total_orders']; ?>
                                                </div>
                                                <div style="font-size: 0.8rem; color: var(--text-light);">
                                                    orders
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="text-align: center;">
                                                <div style="font-size: 1.2rem; font-weight: 700; color: var(--success);">
                                                    <?php echo formatPrice($user['total_spent']); ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="text-align: center;">
                                                <?php if ($user['last_order_date']): ?>
                                                    <div style="font-weight: 600; color: var(--primary-dark); margin-bottom: 0.3rem;">
                                                        <?php echo date('M j, Y', strtotime($user['last_order_date'])); ?>
                                                    </div>
                                                    <div style="font-size: 0.8rem; color: var(--text-light);">
                                                        <?php 
                                                        $days_ago = floor((time() - strtotime($user['last_order_date'])) / (60 * 60 * 24));
                                                        echo $days_ago == 0 ? 'Today' : $days_ago . ' days ago';
                                                        ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span style="color: var(--text-light); font-style: italic;">No orders</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="text-align: center;">
                                                <?php if ($user['total_orders'] > 0): ?>
                                                    <span class="stock-status stock-in">Active Customer</span>
                                                <?php else: ?>
                                                    <span class="stock-status stock-low">New User</span>
                                                <?php endif; ?>
                                                
                                                <?php if ($user['total_spent'] > 50000): ?>
                                                    <div style="margin-top: 0.5rem;">
                                                        <span style="background: linear-gradient(135deg, var(--warning) 0%, #f1c40f 100%); color: white; padding: 0.2rem 0.6rem; border-radius: 12px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase;">
                                                            VIP
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 4rem; color: var(--text-light);">
                        <div style="font-size: 4rem; margin-bottom: 2rem;">👥</div>
                        <h3 style="margin-bottom: 1rem;">No Customers Yet</h3>
                        <p style="margin-bottom: 2rem;">Customer accounts will appear here once users start registering.</p>
                        <a href="../index.php" class="btn btn-primary">Visit Store</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Customer Statistics -->
            <div style="margin-top: 3rem;">
                <h2 style="text-align: center; margin-bottom: 2rem; color: var(--primary-dark); font-size: 2.2rem;">Customer Statistics</h2>
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo mysqli_num_rows($users_result); ?></div>
                        <div class="stat-label">Total Customers</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-number">
                            <?php 
                            mysqli_data_seek($users_result, 0);
                            $active_customers = 0;
                            while ($row = mysqli_fetch_assoc($users_result)) {
                                if ($row['total_orders'] > 0) $active_customers++;
                            }
                            echo $active_customers;
                            ?>
                        </div>
                        <div class="stat-label">Active Customers</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-number">
                            <?php 
                            mysqli_data_seek($users_result, 0);
                            $vip_customers = 0;
                            while ($row = mysqli_fetch_assoc($users_result)) {
                                if ($row['total_spent'] > 50000) $vip_customers++;
                            }
                            echo $vip_customers;
                            ?>
                        </div>
                        <div class="stat-label">VIP Customers</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-number">
                            <?php 
                            mysqli_data_seek($users_result, 0);
                            $total_customer_value = 0;
                            $customer_count = 0;
                            while ($row = mysqli_fetch_assoc($users_result)) {
                                if ($row['total_orders'] > 0) {
                                    $total_customer_value += $row['total_spent'];
                                    $customer_count++;
                                }
                            }
                            $avg_value = $customer_count > 0 ? $total_customer_value / $customer_count : 0;
                            echo formatPrice($avg_value);
                            ?>
                        </div>
                        <div class="stat-label">Avg Customer Value</div>
                    </div>
                </div>
            </div>

            <!-- Recent Registrations -->
            <div class="card" style="margin-top: 3rem;">
                <div class="card-header">
                    <h3>Recent Customer Activity</h3>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                        <div>
                            <h4 style="color: var(--primary-dark); margin-bottom: 1rem;">🆕 Newest Customers</h4>
                            <?php 
                            mysqli_data_seek($users_result, 0);
                            $count = 0;
                            while ($row = mysqli_fetch_assoc($users_result) && $count < 5): 
                                $count++;
                            ?>
                                <div style="display: flex; align-items: center; gap: 1rem; padding: 0.8rem; background: var(--very-light-blue); border-radius: 8px; margin-bottom: 0.5rem;">
                                    <div style="width: 35px; height: 35px; border-radius: 50%; background: var(--secondary-blue); color: white; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; font-weight: 600;">
                                        <?php echo strtoupper(substr($row['name'], 0, 1)); ?>
                                    </div>
                                    <div style="flex: 1;">
                                        <div style="font-weight: 600; color: var(--text-dark);">
                                            <?php echo htmlspecialchars($row['name']); ?>
                                        </div>
                                        <div style="font-size: 0.8rem; color: var(--text-light);">
                                            <?php echo date('M j, Y', strtotime($row['created_at'])); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                        
                        <div>
                            <h4 style="color: var(--primary-dark); margin-bottom: 1rem;">💎 Top Customers</h4>
                            <?php 
                            // Get top customers by spending
                            mysqli_data_seek($users_result, 0);
                            $top_customers = [];
                            while ($row = mysqli_fetch_assoc($users_result)) {
                                if ($row['total_spent'] > 0) {
                                    $top_customers[] = $row;
                                }
                            }
                            usort($top_customers, function($a, $b) {
                                return $b['total_spent'] - $a['total_spent'];
                            });
                            
                            $count = 0;
                            foreach (array_slice($top_customers, 0, 5) as $customer): 
                                $count++;
                            ?>
                                <div style="display: flex; align-items: center; gap: 1rem; padding: 0.8rem; background: var(--very-light-blue); border-radius: 8px; margin-bottom: 0.5rem;">
                                    <div style="width: 35px; height: 35px; border-radius: 50%; background: var(--success); color: white; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; font-weight: 600;">
                                        <?php echo strtoupper(substr($customer['name'], 0, 1)); ?>
                                    </div>
                                    <div style="flex: 1;">
                                        <div style="font-weight: 600; color: var(--text-dark);">
                                            <?php echo htmlspecialchars($customer['name']); ?>
                                        </div>
                                        <div style="font-size: 0.8rem; color: var(--success); font-weight: 600;">
                                            <?php echo formatPrice($customer['total_spent']); ?> • <?php echo $customer['total_orders']; ?> orders
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
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