<?php
require_once 'config.php';

$conn = getConnection();

// Get search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$brand = isset($_GET['brand']) ? $_GET['brand'] : '';
$min_price = isset($_GET['min_price']) ? $_GET['min_price'] : '';
$max_price = isset($_GET['max_price']) ? $_GET['max_price'] : '';

// Build query
$query = "SELECT p.*, c.name as category_name, b.name as brand_name, 
          COALESCE(AVG(r.rating), 0) as avg_rating, COUNT(r.id) as review_count
          FROM products p
          LEFT JOIN categories c ON p.category_id = c.id
          LEFT JOIN brands b ON p.brand_id = b.id
          LEFT JOIN reviews r ON p.id = r.product_id
          WHERE 1=1";

$params = array();
$types = '';

if (!empty($search)) {
    $query .= " AND p.name LIKE ?";
    $params[] = "%$search%";
    $types .= 's';
}

if (!empty($category)) {
    $query .= " AND p.category_id = ?";
    $params[] = $category;
    $types .= 'i';
}

if (!empty($brand)) {
    $query .= " AND p.brand_id = ?";
    $params[] = $brand;
    $types .= 'i';
}

if (!empty($min_price)) {
    $query .= " AND p.price >= ?";
    $params[] = $min_price;
    $types .= 'd';
}

if (!empty($max_price)) {
    $query .= " AND p.price <= ?";
    $params[] = $max_price;
    $types .= 'd';
}

$query .= " GROUP BY p.id ORDER BY p.created_at DESC";

// Prepare and execute query
if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    }
} else {
    $result = mysqli_query($conn, $query);
}

// Get categories and brands for filters
$categories_query = "SELECT * FROM categories ORDER BY name";
$categories_result = mysqli_query($conn, $categories_query);

