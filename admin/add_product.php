<?php
require_once '../config.php';
requireAdmin();

$conn = getConnection();
$message = '';

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
        $insert_query = "INSERT INTO products (name, description, price, category_id, brand_id, stock_quantity, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $insert_stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($insert_stmt, 'ssdiiss', $name, $description, $price, $category_id, $brand_id, $stock_quantity, $image_url);
        
        if (mysqli_stmt_execute($insert_stmt)) {
            $product_id = mysqli_insert_id($conn);
            $message = '<div class="alert alert-success">Product added successfully! <a href="edit_product.php?id=' . $product_id . '">Edit</a> | <a href="../product_details.php?id=' . $product_id . '">View</a></div>';
            
            // Clear form after successful submission
            $name = $description = $image_url = '';
            $price = $stock_quantity = 0;
            $category_id = $brand_id = null;
        } else {
            $message = '<div class="alert alert-error">Error adding product. Please try again.</div>';
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
    <title>Add Product - ShopFlow Admin</title>
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
                <h1 class="admin-title">Add New Product</h1>
                <p class="admin-subtitle">Create a new product for your store</p>
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
                    <h2>Product Information</h2>
                    <p>Fill in the details below to add a new product to your store</p>
                </div>
                
                <div class="product-form-body">
                    <?php echo $message; ?>
                    
                    <form method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Product Name: *</label>
                                <input type="text" id="name" name="name" class="form-control" 
                                       placeholder="Enter product name" required 
                                       value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="price">Price (₹): *</label>
                                <input type="number" id="price" name="price" class="form-control" 
                                       placeholder="0.00" step="0.01" min="0" required
                                       value="<?php echo isset($price) ? $price : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description: *</label>
                            <textarea id="description" name="description" class="form-control" rows="4" 
                                     placeholder="Describe your product in detail..." required><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="category_id">Category:</label>
                                <select id="category_id" name="category_id" class="form-control">
                                    <option value="">Select Category</option>
                                    <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                                        <option value="<?php echo $category['id']; ?>" 
                                                <?php echo (isset($category_id) && $category_id == $category['id']) ? 'selected' : ''; ?>>
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
                                                <?php echo (isset($brand_id) && $brand_id == $brand['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($brand['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="stock_quantity">Stock Quantity: *</label>
                                <input type="number" id="stock_quantity" name="stock_quantity" class="form-control" 
                                       placeholder="0" min="0" required
                                       value="<?php echo isset($stock_quantity) ? $stock_quantity : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="image_url">Product Image URL:</label>
                            <input type="url" id="image_url" name="image_url" class="form-control" 
                                   placeholder="https://example.com/image.jpg"
                                   value="<?php echo isset($image_url) ? htmlspecialchars($image_url) : ''; ?>">
                            <small style="color: var(--text-light); margin-top: 0.5rem; display: block;">
                                Use a direct link to an image (JPG, PNG, etc.). Recommended size: 300x300px
                            </small>
                        </div>
                        
                        <!-- Image Preview -->
                        <div class="form-group">
                            <label>Image Preview:</label>
                            <div class="image-preview" id="imagePreview">
                                <span>Image preview will appear here</span>
                            </div>
                        </div>
                        
                        <div style="text-align: center; margin-top: 3rem;">
                            <button type="submit" class="btn btn-success" style="padding: 1rem 3rem; font-size: 1.2rem; margin-right: 1rem;">
                                ➕ Add Product
                            </button>
                            <a href="products.php" class="btn" style="padding: 1rem 3rem; font-size: 1.2rem;">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sample Product URLs -->
            <div class="card" style="margin-top: 2rem;">
                <div class="card-header">
                    <h3>Sample Product Images (Click to Use)</h3>
                </div>
                <div class="card-body">
                    <p style="color: var(--text-light); margin-bottom: 2rem;">
                        Click on any image below to use it for your product:
                    </p>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                        <?php 
                        $sample_images = [
                            'https://images.pexels.com/photos/404280/pexels-photo-404280.jpeg' => 'Smartphone',
                            'https://images.pexels.com/photos/788946/pexels-photo-788946.jpeg' => 'iPhone',
                            'https://images.pexels.com/photos/2529148/pexels-photo-2529148.jpeg' => 'Running Shoes',
                            'https://images.pexels.com/photos/1464625/pexels-photo-1464625.jpeg' => 'Sports Shoes',
                            'https://images.pexels.com/photos/3394650/pexels-photo-3394650.jpeg' => 'Headphones',
                            'https://images.pexels.com/photos/1201996/pexels-photo-1201996.jpeg' => 'Smart TV',
                            'https://images.pexels.com/photos/996329/pexels-photo-996329.jpeg' => 'T-Shirt',
                            'https://images.pexels.com/photos/1598505/pexels-photo-1598505.jpeg' => 'Jeans'
                        ];
                        
                        foreach ($sample_images as $url => $name): ?>
                            <div style="text-align: center; cursor: pointer; padding: 1rem; border: 2px solid var(--pale-blue); border-radius: 10px; transition: all 0.3s ease;" 
                                 onclick="useImage('<?php echo $url; ?>')" 
                                 onmouseover="this.style.borderColor='var(--secondary-blue)'" 
                                 onmouseout="this.style.borderColor='var(--pale-blue)'">
                                <img src="<?php echo $url; ?>" alt="<?php echo $name; ?>" 
                                     style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px; margin-bottom: 0.5rem;">
                                <div style="font-size: 0.9rem; color: var(--text-dark); font-weight: 600;"><?php echo $name; ?></div>
                            </div>
                        <?php endforeach; ?>
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
            
            if (url) {
                preview.innerHTML = `<img src="${url}" alt="Preview" style="width: 200px; height: 200px; object-fit: cover; border-radius: 12px;">`;
            } else {
                preview.innerHTML = '<span>Image preview will appear here</span>';
            }
        });
        
        // Function to use sample image
        function useImage(url) {
            document.getElementById('image_url').value = url;
            document.getElementById('image_url').dispatchEvent(new Event('input'));
        }
        
        // Auto-resize textarea
        document.getElementById('description').addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    </script>
</body>
</html>