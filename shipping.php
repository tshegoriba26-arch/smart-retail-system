<?php include 'includes/header.php'; ?>

<main class="container">
    <div class="page-header">
        <h1>Shipping Information</h1>
        <p>Learn about our shipping policies and delivery options</p>
    </div>

    <div class="shipping-content">
        <div class="shipping-section">
            <h2><i class="fas fa-shipping-fast"></i> Shipping Options</h2>
            <div class="shipping-options">
                <div class="shipping-option">
                    <h3>Standard Shipping</h3>
                    <p class="shipping-time">3-5 Business Days</p>
                    <p class="shipping-cost">$4.99</p>
                    <p>Free on orders over $50</p>
                </div>
                <div class="shipping-option">
                    <h3>Express Shipping</h3>
                    <p class="shipping-time">1-2 Business Days</p>
                    <p class="shipping-cost">$9.99</p>
                    <p>Free on orders over $100</p>
                </div>
                <div class="shipping-option">
                    <h3>Next Day Delivery</h3>
                    <p class="shipping-time">Next Business Day</p>
                    <p class="shipping-cost">$19.99</p>
                    <p>Order before 2 PM for next day delivery</p>
                </div>
            </div>
        </div>

        <div class="shipping-section">
            <h2><i class="fas fa-globe-americas"></i> International Shipping</h2>
            <div class="international-info">
                <p>We ship to over 50 countries worldwide. International shipping costs and delivery times vary by location.</p>
                <div class="international-details">
                    <div class="detail-item">
                        <strong>Canada & Mexico:</strong> 5-10 business days • $14.99
                    </div>
                    <div class="detail-item">
                        <strong>Europe:</strong> 7-14 business days • $24.99
                    </div>
                    <div class="detail-item">
                        <strong>Asia & Australia:</strong> 10-21 business days • $29.99
                    </div>
                </div>
            </div>
        </div>

        <div class="shipping-section">
            <h2><i class="fas fa-box-open"></i> Order Processing</h2>
            <div class="processing-steps">
                <div class="processing-step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h4>Order Placed</h4>
                        <p>Orders are processed within 24 hours during business days</p>
                    </div>
                </div>
                <div class="processing-step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h4>Order Processed</h4>
                        <p>We prepare your items and generate shipping labels</p>
                    </div>
                </div>
                <div class="processing-step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h4>Shipped</h4>
                        <p>You'll receive tracking information via email</p>
                    </div>
                </div>
                <div class="processing-step">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <h4>Delivered</h4>
                        <p>Your order arrives at your doorstep</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="shipping-section">
            <h2><i class="fas fa-question-circle"></i> Frequently Asked Questions</h2>
            <div class="faq-items">
                <div class="faq-item">
                    <h4>How can I track my order?</h4>
                    <p>Once your order ships, you'll receive a tracking number via email. You can also track your order from your account dashboard.</p>
                </div>
                <div class="faq-item">
                    <h4>What if I'm not home when my package arrives?</h4>
                    <p>Most carriers will attempt delivery 3 times. After that, the package will be returned to our facility. You can contact us to reschedule delivery.</p>
                </div>
                <div class="faq-item">
                    <h4>Do you ship to PO boxes?</h4>
                    <p>Yes, we ship to PO boxes via standard shipping methods. Express and Next Day delivery require street addresses.</p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>

<style>
    .shipping-content {
        max-width: 1000px;
        margin: 0 auto;
    }

    .shipping-section {
        margin-bottom: var(--space-8);
        padding: var(--space-6);
        background: var(--white);
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow);
    }

    .shipping-section h2 {
        display: flex;
        align-items: center;
        gap: var(--space-3);
        margin-bottom: var(--space-5);
        color: var(--primary);
        border-bottom: 2px solid var(--light);
        padding-bottom: var(--space-3);
    }

    .shipping-section h2 i {
        color: var(--secondary);
    }

    .shipping-options {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: var(--space-5);
    }

    .shipping-option {
        text-align: center;
        padding: var(--space-5);
        border: 2px solid var(--light);
        border-radius: var(--border-radius-lg);
        transition: var(--transition);
    }

    .shipping-option:hover {
        border-color: var(--secondary);
        transform: translateY(-2px);
    }

    .shipping-option h3 {
        margin-bottom: var(--space-2);
        color: var(--dark);
    }

    .shipping-time {
        font-size: 1.2rem;
        font-weight: bold;
        color: var(--secondary);
        margin-bottom: var(--space-2);
    }

    .shipping-cost {
        font-size: 1.5rem;
        font-weight: bold;
        color: var(--success);
        margin-bottom: var(--space-2);
    }

    .international-details {
        display: flex;
        flex-direction: column;
        gap: var(--space-3);
        margin-top: var(--space-4);
    }

    .detail-item {
        padding: var(--space-3);
        background: var(--light);
        border-radius: var(--border-radius);
        border-left: 4px solid var(--secondary);
    }

    .processing-steps {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--space-5);
    }

    .processing-step {
        display: flex;
        align-items: flex-start;
        gap: var(--space-4);
    }

    .step-number {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        background: var(--secondary);
        color: var(--white);
        border-radius: 50%;
        font-weight: bold;
        flex-shrink: 0;
    }

    .step-content h4 {
        margin-bottom: var(--space-2);
        color: var(--dark);
    }

    .step-content p {
        color: var(--gray);
        margin: 0;
    }

    .faq-items {
        display: flex;
        flex-direction: column;
        gap: var(--space-4);
    }

    .faq-item {
        padding: var(--space-4);
        background: var(--light);
        border-radius: var(--border-radius);
        border-left: 4px solid var(--info);
    }

    .faq-item h4 {
        margin-bottom: var(--space-2);
        color: var(--dark);
    }

    .faq-item p {
        color: var(--gray);
        margin: 0;
        line-height: 1.6;
    }

    @media (max-width: 768px) {
        .shipping-options {
            grid-template-columns: 1fr;
        }
        
        .processing-steps {
            grid-template-columns: 1fr;
        }
    }
</style>