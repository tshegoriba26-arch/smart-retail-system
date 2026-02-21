<?php include 'includes/header.php'; ?>

<main class="container">
    <div class="page-header">
        <h1>About Smart Retail</h1>
        <p>Learn more about our company and mission</p>
    </div>

    <div class="about-content">
        <div class="about-section">
            <h2>Our Story</h2>
            <p>Smart Retail was founded with a simple mission: to provide high-quality products at affordable prices with exceptional customer service. We believe that everyone deserves access to the latest technology and lifestyle products without breaking the bank.</p>
        </div>

        <div class="about-section">
            <h2>Our Values</h2>
            <div class="values-grid">
                <div class="value-card">
                    <i class="fas fa-gem"></i>
                    <h3>Quality</h3>
                    <p>We carefully select every product to ensure it meets our high standards of quality and reliability.</p>
                </div>
                <div class="value-card">
                    <i class="fas fa-hand-holding-usd"></i>
                    <h3>Value</h3>
                    <p>We work directly with manufacturers to bring you the best prices without compromising on quality.</p>
                </div>
                <div class="value-card">
                    <i class="fas fa-headset"></i>
                    <h3>Support</h3>
                    <p>Our customer support team is here to help you with any questions or concerns you may have.</p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>

<style>
    .about-content {
        max-width: 800px;
        margin: 0 auto;
    }

    .about-section {
        margin-bottom: var(--space-8);
    }

    .about-section h2 {
        margin-bottom: var(--space-4);
        color: var(--primary);
    }

    .about-section p {
        font-size: var(--font-size-lg);
        line-height: 1.7;
        color: var(--dark);
    }

    .values-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: var(--space-5);
        margin-top: var(--space-5);
    }

    .value-card {
        text-align: center;
        padding: var(--space-5);
        background: var(--white);
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow);
        transition: var(--transition);
    }

    .value-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
    }

    .value-card i {
        font-size: 3rem;
        color: var(--secondary);
        margin-bottom: var(--space-3);
    }

    .value-card h3 {
        margin-bottom: var(--space-3);
        color: var(--dark);
    }

    .value-card p {
        color: var(--gray);
        line-height: 1.6;
    }
</style>