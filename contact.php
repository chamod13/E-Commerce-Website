<?php
require_once 'config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message_text = trim($_POST['message']);
    
    if (empty($name) || empty($email) || empty($message_text)) {
        $message = '<div class="alert alert-error">Please fill in all required fields.</div>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = '<div class="alert alert-error">Please enter a valid email address.</div>';
    } else {
        $conn = getConnection();
        $insert_query = "INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)";
        $insert_stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($insert_stmt, 'ssss', $name, $email, $subject, $message_text);
        
        if (mysqli_stmt_execute($insert_stmt)) {
            $message = '<div class="alert alert-success">Thank you for your message! We will get back to you within 24 hours.</div>';
        } else {
            $message = '<div class="alert alert-error">Error sending message. Please try again.</div>';
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
    <title>Contact Us - ShopFlow</title>
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
                    <li><a href="faq.php">FAQ</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <!-- Hero Section -->
        <section style="background: linear-gradient(135deg, var(--light-blue) 0%, var(--lighter-blue) 100%); color: white; padding: 4rem 2rem; border-radius: 20px; text-align: center; margin: 2rem 0;">
            <h1 style="font-size: 3.5rem; margin-bottom: 1rem; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">Contact Us</h1>
            <p style="font-size: 1.3rem; opacity: 0.9; max-width: 600px; margin: 0 auto;">We're here to help! Get in touch with our customer support team</p>
        </section>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; margin: 3rem 0;" class="contact-grid">
            <!-- Contact Form -->
            <div class="card">
                <div class="card-header">
                    <h2>Send us a Message</h2>
                </div>
                <div class="card-body">
                    <?php echo $message; ?>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label for="name">Full Name: *</label>
                            <input type="text" id="name" name="name" class="form-control" 
                                   placeholder="Enter your full name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address: *</label>
                            <input type="email" id="email" name="email" class="form-control" 
                                   placeholder="Enter your email address" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Subject:</label>
                            <input type="text" id="subject" name="subject" class="form-control" 
                                   placeholder="What is this about?">
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message: *</label>
                            <textarea id="message" name="message" class="form-control" rows="6" 
                                     placeholder="Tell us how we can help you..." required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100" style="padding: 1rem; font-size: 1.1rem;">
                            Send Message
                        </button>
                    </form>
                </div>
            </div>

            <!-- Contact Information -->
            <div>
                <div class="card" style="margin-bottom: 2rem;">
                    <div class="card-header">
                        <h2>Get in Touch</h2>
                    </div>
                    <div class="card-body">
                        <div style="display: flex; flex-direction: column; gap: 2rem;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--secondary-blue) 0%, var(--accent-blue) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                                    📧
                                </div>
                                <div>
                                    <h4 style="color: var(--primary-dark); margin-bottom: 0.5rem;">Email Us</h4>
                                    <p style="color: var(--text-light);">support@shopflow.com</p>
                                </div>
                            </div>
                            
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--success) 0%, #2ecc71 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                                    📞
                                </div>
                                <div>
                                    <h4 style="color: var(--primary-dark); margin-bottom: 0.5rem;">Call Us</h4>
                                    <p style="color: var(--text-light);">+91 9876543210</p>
                                </div>
                            </div>
                            
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--warning) 0%, #f1c40f 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                                    🏢
                                </div>
                                <div>
                                    <h4 style="color: var(--primary-dark); margin-bottom: 0.5rem;">Visit Us</h4>
                                    <p style="color: var(--text-light); line-height: 1.5;">
                                        123 Commerce Street<br>
                                        Business District<br>
                                        Mumbai, Maharashtra 400001
                                    </p>
                                </div>
                            </div>
                            
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--accent-blue) 0%, var(--light-blue) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                                    ⏰
                                </div>
                                <div>
                                    <h4 style="color: var(--primary-dark); margin-bottom: 0.5rem;">Business Hours</h4>
                                    <p style="color: var(--text-light); line-height: 1.5;">
                                        Monday - Friday: 9:00 AM - 7:00 PM<br>
                                        Saturday: 10:00 AM - 5:00 PM<br>
                                        Sunday: Closed
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2>Quick Help</h2>
                    </div>
                    <div class="card-body">
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <a href="faq.php" class="btn" style="background: var(--lightest-blue); color: var(--primary-dark); text-align: left; padding: 1rem;">
                                <strong>📚 Frequently Asked Questions</strong><br>
                                <small style="opacity: 0.8;">Find answers to common questions</small>
                            </a>
                            
                            <a href="order_history.php" class="btn" style="background: var(--lightest-blue); color: var(--primary-dark); text-align: left; padding: 1rem;">
                                <strong>📦 Track Your Order</strong><br>
                                <small style="opacity: 0.8;">Check your order status and history</small>
                            </a>
                            
                            <a href="index.php" class="btn" style="background: var(--lightest-blue); color: var(--primary-dark); text-align: left; padding: 1rem;">
                                <strong>🛍️ Return to Shopping</strong><br>
                                <small style="opacity: 0.8;">Continue browsing our products</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <section style="margin: 4rem 0;">
            <h2 style="text-align: center; margin-bottom: 3rem; font-size: 2.5rem; color: var(--primary-dark);">
                Why Choose ShopFlow Support?
            </h2>
            <div class="grid grid-3">
                <div class="card">
                    <div class="card-body" style="text-align: center;">
                        <div style="font-size: 3rem; margin-bottom: 1.5rem;">⚡</div>
                        <h3 style="color: var(--primary-dark); margin-bottom: 1rem;">Fast Response</h3>
                        <p style="color: var(--text-light); line-height: 1.6;">
                            We respond to all inquiries within 2 hours during business hours.
                        </p>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body" style="text-align: center;">
                        <div style="font-size: 3rem; margin-bottom: 1.5rem;">👥</div>
                        <h3 style="color: var(--primary-dark); margin-bottom: 1rem;">Expert Team</h3>
                        <p style="color: var(--text-light); line-height: 1.6;">
                            Our knowledgeable team is ready to help with any questions or concerns.
                        </p>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body" style="text-align: center;">
                        <div style="font-size: 3rem; margin-bottom: 1.5rem;">💯</div>
                        <h3 style="color: var(--primary-dark); margin-bottom: 1rem;">100% Satisfaction</h3>
                        <p style="color: var(--text-light); line-height: 1.6;">
                            We're committed to ensuring you have the best shopping experience.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 ShopFlow. All rights reserved. | Premium E-commerce Experience</p>
        </div>
    </footer>

    <style>
        @media (max-width: 768px) {
            .contact-grid {
                grid-template-columns: 1fr !important;
                gap: 2rem !important;
            }
        }
    </style>
</body>
</html>