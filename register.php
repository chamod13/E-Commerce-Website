<?php
require_once 'config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    if (empty($name) || empty($email) || empty($password)) {
        $message = '<div class="alert alert-error">Please fill in all required fields.</div>';
    } elseif ($password !== $confirm_password) {
        $message = '<div class="alert alert-error">Passwords do not match.</div>';
    } else {
        $conn = getConnection();
        
        // Check if email already exists
        $check_query = "SELECT id FROM users WHERE email = ?";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, 's', $email);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            $message = '<div class="alert alert-error">An account with this email already exists.</div>';
        } else {
            // Insert new user
            $insert_query = "INSERT INTO users (name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)";
            $insert_stmt = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($insert_stmt, 'sssss', $name, $email, $password, $phone, $address);
            
            if (mysqli_stmt_execute($insert_stmt)) {
                $message = '<div class="alert alert-success">Account created successfully! You can now <a href="login.php">login</a>.</div>';
            } else {
                $message = '<div class="alert alert-error">Error creating account. Please try again.</div>';
            }
        }
        
        mysqli_close($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - ShopFlow</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Join ShopFlow</h2>
                <p>Create your account to start shopping</p>
            </div>
            
            <div class="auth-body">
                <?php echo $message; ?>
                
                <form method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="name">Full Name: *</label>
                        <input type="text" id="name" name="name" class="form-control" 
                               placeholder="Enter your full name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address: *</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               placeholder="Enter your email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number:</label>
                        <input type="tel" id="phone" name="phone" class="form-control" 
                               placeholder="Enter your phone number">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password: *</label>
                        <input type="password" id="password" name="password" class="form-control" 
                               placeholder="Create a password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password: *</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                               placeholder="Confirm your password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address:</label>
                        <textarea id="address" name="address" class="form-control" rows="3" 
                                  placeholder="Enter your address"></textarea>
                    </div>
                    
                    <button type="submit" class="auth-btn">Create Account</button>
                </form>
                
                <div class="auth-links">
                    <p>Already have an account? <a href="login.php">Sign In</a></p>
                    <p><a href="index.php">← Back to Home</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>