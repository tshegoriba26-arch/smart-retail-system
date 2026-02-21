<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$database = new Database();
$db = $database->getConnection();
$functions = new Functions($db);

// Get filter parameters
$category_id = $_GET['category'] ?? 0;
$search_query = $_GET['search'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$page = $_GET['page'] ?? 1;
$limit = 12;

// Build query
$query = "SELECT p.*, c.category_name FROM products p 
          LEFT JOIN categories c ON p.category_id = c.category_id 
          WHERE p.is_active = 1";
$count_query = "SELECT COUNT(*) as total FROM products p WHERE p.is_active = 1";
$params = [];
$count_params = [];

if ($category_id) {
    $query .= " AND p.category_id = :category_id";
    $count_query .= " AND p.category_id = :category_id";
    $params[':category_id'] = $category_id;
    $count_params[':category_id'] = $category_id;
}

if ($search_query) {
    $query .= " AND (p.product_name LIKE :search OR p.description LIKE :search OR p.tags LIKE :search)";
    $count_query .= " AND (p.product_name LIKE :search OR p.description LIKE :search OR p.tags LIKE :search)";
    $params[':search'] = "%$search_query%";
    $count_params[':search'] = "%$search_query%";
}

if ($min_price !== '') {
    $query .= " AND p.price >= :min_price";
    $count_query .= " AND p.price >= :min_price";
    $params[':min_price'] = $min_price;
    $count_params[':min_price'] = $min_price;
}

if ($max_price !== '') {
    $query .= " AND p.price <= :max_price";
    $count_query .= " AND p.price <= :max_price";
    $params[':max_price'] = $max_price;
    $count_params[':max_price'] = $max_price;
}

// Add sorting
switch ($sort) {
    case 'price_low':
        $query .= " ORDER BY p.price ASC";
        break;
    case 'price_high':
        $query .= " ORDER BY p.price DESC";
        break;
    case 'name':
        $query .= " ORDER BY p.product_name ASC";
        break;
    case 'popular':
        $query .= " ORDER BY p.sales_count DESC, p.views_count DESC";
        break;
    default:
        $query .= " ORDER BY p.created_at DESC";
}

// Add pagination
$offset = ($page - 1) * $limit;
$query .= " LIMIT :limit OFFSET :offset";
$params[':limit'] = $limit;
$params[':offset'] = $offset;

// Execute query
$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count
$count_stmt = $db->prepare($count_query);
foreach ($count_params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}
$count_stmt->execute();
$total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total / $limit);

