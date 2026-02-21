<?php include 'includes/header.php'; ?>

<main class="container">
    <div class="page-header">
        <h1>Returns & Refunds</h1>
        <p>Our hassle-free return policy</p>
    </div>

    <div class="returns-content">
        <div class="returns-hero">
            <div class="hero-icon">
                <i class="fas fa-undo-alt"></i>
            </div>
            <h2>30-Day Money Back Guarantee</h2>
            <p>Not satisfied with your purchase? We make returns easy and stress-free.</p>
        </div>

        <div class="returns-grid">
            <div class="return-policy-card">
                <div class="policy-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3>30-Day Return Window</h3>
                <p>Return any item within 30 days of delivery for a full refund or exchange.</p>
            </div>

            <div class="return-policy-card">
                <div class="policy-icon">
                    <i class="fas fa-box"></i>
                </div>
                <h3>Easy Returns</h3>
                <p>Start your return online and print a prepaid shipping label.</p>
            </div>

            <div class="return-policy-card">
                <div class="policy-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <h3>Full Refunds</h3>
                <p>Receive your refund within 3-5 business days after we process your return.</p>
            </div>
        </div>

        <div class="returns-process">
            <h2><i class="fas fa-list-ol"></i> How to Return an Item</h2>
            <div class="process-steps">
                <div class="process-step">
                    <div class="step-number">1</div>
                    <div class="step-details">
                        <h4>Start Your Return</h4>
                        <p>Log into your account and go to "Order History" to initiate a return.</p>
                    </div>
                </div>
                <div class="process-step">
                    <div class="step-number">2</div>
                    <div class="step-details">
                        <h4>Print Label</h4>
                        <p>Print the prepaid shipping label we provide.</p>
                    </div>
                </div>
                <div class="process-step">
                    <div class="step-number">3</div>
                    <div class="step-details">
                        <h4>Pack & Ship</h4>
                        <p>Pack the item in its original packaging and drop it off at any shipping location.</p>
                    </div>
                </div>
                <div class="process-step">
                    <div class="step-number">4</div>
                    <div class="step-details">
                        <h4>Get Refund</h4>
                        <p>Once we receive your return, we'll process your refund within 3-5 business days.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="return-conditions">
            <h2><i class="fas fa-clipboard-check"></i> Return Conditions</h2>
            <div class="conditions-grid">
                <div class="condition-item valid">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <h4>Items must be:</h4>
                        <ul>
                            <li>In original condition</li>
                            <li>In original packaging</li>
                            <li>Unused and unwashed</li>
                            <li>Include all tags and accessories</li>
                        </ul>
                    </div>
                </div>
                <div class="condition-item invalid">
                    <i class="fas fa-times-circle"></i>
                    <div>
                        <h4>Cannot return:</h4>
                        <ul>
                            <li>Opened software or games</li>
                            <li>Personal care items</li>
                            <li>Customized products</li>
                            <li>Final sale items</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="refund-timeline">
            <h2><i class="fas fa-clock"></i> Refund Timeline</h2>
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-date">Day 1-2</div>
                    <div class="timeline-content">
                        <h4>Return Received</h4>
                        <p>We process your return upon arrival at our facility</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-date">Day 3-5</div>
                    <div class="timeline-content">
                        <h4>Refund Issued</h4>
                        <p>Refund processed to your original payment method</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-date">Day 5-7</div>
                    <div class="timeline-content">
                        <h4>Funds Available</h4>
                        <p>Refund appears in your account (varies by bank)</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="contact-support">
            <div class="support-card">
                <i class="fas fa-headset"></i>
                <h3>Need Help With a Return?</h3>
                <p>Our customer support team is here to help you with any return-related questions.</p>
                <a href="contact.php" class="btn btn-primary">Contact Support</a>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>

