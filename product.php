<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$database = new Database();
$db = $database->getConnection();
$functions = new Functions($db);

$product_id = $_GET['id'] ?? 0;

if (!$product_id) {
    header('Location: products.php');
    exit;
}

// Get product details
$product_query = "SELECT p.*, c.category_name 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.category_id 
                  WHERE p.product_id = :product_id AND p.is_active = 1";
$product_stmt = $db->prepare($product_query);
$product_stmt->bindValue(':product_id', $product_id);
$product_stmt->execute();
$product = $product_stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: products.php');
    exit;
}

// Increment view count
$view_query = "UPDATE products SET views_count = views_count + 1 WHERE product_id = :product_id";
$view_stmt = $db->prepare($view_query);
$view_stmt->bindValue(':product_id', $product_id);
$view_stmt->execute();

// Get product attributes
$attributes_query = "SELECT * FROM product_attributes WHERE product_id = :product_id ORDER BY display_order";
$attributes_stmt = $db->prepare($attributes_query);
$attributes_stmt->bindValue(':product_id', $product_id);
$attributes_stmt->execute();
$attributes = $attributes_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get product reviews
$reviews_query = "SELECT r.*, u.first_name, u.last_name 
                  FROM product_reviews r 
                  JOIN users u ON r.user_id = u.user_id 
                  WHERE r.product_id = :product_id AND r.is_approved = 1 
                  ORDER BY r.created_at DESC";
$reviews_stmt = $db->prepare($reviews_query);
$reviews_stmt->bindValue(':product_id', $product_id);
$reviews_stmt->execute();
$reviews = $reviews_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get related products
$related_query = "SELECT * FROM products 
                  WHERE category_id = :category_id AND product_id != :product_id AND is_active = 1 
                  ORDER BY RAND() LIMIT 4";
