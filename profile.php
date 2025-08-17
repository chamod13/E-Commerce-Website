<?php
require_once 'config.php';
requireLogin();

$conn = getConnection();
$message = '';

// Get user details
$user_query = "SELECT * FROM users WHERE id = ?";
$user_stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($user_stmt, 'i', $_SESSION['user_id']);
mysqli_stmt_execute($user_stmt);
$user_result = mysqli_stmt_get_result($user_stmt);
$user = mysqli_fetch_assoc($user_result);

// Get user statistics
$stats_query = "SELECT 
    COUNT(DISTINCT o.id) as total_orders,
    COALESCE(SUM(o.total_amount), 0) as total_spent,
    COUNT(DISTINCT r.id) as total_reviews
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id
    LEFT JOIN reviews r ON u.id = r.user_id
    WHERE u.id = ?";
$stats_stmt = mysqli_prepare($conn, $stats_query);
mysqli_stmt_bind_param($stats_stmt, 'i', $_SESSION['user_id']);
mysqli_stmt_execute($stats_stmt);
$stats_result = mysqli_stmt_get_result($stats_stmt);
$stats = mysqli_fetch_assoc($stats_result);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    if (empty($name) || empty($email)) {
        $message = '<div class="alert alert-error">Name and email are required.</div>';
    } else {
        // Check if email is already taken by another user
        $email_check = "SELECT id FROM users WHERE email = ? AND id != ?";
        $email_stmt = mysqli_prepare($conn, $email_check);
        mysqli_stmt_bind_param($email_stmt, 'si', $email, $_SESSION['user_id']);
        mysqli_stmt_execute($email_stmt);
        $email_result = mysqli_stmt_get_result($email_stmt);
        
        if (mysqli_num_rows($email_result) > 0) {
            $message = '<div class="alert alert-error">This email is already registered to another account.</div>';
        } else {
            // Update basic info
            $update_query = "UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?";
            $update_stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($update_stmt, 'ssssi', $name, $email, $phone, $address, $_SESSION['user_id']);
            
            if (mysqli_stmt_execute($update_stmt)) {
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                
                // Handle password change
                if (!empty($current_password) && !empty($new_password)) {
                    if ($current_password == $user['password']) {
                        if ($new_password == $confirm_password) {
                            $password_query = "UPDATE users SET password = ? WHERE id = ?";
                            $password_stmt = mysqli_prepare($conn, $password_query);
                            mysqli_stmt_bind_param($password_stmt, 'si', $new_password, $_SESSION['user_id']);
                            
                            if (mysqli_stmt_execute($password_stmt)) {
                                $message = '<div class="alert alert-success">Profile and password updated successfully!</div>';
                            } else {
                                $message = '<div class="alert alert-warning">Profile updated but password change failed.</div>';
                            }
                        } else {
                            $message = '<div class="alert alert-warning">Profile updated but new passwords do not match.</div>';
                        }
                    } else {
                        $message = '<div class="alert alert-warning">Profile updated but current password is incorrect.</div>';
                    }
                } else {
                    $message = '<div class="alert alert-success">Profile updated successfully!</div>';
                }
                
                // Refresh user data
                mysqli_stmt_execute($user_stmt);
                $user_result = mysqli_stmt_get_result($user_stmt);
                $user = mysqli_fetch_assoc($user_result);
            } else {
                $message = '<div class="alert alert-error">Error updating profile. Please try again.</div>';
            }
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
    <title>My Profile - ShopFlow</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
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

    <main class="profile-container">
        <div class="card">
            <div class="profile-header">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                </div>
                <h1 class="profile-name"><?php echo htmlspecialchars($user['name']); ?></h1>
                <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            
            <div class="profile-body">
                <?php echo $message; ?>
                
                <!-- User Statistics -->
                <div class="profile-stats">
                    <div class="stat-card">
                        <span class="stat-number"><?php echo $stats['total_orders']; ?></span>
                        <span class="stat-label">Total Orders</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number"><?php echo formatPrice($stats['total_spent']); ?></span>
                        <span class="stat-label">Total Spent</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number"><?php echo $stats['total_reviews']; ?></span>
                        <span class="stat-label">Reviews Given</span>
                    </div>
                </div>
                
                <!-- Profile Form -->
                <form method="POST" class="profile-form">
                    <h3 style="color: var(--primary-dark); margin-bottom: 2rem; font-size: 1.8rem; text-align: center;">
                        Update Profile Information
                    </h3>
                    
                    <div class="form-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                        <div class="form-group">
                            <label for="name">Full Name: *</label>
                            <input type="text" id="name" name="name" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address: *</label>
                            <input type="email" id="email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number:</label>
                            <input type="tel" id="phone" name="phone" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['phone']); ?>">
                        </div>
                        
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label for="address">Address:</label>
                            <textarea id="address" name="address" class="form-control" rows="4"><?php echo htmlspecialchars($user['address']); ?></textarea>
                        </div>
                    </div>
                    
                    <hr style="margin: 3rem 0; border: none; height: 2px; background: var(--pale-blue);">
                    
                    <h4 style="color: var(--primary-dark); margin-bottom: 2rem; font-size: 1.5rem; text-align: center;">
                        Change Password (Optional)
                    </h4>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="current_password">Current Password:</label>
                            <input type="password" id="current_password" name="current_password" class="form-control" 
                                   placeholder="Enter current password">
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">New Password:</label>
                            <input type="password" id="new_password" name="new_password" class="form-control" 
                                   placeholder="Enter new password">
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password:</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                                   placeholder="Confirm new password">
                        </div>
                    </div>
                    
                    <div style="text-align: center; margin-top: 3rem;">
                        <button type="submit" class="auth-btn" style="max-width: 300px;">
                            Update Profile
                        </button>
                    </div>
                </form>
                
                <div style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--pale-blue);">
                    <a href="order_history.php" class="btn btn-primary" style="margin-right: 1rem;">View Order History</a>
                    <a href="index.php" class="btn">Continue Shopping</a>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 ShopFlow. All rights reserved. | Premium E-commerce Experience</p>
        </div>
    </footer>
</body>
</html>