<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();
$functions = new Functions($db);

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($method) {
    case 'GET':
        handleGetRequest($action, $functions);
        break;
    case 'POST':
        handlePostRequest($action, $functions);
        break;
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

function handleGetRequest($action, $functions) {
    switch ($action) {
        case 'featured':
            $limit = $_GET['limit'] ?? 8;
            $products = $functions->getFeaturedProducts($limit);
            echo json_encode(['success' => true, 'products' => $products]);
            break;
            
        case 'detail':
            $product_id = $_GET['id'] ?? 0;
            if ($product_id) {
                $product = $functions->getProductById($product_id);
                if ($product) {
                    // Increment view count
                    $update_query = "UPDATE products SET views_count = views_count + 1 WHERE product_id = :product_id";
                    $stmt = $functions->db->prepare($update_query);
                    $stmt->bindValue(':product_id', $product_id);
                    $stmt->execute();
                    
                    // Get product attributes
                    $attr_query = "SELECT * FROM product_attributes WHERE product_id = :product_id ORDER BY display_order";
                    $attr_stmt = $functions->db->prepare($attr_query);
                    $attr_stmt->bindValue(':product_id', $product_id);
                    $attr_stmt->execute();
                    $attributes = $attr_stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Get related products
                    $related_query = "SELECT * FROM products WHERE category_id = :category_id AND product_id != :product_id AND is_active = 1 LIMIT 4";
                    $related_stmt = $functions->db->prepare($related_query);
                    $related_stmt->bindValue(':category_id', $product['category_id']);
                    $related_stmt->bindValue(':product_id', $product_id);
                    $related_stmt->execute();
                    $related_products = $related_stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo json_encode([
                        'success' => true, 
                        'product' => $product,
                        'attributes' => $attributes,
                        'related_products' => $related_products
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Product not found']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Product ID required']);
            }
            break;
            
        case 'category':
            $category_id = $_GET['category_id'] ?? 0;
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 12;
            $sort = $_GET['sort'] ?? 'newest';
            
            $offset = ($page - 1) * $limit;
            
            $query = "SELECT * FROM products WHERE is_active = 1";
            $count_query = "SELECT COUNT(*) as total FROM products WHERE is_active = 1";
            $params = [];
            
            if ($category_id) {
                $query .= " AND category_id = :category_id";
                $count_query .= " AND category_id = :category_id";
                $params[':category_id'] = $category_id;
            }
            
            // Add sorting
            switch ($sort) {
                case 'price_low':
                    $query .= " ORDER BY price ASC";
                    break;
                case 'price_high':
                    $query .= " ORDER BY price DESC";
                    break;
                case 'name':
                    $query .= " ORDER BY product_name ASC";
                    break;
                case 'popular':
                    $query .= " ORDER BY sales_count DESC, views_count DESC";
                    break;
                default:
                    $query .= " ORDER BY created_at DESC";
            }
            
            $query .= " LIMIT :limit OFFSET :offset";
            $params[':limit'] = $limit;
            $params[':offset'] = $offset;
            
            $stmt = $functions->db->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get total count
            $count_stmt = $functions->db->prepare($count_query);
            if ($category_id) {
                $count_stmt->bindValue(':category_id', $category_id);
            }
            $count_stmt->execute();
            $total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            echo json_encode([
                'success' => true,
                'products' => $products,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => ceil($total / $limit),
                    'total_products' => $total,
                    'limit' => $limit
                ]
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

function handlePostRequest($action, $functions) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'review':
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Login required']);
                return;
            }
            
            $product_id = $input['product_id'] ?? 0;
            $rating = $input['rating'] ?? 0;
            $title = Security::sanitizeInput($input['title'] ?? '');
            $review_text = Security::sanitizeInput($input['review_text'] ?? '');
            
            if (!$product_id || $rating < 1 || $rating > 5) {
                echo json_encode(['success' => false, 'message' => 'Invalid input']);
                return;
            }
            
            // Check if user already reviewed this product
            $check_query = "SELECT review_id FROM product_reviews WHERE product_id = :product_id AND user_id = :user_id";
            $check_stmt = $functions->db->prepare($check_query);
            $check_stmt->bindValue(':product_id', $product_id);
            $check_stmt->bindValue(':user_id', $_SESSION['user_id']);
            $check_stmt->execute();
            
            if ($check_stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'You have already reviewed this product']);
                return;
            }
            
            $insert_query = "INSERT INTO product_reviews (product_id, user_id, rating, title, review_text, is_approved) 
                            VALUES (:product_id, :user_id, :rating, :title, :review_text, 1)";
            $stmt = $functions->db->prepare($insert_query);
            $stmt->bindValue(':product_id', $product_id);
            $stmt->bindValue(':user_id', $_SESSION['user_id']);
            $stmt->bindValue(':rating', $rating);
            $stmt->bindValue(':title', $title);
            $stmt->bindValue(':review_text', $review_text);
            
            if ($stmt->execute()) {
                // Update product rating
                $update_query = "UPDATE products SET 
                                rating_avg = (SELECT AVG(rating) FROM product_reviews WHERE product_id = :product_id AND is_approved = 1),
                                review_count = (SELECT COUNT(*) FROM product_reviews WHERE product_id = :product_id AND is_approved = 1)
                                WHERE product_id = :product_id";
                $update_stmt = $functions->db->prepare($update_query);
                $update_stmt->bindValue(':product_id', $product_id);
                $update_stmt->execute();
                
                echo json_encode(['success' => true, 'message' => 'Review submitted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to submit review']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}
?>