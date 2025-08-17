<?php
require_once '../config.php';
requireAdmin();

$conn = getConnection();

// Get all products with category and brand info
$products_query = "SELECT p.*, c.name as category_name, b.name as brand_name 
                   FROM products p
                   LEFT JOIN categories c ON p.category_id = c.id
                   LEFT JOIN brands b ON p.brand_id = b.id
                   ORDER BY p.created_at DESC";
$products_result = mysqli_query($conn, $products_query);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Management - ShopFlow Admin</title>
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
                <h1 class="admin-title">Product Management</h1>
                <p class="admin-subtitle">Manage your product inventory and details</p>
            </div>
        </div>

        <div class="container">
            <!-- Admin Navigation -->
            <nav class="admin-nav">
                <ul class="admin-nav-list">
                    <li class="admin-nav-item"><a href="dashboard.php">📊 Dashboard</a></li>
                    <li class="admin-nav-item"><a href="products.php" class="active">📦 Products</a></li>
                    <li class="admin-nav-item"><a href="orders.php">🛒 Orders</a></li>
                    <li class="admin-nav-item"><a href="users.php">👥 Users</a></li>
                    <li class="admin-nav-item"><a href="messages.php">💬 Messages</a></li>
                </ul>
            </nav>

            <!-- Add Product Button -->
            <div style="text-align: center; margin: 2rem 0;">
                <a href="add_product.php" class="btn btn-success" style="padding: 1rem 2rem; font-size: 1.2rem;">
                    ➕ Add New Product
                </a>
            </div>

            <!-- Products Table -->
            <div class="products-table-container">
                <div class="table-header">
                    <h2>All Products (<?php echo mysqli_num_rows($products_result); ?>)</h2>
                </div>
                
                <?php if (mysqli_num_rows($products_result) > 0): ?>
                    <div style="overflow-x: auto;">
                        <table class="products-table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Product Details</th>
                                    <th>Category</th>
                                    <th>Brand</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($product = mysqli_fetch_assoc($products_result)): ?>
                                    <tr>
                                        <td>
                                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                 class="product-image-admin">
                                        </td>
                                        <td>
                                            <div style="min-width: 200px;">
                                                <h4 style="color: var(--primary-dark); margin-bottom: 0.5rem;">
                                                    <?php echo htmlspecialchars($product['name']); ?>
                                                </h4>
                                                <p style="color: var(--text-light); font-size: 0.9rem;">
                                                    <?php echo htmlspecialchars(substr($product['description'], 0, 80)); ?>...
                                                </p>
                                                <small style="color: var(--text-light);">
                                                    ID: <?php echo $product['id']; ?>
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <span style="background: var(--very-light-blue); color: var(--secondary-blue); padding: 0.3rem 0.8rem; border-radius: 15px; font-size: 0.85rem; font-weight: 600;">
                                                <?php echo htmlspecialchars($product['category_name'] ?? 'No Category'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span style="background: var(--pale-blue); color: var(--primary-dark); padding: 0.3rem 0.8rem; border-radius: 15px; font-size: 0.85rem; font-weight: 600;">
                                                <?php echo htmlspecialchars($product['brand_name'] ?? 'No Brand'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div style="font-size: 1.1rem; font-weight: 700; color: var(--secondary-blue);">
                                                <?php echo formatPrice($product['price']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="text-align: center;">
                                                <div style="font-size: 1.2rem; font-weight: 700; margin-bottom: 0.3rem;">
                                                    <?php echo $product['stock_quantity']; ?>
                                                </div>
                                                <div style="font-size: 0.8rem; color: var(--text-light);">units</div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($product['stock_quantity'] > 10): ?>
                                                <span class="stock-status stock-in">In Stock</span>
                                            <?php elseif ($product['stock_quantity'] > 0): ?>
                                                <span class="stock-status stock-low">Low Stock</span>
                                            <?php else: ?>
                                                <span class="stock-status stock-out">Out of Stock</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="product-actions">
                                                <a href="../product_details.php?id=<?php echo $product['id']; ?>" 
                                                   class="action-btn" style="background: var(--accent-blue); color: white; margin-bottom: 0.5rem; display: block; text-align: center;">
                                                   👁️ View
                                                </a>
                                                <a href="edit_product.php?id=<?php echo $product['id']; ?>" 
                                                   class="action-btn action-btn-edit" style="margin-bottom: 0.5rem; display: block; text-align: center;">
                                                   ✏️ Edit
                                                </a>
                                                <a href="delete_product.php?id=<?php echo $product['id']; ?>" 
                                                   class="action-btn action-btn-delete" 
                                                   style="display: block; text-align: center;"
                                                   onclick="return confirm('Are you sure you want to delete this product?')">
                                                   🗑️ Delete
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 4rem; color: var(--text-light);">
                        <div style="font-size: 4rem; margin-bottom: 2rem;">📦</div>
                        <h3 style="margin-bottom: 1rem;">No Products Yet</h3>
                        <p style="margin-bottom: 2rem;">Start building your store by adding your first product.</p>
                        <a href="add_product.php" class="btn btn-primary">Add Your First Product</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Quick Stats -->
            <div style="margin-top: 3rem;">
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo mysqli_num_rows($products_result); ?></div>
                        <div class="stat-label">Total Products</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-number">
                            <?php 
                            mysqli_data_seek($products_result, 0);
                            $in_stock = 0;
                            while ($row = mysqli_fetch_assoc($products_result)) {
                                if ($row['stock_quantity'] > 0) $in_stock++;
                            }
                            echo $in_stock;
                            ?>
                        </div>
                        <div class="stat-label">In Stock</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-number">
                            <?php 
                            mysqli_data_seek($products_result, 0);
                            $low_stock = 0;
                            while ($row = mysqli_fetch_assoc($products_result)) {
                                if ($row['stock_quantity'] > 0 && $row['stock_quantity'] <= 10) $low_stock++;
                            }
                            echo $low_stock;
                            ?>
                        </div>
                        <div class="stat-label">Low Stock</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-number">
                            <?php 
                            mysqli_data_seek($products_result, 0);
                            $out_of_stock = 0;
                            while ($row = mysqli_fetch_assoc($products_result)) {
                                if ($row['stock_quantity'] == 0) $out_of_stock++;
                            }
                            echo $out_of_stock;
                            ?>
                        </div>
                        <div class="stat-label">Out of Stock</div>
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