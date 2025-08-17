<?php
require_once 'config.php';
requireLogin();

$conn = getConnection();
$message = '';

// Check for direct purchase
$direct_product_id = isset($_GET['direct']) ? (int)$_GET['direct'] : null;

// Get cart or direct product
$cart_products = array();
$total_amount = 0;

if ($direct_product_id) {
    // Direct purchase
    $product_query = "SELECT * FROM products WHERE id = ? AND stock_quantity > 0";
    $product_stmt = mysqli_prepare($conn, $product_query);
    mysqli_stmt_bind_param($product_stmt, 'i', $direct_product_id);
    mysqli_stmt_execute($product_stmt);
    $product_result = mysqli_stmt_get_result($product_stmt);
    
    if ($product = mysqli_fetch_assoc($product_result)) {
        $cart_products[] = array(
            'product' => $product,
            'quantity' => 1,
            'subtotal' => $product['price']
        );
        $total_amount = $product['price'];
    } else {
        header('Location: index.php');
        exit();
    }
} else {
    // Regular cart checkout
    if (empty($_SESSION['cart'])) {
        header('Location: cart.php');
        exit();
    }
    
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

// Handle order placement
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_method = $_POST['payment_method'];
    $shipping_address = trim($_POST['shipping_address']);
    
    if (empty($shipping_address)) {
        $message = '<div class="alert alert-error">Shipping address is required.</div>';
    } else {
        // Start transaction
        mysqli_autocommit($conn, false);
        $transaction_success = true;
        
        try {
            // Create order
            $order_query = "INSERT INTO orders (user_id, total_amount, payment_method, shipping_address) VALUES (?, ?, ?, ?)";
            $order_stmt = mysqli_prepare($conn, $order_query);
            mysqli_stmt_bind_param($order_stmt, 'idss', $_SESSION['user_id'], $total_amount, $payment_method, $shipping_address);
            
            if (mysqli_stmt_execute($order_stmt)) {
                $order_id = mysqli_insert_id($conn);
                
                // Add order items and update inventory
                foreach ($cart_products as $item) {
                    // Insert order item
                    $item_query = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
                    $item_stmt = mysqli_prepare($conn, $item_query);
                    mysqli_stmt_bind_param($item_stmt, 'iiid', $order_id, $item['product']['id'], $item['quantity'], $item['product']['price']);
                    
                    if (!mysqli_stmt_execute($item_stmt)) {
                        $transaction_success = false;
                        break;
                    }
                    
                    // Update product stock
                    $stock_query = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?";
                    $stock_stmt = mysqli_prepare($conn, $stock_query);
                    mysqli_stmt_bind_param($stock_stmt, 'ii', $item['quantity'], $item['product']['id']);
                    
                    if (!mysqli_stmt_execute($stock_stmt)) {
                        $transaction_success = false;
                        break;
                    }
                }
                
                if ($transaction_success) {
                    mysqli_commit($conn);
                    
                    // Clear cart if not direct purchase
                    if (!$direct_product_id) {
                        unset($_SESSION['cart']);
                    }
                    
                    header("Location: order_success.php?order_id=$order_id");
                    exit();
                } else {
                    mysqli_rollback($conn);
                    $message = '<div class="alert alert-error">Error processing order. Please try again.</div>';
                }
            } else {
                mysqli_rollback($conn);
                $message = '<div class="alert alert-error">Error creating order. Please try again.</div>';
            }
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $message = '<div class="alert alert-error">Error processing order. Please try again.</div>';
        }
        
        mysqli_autocommit($conn, true);
    }
}

