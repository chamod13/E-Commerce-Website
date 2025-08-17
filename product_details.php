<?php
require_once 'config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$product_id = (int)$_GET['id'];
$conn = getConnection();

// Get product details with category and brand
$product_query = "SELECT p.*, c.name as category_name, b.name as brand_name,
                  COALESCE(AVG(r.rating), 0) as avg_rating, COUNT(r.id) as review_count
                  FROM products p
                  LEFT JOIN categories c ON p.category_id = c.id
                  LEFT JOIN brands b ON p.brand_id = b.id
                  LEFT JOIN reviews r ON p.id = r.product_id
                  WHERE p.id = ?
                  GROUP BY p.id";
$product_stmt = mysqli_prepare($conn, $product_query);
mysqli_stmt_bind_param($product_stmt, 'i', $product_id);
mysqli_stmt_execute($product_stmt);
$product_result = mysqli_stmt_get_result($product_stmt);

if (!$product = mysqli_fetch_assoc($product_result)) {
    header('Location: index.php');
    exit();
}

// Get reviews for this product
$reviews_query = "SELECT r.*, u.name as user_name 
                  FROM reviews r 
                  JOIN users u ON r.user_id = u.id 
                  WHERE r.product_id = ? 
                  ORDER BY r.created_at DESC";
$reviews_stmt = mysqli_prepare($conn, $reviews_query);
mysqli_stmt_bind_param($reviews_stmt, 'i', $product_id);
mysqli_stmt_execute($reviews_stmt);
$reviews_result = mysqli_stmt_get_result($reviews_stmt);

// Get related products (same category)
$related_query = "SELECT p.*, COALESCE(AVG(r.rating), 0) as avg_rating 
                  FROM products p 
                  LEFT JOIN reviews r ON p.id = r.product_id
                  WHERE p.category_id = ? AND p.id != ? 
                  GROUP BY p.id
                  LIMIT 4";