// Get categories for filter
$categories_query = "SELECT * FROM categories ORDER BY category_name";
$categories_stmt = $db->prepare($categories_query);
$categories_stmt->execute();
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get price range
$price_range_query = "SELECT MIN(price) as min_price, MAX(price) as max_price FROM products WHERE is_active = 1";
$price_range_stmt = $db->prepare($price_range_query);
$price_range_stmt->execute();
$price_range = $price_range_stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Smart Retail System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header (same as index.php) -->
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="products-page">
            <!-- Page Header -->
            <div class="page-header">
                <h1>Products</h1>
                <p>Discover our amazing collection</p>
            </div>

            <div class="products-layout">
                <!-- Sidebar Filters -->
                <aside class="filters-sidebar">
                    <div class="filter-group">
                        <h3>Categories</h3>
                        <ul class="category-list">
                            <li>
                                <a href="products.php" class="<?php echo !$category_id ? 'active' : ''; ?>">
                                    All Categories
                                </a>
                            </li>
                            <?php foreach ($categories as $category): ?>
                                <li>
                                    <a href="products.php?category=<?php echo $category['category_id']; ?>" 
                                       class="<?php echo $category_id == $category['category_id'] ? 'active' : ''; ?>">
                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <div class="filter-group">
                        <h3>Price Range</h3>
                        <form method="GET" class="price-filter">
                            <input type="hidden" name="category" value="<?php echo $category_id; ?>">
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_query); ?>">
                            <input type="hidden" name="sort" value="<?php echo $sort; ?>">
                            
                            <div class="price-inputs">
                                <input type="number" name="min_price" placeholder="Min" 
                                       value="<?php echo $min_price; ?>" min="0" step="0.01">
                                <span>to</span>
                                <input type="number" name="max_price" placeholder="Max" 
                                       value="<?php echo $max_price; ?>" min="0" step="0.01">
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                            <?php if ($min_price || $max_price): ?>
                                <a href="products.php?category=<?php echo $category_id; ?>&search=<?php echo urlencode($search_query); ?>&sort=<?php echo $sort; ?>" 
                                   class="btn btn-outline btn-sm">Clear</a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <div class="filter-group">
                        <h3>Sort By</h3>
                        <ul class="sort-list">
                            <li>
                                <a href="?category=<?php echo $category_id; ?>&search=<?php echo urlencode($search_query); ?>&min_price=<?php echo $min_price; ?>&max_price=<?php echo $max_price; ?>&sort=newest" 
                                   class="<?php echo $sort == 'newest' ? 'active' : ''; ?>">
                                    Newest First
                                </a>
                            </li>
                            <li>
                                <a href="?category=<?php echo $category_id; ?>&search=<?php echo urlencode($search_query); ?>&min_price=<?php echo $min_price; ?>&max_price=<?php echo $max_price; ?>&sort=price_low" 
                                   class="<?php echo $sort == 'price_low' ? 'active' : ''; ?>">
                                    Price: Low to High
                                </a>
                            </li>
                            <li>
                                <a href="?category=<?php echo $category_id; ?>&search=<?php echo urlencode($search_query); ?>&min_price=<?php echo $min_price; ?>&max_price=<?php echo $max_price; ?>&sort=price_high" 
                                   class="<?php echo $sort == 'price_high' ? 'active' : ''; ?>">
                                    Price: High to Low
                                </a>
                            </li>
                            <li>
                                <a href="?category=<?php echo $category_id; ?>&search=<?php echo urlencode($search_query); ?>&min_price=<?php echo $min_price; ?>&max_price=<?php echo $max_price; ?>&sort=name" 
                                   class="<?php echo $sort == 'name' ? 'active' : ''; ?>">
                                    Name A-Z
                                </a>
                            </li>
                            <li>
                                <a href="?category=<?php echo $category_id; ?>&search=<?php echo urlencode($search_query); ?>&min_price=<?php echo $min_price; ?>&max_price=<?php echo $max_price; ?>&sort=popular" 
                                   class="<?php echo $sort == 'popular' ? 'active' : ''; ?>">
                                    Most Popular
                                </a>
                            </li>
                        </ul>
                    </div>
                </aside>

                <!-- Main Content -->
                <div class="products-main">
                    <!-- Results Header -->
                    <div class="results-header">
                        <p class="results-count">
                            Showing <?php echo count($products); ?> of <?php echo $total; ?> products
                            <?php if ($search_query): ?>
                                for "<?php echo htmlspecialchars($search_query); ?>"
                            <?php endif; ?>
                        </p>
                        
                        <div class="view-options">
                            <button class="view-btn active" data-view="grid">
                                <i class="fas fa-th"></i>
                            </button>
                            <button class="view-btn" data-view="list">
                                <i class="fas fa-list"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Products Grid -->
                    <div class="products-grid grid grid-3" id="productsContainer">
                        <?php if (empty($products)): ?>
                            <div class="no-products">
                                <i class="fas fa-search fa-3x"></i>
                                <h3>No products found</h3>
                                <p>Try adjusting your search criteria</p>
                                <a href="products.php" class="btn btn-primary">Browse All Products</a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                                <div class="product-card">
                                    <?php if ($product['compare_price'] && $product['compare_price'] > $product['price']): ?>
                                        <div class="product-badge">Sale</div>
                                    <?php endif; ?>
                                    
                                    <img src="<?php echo $product['image_url'] ?? 'images/placeholder.jpg'; ?>" 
                                         alt="<?php echo htmlspecialchars($product['product_name']); ?>" 
                                         class="product-image">
                                    
                                    <div class="product-content">
                                        <h3 class="product-title">
                                            <a href="product.php?id=<?php echo $product['product_id']; ?>">
                                                <?php echo htmlspecialchars($product['product_name']); ?>
                                            </a>
                                        </h3>
                                        
                                        <p class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></p>
                                        
                                        <p class="product-description">
                                            <?php echo htmlspecialchars($product['short_description'] ?? substr($product['description'], 0, 150) . '...'); ?>
                                        </p>
                                        
                                        <div class="product-price">
                                            <span class="current-price">$<?php echo number_format($product['price'], 2); ?></span>
                                            <?php if ($product['compare_price'] && $product['compare_price'] > $product['price']): ?>
                                                <span class="original-price">$<?php echo number_format($product['compare_price'], 2); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="product-stock">
                                            <span class="stock-badge <?php echo $product['stock_quantity'] > 10 ? 'in-stock' : ($product['stock_quantity'] > 0 ? 'low-stock' : 'out-of-stock'); ?>">
                                                <?php echo $product['stock_quantity'] > 10 ? 'In Stock' : ($product['stock_quantity'] > 0 ? 'Low Stock' : 'Out of Stock'); ?>
                                            </span>
                                            <?php if ($product['rating_avg'] > 0): ?>
                                                <div class="product-rating">
                                                    <span class="stars">
                                                        <?php
                                                        $full_stars = floor($product['rating_avg']);
                                                        $half_star = $product['rating_avg'] - $full_stars >= 0.5;
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
                                                    </span>
                                                    <span class="rating-count">(<?php echo $product['review_count']; ?>)</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="product-actions">
                                            <button class="btn btn-primary add-to-cart-btn" 
                                                    data-product-id="<?php echo $product['product_id']; ?>"
                                                    <?php echo $product['stock_quantity'] == 0 ? 'disabled' : ''; ?>>
                                                <i class="fas fa-shopping-cart"></i>
                                                <?php echo $product['stock_quantity'] == 0 ? 'Out of Stock' : 'Add to Cart'; ?>
                                            </button>
                                            
                                            <button class="btn btn-outline wishlist-btn" 
                                                    data-product-id="<?php echo $product['product_id']; ?>">
                                                <i class="far fa-heart"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?category=<?php echo $category_id; ?>&search=<?php echo urlencode($search_query); ?>&min_price=<?php echo $min_price; ?>&max_price=<?php echo $max_price; ?>&sort=<?php echo $sort; ?>&page=<?php echo $page - 1; ?>" 
                                   class="pagination-btn">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            <?php endif; ?>
                            
                            <div class="pagination-numbers">
                                <?php
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);
                                
                                for ($i = $start_page; $i <= $end_page; $i++):
                                ?>
                                    <a href="?category=<?php echo $category_id; ?>&search=<?php echo urlencode($search_query); ?>&min_price=<?php echo $min_price; ?>&max_price=<?php echo $max_price; ?>&sort=<?php echo $sort; ?>&page=<?php echo $i; ?>" 
                                       class="pagination-number <?php echo $i == $page ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                            </div>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?category=<?php echo $category_id; ?>&search=<?php echo urlencode($search_query); ?>&min_price=<?php echo $min_price; ?>&max_price=<?php echo $max_price; ?>&sort=<?php echo $sort; ?>&page=<?php echo $page + 1; ?>" 
                                   class="pagination-btn">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script src="js/main.js"></script>
    <script>
        // View toggle functionality
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                const view = this.dataset.view;
                const container = document.getElementById('productsContainer');
                
                if (view === 'list') {
                    container.classList.add('list-view');
                    container.classList.remove('grid-view');
                } else {
                    container.classList.add('grid-view');
                    container.classList.remove('list-view');
                }
            });
        });

        // Price range validation
        const minPriceInput = document.querySelector('input[name="min_price"]');
        const maxPriceInput = document.querySelector('input[name="max_price"]');
        
        if (minPriceInput && maxPriceInput) {
            minPriceInput.addEventListener('change', function() {
                if (this.value && maxPriceInput.value && parseFloat(this.value) > parseFloat(maxPriceInput.value)) {
                    maxPriceInput.value = this.value;
                }
            });
            
            maxPriceInput.addEventListener('change', function() {
                if (this.value && minPriceInput.value && parseFloat(this.value) < parseFloat(minPriceInput.value)) {
                    minPriceInput.value = this.value;
                }
            });
        }
    </script>
</body>
</html>