// Get user address
$user_query = "SELECT address FROM users WHERE id = ?";
$user_stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($user_stmt, 'i', $_SESSION['user_id']);
mysqli_stmt_execute($user_stmt);
$user_result = mysqli_stmt_get_result($user_stmt);
$user = mysqli_fetch_assoc($user_result);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - ShopFlow</title>
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
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="cart-container">
        <div class="cart-header">
            <h1 class="cart-title">Secure Checkout</h1>
            <p class="cart-subtitle">Complete your order with confidence</p>
        </div>

        <div class="cart-content">
            <?php echo $message; ?>
            
            <div class="cart-items">
                <div class="cart-items-header">
                    <h2>Order Summary (<?php echo count($cart_products); ?> items)</h2>
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
                                <div class="cart-item-price">
                                    <?php echo formatPrice($item['product']['price']); ?> × <?php echo $item['quantity']; ?>
                                </div>
                            </div>
                            
                            <div style="text-align: center;">
                                <div style="font-size: 1.3rem; font-weight: 700; color: var(--primary-blue);">
                                    <?php echo formatPrice($item['subtotal']); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="cart-summary">
                <div class="cart-summary-header">
                    <h2>Checkout Details</h2>
                </div>
                <div class="cart-summary-body">
                    <form method="POST" style="margin-bottom: 2rem;">
                        <div class="form-group">
                            <label for="shipping_address">Shipping Address: *</label>
                            <textarea id="shipping_address" name="shipping_address" class="form-control" 
                                     rows="4" required placeholder="Enter your complete shipping address"><?php echo htmlspecialchars($user['address']); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Payment Method: *</label>
                            <div style="display: grid; gap: 1rem; margin-top: 1rem;">
                                <label style="display: flex; align-items: center; gap: 0.5rem; padding: 1rem; background: var(--very-light-blue); border-radius: 10px; cursor: pointer; border: 2px solid transparent;">
                                    <input type="radio" name="payment_method" value="Credit/Debit Card" required>
                                    <span style="font-weight: 600;">💳 Credit/Debit Card</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 0.5rem; padding: 1rem; background: var(--very-light-blue); border-radius: 10px; cursor: pointer; border: 2px solid transparent;">
                                    <input type="radio" name="payment_method" value="PayPal" required>
                                    <span style="font-weight: 600;">🅿️ PayPal</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 0.5rem; padding: 1rem; background: var(--very-light-blue); border-radius: 10px; cursor: pointer; border: 2px solid transparent;">
                                    <input type="radio" name="payment_method" value="UPI" required>
                                    <span style="font-weight: 600;">📱 UPI</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 0.5rem; padding: 1rem; background: var(--very-light-blue); border-radius: 10px; cursor: pointer; border: 2px solid transparent;">
                                    <input type="radio" name="payment_method" value="Cash on Delivery" required>
                                    <span style="font-weight: 600;">💰 Cash on Delivery</span>
                                </label>
                            </div>
                        </div>
                        
                        <hr style="margin: 2rem 0; border: none; height: 1px; background: var(--pale-blue);">
                        
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
                        
                        <button type="submit" class="checkout-btn">
                            Complete Order - <?php echo formatPrice($total_amount); ?>
                        </button>
                    </form>
                    
                    <div style="text-align: center; padding: 1.5rem; background: var(--very-light-blue); border-radius: 12px; border-left: 4px solid var(--success);">
                        <p style="color: var(--text-dark); margin: 0; font-size: 0.95rem; line-height: 1.5;">
                            🔒 <strong>Secure Payment:</strong> This is a demo checkout. No real payment will be processed.
                            Your order will be marked as "Pending" for demonstration purposes.
                        </p>
                    </div>
                    
                    <div class="continue-shopping">
                        <?php if ($direct_product_id): ?>
                            <a href="product_details.php?id=<?php echo $direct_product_id; ?>">← Back to Product</a>
                        <?php else: ?>
                            <a href="cart.php">← Back to Cart</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; 2025 ShopFlow. All rights reserved. | Premium E-commerce Experience</p>
        </div>
    </footer>

    <script>
        // Add visual feedback for radio button selection
        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', function() {
                // Remove selected state from all labels
                document.querySelectorAll('label').forEach(label => {
                    if (label.querySelector('input[name="payment_method"]')) {
                        label.style.borderColor = 'transparent';
                        label.style.background = 'var(--very-light-blue)';
                    }
                });
                
                // Add selected state to chosen label
                const label = this.closest('label');
                label.style.borderColor = 'var(--secondary-blue)';
                label.style.background = 'rgba(0, 119, 182, 0.1)';
            });
        });
    </script>
</body>
</html>