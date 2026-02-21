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
    case 'DELETE':
        handleDeleteRequest($action, $functions);
        break;
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

function handleGetRequest($action, $functions) {
    switch ($action) {
        case 'count':
            $items = $functions->getCartItems();
            $count = 0;
            foreach ($items as $item) {
                $count += $item['quantity'];
            }
            echo json_encode(['success' => true, 'count' => $count]);
            break;
            
        case 'items':
            $items = $functions->getCartItems();
            echo json_encode(['success' => true, 'items' => $items]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

function handlePostRequest($action, $functions) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'add':
            $product_id = $input['product_id'] ?? 0;
            $quantity = $input['quantity'] ?? 1;
            $attributes = $input['attributes'] ?? [];
            
            if (!$product_id || $quantity < 1) {
                echo json_encode(['success' => false, 'message' => 'Invalid product or quantity']);
                return;
            }
            
            // Check product availability
            $product_query = "SELECT stock_quantity, product_name, is_active FROM products WHERE product_id = :product_id";
            $product_stmt = $functions->db->prepare($product_query);
            $product_stmt->bindValue(':product_id', $product_id);
            $product_stmt->execute();
            $product = $product_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$product || !$product['is_active']) {
                echo json_encode(['success' => false, 'message' => 'Product not available']);
                return;
            }
            
            if ($product['stock_quantity'] < $quantity) {
                echo json_encode(['success' => false, 'message' => 'Insufficient stock. Only ' . $product['stock_quantity'] . ' available']);
                return;
            }
            
            $result = $functions->addToCart($product_id, $quantity, $attributes);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Product added to cart']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add product to cart']);
            }
            break;
            
        case 'update':
            $cart_id = $input['cart_id'] ?? 0;
            $quantity = $input['quantity'] ?? 1;
            
            if (!$cart_id || $quantity < 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid input']);
                return;
            }
            
            if ($quantity == 0) {
                // Remove item
                $delete_query = "DELETE FROM shopping_cart WHERE cart_id = :cart_id";
                $delete_stmt = $functions->db->prepare($delete_query);
                $delete_stmt->bindValue(':cart_id', $cart_id);
                $result = $delete_stmt->execute();
            } else {
                // Update quantity
                $update_query = "UPDATE shopping_cart SET quantity = :quantity, updated_at = NOW() WHERE cart_id = :cart_id";
                $update_stmt = $functions->db->prepare($update_query);
                $update_stmt->bindValue(':quantity', $quantity);
                $update_stmt->bindValue(':cart_id', $cart_id);
                $result = $update_stmt->execute();
            }
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Cart updated']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update cart']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

function handleDeleteRequest($action, $functions) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'remove':
            $cart_id = $input['cart_id'] ?? 0;
            
            if (!$cart_id) {
                echo json_encode(['success' => false, 'message' => 'Cart ID required']);
                return;
            }
            
            $delete_query = "DELETE FROM shopping_cart WHERE cart_id = :cart_id";
            $delete_stmt = $functions->db->prepare($delete_query);
            $delete_stmt->bindValue(':cart_id', $cart_id);
            
            if ($delete_stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to remove item']);
            }
            break;
            
        case 'clear':
            $user_id = $_SESSION['user_id'] ?? null;
            $session_id = session_id();
            
            $delete_query = "DELETE FROM shopping_cart WHERE ";
            $params = [];
            
            if ($user_id) {
                $delete_query .= "user_id = :user_id";
                $params[':user_id'] = $user_id;
            } else {
                $delete_query .= "session_id = :session_id";
                $params[':session_id'] = $session_id;
            }
            
            $delete_stmt = $functions->db->prepare($delete_query);
            foreach ($params as $key => $value) {
                $delete_stmt->bindValue($key, $value);
            }
            
            if ($delete_stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Cart cleared']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to clear cart']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}
?>