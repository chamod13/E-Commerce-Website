<?php
require_once '../config.php';
requireAdmin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: products.php');
    exit();
}

$product_id = (int)$_GET['id'];
$conn = getConnection();
$message = '';

// Get product details
$product_query = "SELECT * FROM products WHERE id = ?";
$product_stmt = mysqli_prepare($conn, $product_query);
mysqli_stmt_bind_param($product_stmt, 'i', $product_id);
mysqli_stmt_execute($product_stmt);
$product_result = mysqli_stmt_get_result($product_stmt);

if (!$product = mysqli_fetch_assoc($product_result)) {
    header('Location: products.php');
    exit();
}

// Get categories and brands
$categories_query = "SELECT * FROM categories ORDER BY name";
$categories_result = mysqli_query($conn, $categories_query);

$brands_query = "SELECT * FROM brands ORDER BY name";
$brands_result = mysqli_query($conn, $brands_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $brand_id = !empty($_POST['brand_id']) ? (int)$_POST['brand_id'] : null;
    $stock_quantity = (int)$_POST['stock_quantity'];
    $image_url = trim($_POST['image_url']);
    
    if (empty($name) || empty($description) || $price <= 0 || $stock_quantity < 0) {
        $message = '<div class="alert alert-error">Please fill in all required fields with valid values.</div>';
    } else {
        $update_query = "UPDATE products SET name = ?, description = ?, price = ?, category_id = ?, brand_id = ?, stock_quantity = ?, image_url = ? WHERE id = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, 'ssdiissi', $name, $description, $price, $category_id, $brand_id, $stock_quantity, $image_url, $product_id);
        
        if (mysqli_stmt_execute($update_stmt)) {
            $message = '<div class="alert alert-success">Product updated successfully! <a href="../product_details.php?id=' . $product_id . '">View Product</a></div>';
            
            // Refresh product data
            mysqli_stmt_execute($product_stmt);
            $product_result = mysqli_stmt_get_result($product_stmt);
            $product = mysqli_fetch_assoc($product_result);
        } else {
            $message = '<div class="alert alert-error">Error updating product. Please try again.</div>';
        }
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - ShopFlow Admin</title>
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
                    <li><a href="products.php">← Back to Products</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="admin-container">
        <div class="admin-header">
            <div class="admin-header-content">
                <h1 class="admin-title">Edit Product</h1>
                <p class="admin-subtitle">Update product information and inventory</p>
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

            <div class="product-form-container">
                <div class="product-form-header">
                    <h2>Edit: <?php echo htmlspecialchars($product['name']); ?></h2>
                    <p>Product ID: <?php echo $product['id']; ?> | Created: <?php echo date('M j, Y', strtotime($product['created_at'])); ?></p>
                </div>
                
                <div class="product-form-body">
                    <?php echo $message; ?>
                    
                    <form method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Product Name: *</label>
                                <input type="text" id="name" name="name" class="form-control" 
                                       placeholder="Enter product name" required 
                                       value="<?php echo htmlspecialchars($product['name']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="price">Price (₹): *</label>
                                <input type="number" id="price" name="price" class="form-control" 
                                       placeholder="0.00" step="0.01" min="0" required
                                       value="<?php echo $product['price']; ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description: *</label>
                            <textarea id="description" name="description" class="form-control" rows="4" 
                                     placeholder="Describe your product in detail..." required><?php echo htmlspecialchars($product['description']); ?></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="category_id">Category:</label>
                                <select id="category_id" name="category_id" class="form-control">
                                    <option value="">Select Category</option>
                                    <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                                        <option value="<?php echo $category['id']; ?>" 
                                                <?php echo $product['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="brand_id">Brand:</label>
                                <select id="brand_id" name="brand_id" class="form-control">
                                    <option value="">Select Brand</option>
                                    <?php while ($brand = mysqli_fetch_assoc($brands_result)): ?>
                                        <option value="<?php echo $brand['id']; ?>"
                                                <?php echo $product['brand_id'] == $brand['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($brand['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="stock_quantity">Stock Quantity: *</label>
                                <input type="number" id="stock_quantity" name="stock_quantity" class="form-control" 
                                       placeholder="0" min="0" required
                                       value="<?php echo $product['stock_quantity']; ?>">
                                <small style="color: var(--text-light); margin-top: 0.5rem; display: block;">
                                    Current stock: <?php echo $product['stock_quantity']; ?> units
                                </small>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="image_url">Product Image URL:</label>
                            <input type="url" id="image_url" name="image_url" class="form-control" 
                                   placeholder="https://example.com/image.jpg"
                                   value="<?php echo htmlspecialchars($product['image_url']); ?>">
                            <small style="color: var(--text-light); margin-top: 0.5rem; display: block;">
                                Use a direct link to an image (JPG, PNG, etc.). Recommended size: 300x300px
                            </small>
                        </div>
                        
                        <!-- Current and New Image Preview -->
                        <div class="form-row">
                            <div class="form-group">
                                <label>Current Image:</label>
                                <div class="image-preview">
                                    <?php if (!empty($product['image_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                             alt="Current product image" 
                                             style="width: 200px; height: 200px; object-fit: cover; border-radius: 12px;">
                                    <?php else: ?>
                                        <span>No image set</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>New Image Preview:</label>
                                <div class="image-preview" id="imagePreview">
                                    <span>New image preview will appear here</span>
                                </div>
                            </div>
                        </div>
                        
                        <div style="text-align: center; margin-top: 3rem;">
                            <button type="submit" class="btn btn-success" style="padding: 1rem 3rem; font-size: 1.2rem; margin-right: 1rem;">
                                💾 Update Product
                            </button>
                            <a href="products.php" class="btn" style="padding: 1rem 3rem; font-size: 1.2rem; margin-right: 1rem;">
                                Cancel
                            </a>
                            <a href="../product_details.php?id=<?php echo $product['id']; ?>" 
                               class="btn" style="padding: 1rem 3rem; font-size: 1.2rem; background: var(--accent-blue); color: white;">
                                👁️ View Product
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Product Statistics -->
            <div class="card" style="margin-top: 2rem;">
                <div class="card-header">
                    <h3>Product Statistics</h3>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem;">
                        <div style="text-align: center; padding: 1.5rem; background: var(--very-light-blue); border-radius: 12px;">
                            <div style="font-size: 2rem; font-weight: 700; color: var(--secondary-blue); margin-bottom: 0.5rem;">
                                <?php echo $product['stock_quantity']; ?>
                            </div>
                            <div style="color: var(--text-light); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">
                                Current Stock
                            </div>
                        </div>
                        
                        <div style="text-align: center; padding: 1.5rem; background: var(--very-light-blue); border-radius: 12px;">
                            <div style="font-size: 2rem; font-weight: 700; color: var(--success); margin-bottom: 0.5rem;">
                                <?php echo formatPrice($product['price']); ?>
                            </div>
                            <div style="color: var(--text-light); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">
                                Current Price
                            </div>
                        </div>
                        
                        <div style="text-align: center; padding: 1.5rem; background: var(--very-light-blue); border-radius: 12px;">
                            <div style="font-size: 2rem; font-weight: 700; color: var(--warning); margin-bottom: 0.5rem;">
                                <?php echo date('M j', strtotime($product['created_at'])); ?>
                            </div>
                            <div style="color: var(--text-light); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">
                                Date Added
                            </div>
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

    <script>
        // Image preview functionality
        document.getElementById('image_url').addEventListener('input', function() {
            const url = this.value;
            const preview = document.getElementById('imagePreview');
            
            if (url && url !== '<?php echo htmlspecialchars($product['image_url']); ?>') {
                preview.innerHTML = `<img src="${url}" alt="Preview" style="width: 200px; height: 200px; object-fit: cover; border-radius: 12px;">`;
            } else {
                preview.innerHTML = '<span>New image preview will appear here</span>';
            }
        });
        
        // Auto-resize textarea
        document.getElementById('description').addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
        
        // Set initial textarea height
        const textarea = document.getElementById('description');
        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';
    </script>
</body>
</html>