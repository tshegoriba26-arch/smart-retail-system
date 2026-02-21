<?php include 'includes/header.php'; ?>

<main class="container">
    <div class="page-header">
        <h1>Frequently Asked Questions</h1>
        <p>Find answers to common questions about shopping with us</p>
    </div>

    <div class="faq-content">
        <div class="faq-search">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="faqSearch" placeholder="Search FAQs...">
            </div>
        </div>

        <div class="faq-categories">
            <div class="category-tabs">
                <button class="category-tab active" data-category="all">All Questions</button>
                <button class="category-tab" data-category="ordering">Ordering</button>
                <button class="category-tab" data-category="shipping">Shipping</button>
                <button class="category-tab" data-category="returns">Returns</button>
                <button class="category-tab" data-category="account">Account</button>
            </div>
        </div>

        <div class="faq-sections">
            <!-- Ordering FAQs -->
            <div class="faq-section" data-category="ordering">
                <h2><i class="fas fa-shopping-cart"></i> Ordering & Payment</h2>
                <div class="faq-items">
                    <div class="faq-item">
                        <div class="faq-question">
                            <h4>How do I place an order?</h4>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>To place an order, simply browse our products, add items to your cart, and proceed to checkout. You'll need to create an account or checkout as a guest, enter your shipping information, and complete payment.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <h4>What payment methods do you accept?</h4>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>We accept all major credit cards (Visa, MasterCard, American Express, Discover), PayPal, Apple Pay, and Google Pay. All payments are processed securely through encrypted connections.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <h4>Can I modify or cancel my order?</h4>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>You can modify or cancel your order within 1 hour of placing it. After that, orders enter our processing system and cannot be changed. Contact customer support immediately if you need to make changes.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <h4>Do you offer student or military discounts?</h4>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Yes! We offer 10% discounts for students and military personnel. Verify your status during checkout to automatically apply the discount to your order.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shipping FAQs -->
            <div class="faq-section" data-category="shipping">
                <h2><i class="fas fa-shipping-fast"></i> Shipping & Delivery</h2>
                <div class="faq-items">
                    <div class="faq-item">
                        <div class="faq-question">
                            <h4>How long does shipping take?</h4>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Shipping times vary by location and method:
                                <br>• Standard: 3-5 business days
                                <br>• Express: 1-2 business days
                                <br>• Next Day: Next business day (order before 2 PM)
                            </p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <h4>Do you ship internationally?</h4>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Yes, we ship to over 50 countries worldwide. International shipping costs and delivery times vary by location. You can view available countries and rates during checkout.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <h4>How can I track my order?</h4>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Once your order ships, you'll receive a tracking number via email. You can also track your order by logging into your account and viewing your order history.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <h4>What if I'm not home for delivery?</h4>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Most carriers will attempt delivery 3 times. After that, packages are held at local facilities for pickup. You'll receive instructions for pickup in delivery notifications.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Returns FAQs -->
            <div class="faq-section" data-category="returns">
                <h2><i class="fas fa-undo-alt"></i> Returns & Refunds</h2>
                <div class="faq-items">
                    <div class="faq-item">
                        <div class="faq-question">
                            <h4>What is your return policy?</h4>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>We offer a 30-day money-back guarantee. Items must be in original condition with tags and packaging. Some items like software and personalized products are final sale.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <h4>How long do refunds take?</h4>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Refunds are processed within 3-5 business days after we receive your return. It may take additional time for the funds to appear in your account, depending on your bank.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <h4>Who pays for return shipping?</h4>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>We provide prepaid return labels for defective or incorrect items. For other returns, return shipping is the customer's responsibility unless you choose to exchange the item.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account FAQs -->
            <div class="faq-section" data-category="account">
                <h2><i class="fas fa-user"></i> Account & Security</h2>
                <div class="faq-items">
                    <div class="faq-item">
                        <div class="faq-question">
                            <h4>How do I create an account?</h4>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Click "Sign Up" in the top navigation and fill out the registration form. You'll need to provide your email address, create a password, and verify your email to activate your account.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <h4>I forgot my password. What should I do?</h4>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Click "Forgot Password" on the login page and enter your email address. We'll send you a link to reset your password. The link expires after 24 hours for security.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <h4>How do I update my account information?</h4>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Log into your account and go to "Profile Settings." From there, you can update your personal information, shipping addresses, payment methods, and communication preferences.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <h4>Is my personal information secure?</h4>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Yes! We use industry-standard SSL encryption to protect your data. We never store your complete payment information and adhere to strict privacy policies to keep your information safe.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="faq-contact">
            <div class="contact-promo">
                <i class="fas fa-comments"></i>
                <h3>Still have questions?</h3>
                <p>Our customer support team is ready to help you with any additional questions.</p>
                <a href="contact.php" class="btn btn-primary">Contact Support</a>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>

