<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$database = new Database();
$db = $database->getConnection();
$functions = new Functions($db);
$auth = new Auth($db);

// Get featured products
$featured_products = $functions->getFeaturedProducts(8);

// Get categories
$categories_query = "SELECT * FROM categories ORDER BY category_name";
$categories_stmt = $db->prepare($categories_query);
$categories_stmt->execute();
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get new arrivals
$new_arrivals_query = "SELECT * FROM products WHERE is_active = 1 ORDER BY created_at DESC LIMIT 6";
$new_arrivals_stmt = $db->prepare($new_arrivals_query);
$new_arrivals_stmt->execute();
$new_arrivals = $new_arrivals_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get best sellers
$best_sellers_query = "SELECT * FROM products WHERE is_active = 1 ORDER BY sales_count DESC LIMIT 6";
$best_sellers_stmt = $db->prepare($best_sellers_query);
$best_sellers_stmt->execute();
$best_sellers = $best_sellers_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Retail System - Home</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .category-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border: 1px solid #e0e0e0;
        }

        .category-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }

        .category-icon {
            font-size: 3rem;
            color: #3498db;
            margin-bottom: 1rem;
        }

        .category-card h3 {
            color: #2c3e50;
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }

        .category-image {
            width: 80px;
            height: 80px;
            margin: 0 auto 1rem;
            background: #f8f9fa;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: #3498db;
        }

        .category-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5rem 2rem;
            text-align: center;
            border-radius: 15px;
            margin-bottom: 3rem;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><polygon fill="rgba(255,255,255,0.05)" points="0,1000 1000,0 1000,1000"/></svg>');
            background-size: cover;
        }

        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            font-weight: 700;
            position: relative;
        }

        .hero p {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            position: relative;
        }

        .section {
            margin-bottom: 4rem;
        }

        .section h2 {
            text-align: center;
            margin-bottom: 3rem;
            color: #2c3e50;
            font-size: 2.5rem;
            font-weight: 600;
            position: relative;
        }

        .section h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, #3498db, #2c3e50);
            border-radius: 2px;
        }

        .product-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border: 1px solid #e0e0e0;
            height: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .product-badge {
            position: absolute;
            top: 1rem;
            left: 1rem;
            background: #e74c3c;
            color: white;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
            z-index: 2;
        }

        .product-badge.featured {
            background: #27ae60;
        }

        .product-badge.new {
            background: #3498db;
        }

        .product-image {
            width: 100%;
            height: 200px;
            object-fit: contain;
            border-radius: 10px;
            margin-bottom: 1rem;
            background: #f8f9fa;
            padding: 1rem;
        }

        .product-title {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            color: #2c3e50;
            font-weight: 600;
            line-height: 1.3;
        }

        .product-description {
            color: #7f8c8d;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            line-height: 1.5;
            flex: 1;
        }

        .product-price {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .current-price {
            font-size: 1.4rem;
            font-weight: bold;
            color: #e74c3c;
        }

        .original-price {
            text-decoration: line-through;
            color: #7f8c8d;
            font-size: 1rem;
        }

        .discount {
            background: #27ae60;
            color: white;
            padding: 0.3rem 0.6rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .product-stock {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            flex-wrap: wrap;
        }

        .stock-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .in-stock {
            background: #d4edda;
            color: #155724;
        }

        .low-stock {
            background: #fff3cd;
            color: #856404;
        }

        .out-of-stock {
            background: #f8d7da;
            color: #721c24;
        }

        .product-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: auto;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            justify-content: center;
            text-align: center;
        }

        .btn-primary {
            background: #3498db;
            color: white;
            flex: 2;
        }

        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid #3498db;
            color: #3498db;
            flex: 1;
        }

        .btn-outline:hover {
            background: #3498db;
            color: white;
            transform: translateY(-2px);
        }

        .grid {
            display: grid;
            gap: 2rem;
        }

        .grid-4 {
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        }

        .grid-3 {
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }

            .hero p {
                font-size: 1.1rem;
            }

            .section h2 {
                font-size: 2rem;
            }

            .product-actions {
                flex-direction: column;
            }

            .grid-4,
            .grid-3 {
                grid-template-columns: 1fr;
            }
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <a href="index.php" class="logo">Smart<span>Retail</span></a>
            
            <nav>
                <ul class="nav-menu">
                    <li><a href="index.php" class="active">Home</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="categories.php">Categories</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </nav>
            
            <div class="header-actions">
                <div class="search-box">
                    <input type="text" id="searchInput" class="search-input" placeholder="Search products...">
                    <button class="search-btn"><i class="fas fa-search"></i></button>
                    <div id="searchSuggestions" class="search-suggestions"></div>
                </div>
                
                <?php if ($auth->isLoggedIn()): ?>
                    <a href="profile.php" class="user-icon">
                        <i class="fas fa-user"></i>
                    </a>
                <?php else: ?>
                    <a href="login.php" class="user-icon">
                        <i class="fas fa-user"></i>
                    </a>
                <?php endif; ?>
                
                <a href="cart.php" class="cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                    <span id="cartCount" class="cart-count">0</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Welcome to Smart Retail</h1>
            <p>Discover amazing products at unbeatable prices with fast shipping and excellent customer service</p>
            <a href="products.php" class="btn btn-primary" style="background: white; color: #667eea; padding: 1rem 2rem; font-size: 1.1rem; font-weight: 600;">
                <i class="fas fa-shopping-bag"></i> Start Shopping Now
            </a>
        </div>
    </section>

    <!-- Main Content -->
    <main class="container">
        <!-- Featured Categories -->
        <section class="section">
            <h2>Shop by Category</h2>
            <div class="grid grid-4">
                <?php foreach ($categories as $category): ?>
                    <div class="category-card">
                        <div class="category-image">
                            <?php
                            $category_icons = [
                                'Electronics' => 'fas fa-laptop',
                                'Smartphones' => 'fas fa-mobile-alt',
                                'Laptops' => 'fas fa-laptop-code',
                                'Home Appliances' => 'fas fa-home',
                                'Books' => 'fas fa-book',
                                'Clothing' => 'fas fa-tshirt'
                            ];
                            $icon = $category_icons[$category['category_name']] ?? 'fas fa-shopping-bag';
                            ?>
                            <i class="<?php echo $icon; ?>"></i>
                        </div>
                        <h3><?php echo htmlspecialchars($category['category_name']); ?></h3>
                        <p style="color: #7f8c8d; margin-bottom: 1.5rem; font-size: 0.9rem;">
                            <?php echo htmlspecialchars($category['description'] ?? 'Explore our products'); ?>
                        </p>
                        <a href="products.php?category=<?php echo $category['category_id']; ?>" class="btn btn-outline">
                            <i class="fas fa-arrow-right"></i> Browse
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Featured Products -->
        <section class="section">
            <h2>Featured Products</h2>
            <div class="grid grid-4">
                <?php foreach ($featured_products as $product): ?>
                    <div class="product-card">
                        <?php if ($product['compare_price'] && $product['compare_price'] > $product['price']): ?>
                            <div class="product-badge">Sale</div>
                        <?php endif; ?>
                        
                        <?php if ($product['is_featured']): ?>
                            <div class="product-badge featured">Featured</div>
                        <?php endif; ?>
                        
                        <img src="<?php echo $functions->getProductImage($product); ?>" 
                             alt="<?php echo htmlspecialchars($product['product_name']); ?>" 
                             class="product-image"
                             onerror="this.src='images/placeholder.jpg'">
                        
                        <h3 class="product-title"><?php echo htmlspecialchars($product['product_name']); ?></h3>
                        <p class="product-description"><?php echo htmlspecialchars($product['short_description'] ?? substr($product['description'], 0, 100) . '...'); ?></p>
                        
                        <div class="product-price">
                            <span class="current-price">$<?php echo number_format($product['price'], 2); ?></span>
                            <?php if ($product['compare_price'] && $product['compare_price'] > $product['price']): ?>
                                <span class="original-price">$<?php echo number_format($product['compare_price'], 2); ?></span>
                                <span class="discount"><?php echo round(($product['compare_price'] - $product['price']) / $product['compare_price'] * 100); ?>% OFF</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-stock">
                            <span class="stock-badge <?php echo $product['stock_quantity'] > 10 ? 'in-stock' : ($product['stock_quantity'] > 0 ? 'low-stock' : 'out-of-stock'); ?>">
                                <?php echo $product['stock_quantity'] > 10 ? 'In Stock' : ($product['stock_quantity'] > 0 ? 'Low Stock' : 'Out of Stock'); ?>
                            </span>
                            <?php if ($product['rating_avg'] > 0): ?>
                                <div style="display: flex; align-items: center; gap: 0.3rem; color: #f39c12;">
                                    <i class="fas fa-star"></i>
                                    <span><?php echo number_format($product['rating_avg'], 1); ?></span>
                                    <span style="color: #7f8c8d;">(<?php echo $product['review_count']; ?>)</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-actions">
                            <button class="btn btn-primary add-to-cart-btn" 
                                    data-product-id="<?php echo $product['product_id']; ?>"
                                    data-product-name="<?php echo htmlspecialchars($product['product_name']); ?>">
                                <i class="fas fa-shopping-cart"></i> Add to Cart
                            </button>
                            <a href="product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-outline">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- New Arrivals -->
        <section class="section">
            <h2>New Arrivals</h2>
            <div class="grid grid-3">
                <?php foreach ($new_arrivals as $product): ?>
                    <div class="product-card">
                        <div class="product-badge new">New</div>
                        
                        <img src="<?php echo $functions->getProductImage($product); ?>" 
                             alt="<?php echo htmlspecialchars($product['product_name']); ?>" 
                             class="product-image"
                             onerror="this.src='images/placeholder.jpg'">
                        
                        <h3 class="product-title"><?php echo htmlspecialchars($product['product_name']); ?></h3>
                        
                        <div class="product-price">
                            <span class="current-price">$<?php echo number_format($product['price'], 2); ?></span>
                        </div>
                        
                        <div class="product-stock">
                            <span class="stock-badge <?php echo $product['stock_quantity'] > 10 ? 'in-stock' : ($product['stock_quantity'] > 0 ? 'low-stock' : 'out-of-stock'); ?>">
                                <?php echo $product['stock_quantity'] > 10 ? 'In Stock' : ($product['stock_quantity'] > 0 ? 'Low Stock' : 'Out of Stock'); ?>
                            </span>
                        </div>
                        
                        <div class="product-actions">
                            <button class="btn btn-primary add-to-cart-btn" 
                                    data-product-id="<?php echo $product['product_id']; ?>"
                                    data-product-name="<?php echo htmlspecialchars($product['product_name']); ?>">
                                <i class="fas fa-shopping-cart"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Best Sellers -->
        <section class="section">
            <h2>Best Sellers</h2>
            <div class="grid grid-3">
                <?php foreach ($best_sellers as $product): ?>
                    <div class="product-card">
                        <div class="product-badge" style="background: #f39c12;">Bestseller</div>
                        
                        <img src="<?php echo $functions->getProductImage($product); ?>" 
                             alt="<?php echo htmlspecialchars($product['product_name']); ?>" 
                             class="product-image"
                             onerror="this.src='images/placeholder.jpg'">
                        
                        <h3 class="product-title"><?php echo htmlspecialchars($product['product_name']); ?></h3>
                        
                        <div class="product-price">
                            <span class="current-price">$<?php echo number_format($product['price'], 2); ?></span>
                        </div>
                        
                        <div class="product-stock">
                            <span class="stock-badge <?php echo $product['stock_quantity'] > 10 ? 'in-stock' : ($product['stock_quantity'] > 0 ? 'low-stock' : 'out-of-stock'); ?>">
                                <?php echo $product['stock_quantity'] > 10 ? 'In Stock' : ($product['stock_quantity'] > 0 ? 'Low Stock' : 'Out of Stock'); ?>
                            </span>
                            <span style="color: #7f8c8d; font-size: 0.9rem;">
                                <i class="fas fa-chart-line"></i> <?php echo $product['sales_count'] ?? 0; ?> sold
                            </span>
                        </div>
                        
                        <div class="product-actions">
                            <button class="btn btn-primary add-to-cart-btn" 
                                    data-product-id="<?php echo $product['product_id']; ?>"
                                    data-product-name="<?php echo htmlspecialchars($product['product_name']); ?>">
                                <i class="fas fa-shopping-cart"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="grid grid-4">
                <div>
                    <h3>Smart Retail</h3>
                    <p>Your one-stop shop for all your needs.</p>
                </div>
                <div>
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="products.php">Products</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Customer Service</h4>
                    <ul>
                        <li><a href="shipping.php">Shipping Info</a></li>
                        <li><a href="returns.php">Returns</a></li>
                        <li><a href="faq.php">FAQ</a></li>
                        <li><a href="privacy.php">Privacy Policy</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Contact Info</h4>
                    <p>Email: info@smartretail.com</p>
                    <p>Phone: +1 (555) 123-4567</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Smart Retail System. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="js/main.js"></script>
    <script>
        // Add to cart functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Load cart count
            loadCartCount();
            
            // Add to cart buttons
            document.querySelectorAll('.add-to-cart-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const productId = this.dataset.productId;
                    const productName = this.dataset.productName;
                    
                    // Show loading state
                    const originalText = this.innerHTML;
                    this.innerHTML = '<span class="loading"></span>';
                    this.disabled = true;
                    
                    // Add to cart via API
                    fetch('api/cart.php?action=add', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            product_id: productId,
                            quantity: 1
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification(productName + ' added to cart!', 'success');
                            loadCartCount();
                        } else {
                            showNotification(data.message || 'Failed to add product to cart', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('An error occurred', 'error');
                    })
                    .finally(() => {
                        // Restore button state
                        this.innerHTML = originalText;
                        this.disabled = false;
                    });
                });
            });
        });

        function loadCartCount() {
            fetch('api/cart.php?action=count')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const cartCount = document.getElementById('cartCount');
                        if (cartCount) {
                            cartCount.textContent = data.count;
                            cartCount.style.display = data.count > 0 ? 'block' : 'none';
                        }
                    }
                })
                .catch(error => console.error('Error loading cart count:', error));
        }

        function showNotification(message, type = 'info') {
            // Remove existing notifications
            const existingNotifications = document.querySelectorAll('.notification');
            existingNotifications.forEach(notification => notification.remove());

            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 1rem 1.5rem;
                border-radius: 8px;
                color: white;
                z-index: 10000;
                max-width: 400px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.2);
                animation: slideInRight 0.3s ease;
                display: flex;
                align-items: center;
                gap: 0.75rem;
            `;

            const bgColor = type === 'error' ? '#e74c3c' : type === 'success' ? '#27ae60' : '#3498db';
            notification.style.background = bgColor;

            notification.innerHTML = `
                <i class="fas fa-${type === 'error' ? 'exclamation-triangle' : type === 'success' ? 'check-circle' : 'info-circle'}"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.remove()" style="background: none; border: none; color: white; cursor: pointer; margin-left: auto;">
                    <i class="fas fa-times"></i>
                </button>
            `;

            document.body.appendChild(notification);

            // Auto remove after 5 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.style.animation = 'slideOutRight 0.3s ease forwards';
                    setTimeout(() => notification.remove(), 300);
                }
            }, 5000);
        }

        // Add CSS for animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOutRight {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }

            .notification {
                font-family: inherit;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>