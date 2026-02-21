<?php
// includes/footer.php
?>
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="grid grid-4">
                <div class="footer-section">
                    <h3>Smart Retail</h3>
                    <p>Your one-stop shop for all your needs. Quality products at affordable prices with excellent customer service.</p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="products.php">Products</a></li>
                        <li><a href="categories.php">Categories</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Customer Service</h4>
                    <ul class="footer-links">
                        <li><a href="shipping.php">Shipping Info</a></li>
                        <li><a href="returns.php">Returns & Refunds</a></li>
                        <li><a href="faq.php">FAQ</a></li>
                        <li><a href="privacy.php">Privacy Policy</a></li>
                        <li><a href="terms.php">Terms of Service</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact Info</h4>
                    <div class="contact-info">
                        <p><i class="fas fa-map-marker-alt"></i> 123 Commerce Street, City, State 12345</p>
                        <p><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
                        <p><i class="fas fa-envelope"></i> info@smartretail.com</p>
                        <p><i class="fas fa-clock"></i> Mon-Fri: 9AM-6PM</p>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Smart Retail System. All rights reserved. | Designed with <i class="fas fa-heart" style="color: #e74c3c;"></i> for amazing shopping experiences</p>
            </div>
        </div>
    </footer>

    <script src="js/main.js"></script>
    
    <style>
        .footer {
            background: var(--primary);
            color: var(--white);
            padding: var(--space-8) 0 var(--space-4);
            margin-top: auto;
        }

        .footer-section h3,
        .footer-section h4 {
            color: var(--white);
            margin-bottom: var(--space-4);
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: var(--space-2);
        }

        .footer-links a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-links a:hover {
            color: var(--white);
            text-decoration: underline;
        }

        .social-links {
            display: flex;
            gap: var(--space-3);
            margin-top: var(--space-4);
        }

        .social-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.1);
            color: var(--white);
            border-radius: 50%;
            text-decoration: none;
            transition: var(--transition);
        }

        .social-link:hover {
            background: var(--secondary);
            transform: translateY(-2px);
        }

        .contact-info p {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            margin-bottom: var(--space-2);
            color: rgba(255,255,255,0.8);
        }

        .contact-info i {
            width: 20px;
            color: var(--secondary);
        }

        .footer-bottom {
            text-align: center;
            padding-top: var(--space-4);
            border-top: 1px solid rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.6);
            font-size: var(--font-size-sm);
            margin-top: var(--space-6);
        }

        @media (max-width: 768px) {
            .footer .grid {
                grid-template-columns: 1fr;
                gap: var(--space-6);
            }
        }
    </style>
</body>
</html>