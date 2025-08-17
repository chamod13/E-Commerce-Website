<?php
require_once '../config.php';
requireAdmin();

$conn = getConnection();
$message = '';

// Handle message deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $message_id = (int)$_GET['delete'];
    $delete_query = "DELETE FROM contact_messages WHERE id = ?";
    $delete_stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($delete_stmt, 'i', $message_id);
    
    if (mysqli_stmt_execute($delete_stmt)) {
        $message = '<div class="alert alert-success">Message deleted successfully!</div>';
    } else {
        $message = '<div class="alert alert-error">Error deleting message.</div>';
    }
}

// Get all contact messages
$messages_query = "SELECT * FROM contact_messages ORDER BY created_at DESC";
$messages_result = mysqli_query($conn, $messages_query);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Messages - ShopFlow Admin</title>
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
                    <li><a href="../profile.php">Profile</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="admin-container">
        <div class="admin-header">
            <div class="admin-header-content">
                <h1 class="admin-title">Customer Messages</h1>
                <p class="admin-subtitle">View and manage customer inquiries</p>
            </div>
        </div>

        <div class="container">
            <!-- Admin Navigation -->
            <nav class="admin-nav">
                <ul class="admin-nav-list">
                    <li class="admin-nav-item"><a href="dashboard.php">📊 Dashboard</a></li>
                    <li class="admin-nav-item"><a href="products.php">📦 Products</a></li>
                    <li class="admin-nav-item"><a href="orders.php">🛒 Orders</a></li>
                    <li class="admin-nav-item"><a href="users.php">👥 Users</a></li>
                    <li class="admin-nav-item"><a href="messages.php" class="active">💬 Messages</a></li>
                </ul>
            </nav>

            <?php echo $message; ?>

            <!-- Messages List -->
            <?php if (mysqli_num_rows($messages_result) > 0): ?>
                <div class="messages-container">
                    <?php while ($msg = mysqli_fetch_assoc($messages_result)): ?>
                        <div class="card" style="margin-bottom: 2rem;">
                            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                                <div>
                                    <h3 style="margin-bottom: 0.5rem;">
                                        <?php echo !empty($msg['subject']) ? htmlspecialchars($msg['subject']) : 'No Subject'; ?>
                                    </h3>
                                    <div style="display: flex; align-items: center; gap: 2rem; flex-wrap: wrap;">
                                        <div>
                                            <strong>From:</strong> <?php echo htmlspecialchars($msg['name']); ?>
                                        </div>
                                        <div>
                                            <strong>Email:</strong> 
                                            <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>" 
                                               style="color: white; text-decoration: none;">
                                                <?php echo htmlspecialchars($msg['email']); ?>
                                            </a>
                                        </div>
                                        <div>
                                            <strong>Date:</strong> <?php echo date('M j, Y \a\t g:i A', strtotime($msg['created_at'])); ?>
                                        </div>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>?subject=Re: <?php echo urlencode($msg['subject']); ?>" 
                                       class="btn" style="background: var(--success); color: white; padding: 0.5rem 1rem;">
                                        📧 Reply
                                    </a>
                                    <a href="?delete=<?php echo $msg['id']; ?>" 
                                       class="btn" style="background: var(--error); color: white; padding: 0.5rem 1rem;"
                                       onclick="return confirm('Are you sure you want to delete this message?')">
                                        🗑️ Delete
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div style="background: var(--very-light-blue); padding: 2rem; border-radius: 12px; border-left: 4px solid var(--accent-blue);">
                                    <p style="color: var(--text-dark); line-height: 1.7; font-size: 1.05rem; margin: 0; white-space: pre-wrap;">
                                        <?php echo htmlspecialchars($msg['message']); ?>
                                    </p>
                                </div>
                                
                                <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--pale-blue); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                                    <div style="color: var(--text-light); font-size: 0.9rem;">
                                        Message ID: #<?php echo $msg['id']; ?> • 
                                        Received: <?php echo date('M j, Y \a\t g:i A', strtotime($msg['created_at'])); ?>
                                    </div>
                                    <div>
                                        <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>?subject=Re: <?php echo urlencode($msg['subject'] ?: 'Your inquiry'); ?>&body=Hi <?php echo urlencode($msg['name']); ?>,%0A%0AThank you for contacting ShopFlow. " 
                                           class="btn btn-primary">
                                            Send Reply
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 4rem; background: var(--white); border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                    <div style="font-size: 6rem; color: var(--pale-blue); margin-bottom: 2rem;">💬</div>
                    <h2 style="color: var(--text-light); font-size: 2.5rem; margin-bottom: 1rem;">No Messages Yet</h2>
                    <p style="color: var(--text-light); font-size: 1.2rem; margin-bottom: 2rem;">
                        Customer messages will appear here when they contact you through the contact form.
                    </p>
                    <a href="../contact.php" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.2rem;">
                        View Contact Form
                    </a>
                </div>
            <?php endif; ?>

            <!-- Message Statistics -->
            <div style="margin-top: 3rem;">
                <h2 style="text-align: center; margin-bottom: 2rem; color: var(--primary-dark); font-size: 2.2rem;">Message Statistics</h2>
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo mysqli_num_rows($messages_result); ?></div>
                        <div class="stat-label">Total Messages</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-number">
                            <?php 
                            mysqli_data_seek($messages_result, 0);
                            $today_messages = 0;
                            while ($row = mysqli_fetch_assoc($messages_result)) {
                                if (date('Y-m-d', strtotime($row['created_at'])) == date('Y-m-d')) {
                                    $today_messages++;
                                }
                            }
                            echo $today_messages;
                            ?>
                        </div>
                        <div class="stat-label">Today's Messages</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-number">
                            <?php 
                            mysqli_data_seek($messages_result, 0);
                            $week_messages = 0;
                            $week_ago = strtotime('-7 days');
                            while ($row = mysqli_fetch_assoc($messages_result)) {
                                if (strtotime($row['created_at']) >= $week_ago) {
                                    $week_messages++;
                                }
                            }
                            echo $week_messages;
                            ?>
                        </div>
                        <div class="stat-label">This Week</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-number">
                            <?php 
                            mysqli_data_seek($messages_result, 0);
                            $unique_customers = [];
                            while ($row = mysqli_fetch_assoc($messages_result)) {
                                $unique_customers[$row['email']] = true;
                            }
                            echo count($unique_customers);
                            ?>
                        </div>
                        <div class="stat-label">Unique Customers</div>
                    </div>
                </div>
            </div>

            <!-- Quick Response Templates -->
            <div class="card" style="margin-top: 3rem;">
                <div class="card-header">
                    <h3>Quick Response Templates</h3>
                </div>
                <div class="card-body">
                    <p style="color: var(--text-light); margin-bottom: 2rem;">
                        Use these templates to quickly respond to common customer inquiries:
                    </p>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                        <div style="background: var(--very-light-blue); padding: 1.5rem; border-radius: 12px;">
                            <h4 style="color: var(--primary-dark); margin-bottom: 1rem;">📦 Order Status Inquiry</h4>
                            <p style="color: var(--text-dark); font-size: 0.9rem; line-height: 1.5; margin-bottom: 1rem;">
                                "Thank you for contacting ShopFlow. I'd be happy to help you track your order. Please provide your order number and I'll get back to you with the current status and tracking information."
                            </p>
                            <button onclick="copyTemplate(this)" class="btn btn-primary btn-small">Copy Template</button>
                        </div>
                        
                        <div style="background: var(--very-light-blue); padding: 1.5rem; border-radius: 12px;">
                            <h4 style="color: var(--primary-dark); margin-bottom: 1rem;">🔄 Return/Exchange Request</h4>
                            <p style="color: var(--text-dark); font-size: 0.9rem; line-height: 1.5; margin-bottom: 1rem;">
                                "Thank you for reaching out. We offer a 30-day return policy for most items. Please provide your order number and reason for return, and I'll send you a return shipping label and instructions."
                            </p>
                            <button onclick="copyTemplate(this)" class="btn btn-primary btn-small">Copy Template</button>
                        </div>
                        
                        <div style="background: var(--very-light-blue); padding: 1.5rem; border-radius: 12px;">
                            <h4 style="color: var(--primary-dark); margin-bottom: 1rem;">❓ General Inquiry</h4>
                            <p style="color: var(--text-dark); font-size: 0.9rem; line-height: 1.5; margin-bottom: 1rem;">
                                "Thank you for contacting ShopFlow! We appreciate your interest in our products. I'll be happy to help you with your inquiry. Please allow 24-48 hours for a detailed response."
                            </p>
                            <button onclick="copyTemplate(this)" class="btn btn-primary btn-small">Copy Template</button>
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
        function copyTemplate(button) {
            const template = button.parentElement.querySelector('p').textContent;
            navigator.clipboard.writeText(template).then(function() {
                const originalText = button.textContent;
                button.textContent = 'Copied!';
                button.style.background = 'var(--success)';
                setTimeout(function() {
                    button.textContent = originalText;
                    button.style.background = '';
                }, 2000);
            });
        }
    </script>
</body>
</html>