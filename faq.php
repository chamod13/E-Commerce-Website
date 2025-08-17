<?php require_once 'config.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - ShopFlow</title>
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
        <section style="background: linear-gradient(135deg, var(--accent-blue) 0%, var(--light-blue) 100%); color: white; padding: 4rem 2rem; border-radius: 20px; text-align: center; margin: 2rem 0;">
            <h1 style="font-size: 3.5rem; margin-bottom: 1rem; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">Frequently Asked Questions</h1>
            <p style="font-size: 1.3rem; opacity: 0.9; max-width: 600px; margin: 0 auto;">Find quick answers to common questions about shopping with ShopFlow</p>
        </section>

        <div style="max-width: 900px; margin: 0 auto;">
            <!-- FAQ Categories -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin: 3rem 0;">
                <div class="card" style="text-align: center; cursor: pointer;" onclick="scrollToSection('ordering')">
                    <div class="card-body">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">🛒</div>
                        <h3 style="color: var(--primary-dark);">Ordering</h3>
                    </div>
                </div>
                <div class="card" style="text-align: center; cursor: pointer;" onclick="scrollToSection('payment')">
                    <div class="card-body">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">💳</div>
                        <h3 style="color: var(--primary-dark);">Payment</h3>
                    </div>
                </div>
                <div class="card" style="text-align: center; cursor: pointer;" onclick="scrollToSection('shipping')">
                    <div class="card-body">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">🚚</div>
                        <h3 style="color: var(--primary-dark);">Shipping</h3>
                    </div>
                </div>
                <div class="card" style="text-align: center; cursor: pointer;" onclick="scrollToSection('returns')">
                    <div class="card-body">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">↩️</div>
                        <h3 style="color: var(--primary-dark);">Returns</h3>
                    </div>
                </div>
            </div>

            <!-- Ordering Questions -->
            <section id="ordering" class="faq-section">
                <h2 style="color: var(--primary-dark); margin: 3rem 0 2rem; font-size: 2.2rem; text-align: center;">🛒 Ordering Questions</h2>
                
                <div class="faq-item card" style="margin-bottom: 1.5rem;">
                    <div class="faq-question" style="padding: 1.5rem; cursor: pointer; display: flex; justify-content: space-between; align-items: center;" onclick="toggleFAQ(this)">
                        <h3 style="color: var(--primary-dark); margin: 0;">How do I place an order?</h3>
                        <span class="faq-toggle" style="font-size: 1.5rem; color: var(--secondary-blue);">+</span>
                    </div>
                    <div class="faq-answer" style="padding: 0 1.5rem; max-height: 0; overflow: hidden; transition: all 0.3s ease;">
                        <div style="padding-bottom: 1.5rem; color: var(--text-dark); line-height: 1.6;">
                            <p>Placing an order is simple:</p>
                            <ol style="margin-left: 1.5rem; margin-top: 1rem;">
                                <li>Browse our products and click on items you want to purchase</li>
                                <li>Click "Add to Cart" for each product</li>
                                <li>Go to your shopping cart and review your items</li>
                                <li>Click "Proceed to Checkout"</li>
                                <li>Enter your shipping address and select payment method</li>
                                <li>Complete your order and you'll receive a confirmation</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="faq-item card" style="margin-bottom: 1.5rem;">
                    <div class="faq-question" style="padding: 1.5rem; cursor: pointer; display: flex; justify-content: space-between; align-items: center;" onclick="toggleFAQ(this)">
                        <h3 style="color: var(--primary-dark); margin: 0;">Can I modify or cancel my order?</h3>
                        <span class="faq-toggle" style="font-size: 1.5rem; color: var(--secondary-blue);">+</span>
                    </div>
                    <div class="faq-answer" style="padding: 0 1.5rem; max-height: 0; overflow: hidden; transition: all 0.3s ease;">
                        <div style="padding-bottom: 1.5rem; color: var(--text-dark); line-height: 1.6;">
                            <p>Orders can be modified or cancelled within 1 hour of placement, provided they haven't been processed yet. Contact our customer support immediately if you need to make changes to your order.</p>
                        </div>
                    </div>
                </div>

                <div class="faq-item card" style="margin-bottom: 1.5rem;">
                    <div class="faq-question" style="padding: 1.5rem; cursor: pointer; display: flex; justify-content: space-between; align-items: center;" onclick="toggleFAQ(this)">
                        <h3 style="color: var(--primary-dark); margin: 0;">Do I need to create an account to shop?</h3>
                        <span class="faq-toggle" style="font-size: 1.5rem; color: var(--secondary-blue);">+</span>
                    </div>
                    <div class="faq-answer" style="padding: 0 1.5rem; max-height: 0; overflow: hidden; transition: all 0.3s ease;">
                        <div style="padding-bottom: 1.5rem; color: var(--text-dark); line-height: 1.6;">
                            <p>Yes, you need to create an account to place orders. Creating an account allows you to:</p>
                            <ul style="margin-left: 1.5rem; margin-top: 1rem;">
                                <li>Track your orders</li>
                                <li>Save your shipping addresses</li>
                                <li>View order history</li>
                                <li>Leave product reviews</li>
                                <li>Receive exclusive offers</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Payment Questions -->
            <section id="payment" class="faq-section">
                <h2 style="color: var(--primary-dark); margin: 3rem 0 2rem; font-size: 2.2rem; text-align: center;">💳 Payment Questions</h2>
                
                <div class="faq-item card" style="margin-bottom: 1.5rem;">
                    <div class="faq-question" style="padding: 1.5rem; cursor: pointer; display: flex; justify-content: space-between; align-items: center;" onclick="toggleFAQ(this)">
                        <h3 style="color: var(--primary-dark); margin: 0;">What payment methods do you accept?</h3>
                        <span class="faq-toggle" style="font-size: 1.5rem; color: var(--secondary-blue);">+</span>
                    </div>
                    <div class="faq-answer" style="padding: 0 1.5rem; max-height: 0; overflow: hidden; transition: all 0.3s ease;">
                        <div style="padding-bottom: 1.5rem; color: var(--text-dark); line-height: 1.6;">
                            <p>We accept the following payment methods:</p>
                            <ul style="margin-left: 1.5rem; margin-top: 1rem;">
                                <li><strong>Credit/Debit Cards</strong> - Visa, MasterCard, RuPay</li>
                                <li><strong>UPI</strong> - PhonePe, Google Pay, Paytm</li>
                                <li><strong>Digital Wallets</strong> - PayPal, Paytm Wallet</li>
                                <li><strong>Cash on Delivery</strong> - Available for orders under ₹50,000</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="faq-item card" style="margin-bottom: 1.5rem;">
                    <div class="faq-question" style="padding: 1.5rem; cursor: pointer; display: flex; justify-content: space-between; align-items: center;" onclick="toggleFAQ(this)">
                        <h3 style="color: var(--primary-dark); margin: 0;">Is my payment information secure?</h3>
                        <span class="faq-toggle" style="font-size: 1.5rem; color: var(--secondary-blue);">+</span>
                    </div>
                    <div class="faq-answer" style="padding: 0 1.5rem; max-height: 0; overflow: hidden; transition: all 0.3s ease;">
                        <div style="padding-bottom: 1.5rem; color: var(--text-dark); line-height: 1.6;">
                            <p>Yes, absolutely! We use industry-standard SSL encryption to protect your payment information. We never store your complete card details on our servers, and all transactions are processed through secure payment gateways.</p>
                        </div>
                    </div>
                </div>

                <div class="faq-item card" style="margin-bottom: 1.5rem;">
                    <div class="faq-question" style="padding: 1.5rem; cursor: pointer; display: flex; justify-content: space-between; align-items: center;" onclick="toggleFAQ(this)">
                        <h3 style="color: var(--primary-dark); margin: 0;">Why was my payment declined?</h3>
                        <span class="faq-toggle" style="font-size: 1.5rem; color: var(--secondary-blue);">+</span>
                    </div>
                    <div class="faq-answer" style="padding: 0 1.5rem; max-height: 0; overflow: hidden; transition: all 0.3s ease;">
                        <div style="padding-bottom: 1.5rem; color: var(--text-dark); line-height: 1.6;">
                            <p>Payment declines can happen for several reasons:</p>
                            <ul style="margin-left: 1.5rem; margin-top: 1rem;">
                                <li>Insufficient funds in your account</li>
                                <li>Incorrect card details entered</li>
                                <li>Your bank's security settings</li>
                                <li>Card expiry or being blocked</li>
                            </ul>
                            <p style="margin-top: 1rem;">If your payment is declined, please try again or contact your bank.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Shipping Questions -->
            <section id="shipping" class="faq-section">
                <h2 style="color: var(--primary-dark); margin: 3rem 0 2rem; font-size: 2.2rem; text-align: center;">🚚 Shipping Questions</h2>
                
                <div class="faq-item card" style="margin-bottom: 1.5rem;">
                    <div class="faq-question" style="padding: 1.5rem; cursor: pointer; display: flex; justify-content: space-between; align-items: center;" onclick="toggleFAQ(this)">
                        <h3 style="color: var(--primary-dark); margin: 0;">How much does shipping cost?</h3>
                        <span class="faq-toggle" style="font-size: 1.5rem; color: var(--secondary-blue);">+</span>
                    </div>
                    <div class="faq-answer" style="padding: 0 1.5rem; max-height: 0; overflow: hidden; transition: all 0.3s ease;">
                        <div style="padding-bottom: 1.5rem; color: var(--text-dark); line-height: 1.6;">
                            <p><strong>Great news!</strong> We offer <strong>FREE shipping</strong> on all orders across India. No minimum order value required!</p>
                        </div>
                    </div>
                </div>

                <div class="faq-item card" style="margin-bottom: 1.5rem;">
                    <div class="faq-question" style="padding: 1.5rem; cursor: pointer; display: flex; justify-content: space-between; align-items: center;" onclick="toggleFAQ(this)">
                        <h3 style="color: var(--primary-dark); margin: 0;">How long does delivery take?</h3>
                        <span class="faq-toggle" style="font-size: 1.5rem; color: var(--secondary-blue);">+</span>
                    </div>
                    <div class="faq-answer" style="padding: 0 1.5rem; max-height: 0; overflow: hidden; transition: all 0.3s ease;">
                        <div style="padding-bottom: 1.5rem; color: var(--text-dark); line-height: 1.6;">
                            <p>Delivery times vary by location:</p>
                            <ul style="margin-left: 1.5rem; margin-top: 1rem;">
                                <li><strong>Metro Cities</strong> - 1-2 business days</li>
                                <li><strong>Major Cities</strong> - 2-3 business days</li>
                                <li><strong>Other Areas</strong> - 3-5 business days</li>
                                <li><strong>Remote Areas</strong> - 5-7 business days</li>
                            </ul>
                            <p style="margin-top: 1rem;">Orders are processed within 24 hours on business days.</p>
                        </div>
                    </div>
                </div>

                <div class="faq-item card" style="margin-bottom: 1.5rem;">
                    <div class="faq-question" style="padding: 1.5rem; cursor: pointer; display: flex; justify-content: space-between; align-items: center;" onclick="toggleFAQ(this)">
                        <h3 style="color: var(--primary-dark); margin: 0;">How can I track my order?</h3>
                        <span class="faq-toggle" style="font-size: 1.5rem; color: var(--secondary-blue);">+</span>
                    </div>
                    <div class="faq-answer" style="padding: 0 1.5rem; max-height: 0; overflow: hidden; transition: all 0.3s ease;">
                        <div style="padding-bottom: 1.5rem; color: var(--text-dark); line-height: 1.6;">
                            <p>You can track your order easily:</p>
                            <ol style="margin-left: 1.5rem; margin-top: 1rem;">
                                <li>Log into your account</li>
                                <li>Go to "Order History"</li>
                                <li>Click on the order you want to track</li>
                                <li>View detailed order status and tracking information</li>
                            </ol>
                            <p style="margin-top: 1rem;">You'll also receive email updates at each stage of delivery.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Returns Questions -->
            <section id="returns" class="faq-section">
                <h2 style="color: var(--primary-dark); margin: 3rem 0 2rem; font-size: 2.2rem; text-align: center;">↩️ Returns & Exchanges</h2>
                
                <div class="faq-item card" style="margin-bottom: 1.5rem;">
                    <div class="faq-question" style="padding: 1.5rem; cursor: pointer; display: flex; justify-content: space-between; align-items: center;" onclick="toggleFAQ(this)">
                        <h3 style="color: var(--primary-dark); margin: 0;">What is your return policy?</h3>
                        <span class="faq-toggle" style="font-size: 1.5rem; color: var(--secondary-blue);">+</span>
                    </div>
                    <div class="faq-answer" style="padding: 0 1.5rem; max-height: 0; overflow: hidden; transition: all 0.3s ease;">
                        <div style="padding-bottom: 1.5rem; color: var(--text-dark); line-height: 1.6;">
                            <p>We offer a <strong>30-day return policy</strong> for most items:</p>
                            <ul style="margin-left: 1.5rem; margin-top: 1rem;">
                                <li>Items must be in original condition with tags</li>
                                <li>Original packaging required</li>
                                <li>Return shipping is free for defective items</li>
                                <li>Refunds processed within 5-7 business days</li>
                            </ul>
                            <p style="margin-top: 1rem;"><strong>Note:</strong> Some items like undergarments, food items, and personalized products cannot be returned.</p>
                        </div>
                    </div>
                </div>

                <div class="faq-item card" style="margin-bottom: 1.5rem;">
                    <div class="faq-question" style="padding: 1.5rem; cursor: pointer; display: flex; justify-content: space-between; align-items: center;" onclick="toggleFAQ(this)">
                        <h3 style="color: var(--primary-dark); margin: 0;">How do I initiate a return?</h3>
                        <span class="faq-toggle" style="font-size: 1.5rem; color: var(--secondary-blue);">+</span>
                    </div>
                    <div class="faq-answer" style="padding: 0 1.5rem; max-height: 0; overflow: hidden; transition: all 0.3s ease;">
                        <div style="padding-bottom: 1.5rem; color: var(--text-dark); line-height: 1.6;">
                            <p>To return an item:</p>
                            <ol style="margin-left: 1.5rem; margin-top: 1rem;">
                                <li>Contact our customer support with your order number</li>
                                <li>Provide reason for return</li>
                                <li>We'll email you a return shipping label</li>
                                <li>Package the item securely with original packaging</li>
                                <li>Drop it off at any authorized shipping location</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="faq-item card" style="margin-bottom: 1.5rem;">
                    <div class="faq-question" style="padding: 1.5rem; cursor: pointer; display: flex; justify-content: space-between; align-items: center;" onclick="toggleFAQ(this)">
                        <h3 style="color: var(--primary-dark); margin: 0;">Can I exchange an item for a different size or color?</h3>
                        <span class="faq-toggle" style="font-size: 1.5rem; color: var(--secondary-blue);">+</span>
                    </div>
                    <div class="faq-answer" style="padding: 0 1.5rem; max-height: 0; overflow: hidden; transition: all 0.3s ease;">
                        <div style="padding-bottom: 1.5rem; color: var(--text-dark); line-height: 1.6;">
                            <p>Yes! We offer free exchanges for size and color within 30 days of purchase. The exchange process is similar to returns - just let us know what you'd like to exchange it for when you contact customer support.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Still Need Help -->
            <div style="background: linear-gradient(135deg, var(--lightest-blue) 0%, var(--pale-blue) 100%); padding: 3rem; border-radius: 20px; text-align: center; margin: 4rem 0;">
                <h2 style="color: var(--primary-dark); margin-bottom: 1.5rem; font-size: 2rem;">Still Need Help?</h2>
                <p style="color: var(--text-dark); margin-bottom: 2rem; font-size: 1.1rem;">
                    Can't find what you're looking for? Our customer support team is here to help!
                </p>
                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <a href="contact.php" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.1rem;">
                        Contact Support
                    </a>
                    <a href="index.php" class="btn" style="padding: 1rem 2rem; font-size: 1.1rem; background: var(--secondary-blue); color: white;">
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 ShopFlow. All rights reserved. | Premium E-commerce Experience</p>
        </div>
    </footer>

    <script>
        function toggleFAQ(element) {
            const answer = element.nextElementSibling;
            const toggle = element.querySelector('.faq-toggle');
            
            if (answer.style.maxHeight && answer.style.maxHeight !== '0px') {
                answer.style.maxHeight = '0px';
                answer.style.paddingTop = '0';
                toggle.textContent = '+';
                element.style.borderBottom = 'none';
            } else {
                answer.style.maxHeight = answer.scrollHeight + 'px';
                answer.style.paddingTop = '1.5rem';
                toggle.textContent = '−';
                element.style.borderBottom = '1px solid var(--pale-blue)';
            }
        }
        
        function scrollToSection(sectionId) {
            document.getElementById(sectionId).scrollIntoView({ 
                behavior: 'smooth',
                block: 'start'
            });
        }
    </script>
</body>
</html>