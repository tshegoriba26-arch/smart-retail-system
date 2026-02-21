<?php
require_once 'security.php';

class Functions {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Get featured products with error handling
     */
    public function getFeaturedProducts($limit = 8) {
        try {
            if (!$this->tableExists('products')) {
                return $this->getSampleProducts($limit);
            }
            
            $query = "SELECT p.*, c.category_name 
                      FROM products p 
                      LEFT JOIN categories c ON p.category_id = c.category_id 
                      WHERE p.is_featured = 1 AND p.is_active = 1 
                      ORDER BY p.created_at DESC 
                      LIMIT :limit";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return !empty($products) ? $products : $this->getSampleProducts($limit);
            
        } catch (PDOException $e) {
            error_log("getFeaturedProducts error: " . $e->getMessage());
            return $this->getSampleProducts($limit);
        }
    }
    
    /**
     * Get product by ID with error handling
     */
    public function getProductById($product_id) {
        try {
            if (!$this->tableExists('products')) {
                return null;
            }
            
            $query = "SELECT p.*, c.category_name 
                      FROM products p 
                      LEFT JOIN categories c ON p.category_id = c.category_id 
                      WHERE p.product_id = :product_id AND p.is_active = 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':product_id', $product_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($product) {
                // Increment view count
                $this->incrementProductViews($product_id);
            }
            
            return $product;
            
        } catch (PDOException $e) {
            error_log("getProductById error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Search products with filters
     */
    public function searchProducts($search_term, $category_id = null, $min_price = null, $max_price = null, $sort = 'newest') {
        try {
            if (!$this->tableExists('products')) {
                return $this->getSampleProducts(12);
            }
            
            $query = "SELECT p.*, c.category_name 
                      FROM products p 
                      LEFT JOIN categories c ON p.category_id = c.category_id 
                      WHERE p.is_active = 1 
                      AND (p.product_name LIKE :search 
                           OR p.description LIKE :search 
                           OR p.short_description LIKE :search 
                           OR p.tags LIKE :search)";
            
            $params = [':search' => "%$search_term%"];
            
            if ($category_id) {
                $query .= " AND p.category_id = :category_id";
                $params[':category_id'] = $category_id;
            }
            
            if ($min_price !== null) {
                $query .= " AND p.price >= :min_price";
                $params[':min_price'] = $min_price;
            }
            
            if ($max_price !== null) {
                $query .= " AND p.price <= :max_price";
                $params[':max_price'] = $max_price;
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
            
            $stmt = $this->db->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return !empty($results) ? $results : [];
            
        } catch (PDOException $e) {
            error_log("searchProducts error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Add item to shopping cart
     */
    public function addToCart($product_id, $quantity = 1, $attributes = []) {
        try {
            if (!$this->tableExists('shopping_cart') || !$this->tableExists('products')) {
                return false;
            }
            
            $user_id = $_SESSION['user_id'] ?? null;
            $session_id = session_id();
            
            // Check if product exists and is available
            $product = $this->getProductById($product_id);
            if (!$product || $product['stock_quantity'] < $quantity) {
                return false;
            }
            
            // Check if item already in cart
            $check_query = "SELECT * FROM shopping_cart WHERE product_id = :product_id AND ";
            if ($user_id) {
                $check_query .= "user_id = :user_id";
                $check_params = [':product_id' => $product_id, ':user_id' => $user_id];
            } else {
                $check_query .= "session_id = :session_id";
                $check_params = [':product_id' => $product_id, ':session_id' => $session_id];
            }
            
            $check_stmt = $this->db->prepare($check_query);
            foreach ($check_params as $key => $value) {
                $check_stmt->bindValue($key, $value);
            }
            $check_stmt->execute();
            $existing_item = $check_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing_item) {
                // Update quantity
                $new_quantity = $existing_item['quantity'] + $quantity;
                $update_query = "UPDATE shopping_cart SET quantity = :quantity, updated_at = NOW() WHERE cart_id = :cart_id";
                $update_stmt = $this->db->prepare($update_query);
                $update_stmt->bindValue(':quantity', $new_quantity);
                $update_stmt->bindValue(':cart_id', $existing_item['cart_id']);
                return $update_stmt->execute();
            } else {
                // Insert new item
                $insert_query = "INSERT INTO shopping_cart (user_id, session_id, product_id, quantity, attributes) 
                                VALUES (:user_id, :session_id, :product_id, :quantity, :attributes)";
                $insert_stmt = $this->db->prepare($insert_query);
                $insert_stmt->bindValue(':user_id', $user_id);
                $insert_stmt->bindValue(':session_id', $session_id);
                $insert_stmt->bindValue(':product_id', $product_id);
                $insert_stmt->bindValue(':quantity', $quantity);
                $insert_stmt->bindValue(':attributes', json_encode($attributes));
                return $insert_stmt->execute();
            }
            
        } catch (PDOException $e) {
            error_log("addToCart error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get cart items for current user/session
     */
    public function getCartItems() {
        try {
            if (!$this->tableExists('shopping_cart') || !$this->tableExists('products')) {
                return [];
            }
            
            $user_id = $_SESSION['user_id'] ?? null;
            $session_id = session_id();
            
            $query = "SELECT sc.*, p.product_name, p.price, p.stock_quantity, p.image_url, 
                             (sc.quantity * p.price) as item_total 
                      FROM shopping_cart sc 
                      JOIN products p ON sc.product_id = p.product_id 
                      WHERE p.is_active = 1 AND ";
            
            if ($user_id) {
                $query .= "sc.user_id = :user_id";
                $params = [':user_id' => $user_id];
            } else {
                $query .= "sc.session_id = :session_id";
                $params = [':session_id' => $session_id];
            }
            
            $query .= " ORDER BY sc.added_at DESC";
            
            $stmt = $this->db->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("getCartItems error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get cart total amount
     */
    public function getCartTotal() {
        $items = $this->getCartItems();
        $total = 0;
        foreach ($items as $item) {
            $total += $item['item_total'];
        }
        return $total;
    }
    
    /**
     * Get categories list
     */
    public function getCategories() {
        try {
            if (!$this->tableExists('categories')) {
                return $this->getSampleCategories();
            }
            
            $query = "SELECT * FROM categories ORDER BY category_name";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return !empty($categories) ? $categories : $this->getSampleCategories();
            
        } catch (PDOException $e) {
            error_log("getCategories error: " . $e->getMessage());
            return $this->getSampleCategories();
        }
    }
    
    /**
     * Get products by category
     */
    public function getProductsByCategory($category_id, $limit = 12, $page = 1) {
        try {
            if (!$this->tableExists('products')) {
                return [
                    'products' => $this->getSampleProducts($limit),
                    'total' => $limit,
                    'pages' => 1
                ];
            }
            
            $offset = ($page - 1) * $limit;
            
            // Get products
            $query = "SELECT p.*, c.category_name 
                      FROM products p 
                      LEFT JOIN categories c ON p.category_id = c.category_id 
                      WHERE p.category_id = :category_id AND p.is_active = 1 
                      ORDER BY p.created_at DESC 
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get total count
            $count_query = "SELECT COUNT(*) as total FROM products WHERE category_id = :category_id AND is_active = 1";
            $count_stmt = $this->db->prepare($count_query);
            $count_stmt->bindValue(':category_id', $category_id);
            $count_stmt->execute();
            $total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            return [
                'products' => !empty($products) ? $products : [],
                'total' => $total,
                'pages' => ceil($total / $limit)
            ];
            
        } catch (PDOException $e) {
            error_log("getProductsByCategory error: " . $e->getMessage());
            return [
                'products' => [],
                'total' => 0,
                'pages' => 0
            ];
        }
    }
    
    /**
     * Get user orders
     */
    public function getUserOrders($user_id, $limit = 10, $page = 1) {
        try {
            if (!$this->tableExists('orders')) {
                return [
                    'orders' => [],
                    'total' => 0,
                    'pages' => 0
                ];
            }
            
            $offset = ($page - 1) * $limit;
            
            $query = "SELECT o.*, 
                             (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.order_id) as item_count
                      FROM orders o 
                      WHERE o.user_id = :user_id 
                      ORDER BY o.order_date DESC 
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get total count
            $count_query = "SELECT COUNT(*) as total FROM orders WHERE user_id = :user_id";
            $count_stmt = $this->db->prepare($count_query);
            $count_stmt->bindValue(':user_id', $user_id);
            $count_stmt->execute();
            $total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            return [
                'orders' => $orders,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ];
            
        } catch (PDOException $e) {
            error_log("getUserOrders error: " . $e->getMessage());
            return [
                'orders' => [],
                'total' => 0,
                'pages' => 0
            ];
        }
    }
    
    /**
     * Get order details
     */
    public function getOrderDetails($order_id, $user_id = null) {
        try {
            if (!$this->tableExists('orders') || !$this->tableExists('order_items')) {
                return null;
            }
            
            $query = "SELECT o.*, p.payment_status, p.transaction_id 
                      FROM orders o 
                      LEFT JOIN payments p ON o.order_id = p.order_id 
                      WHERE o.order_id = :order_id";
            
            if ($user_id) {
                $query .= " AND o.user_id = :user_id";
                $params = [':order_id' => $order_id, ':user_id' => $user_id];
            } else {
                $params = [':order_id' => $order_id];
            }
            
            $stmt = $this->db->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$order) {
                return null;
            }
            
            // Get order items
            $items_query = "SELECT oi.*, p.image_url 
                           FROM order_items oi 
                           LEFT JOIN products p ON oi.product_id = p.product_id 
                           WHERE oi.order_id = :order_id";
            $items_stmt = $this->db->prepare($items_query);
            $items_stmt->bindValue(':order_id', $order_id);
            $items_stmt->execute();
            $items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'order' => $order,
                'items' => $items
            ];
            
        } catch (PDOException $e) {
            error_log("getOrderDetails error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create new order
     */
    public function createOrder($order_data) {
        try {
            if (!$this->tableExists('orders') || !$this->tableExists('order_items')) {
                return ['success' => false, 'message' => 'Database tables not ready'];
            }
            
            $this->db->beginTransaction();
            
            $user_id = $order_data['user_id'];
            $cart_items = $order_data['cart_items'];
            $shipping_address = $order_data['shipping_address'];
            $billing_address = $order_data['billing_address'] ?? $shipping_address;
            $payment_method = $order_data['payment_method'] ?? 'credit_card';
            $customer_notes = $order_data['customer_notes'] ?? '';
            
            // Calculate totals
            $subtotal = 0;
            foreach ($cart_items as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            
            $tax_amount = $subtotal * 0.1; // 10% tax
            $shipping_amount = 9.99; // Fixed shipping
            $grand_total = $subtotal + $tax_amount + $shipping_amount;
            
            // Generate order number
            $order_number = 'ORD-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Create order
            $order_query = "INSERT INTO orders (order_number, user_id, total_amount, tax_amount, shipping_amount, grand_total, 
                           shipping_address, billing_address, payment_method, customer_notes, ip_address, user_agent) 
                           VALUES (:order_number, :user_id, :total_amount, :tax_amount, :shipping_amount, :grand_total, 
                           :shipping_address, :billing_address, :payment_method, :customer_notes, :ip_address, :user_agent)";
            
            $order_stmt = $this->db->prepare($order_query);
            $order_stmt->bindValue(':order_number', $order_number);
            $order_stmt->bindValue(':user_id', $user_id);
            $order_stmt->bindValue(':total_amount', $subtotal);
            $order_stmt->bindValue(':tax_amount', $tax_amount);
            $order_stmt->bindValue(':shipping_amount', $shipping_amount);
            $order_stmt->bindValue(':grand_total', $grand_total);
            $order_stmt->bindValue(':shipping_address', $shipping_address);
            $order_stmt->bindValue(':billing_address', $billing_address);
            $order_stmt->bindValue(':payment_method', $payment_method);
            $order_stmt->bindValue(':customer_notes', $customer_notes);
            $order_stmt->bindValue(':ip_address', $_SERVER['REMOTE_ADDR'] ?? '');
            $order_stmt->bindValue(':user_agent', $_SERVER['HTTP_USER_AGENT'] ?? '');
            $order_stmt->execute();
            
            $order_id = $this->db->lastInsertId();
            
            // Add order items
            foreach ($cart_items as $item) {
                $item_query = "INSERT INTO order_items (order_id, product_id, product_name, product_sku, quantity, unit_price, total_price) 
                              VALUES (:order_id, :product_id, :product_name, :product_sku, :quantity, :unit_price, :total_price)";
                $item_stmt = $this->db->prepare($item_query);
                $item_stmt->bindValue(':order_id', $order_id);
                $item_stmt->bindValue(':product_id', $item['product_id']);
                $item_stmt->bindValue(':product_name', $item['product_name']);
                $item_stmt->bindValue(':product_sku', $item['sku'] ?? '');
                $item_stmt->bindValue(':quantity', $item['quantity']);
                $item_stmt->bindValue(':unit_price', $item['price']);
                $item_stmt->bindValue(':total_price', $item['price'] * $item['quantity']);
                $item_stmt->execute();
                
                // Update product sales count and stock
                $this->updateProductAfterSale($item['product_id'], $item['quantity']);
            }
            
            // Create payment record
            $payment_query = "INSERT INTO payments (order_id, payment_method, amount, payment_status) 
                             VALUES (:order_id, :payment_method, :amount, 'pending')";
            $payment_stmt = $this->db->prepare($payment_query);
            $payment_stmt->bindValue(':order_id', $order_id);
            $payment_stmt->bindValue(':payment_method', $payment_method);
            $payment_stmt->bindValue(':amount', $grand_total);
            $payment_stmt->execute();
            
            // Clear cart
            $this->clearCart($user_id);
            
            $this->db->commit();
            
            // Send notification
            $this->sendNotification($user_id, 'Order Placed', "Your order #{$order_number} has been placed successfully.", 'success');
            
            return [
                'success' => true, 
                'message' => 'Order created successfully',
                'order_id' => $order_id,
                'order_number' => $order_number
            ];
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("createOrder error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Order creation failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Send notification to user
     */
    public function sendNotification($user_id, $title, $message, $type = 'info') {
        try {
            if (!$this->tableExists('notifications')) {
                return false;
            }
            
            $query = "INSERT INTO notifications (user_id, title, message, type) 
                      VALUES (:user_id, :title, :message, :type)";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':user_id', $user_id);
            $stmt->bindValue(':title', $title);
            $stmt->bindValue(':message', $message);
            $stmt->bindValue(':type', $type);
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("sendNotification error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user notifications
     */
    public function getUserNotifications($user_id, $limit = 10) {
        try {
            if (!$this->tableExists('notifications')) {
                return [];
            }
            
            $query = "SELECT * FROM notifications 
                      WHERE user_id = :user_id 
                      ORDER BY created_at DESC 
                      LIMIT :limit";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':user_id', $user_id);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("getUserNotifications error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Add product review
     */
    public function addProductReview($review_data) {
        try {
            if (!$this->tableExists('product_reviews')) {
                return ['success' => false, 'message' => 'Reviews system not available'];
            }
            
            $product_id = $review_data['product_id'];
            $user_id = $review_data['user_id'];
            $rating = $review_data['rating'];
            $title = Security::sanitizeInput($review_data['title'] ?? '');
            $review_text = Security::sanitizeInput($review_data['review_text'] ?? '');
            
            // Check if user already reviewed this product
            $check_query = "SELECT review_id FROM product_reviews WHERE product_id = :product_id AND user_id = :user_id";
            $check_stmt = $this->db->prepare($check_query);
            $check_stmt->bindValue(':product_id', $product_id);
            $check_stmt->bindValue(':user_id', $user_id);
            $check_stmt->execute();
            
            if ($check_stmt->fetch()) {
                return ['success' => false, 'message' => 'You have already reviewed this product'];
            }
            
            $insert_query = "INSERT INTO product_reviews (product_id, user_id, rating, title, review_text, is_approved) 
                            VALUES (:product_id, :user_id, :rating, :title, :review_text, 1)";
            $stmt = $this->db->prepare($insert_query);
            $stmt->bindValue(':product_id', $product_id);
            $stmt->bindValue(':user_id', $user_id);
            $stmt->bindValue(':rating', $rating);
            $stmt->bindValue(':title', $title);
            $stmt->bindValue(':review_text', $review_text);
            
            if ($stmt->execute()) {
                // Update product rating
                $this->updateProductRating($product_id);
                return ['success' => true, 'message' => 'Review submitted successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to submit review'];
            }
            
        } catch (PDOException $e) {
            error_log("addProductReview error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Review submission failed'];
        }
    }
    
    /**
     * Get product reviews
     */
    public function getProductReviews($product_id, $limit = 10) {
        try {
            if (!$this->tableExists('product_reviews')) {
                return [];
            }
            
            $query = "SELECT r.*, u.first_name, u.last_name 
                      FROM product_reviews r 
                      JOIN users u ON r.user_id = u.user_id 
                      WHERE r.product_id = :product_id AND r.is_approved = 1 
                      ORDER BY r.created_at DESC 
                      LIMIT :limit";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':product_id', $product_id);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("getProductReviews error: " . $e->getMessage());
            return [];
        }
    }
    
    // ===== PRIVATE HELPER METHODS =====
    
    /**
     * Check if table exists
     */
    private function tableExists($table_name) {
        try {
            $result = $this->db->query("SELECT 1 FROM $table_name LIMIT 1");
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Increment product view count
     */
    private function incrementProductViews($product_id) {
        try {
            $query = "UPDATE products SET views_count = views_count + 1 WHERE product_id = :product_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':product_id', $product_id);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("incrementProductViews error: " . $e->getMessage());
        }
    }
    
    /**
     * Update product after sale
     */
    private function updateProductAfterSale($product_id, $quantity) {
        try {
            $query = "UPDATE products SET 
                      sales_count = sales_count + :quantity,
                      stock_quantity = stock_quantity - :quantity
                      WHERE product_id = :product_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':product_id', $product_id);
            $stmt->bindValue(':quantity', $quantity);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("updateProductAfterSale error: " . $e->getMessage());
        }
    }
    
    /**
     * Update product rating
     */
    private function updateProductRating($product_id) {
        try {
            $query = "UPDATE products SET 
                      rating_avg = (SELECT AVG(rating) FROM product_reviews WHERE product_id = :product_id AND is_approved = 1),
                      review_count = (SELECT COUNT(*) FROM product_reviews WHERE product_id = :product_id AND is_approved = 1)
                      WHERE product_id = :product_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':product_id', $product_id);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("updateProductRating error: " . $e->getMessage());
        }
    }
    
    /**
     * Clear user cart
     */
    private function clearCart($user_id) {
        try {
            $session_id = session_id();
            
            $delete_query = "DELETE FROM shopping_cart WHERE user_id = :user_id OR session_id = :session_id";
            $delete_stmt = $this->db->prepare($delete_query);
            $delete_stmt->bindValue(':user_id', $user_id);
            $delete_stmt->bindValue(':session_id', $session_id);
            $delete_stmt->execute();
        } catch (PDOException $e) {
            error_log("clearCart error: " . $e->getMessage());
        }
    }
    
    /**
     * Get sample products for demo when database is not ready
     */
    
private function getSampleProducts($limit = 8) {
    return [
        [
            'product_id' => 1,
            'product_name' => 'iPhone 15 Pro',
            'short_description' => 'Latest Apple iPhone with advanced features',
            'description' => 'The most advanced iPhone with titanium design, A17 Pro chip, and professional camera system.',
            'price' => 999.99,
            'compare_price' => 1099.99,
            'image_url' => 'images/iphone15.jpg',
            'stock_quantity' => 50,
            'is_featured' => 1,
            'category_name' => 'Smartphones',
            'rating_avg' => 4.5,
            'review_count' => 128
        ],
        [
            'product_id' => 2,
            'product_name' => 'Samsung Galaxy S24',
            'short_description' => 'AI-powered smartphone',
            'description' => 'Next-generation smartphone with advanced AI capabilities and stunning display.',
            'price' => 849.99,
            'compare_price' => 899.99,
            'image_url' => 'images/s24.jpg',
            'stock_quantity' => 75,
            'is_featured' => 1,
            'category_name' => 'Smartphones',
            'rating_avg' => 4.3,
            'review_count' => 89
        ],
        [
            'product_id' => 3,
            'product_name' => 'MacBook Pro 16"',
            'short_description' => 'Professional laptop',
            'description' => 'Powerful laptop for professionals with M3 Max chip and stunning Retina display.',
            'price' => 2499.99,
            'compare_price' => 2799.99,
            'image_url' => 'images/macbook.png',
            'stock_quantity' => 25,
            'is_featured' => 1,
            'category_name' => 'Laptops',
            'rating_avg' => 4.8,
            'review_count' => 64
        ],
        [
            'product_id' => 4,
            'product_name' => 'Dell XPS 15',
            'short_description' => 'Powerful Windows laptop',
            'description' => 'High-performance Windows laptop with OLED display and Intel Core i9 processor.',
            'price' => 1899.99,
            'compare_price' => 1999.99,
            'image_url' => 'images/dell.jpg',
            'stock_quantity' => 30,
            'is_featured' => 0,
            'category_name' => 'Laptops',
            'rating_avg' => 4.6,
            'review_count' => 42
        ],
        [
            'product_id' => 5,
            'product_name' => 'Wireless Headphones',
            'short_description' => 'Noise-cancelling audio',
            'description' => 'Premium wireless headphones with active noise cancellation and 30-hour battery.',
            'price' => 199.99,
            'compare_price' => 249.99,
            'image_url' => 'images/headphone.jpg',
            'stock_quantity' => 100,
            'is_featured' => 1,
            'category_name' => 'Electronics',
            'rating_avg' => 4.6,
            'review_count' => 215
        ],
        [
            'product_id' => 6,
            'product_name' => 'Smart Watch Series 8',
            'short_description' => 'Advanced health monitoring',
            'description' => 'Feature-rich smartwatch with comprehensive health tracking and always-on display.',
            'price' => 399.99,
            'compare_price' => 429.99,
            'image_url' => 'images/watch.jpg',
            'stock_quantity' => 60,
            'is_featured' => 1,
            'category_name' => 'Electronics',
            'rating_avg' => 4.4,
            'review_count' => 156
        ],
        [
            'product_id' => 7,
            'product_name' => 'Programming Book',
            'short_description' => 'Complete web development guide',
            'description' => 'Comprehensive guide covering modern web development technologies and best practices.',
            'price' => 49.99,
            'compare_price' => 59.99,
            'image_url' => 'images/book.jpg',
            'stock_quantity' => 200,
            'is_featured' => 1,
            'category_name' => 'Books',
            'rating_avg' => 4.7,
            'review_count' => 89
        ],
        [
            'product_id' => 8,
            'product_name' => 'Cotton T-Shirt',
            'short_description' => 'Comfortable everyday wear',
            'description' => 'Soft and durable cotton t-shirt perfect for casual wear and everyday comfort.',
            'price' => 24.99,
            'compare_price' => 29.99,
            'image_url' => 'images/cotton.jpg',
            'stock_quantity' => 150,
            'is_featured' => 0,
            'category_name' => 'Clothing',
            'rating_avg' => 4.2,
            'review_count' => 67
        ]
    ];
}
    
    /**
     * Get sample categories for demo
     */
    private function getSampleCategories() {
        return [
            [
                'category_id' => 1,
                'category_name' => 'Electronics',
                'description' => 'Latest electronic devices'
            ],
            [
                'category_id' => 2,
                'category_name' => 'Smartphones',
                'description' => 'Mobile phones and accessories'
            ],
            [
                'category_id' => 3,
                'category_name' => 'Laptops',
                'description' => 'Computing devices'
            ],
            [
                'category_id' => 4,
                'category_name' => 'Home Appliances',
                'description' => 'Home and kitchen appliances'
            ]
        ];
    }
    /**
 * Get product image URL with fallback
 */
public function getProductImage($product, $default = 'images/placeholder.jpg') {
    $image_url = $product['image_url'] ?? '';
    
    // Check if image file exists
    if (!empty($image_url) && file_exists($image_url)) {
        return $image_url;
    }
    
    // Fallback to default image
    return $default;
}

/**
 * Check if image exists
 */
private function imageExists($image_path) {
    return !empty($image_path) && file_exists($image_path);
}
    /**
     * Get dashboard statistics
     */
    public function getDashboardStats() {
        try {
            $stats = [];
            
            // Total users
            if ($this->tableExists('users')) {
                $stmt = $this->db->query("SELECT COUNT(*) as count FROM users");
                $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            } else {
                $stats['total_users'] = 2;
            }
            
            // Total products
            if ($this->tableExists('products')) {
                $stmt = $this->db->query("SELECT COUNT(*) as count FROM products");
                $stats['total_products'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            } else {
                $stats['total_products'] = 6;
            }
            
            // Total orders
            if ($this->tableExists('orders')) {
                $stmt = $this->db->query("SELECT COUNT(*) as count FROM orders");
                $stats['total_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                $stmt = $this->db->query("SELECT SUM(grand_total) as total FROM orders WHERE status != 'cancelled'");
                $stats['total_revenue'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            } else {
                $stats['total_orders'] = 0;
                $stats['total_revenue'] = 0;
            }
            
            return $stats;
            
        } catch (PDOException $e) {
            error_log("getDashboardStats error: " . $e->getMessage());
            return [
                'total_users' => 2,
                'total_products' => 6,
                'total_orders' => 0,
                'total_revenue' => 0
                
            ];
        }
    }
}
?>