<style>
    .returns-content {
        max-width: 1000px;
        margin: 0 auto;
    }

    .returns-hero {
        text-align: center;
        padding: var(--space-8);
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: var(--white);
        border-radius: var(--border-radius-lg);
        margin-bottom: var(--space-8);
    }

    .hero-icon {
        font-size: 4rem;
        margin-bottom: var(--space-4);
    }

    .returns-hero h2 {
        margin-bottom: var(--space-3);
        color: var(--white);
    }

    .returns-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: var(--space-5);
        margin-bottom: var(--space-8);
    }

    .return-policy-card {
        text-align: center;
        padding: var(--space-5);
        background: var(--white);
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow);
        transition: var(--transition);
    }

    .return-policy-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
    }

    .policy-icon {
        font-size: 3rem;
        color: var(--secondary);
        margin-bottom: var(--space-3);
    }

    .return-policy-card h3 {
        margin-bottom: var(--space-3);
        color: var(--dark);
    }

    .returns-process {
        background: var(--white);
        padding: var(--space-6);
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow);
        margin-bottom: var(--space-8);
    }

    .returns-process h2 {
        display: flex;
        align-items: center;
        gap: var(--space-3);
        margin-bottom: var(--space-5);
        color: var(--primary);
    }

    .process-steps {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: var(--space-5);
    }

    .process-step {
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
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .step-details h4 {
        margin-bottom: var(--space-2);
        color: var(--dark);
    }

    .step-details p {
        color: var(--gray);
        margin: 0;
    }

    .return-conditions {
        background: var(--white);
        padding: var(--space-6);
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow);
        margin-bottom: var(--space-8);
    }

    .return-conditions h2 {
        display: flex;
        align-items: center;
        gap: var(--space-3);
        margin-bottom: var(--space-5);
        color: var(--primary);
    }

    .conditions-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: var(--space-5);
    }

    .condition-item {
        display: flex;
        align-items: flex-start;
        gap: var(--space-4);
        padding: var(--space-4);
        border-radius: var(--border-radius);
    }

    .condition-item.valid {
        background: rgba(39, 174, 96, 0.1);
        border-left: 4px solid var(--success);
    }

    .condition-item.invalid {
        background: rgba(231, 76, 60, 0.1);
        border-left: 4px solid var(--danger);
    }

    .condition-item i {
        font-size: 1.5rem;
        margin-top: var(--space-1);
        flex-shrink: 0;
    }

    .condition-item.valid i {
        color: var(--success);
    }

    .condition-item.invalid i {
        color: var(--danger);
    }

    .condition-item h4 {
        margin-bottom: var(--space-2);
        color: var(--dark);
    }

    .condition-item ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .condition-item li {
        padding: var(--space-1) 0;
        color: var(--gray);
    }

    .refund-timeline {
        background: var(--white);
        padding: var(--space-6);
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow);
        margin-bottom: var(--space-8);
    }

    .refund-timeline h2 {
        display: flex;
        align-items: center;
        gap: var(--space-3);
        margin-bottom: var(--space-5);
        color: var(--primary);
    }

    .timeline {
        position: relative;
        padding-left: var(--space-8);
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 20px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: var(--secondary);
    }

    .timeline-item {
        position: relative;
        margin-bottom: var(--space-6);
    }

    .timeline-item:last-child {
        margin-bottom: 0;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -28px;
        top: 5px;
        width: 12px;
        height: 12px;
        background: var(--secondary);
        border-radius: 50%;
    }

    .timeline-date {
        font-weight: bold;
        color: var(--secondary);
        margin-bottom: var(--space-2);
    }

    .timeline-content h4 {
        margin-bottom: var(--space-2);
        color: var(--dark);
    }

    .timeline-content p {
        color: var(--gray);
        margin: 0;
    }

    .contact-support {
        text-align: center;
    }

    .support-card {
        background: var(--white);
        padding: var(--space-8);
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow);
        max-width: 500px;
        margin: 0 auto;
    }

    .support-card i {
        font-size: 4rem;
        color: var(--secondary);
        margin-bottom: var(--space-4);
    }

    .support-card h3 {
        margin-bottom: var(--space-3);
        color: var(--dark);
    }

    .support-card p {
        color: var(--gray);
        margin-bottom: var(--space-5);
        font-size: var(--font-size-lg);
    }

    @media (max-width: 768px) {
        .conditions-grid {
            grid-template-columns: 1fr;
        }
        
        .process-steps {
            grid-template-columns: 1fr;
        }
        
        .timeline {
            padding-left: var(--space-6);
        }
    }
</style>