$related_stmt = mysqli_prepare($conn, $related_query);
mysqli_stmt_bind_param($related_stmt, 'ii', $product['category_id'], $product_id);
mysqli_stmt_execute($related_stmt);
$related_result = mysqli_stmt_get_result($related_stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - ShopFlow</title>
    <link rel="stylesheet" href="css/style.css">
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
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <!-- Breadcrumb -->
        <nav style="margin: 2rem 0; padding: 1rem; background: var(--white); border-radius: 10px;">
            <a href="index.php" style="color: var(--secondary-blue); text-decoration: none;">Home</a> 
            <span style="color: var(--text-light);"> > </span>
            <a href="index.php?category=<?php echo $product['category_id']; ?>" style="color: var(--secondary-blue); text-decoration: none;">
                <?php echo htmlspecialchars($product['category_name']); ?>
            </a>
            <span style="color: var(--text-light);"> > </span>
            <span style="color: var(--text-dark);"><?php echo htmlspecialchars($product['name']); ?></span>
        </nav>

        <!-- Product Details -->
        <div class="card">
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: start;">
                    <!-- Product Image -->
                    <div style="text-align: center;">
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             class="product-image" 
                             style="max-width: 100%; height: auto; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
                    </div>

                    <!-- Product Info -->
                    <div>
                        <h1 style="color: var(--primary-dark); font-size: 2.5rem; margin-bottom: 1rem; font-weight: 700;">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </h1>

                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap;">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <div style="color: #ffc107; font-size: 1.5rem;">
                                    <?php 
                                    $rating = round($product['avg_rating']);
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo $i <= $rating ? '★' : '☆';
                                    }
                                    ?>
                                </div>
                                <span style="color: var(--text-light); font-size: 1.1rem;">
                                    (<?php echo $product['review_count']; ?> reviews)
                                </span>
                            </div>
                            
                            <div style="padding: 0.5rem 1rem; background: var(--very-light-blue); border-radius: 20px; font-size: 0.9rem; color: var(--secondary-blue); font-weight: 600;">
                                <?php echo htmlspecialchars($product['brand_name']); ?>
                            </div>
                        </div>

                        <div style="font-size: 3rem; font-weight: 700; color: var(--secondary-blue); margin-bottom: 2rem;">
                            <?php echo formatPrice($product['price']); ?>
                        </div>

                        <div style="margin-bottom: 2rem;">
                            <h3 style="color: var(--text-dark); margin-bottom: 1rem; font-size: 1.3rem;">Description</h3>
                            <p style="color: var(--text-light); line-height: 1.8; font-size: 1.1rem;">
                                <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                            </p>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem; padding: 2rem; background: var(--very-light-blue); border-radius: 15px;">
                            <div>
                                <h4 style="color: var(--text-dark); margin-bottom: 0.5rem;">Category</h4>
                                <p style="color: var(--secondary-blue); font-weight: 600;">
                                    <?php echo htmlspecialchars($product['category_name']); ?>
                                </p>
                            </div>
                            <div>
                                <h4 style="color: var(--text-dark); margin-bottom: 0.5rem;">Stock Status</h4>
                                <p style="color: <?php echo $product['stock_quantity'] > 0 ? 'var(--success)' : 'var(--error)'; ?>; font-weight: 600;">
                                    <?php echo $product['stock_quantity'] > 0 ? "In Stock ({$product['stock_quantity']} available)" : "Out of Stock"; ?>
                                </p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div style="display: flex; gap: 1rem; margin-bottom: 2rem;">
                            <?php if ($product['stock_quantity'] > 0): ?>
                                <?php if (isLoggedIn()): ?>
                                    <form method="POST" action="cart.php" style="flex: 1;">
                                        <input type="hidden" name="action" value="add">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <button type="submit" class="btn btn-success w-100" style="padding: 1.2rem; font-size: 1.2rem; font-weight: 700;">
                                            Add to Cart
                                        </button>
                                    </form>
                                    <a href="checkout.php?direct=<?php echo $product['id']; ?>" 
                                       class="btn btn-primary" style="flex: 1; padding: 1.2rem; font-size: 1.2rem; text-align: center;">
                                        Buy Now
                                    </a>
                                <?php else: ?>
                                    <a href="login.php" class="btn btn-success w-100" style="padding: 1.2rem; font-size: 1.2rem; text-align: center;">
                                        Login to Purchase
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <button class="btn w-100" style="padding: 1.2rem; background: var(--text-light); cursor: not-allowed;" disabled>
                                    Out of Stock
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews Section -->
        <div class="card">
            <div class="card-header">
                <h2>Customer Reviews (<?php echo $product['review_count']; ?>)</h2>
            </div>
            <div class="card-body">
                <?php if (isLoggedIn()): ?>
                    <div style="background: var(--very-light-blue); padding: 2rem; border-radius: 15px; margin-bottom: 2rem;">
                        <h3 style="margin-bottom: 1.5rem; color: var(--primary-dark);">Write a Review</h3>
                        <form method="POST" action="add_review.php">
                            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                            <div class="form-group">
                                <label>Rating:</label>
                                <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <label style="cursor: pointer; font-size: 2rem; color: #ddd;" 
                                               onmouseover="highlightStars(<?php echo $i; ?>)" 
                                               onmouseout="resetStars()">
                                            <input type="radio" name="rating" value="<?php echo $i; ?>" 
                                                   style="display: none;" onchange="setRating(<?php echo $i; ?>)">
                                            <span id="star-<?php echo $i; ?>">☆</span>
                                        </label>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="comment">Comment:</label>
                                <textarea name="comment" id="comment" class="form-control" rows="4" 
                                         placeholder="Share your experience with this product..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Review</button>
                        </form>
                    </div>
                    
                    <script>
                        let selectedRating = 0;
                        
                        function highlightStars(rating) {
                            for (let i = 1; i <= 5; i++) {
                                const star = document.getElementById(`star-${i}`);
                                if (i <= rating) {
                                    star.style.color = '#ffc107';
                                    star.innerHTML = '★';
                                } else {
                                    star.style.color = '#ddd';
                                    star.innerHTML = '☆';
                                }
                            }
                        }
                        
                        function resetStars() {
                            if (selectedRating === 0) {
                                for (let i = 1; i <= 5; i++) {
                                    const star = document.getElementById(`star-${i}`);
                                    star.style.color = '#ddd';
                                    star.innerHTML = '☆';
                                }
                            } else {
                                highlightStars(selectedRating);
                            }
                        }
                        
                        function setRating(rating) {
                            selectedRating = rating;
                            highlightStars(rating);
                        }
                    </script>
                <?php endif; ?>

                <div class="reviews-list">
                    <?php if (mysqli_num_rows($reviews_result) > 0): ?>
                        <?php while ($review = mysqli_fetch_assoc($reviews_result)): ?>
                            <div style="border-bottom: 1px solid var(--pale-blue); padding: 2rem 0;">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
                                    <div>
                                        <h4 style="color: var(--text-dark); margin-bottom: 0.5rem;">
                                            <?php echo htmlspecialchars($review['user_name']); ?>
                                        </h4>
                                        <div style="color: #ffc107; font-size: 1.2rem; margin-bottom: 0.5rem;">
                                            <?php 
                                            for ($i = 1; $i <= 5; $i++) {
                                                echo $i <= $review['rating'] ? '★' : '☆';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div style="color: var(--text-light); font-size: 0.9rem;">
                                        <?php echo date('M j, Y', strtotime($review['created_at'])); ?>
                                    </div>
                                </div>
                                <?php if (!empty($review['comment'])): ?>
                                    <p style="color: var(--text-dark); line-height: 1.6; font-size: 1.05rem;">
                                        <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div style="text-align: center; padding: 3rem; color: var(--text-light);">
                            <p style="font-size: 1.2rem;">No reviews yet. Be the first to review this product!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if (mysqli_num_rows($related_result) > 0): ?>
            <section>
                <h2 style="text-align: center; margin: 3rem 0 2rem; font-size: 2.5rem; color: var(--primary-dark);">
                    Related Products
                </h2>
                <div class="grid grid-4">
                    <?php while ($related = mysqli_fetch_assoc($related_result)): ?>
                        <div class="card">
                            <div style="text-align: center; padding: 1.5rem 1.5rem 1rem;">
                                <img src="<?php echo htmlspecialchars($related['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($related['name']); ?>" 
                                     class="product-image-small" 
                                     style="width: 200px; height: 200px; object-fit: cover; border-radius: 10px;">
                            </div>
                            <div class="card-body">
                                <h3 style="color: var(--primary-dark); margin-bottom: 0.5rem; font-size: 1.2rem;">
                                    <?php echo htmlspecialchars($related['name']); ?>
                                </h3>
                                <div style="color: #ffc107; margin-bottom: 1rem;">
                                    <?php 
                                    $rating = round($related['avg_rating']);
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo $i <= $rating ? '★' : '☆';
                                    }
                                    ?>
                                </div>
                                <div style="font-size: 1.3rem; font-weight: 700; color: var(--secondary-blue); margin-bottom: 1rem;">
                                    <?php echo formatPrice($related['price']); ?>
                                </div>
                                <a href="product_details.php?id=<?php echo $related['id']; ?>" 
                                   class="btn btn-primary w-100">View Details</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </section>
        <?php endif; ?>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 ShopFlow. All rights reserved. | Premium E-commerce Experience</p>
        </div>
    </footer>
</body>
</html>

<?php mysqli_close($conn); ?>