$brands_query = "SELECT * FROM brands ORDER BY name";
$brands_result = mysqli_query($conn, $brands_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopFlow - Premium E-commerce Store</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <h1>ShopFlow</h1>
            </div>
            <nav>
                <ul class="nav-menu">
                    <li><a href="index.php">Home</a></li>
                    <?php if (isLoggedIn()): ?>
                        <li><a href="profile.php">Profile</a></li>
                        <li><a href="order_history.php">Orders</a></li>
                        <li><a href="cart.php">Cart <span class="cart-badge"><?php echo getCartCount(); ?></span></a></li>
                        <?php if (isAdmin()): ?>
                            <li><a href="admin/dashboard.php">Admin</a></li>
                        <?php endif; ?>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    <?php endif; ?>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="faq.php">FAQ</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <!-- Hero Section -->
        <section class="hero" style="background: linear-gradient(135deg, var(--primary-blue) 0%, var(--light-blue) 100%); color: white; padding: 4rem 2rem; border-radius: 20px; text-align: center; margin-bottom: 3rem;">
            <h2 style="font-size: 3.5rem; margin-bottom: 1rem; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">Welcome to ShopFlow</h2>
            <p style="font-size: 1.3rem; opacity: 0.9; max-width: 600px; margin: 0 auto;">Discover amazing products at unbeatable prices with fast shipping and excellent customer service</p>
        </section>

        <!-- Search and Filter Section -->
        <div class="search-filter-section">
            <h3 style="text-align: center; margin-bottom: 2rem; color: var(--primary-dark); font-size: 2rem;">Find Your Perfect Product</h3>
            <form method="GET" action="" class="filter-row">
                <div class="form-group">
                    <label for="search">Search Products:</label>
                    <input type="text" id="search" name="search" class="form-control" 
                           placeholder="Search by product name..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="form-group">
                    <label for="category">Category:</label>
                    <select id="category" name="category" class="form-control">
                        <option value="">All Categories</option>
                        <?php while ($cat = mysqli_fetch_assoc($categories_result)): ?>
                            <option value="<?php echo $cat['id']; ?>" 
                                    <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="brand">Brand:</label>
                    <select id="brand" name="brand" class="form-control">
                        <option value="">All Brands</option>
                        <?php while ($brand_row = mysqli_fetch_assoc($brands_result)): ?>
                            <option value="<?php echo $brand_row['id']; ?>" 
                                    <?php echo $brand == $brand_row['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($brand_row['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="min_price">Min Price (₹):</label>
                    <input type="number" id="min_price" name="min_price" class="form-control" 
                           placeholder="0" value="<?php echo htmlspecialchars($min_price); ?>">
                </div>
                
                <div class="form-group">
                    <label for="max_price">Max Price (₹):</label>
                    <input type="number" id="max_price" name="max_price" class="form-control" 
                           placeholder="999999" value="<?php echo htmlspecialchars($max_price); ?>">
                </div>
                
                <div class="form-group" style="display: flex; gap: 1rem; align-items: end;">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <a href="index.php" class="btn" style="background: var(--text-light);">Clear</a>
                </div>
            </form>
        </div>

        <!-- Products Grid -->
        <section>
            <h2 style="text-align: center; margin: 3rem 0 2rem; font-size: 2.5rem; color: var(--primary-dark);">
                <?php 
                if (!empty($search) || !empty($category) || !empty($brand) || !empty($min_price) || !empty($max_price)) {
                    echo "Search Results";
                } else {
                    echo "Featured Products";
                }
                ?>
            </h2>
            
            <div class="grid grid-4">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($product = mysqli_fetch_assoc($result)): ?>
                        <div class="card">
                            <div style="text-align: center; padding: 1.5rem 1.5rem 1rem;">
                                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                     class="product-image">
                            </div>
                            <div class="card-body">
                                <h3 style="color: var(--primary-dark); margin-bottom: 0.5rem; font-size: 1.3rem;">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </h3>
                                <p style="color: var(--text-light); margin-bottom: 1rem; font-size: 0.95rem;">
                                    <?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...
                                </p>
                                
                                <div style="margin-bottom: 1rem;">
                                    <span style="color: var(--text-light); font-size: 0.9rem;">
                                        <?php echo htmlspecialchars($product['category_name']); ?> • 
                                        <?php echo htmlspecialchars($product['brand_name']); ?>
                                    </span>
                                </div>
                                
                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                                    <div style="color: #ffc107; font-size: 1.1rem;">
                                        <?php 
                                        $rating = round($product['avg_rating']);
                                        for ($i = 1; $i <= 5; $i++) {
                                            echo $i <= $rating ? '★' : '☆';
                                        }
                                        ?>
                                    </div>
                                    <span style="color: var(--text-light); font-size: 0.9rem;">
                                        (<?php echo $product['review_count']; ?> reviews)
                                    </span>
                                </div>
                                
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--secondary-blue);">
                                        <?php echo formatPrice($product['price']); ?>
                                    </div>
                                    <div style="font-size: 0.9rem; color: <?php echo $product['stock_quantity'] > 0 ? 'var(--success)' : 'var(--error)'; ?>;">
                                        <?php echo $product['stock_quantity'] > 0 ? "In Stock ({$product['stock_quantity']})" : "Out of Stock"; ?>
                                    </div>
                                </div>
                                
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="product_details.php?id=<?php echo $product['id']; ?>" 
                                       class="btn btn-primary" style="flex: 1; text-align: center;">View Details</a>
                                    <?php if ($product['stock_quantity'] > 0): ?>
                                        <?php if (isLoggedIn()): ?>
                                            <form method="POST" action="cart.php" style="flex: 1;">
                                                <input type="hidden" name="action" value="add">
                                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                <button type="submit" class="btn btn-success w-100">Add to Cart</button>
                                            </form>
                                        <?php else: ?>
                                            <a href="login.php" class="btn btn-success" style="flex: 1; text-align: center;">Login to Buy</a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <button class="btn" style="flex: 1; background: var(--text-light); cursor: not-allowed;" disabled>Out of Stock</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="grid-column: 1 / -1; text-align: center; padding: 4rem; background: var(--white); border-radius: 20px;">
                        <h3 style="color: var(--text-light); font-size: 2rem; margin-bottom: 1rem;">No Products Found</h3>
                        <p style="color: var(--text-light); font-size: 1.2rem; margin-bottom: 2rem;">
                            Try adjusting your search criteria or browse all products.
                        </p>
                        <a href="index.php" class="btn btn-primary">Browse All Products</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 ShopFlow. All rights reserved. | Premium E-commerce Experience</p>
        </div>
    </footer>
</body>
</html>

<?php
mysqli_close($conn);
?>