<style>
    .faq-content {
        max-width: 800px;
        margin: 0 auto;
    }

    .faq-search {
        margin-bottom: var(--space-6);
    }

    .search-box {
        position: relative;
        max-width: 500px;
        margin: 0 auto;
    }

    .search-box i {
        position: absolute;
        left: var(--space-4);
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray);
    }

    .search-box input {
        width: 100%;
        padding: var(--space-4) var(--space-4) var(--space-4) var(--space-8);
        border: 2px solid var(--light);
        border-radius: var(--border-radius-lg);
        font-size: var(--font-size-lg);
        transition: var(--transition);
    }

    .search-box input:focus {
        outline: none;
        border-color: var(--secondary);
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }

    .faq-categories {
        margin-bottom: var(--space-6);
    }

    .category-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: var(--space-3);
        justify-content: center;
    }

    .category-tab {
        padding: var(--space-3) var(--space-4);
        background: var(--white);
        border: 2px solid var(--light);
        border-radius: var(--border-radius);
        cursor: pointer;
        transition: var(--transition);
        font-weight: 500;
    }

    .category-tab:hover {
        border-color: var(--secondary);
        color: var(--secondary);
    }

    .category-tab.active {
        background: var(--secondary);
        color: var(--white);
        border-color: var(--secondary);
    }

    .faq-sections {
        margin-bottom: var(--space-8);
    }

    .faq-section {
        margin-bottom: var(--space-6);
    }

    .faq-section h2 {
        display: flex;
        align-items: center;
        gap: var(--space-3);
        margin-bottom: var(--space-4);
        color: var(--primary);
        padding-bottom: var(--space-3);
        border-bottom: 2px solid var(--light);
    }

    .faq-section h2 i {
        color: var(--secondary);
    }

    .faq-items {
        display: flex;
        flex-direction: column;
        gap: var(--space-3);
    }

    .faq-item {
        background: var(--white);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        transition: var(--transition);
    }

    .faq-item:hover {
        box-shadow: var(--shadow);
    }

    .faq-question {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: var(--space-4);
        cursor: pointer;
        transition: var(--transition);
    }

    .faq-question:hover {
        background: var(--light);
    }

    .faq-question h4 {
        margin: 0;
        color: var(--dark);
        font-weight: 600;
    }

    .faq-question i {
        color: var(--gray);
        transition: var(--transition);
    }

    .faq-item.active .faq-question i {
        transform: rotate(180deg);
        color: var(--secondary);
    }

    .faq-answer {
        padding: 0 var(--space-4);
        max-height: 0;
        overflow: hidden;
        transition: var(--transition);
    }

    .faq-item.active .faq-answer {
        padding: 0 var(--space-4) var(--space-4);
        max-height: 500px;
    }

    .faq-answer p {
        color: var(--gray);
        line-height: 1.6;
        margin: 0;
    }

    .faq-contact {
        text-align: center;
        padding: var(--space-8);
        background: var(--white);
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow);
    }

    .contact-promo i {
        font-size: 4rem;
        color: var(--secondary);
        margin-bottom: var(--space-4);
    }

    .contact-promo h3 {
        margin-bottom: var(--space-3);
        color: var(--dark);
    }

    .contact-promo p {
        color: var(--gray);
        margin-bottom: var(--space-5);
        font-size: var(--font-size-lg);
    }

    @media (max-width: 768px) {
        .category-tabs {
            flex-direction: column;
        }
        
        .category-tab {
            text-align: center;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // FAQ accordion functionality
        const faqItems = document.querySelectorAll('.faq-item');
        
        faqItems.forEach(item => {
            const question = item.querySelector('.faq-question');
            
            question.addEventListener('click', () => {
                // Close all other items
                faqItems.forEach(otherItem => {
                    if (otherItem !== item && otherItem.classList.contains('active')) {
                        otherItem.classList.remove('active');
                    }
                });
                
                // Toggle current item
                item.classList.toggle('active');
            });
        });

        // Category filtering
        const categoryTabs = document.querySelectorAll('.category-tab');
        const faqSections = document.querySelectorAll('.faq-section');
        
        categoryTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const category = tab.dataset.category;
                
                // Update active tab
                categoryTabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                
                // Show/hide sections
                faqSections.forEach(section => {
                    if (category === 'all' || section.dataset.category === category) {
                        section.style.display = 'block';
                    } else {
                        section.style.display = 'none';
                    }
                });
            });
        });

        // Search functionality
        const searchInput = document.getElementById('faqSearch');
        
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            
            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question h4').textContent.toLowerCase();
                const answer = item.querySelector('.faq-answer p').textContent.toLowerCase();
                
                if (question.includes(searchTerm) || answer.includes(searchTerm)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
</script>