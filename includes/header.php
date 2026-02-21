<?php
// includes/header.php
require_once 'config/database.php';
require_once 'includes/auth.php';

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Retail System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <a href="index.php" class="logo">Smart<span>Retail</span></a>
            
            <nav>
                <ul class="nav-menu">
                    <li><a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Home</a></li>
                    <li><a href="products.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">Products</a></li>
                    <li><a href="categories.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">Categories</a></li>
                    <li><a href="about.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>">About</a></li>
                    <li><a href="contact.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>">Contact</a></li>
                </ul>
            </nav>
            
            <div class="header-actions">
                <div class="search-box">
                    <input type="text" id="searchInput" class="search-input" placeholder="Search products...">
                    <button class="search-btn"><i class="fas fa-search"></i></button>
                    <div id="searchSuggestions" class="search-suggestions"></div>
                </div>
                
                <?php if ($auth->isLoggedIn()): ?>
                    <a href="profile.php" class="user-icon" title="My Account">
                        <i class="fas fa-user"></i>
                    </a>
                    <?php if ($auth->isAdmin()): ?>
                        <a href="admin/dashboard.php" class="user-icon" title="Admin Dashboard" style="color: #ffd700;">
                            <i class="fas fa-cog"></i>
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="login.php" class="user-icon" title="Login">
                        <i class="fas fa-user"></i>
                    </a>
                <?php endif; ?>
                
                <a href="cart.php" class="cart-icon" title="Shopping Cart">
                    <i class="fas fa-shopping-cart"></i>
                    <span id="cartCount" class="cart-count">0</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Notifications Container -->
    <div id="notificationsContainer"></div>

    <script>
        // Load cart count on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadCartCount();
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

        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const searchSuggestions = document.getElementById('searchSuggestions');

        if (searchInput && searchSuggestions) {
            let searchTimeout;

            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const query = this.value.trim();
                
                if (query.length < 2) {
                    searchSuggestions.style.display = 'none';
                    return;
                }

                searchTimeout = setTimeout(() => {
                    fetch(`api/search.php?q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.results.length > 0) {
                                displaySearchSuggestions(data.results);
                            } else {
                                searchSuggestions.style.display = 'none';
                            }
                        })
                        .catch(error => {
                            console.error('Search error:', error);
                            searchSuggestions.style.display = 'none';
                        });
                }, 300);
            });

            // Hide suggestions when clicking outside
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !searchSuggestions.contains(e.target)) {
                    searchSuggestions.style.display = 'none';
                }
            });

            function displaySearchSuggestions(products) {
                searchSuggestions.innerHTML = products.map(product => `
                    <div class="suggestion-item" data-product-id="${product.product_id}">
                        <img src="${product.image_url || 'images/placeholder.jpg'}" alt="${product.product_name}">
                        <div class="suggestion-info">
                            <div class="suggestion-title">${product.product_name}</div>
                            <div class="suggestion-price">$${product.price}</div>
                        </div>
                    </div>
                `).join('');

                searchSuggestions.style.display = 'block';

                // Add click handlers
                searchSuggestions.querySelectorAll('.suggestion-item').forEach(item => {
                    item.addEventListener('click', function() {
                        const productId = this.dataset.productId;
                        window.location.href = `product.php?id=${productId}`;
                    });
                });
            }
        }
    </script>