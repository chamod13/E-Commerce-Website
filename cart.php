<?php
require_once 'config.php';
requireLogin();

$conn = getConnection();
$message = '';

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $product_id = (int)$_POST['product_id'];
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }
    
    switch ($action) {
        case 'add':
            // Check product stock
            $stock_query = "SELECT stock_quantity FROM products WHERE id = ?";
            $stock_stmt = mysqli_prepare($conn, $stock_query);
            mysqli_stmt_bind_param($stock_stmt, 'i', $product_id);
            mysqli_stmt_execute($stock_stmt);
            $stock_result = mysqli_stmt_get_result($stock_stmt);
            $stock_data = mysqli_fetch_assoc($stock_result);
            
            if ($stock_data && $stock_data['stock_quantity'] > 0) {
                $current_qty = isset($_SESSION['cart'][$product_id]) ? $_SESSION['cart'][$product_id] : 0;
                if ($current_qty < $stock_data['stock_quantity']) {
                    $_SESSION['cart'][$product_id] = $current_qty + 1;
                    $message = '<div class="alert alert-success">Product added to cart!</div>';
                } else {
                    $message = '<div class="alert alert-warning">Cannot add more items. Maximum stock reached.</div>';
                }
            } else {
                $message = '<div class="alert alert-error">Product is out of stock.</div>';
            }
            break;
            
        case 'update':
            $quantity = (int)$_POST['quantity'];
            if ($quantity > 0) {
                // Check stock
                $stock_query = "SELECT stock_quantity FROM products WHERE id = ?";
                $stock_stmt = mysqli_prepare($conn, $stock_query);
                mysqli_stmt_bind_param($stock_stmt, 'i', $product_id);
                mysqli_stmt_execute($stock_stmt);
                $stock_result = mysqli_stmt_get_result($stock_stmt);
                $stock_data = mysqli_fetch_assoc($stock_result);
                
                if ($stock_data && $quantity <= $stock_data['stock_quantity']) {
                    $_SESSION['cart'][$product_id] = $quantity;
                    $message = '<div class="alert alert-success">Cart updated!</div>';
                } else {
                    $message = '<div class="alert alert-warning">Quantity exceeds available stock.</div>';
                }
            } else {
                unset($_SESSION['cart'][$product_id]);
                $message = '<div class="alert alert-success">Item removed from cart!</div>';
            }
            break;
            
        case 'remove':
            unset($_SESSION['cart'][$product_id]);
            $message = '<div class="alert alert-success">Item removed from cart!</div>';
            break;
    }
}

// Get cart products
$cart_products = array();
$total_amount = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $product_ids = implode(',', array_keys($_SESSION['cart']));
    $products_query = "SELECT * FROM products WHERE id IN ($product_ids)";
    $products_result = mysqli_query($conn, $products_query);
    
    while ($product = mysqli_fetch_assoc($products_result)) {
        $quantity = $_SESSION['cart'][$product['id']];
        $subtotal = $product['price'] * $quantity;
        $total_amount += $subtotal;
        
        $cart_products[] = array(
            'product' => $product,
            'quantity' => $quantity,
            'subtotal' => $subtotal
        );
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - ShopFlow</title>
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
                    <?php if (isAdmin()): ?>
                        <li><a href="admin/dashboard.php">Admin</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="cart-container">
        <div class="cart-header">
            <h1 class="cart-title">Shopping Cart</h1>
            <p class="cart-subtitle">Review your items before checkout</p>
        </div>

        <div class="cart-content">
            <?php if (empty($cart_products)): ?>
                <div class="empty-cart">
                    <div class="empty-cart-icon">🛒</div>
                    <h2>Your cart is empty</h2>
                    <p>Looks like you haven't added any items to your cart yet.</p>
                    <a href="index.php" class="btn btn-primary">Start Shopping</a>
                </div>
            <?php else: ?>
                <?php echo $message; ?>
                
                <div class="cart-items">
                    <div class="cart-items-header">
                        <h2>Cart Items (<?php echo count($cart_products); ?>)</h2>
                    </div>
                    <div class="cart-items-body">
                        <?php foreach ($cart_products as $item): ?>
                            <div class="cart-item">
                                <img src="<?php echo htmlspecialchars($item['product']['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['product']['name']); ?>" 
                                     class="cart-item-image">
                                
                                <div class="cart-item-details">
                                    <h3 class="cart-item-name">
                                        <?php echo htmlspecialchars($item['product']['name']); ?>
                                    </h3>
                                    <p class="cart-item-description">
                                        <?php echo htmlspecialchars(substr($item['product']['description'], 0, 150)); ?>...
                                    </p>
                                    <div class="cart-item-price">
                                        <?php echo formatPrice($item['product']['price']); ?> each
                                    </div>
                                </div>
                                
                                <form method="POST" class="quantity-controls">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                                    
                                    <button type="button" class="quantity-btn" onclick="decreaseQuantity(this)">-</button>
                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                           min="1" max="<?php echo $item['product']['stock_quantity']; ?>" 
                                           class="quantity-input" onchange="this.form.submit()">
                                    <button type="button" class="quantity-btn" onclick="increaseQuantity(this)">+</button>
                                </form>
                                
                                <div style="text-align: center;">
                                    <div style="font-size: 1.3rem; font-weight: 700; color: var(--primary-blue); margin-bottom: 1rem;">
                                        <?php echo formatPrice($item['subtotal']); ?>
                                    </div>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                                        <button type="submit" class="remove-btn">Remove</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="cart-summary">
                    <div class="cart-summary-header">
                        <h2>Order Summary</h2>
                    </div>
                    <div class="cart-summary-body">
                        <div class="summary-row">
                            <span class="summary-label">Subtotal:</span>
                            <span class="summary-value"><?php echo formatPrice($total_amount); ?></span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">Shipping:</span>
                            <span class="summary-value">FREE</span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">Tax:</span>
                            <span class="summary-value">Included</span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">Total:</span>
                            <span class="summary-value"><?php echo formatPrice($total_amount); ?></span>
                        </div>
                        
                        <a href="checkout.php" class="checkout-btn">
                            Proceed to Checkout
                        </a>
                        
                        <div class="continue-shopping">
                            <a href="index.php">← Continue Shopping</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; 2025 ShopFlow. All rights reserved. | Premium E-commerce Experience</p>
        </div>
    </footer>

    <script>
        function increaseQuantity(button) {
            const input = button.parentElement.querySelector('.quantity-input');
            const max = parseInt(input.max);
            const current = parseInt(input.value);
            if (current < max) {
                input.value = current + 1;
                input.form.submit();
            }
        }
        
        function decreaseQuantity(button) {
            const input = button.parentElement.querySelector('.quantity-input');
            const current = parseInt(input.value);
            if (current > 1) {
                input.value = current - 1;
                input.form.submit();
            }
        }
    </script>
</body>
</html>