$related_stmt = $db->prepare($related_query);
$related_stmt->bindValue(':category_id', $product['category_id']);
$related_stmt->bindValue(':product_id', $product_id);
$related_stmt->execute();
$related_products = $related_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate average rating
$avg_rating = $product['rating_avg'];
$review_count = $product['review_count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['product_name']); ?> - Smart Retail System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <a href="index.php">Home</a>
            <i class="fas fa-chevron-right"></i>
            <a href="products.php">Products</a>
            <i class="fas fa-chevron-right"></i>
            <a href="products.php?category=<?php echo $product['category_id']; ?>">
                <?php echo htmlspecialchars($product['category_name']); ?>
            </a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo htmlspecialchars($product['product_name']); ?></span>
        </nav>

        <div class="product-detail">
            <!-- Product Gallery & Info -->
            <div class="product-main">
                <!-- Product Images -->
                <div class="product-gallery">
                    <div class="main-image">
                        <img src="<?php echo $product['image_url'] ?? 'images/placeholder.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($product['product_name']); ?>" 
                             id="mainProductImage">
                    </div>
                    
                    <?php if (!empty($product['image_gallery'])): ?>
                        <?php $gallery_images = json_decode($product['image_gallery'], true); ?>
                        <div class="image-thumbnails">
                            <div class="thumbnail active">
                                <img src="<?php echo $product['image_url'] ?? 'images/placeholder.jpg'; ?>" 
                                     alt="Main image">
                            </div>
                            <?php foreach ($gallery_images as $image): ?>
                                <div class="thumbnail">
                                    <img src="<?php echo $image; ?>" alt="Product image">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Product Info -->
                <div class="product-info">
                    <h1 class="product-title"><?php echo htmlspecialchars($product['product_name']); ?></h1>
                    
                    <!-- Rating -->
                    <?php if ($avg_rating > 0): ?>
                        <div class="product-rating-large">
                            <div class="stars">
                                <?php
                                $full_stars = floor($avg_rating);
                                $half_star = $avg_rating - $full_stars >= 0.5;
                                $empty_stars = 5 - $full_stars - ($half_star ? 1 : 0);
                                
                                for ($i = 0; $i < $full_stars; $i++) {
                                    echo '<i class="fas fa-star"></i>';
                                }
                                if ($half_star) {
                                    echo '<i class="fas fa-star-half-alt"></i>';
                                }
                                for ($i = 0; $i < $empty_stars; $i++) {
                                    echo '<i class="far fa-star"></i>';
                                }
                                ?>
                            </div>
                            <span class="rating-value"><?php echo number_format($avg_rating, 1); ?></span>
                            <span class="review-count">(<?php echo $review_count; ?> reviews)</span>
                            <a href="#reviews" class="see-reviews">See all reviews</a>
                        </div>
                    <?php else: ?>
                        <div class="product-rating-large">
                            <span class="no-reviews">No reviews yet</span>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Price -->
                    <div class="product-price-large">
                        <span class="current-price">$<?php echo number_format($product['price'], 2); ?></span>
                        <?php if ($product['compare_price'] && $product['compare_price'] > $product['price']): ?>
                            <span class="original-price">$<?php echo number_format($product['compare_price'], 2); ?></span>
                            <span class="discount">Save $<?php echo number_format($product['compare_price'] - $product['price'], 2); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Stock Status -->
                    <div class="stock-status">
                        <span class="stock-badge <?php echo $product['stock_quantity'] > 10 ? 'in-stock' : ($product['stock_quantity'] > 0 ? 'low-stock' : 'out-of-stock'); ?>">
                            <?php echo $product['stock_quantity'] > 10 ? 'In Stock' : ($product['stock_quantity'] > 0 ? 'Low Stock' : 'Out of Stock'); ?>
                        </span>
                        <span class="stock-quantity">
                            <?php echo $product['stock_quantity']; ?> units available
                        </span>
                    </div>
                    
                    <!-- Short Description -->
                    <div class="product-short-description">
                        <p><?php echo htmlspecialchars($product['short_description']); ?></p>
                    </div>
                    
                    <!-- Product Attributes -->
                    <?php if (!empty($attributes)): ?>
                        <div class="product-attributes">
                            <h4>Specifications:</h4>
                            <ul>
                                <?php foreach ($attributes as $attribute): ?>
                                    <li>
                                        <strong><?php echo htmlspecialchars($attribute['attribute_name']); ?>:</strong>
                                        <span><?php echo htmlspecialchars($attribute['attribute_value']); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Add to Cart -->
                    <div class="add-to-cart-section">
                        <div class="quantity-selector">
                            <label for="quantity">Quantity:</label>
                            <div class="quantity-controls">
                                <button type="button" class="quantity-btn" data-action="decrease">-</button>
                                <input type="number" id="quantity" name="quantity" value="1" min="1" 
                                       max="<?php echo $product['stock_quantity']; ?>" class="quantity-input">
                                <button type="button" class="quantity-btn" data-action="increase">+</button>
                            </div>
                        </div>
                        
                        <div class="action-buttons">
                            <button class="btn btn-primary btn-lg add-to-cart-btn" 
                                    data-product-id="<?php echo $product['product_id']; ?>"
                                    <?php echo $product['stock_quantity'] == 0 ? 'disabled' : ''; ?>>
                                <i class="fas fa-shopping-cart"></i>
                                <?php echo $product['stock_quantity'] == 0 ? 'Out of Stock' : 'Add to Cart'; ?>
                            </button>
                            
                            <button class="btn btn-outline wishlist-btn" 
                                    data-product-id="<?php echo $product['product_id']; ?>">
                                <i class="far fa-heart"></i> Add to Wishlist
                            </button>
                        </div>
                    </div>
                    
                    <!-- Product Meta -->
                    <div class="product-meta">
                        <div class="meta-item">
                            <i class="fas fa-shipping-fast"></i>
                            <span>Free shipping on orders over $50</span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-undo"></i>
                            <span>30-day return policy</span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>2-year warranty</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Tabs -->
            <div class="product-tabs">
                <div class="tab-headers">
                    <button class="tab-header active" data-tab="description">Description</button>
                    <button class="tab-header" data-tab="specifications">Specifications</button>
                    <button class="tab-header" data-tab="reviews">Reviews (<?php echo $review_count; ?>)</button>
                </div>
                
                <div class="tab-content">
                    <!-- Description Tab -->
                    <div class="tab-pane active" id="description">
                        <div class="product-description-full">
                            <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                        </div>
                    </div>
                    
                    <!-- Specifications Tab -->
                    <div class="tab-pane" id="specifications">
                        <?php if (!empty($attributes)): ?>
                            <table class="specifications-table">
                                <?php foreach ($attributes as $attribute): ?>
                                    <tr>
                                        <td class="spec-name"><?php echo htmlspecialchars($attribute['attribute_name']); ?></td>
                                        <td class="spec-value"><?php echo htmlspecialchars($attribute['attribute_value']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        <?php else: ?>
                            <p>No specifications available for this product.</p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Reviews Tab -->
                    <div class="tab-pane" id="reviews">
                        <div class="reviews-section">
                            <!-- Review Summary -->
                            <div class="review-summary">
                                <div class="overall-rating">
                                    <div class="rating-number"><?php echo number_format($avg_rating, 1); ?></div>
                                    <div class="rating-stars">
                                        <?php
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= floor($avg_rating)) {
                                                echo '<i class="fas fa-star"></i>';
                                            } elseif ($i == ceil($avg_rating) && $avg_rating - floor($avg_rating) >= 0.5) {
                                                echo '<i class="fas fa-star-half-alt"></i>';
                                            } else {
                                                echo '<i class="far fa-star"></i>';
                                            }
                                        }
                                        ?>
                                    </div>
                                    <div class="rating-count">Based on <?php echo $review_count; ?> reviews</div>
                                </div>
                                
                                <!-- Add Review Button -->
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <button class="btn btn-primary" id="addReviewBtn">Write a Review</button>
                                <?php else: ?>
                                    <a href="login.php" class="btn btn-primary">Login to Write a Review</a>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Reviews List -->
                            <div class="reviews-list">
                                <?php if (empty($reviews)): ?>
                                    <div class="no-reviews">
                                        <p>No reviews yet. Be the first to review this product!</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($reviews as $review): ?>
                                        <div class="review-item">
                                            <div class="review-header">
                                                <div class="reviewer-info">
                                                    <strong><?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?></strong>
                                                    <div class="review-rating">
                                                        <?php
                                                        for ($i = 1; $i <= 5; $i++) {
                                                            if ($i <= $review['rating']) {
                                                                echo '<i class="fas fa-star"></i>';
                                                            } else {
                                                                echo '<i class="far fa-star"></i>';
                                                            }
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                                <div class="review-date">
                                                    <?php echo date('F j, Y', strtotime($review['created_at'])); ?>
                                                </div>
                                            </div>
                                            
                                            <?php if (!empty($review['title'])): ?>
                                                <h4 class="review-title"><?php echo htmlspecialchars($review['title']); ?></h4>
                                            <?php endif; ?>
                                            
                                            <div class="review-text">
                                                <?php echo nl2br(htmlspecialchars($review['review_text'])); ?>
                                            </div>
                                            
                                            <div class="review-helpful">
                                                <button class="helpful-btn" data-review-id="<?php echo $review['review_id']; ?>">
                                                    <i class="far fa-thumbs-up"></i> Helpful (<?php echo $review['helpful_count']; ?>)
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Products -->
            <?php if (!empty($related_products)): ?>
                <section class="related-products">
                    <h2>Related Products</h2>
                    <div class="grid grid-4">
                        <?php foreach ($related_products as $related_product): ?>
                            <div class="product-card">
                                <img src="<?php echo $related_product['image_url'] ?? 'images/placeholder.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($related_product['product_name']); ?>" 
                                     class="product-image">
                                <h3 class="product-title">
                                    <a href="product.php?id=<?php echo $related_product['product_id']; ?>">
                                        <?php echo htmlspecialchars($related_product['product_name']); ?>
                                    </a>
                                </h3>
                                <div class="product-price">
                                    <span class="current-price">$<?php echo number_format($related_product['price'], 2); ?></span>
                                </div>
                                <button class="btn btn-primary add-to-cart-btn" 
                                        data-product-id="<?php echo $related_product['product_id']; ?>">
                                    <i class="fas fa-shopping-cart"></i> Add to Cart
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Review Modal -->
    <div id="reviewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Write a Review</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="reviewForm" class="ajax-form">
                    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Rating *</label>
                        <div class="rating-input">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <input type="radio" name="rating" value="<?php echo $i; ?>" id="rating<?php echo $i; ?>" required>
                                <label for="rating<?php echo $i; ?>">
                                    <i class="far fa-star"></i>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="reviewTitle" class="form-label">Review Title</label>
                        <input type="text" id="reviewTitle" name="title" class="form-control" maxlength="255">
                    </div>
                    
                    <div class="form-group">
                        <label for="reviewText" class="form-label">Review *</label>
                        <textarea id="reviewText" name="review_text" class="form-control" rows="5" required></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline modal-cancel">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit Review</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="js/main.js"></script>
    <script>
        // Product gallery
        document.querySelectorAll('.thumbnail').forEach(thumb => {
            thumb.addEventListener('click', function() {
                const mainImage = document.getElementById('mainProductImage');
                const thumbImage = this.querySelector('img').src;
                mainImage.src = thumbImage;
                
                document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Quantity controls
        document.querySelectorAll('.quantity-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const input = document.getElementById('quantity');
                const action = this.dataset.action;
                let value = parseInt(input.value);
                
                if (action === 'increase') {
                    value = Math.min(value + 1, <?php echo $product['stock_quantity']; ?>);
                } else if (action === 'decrease') {
                    value = Math.max(value - 1, 1);
                }
                
                input.value = value;
            });
        });

        // Tab functionality
        document.querySelectorAll('.tab-header').forEach(header => {
            header.addEventListener('click', function() {
                const tabId = this.dataset.tab;
                
                // Update active tab header
                document.querySelectorAll('.tab-header').forEach(h => h.classList.remove('active'));
                this.classList.add('active');
                
                // Update active tab content
                document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
                document.getElementById(tabId).classList.add('active');
            });
        });

        // Review modal
        const reviewModal = document.getElementById('reviewModal');
        const addReviewBtn = document.getElementById('addReviewBtn');
        const modalClose = document.querySelector('.modal-close');
        const modalCancel = document.querySelector('.modal-cancel');

        if (addReviewBtn) {
            addReviewBtn.addEventListener('click', () => {
                reviewModal.style.display = 'block';
            });
        }

        [modalClose, modalCancel].forEach(btn => {
            btn.addEventListener('click', () => {
                reviewModal.style.display = 'none';
            });
        });

        // Rating stars
        document.querySelectorAll('.rating-input input').forEach(radio => {
            radio.addEventListener('change', function() {
                const rating = parseInt(this.value);
                const labels = document.querySelectorAll('.rating-input label');
                
                labels.forEach((label, index) => {
                    const icon = label.querySelector('i');
                    if (index < rating) {
                        icon.className = 'fas fa-star';
                    } else {
                        icon.className = 'far fa-star';
                    }
                });
            });
        });

        // Close modal when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target === reviewModal) {
                reviewModal.style.display = 'none';
            }
        });
    </script>
</body>
</html>