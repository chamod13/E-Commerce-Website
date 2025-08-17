<?php
require_once 'config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    if (empty($email) || empty($password)) {
        $message = '<div class="alert alert-error">Please fill in all fields.</div>';
    } else {
        $conn = getConnection();
        $query = "SELECT id, name, email, password, is_admin FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($user = mysqli_fetch_assoc($result)) {
            // Simple password check (no hashing as requested)
            if ($password == $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['is_admin'] = $user['is_admin'];
                
                header('Location: index.php');
                exit();
            } else {
                $message = '<div class="alert alert-error">Invalid email or password.</div>';
            }
        } else {
            $message = '<div class="alert alert-error">Invalid email or password.</div>';
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
    <title>Login - ShopFlow</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Welcome Back</h2>
                <p>Sign in to your ShopFlow account</p>
            </div>
            
            <div class="auth-body">
                <?php echo $message; ?>
                
                <form method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="email">Email Address:</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               placeholder="Enter your email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" class="form-control" 
                               placeholder="Enter your password" required>
                    </div>
                    
                    <button type="submit" class="auth-btn">Sign In</button>
                </form>
                
                <div class="auth-links">
                    <p>Don't have an account? <a href="register.php">Create Account</a></p>
                    <p><a href="index.php">← Back to Home</a></p>
                </div>
                
                <div style="margin-top: 2rem; padding: 1.5rem; background: var(--very-light-blue); border-radius: 12px; border-left: 4px solid var(--accent-blue);">
                    <h4 style="color: var(--primary-dark); margin-bottom: 1rem;">Demo Accounts:</h4>
                    <div style="font-size: 0.9rem; color: var(--text-dark);">
                        <p><strong>Admin:</strong> admin@ecommerce.com / admin123</p>
                        <p><strong>User:</strong> john@example.com / user123</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>