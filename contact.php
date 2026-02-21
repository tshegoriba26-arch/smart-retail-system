<?php include 'includes/header.php'; ?>

<main class="container">
    <div class="page-header">
        <h1>Contact Us</h1>
        <p>Get in touch with our team</p>
    </div>

    <div class="contact-layout">
        <div class="contact-info">
            <h2>Get In Touch</h2>
            <div class="contact-method">
                <i class="fas fa-map-marker-alt"></i>
                <div>
                    <h3>Address</h3>
                    <p>123 Commerce Street<br>City, State 12345</p>
                </div>
            </div>
            <div class="contact-method">
                <i class="fas fa-phone"></i>
                <div>
                    <h3>Phone</h3>
                    <p>+1 (555) 123-4567</p>
                </div>
            </div>
            <div class="contact-method">
                <i class="fas fa-envelope"></i>
                <div>
                    <h3>Email</h3>
                    <p>info@smartretail.com</p>
                </div>
            </div>
            <div class="contact-method">
                <i class="fas fa-clock"></i>
                <div>
                    <h3>Business Hours</h3>
                    <p>Monday - Friday: 9:00 AM - 6:00 PM<br>Saturday: 10:00 AM - 4:00 PM</p>
                </div>
            </div>
        </div>

        <div class="contact-form">
            <h2>Send us a Message</h2>
            <form class="ajax-form">
                <div class="form-group">
                    <label for="name" class="form-label">Your Name</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="subject" class="form-label">Subject</label>
                    <input type="text" id="subject" name="subject" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="message" class="form-label">Message</label>
                    <textarea id="message" name="message" class="form-control" rows="5" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Send Message</button>
            </form>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>

<style>
    .contact-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: var(--space-8);
        max-width: 1000px;
        margin: 0 auto;
    }

    .contact-info h2,
    .contact-form h2 {
        margin-bottom: var(--space-5);
        color: var(--primary);
    }

    .contact-method {
        display: flex;
        align-items: flex-start;
        gap: var(--space-4);
        margin-bottom: var(--space-5);
        padding: var(--space-4);
        background: var(--white);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-sm);
    }

    .contact-method i {
        font-size: 1.5rem;
        color: var(--secondary);
        margin-top: var(--space-1);
    }

    .contact-method h3 {
        margin-bottom: var(--space-1);
        color: var(--dark);
    }

    .contact-method p {
        color: var(--gray);
        margin: 0;
    }

    .contact-form {
        background: var(--white);
        padding: var(--space-6);
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow);
    }

    @media (max-width: 768px) {
        .contact-layout {
            grid-template-columns: 1fr;
        }
    }
</style>