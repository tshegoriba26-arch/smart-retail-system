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

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

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
    $user_id = $_SESSION['user_id'];
    
    switch ($action) {
        case 'list':
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 10;
            $offset = ($page - 1) * $limit;
            
            $query = "SELECT * FROM orders WHERE user_id = :user_id ORDER BY order_date DESC LIMIT :limit OFFSET :offset";
            $stmt = $functions->db->prepare($query);
            $stmt->bindValue(':user_id', $user_id);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get total count
            $count_query = "SELECT COUNT(*) as total FROM orders WHERE user_id = :user_id";
            $count_stmt = $functions->db->prepare($count_query);
            $count_stmt->bindValue(':user_id', $user_id);
            $count_stmt->execute();
            $total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            echo json_encode([
                'success' => true,
                'orders' => $orders,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => ceil($total / $limit),
                    'total_orders' => $total
                ]
            ]);
            break;
            
        case 'detail':
            $order_id = $_GET['order_id'] ?? 0;
            
            if (!$order_id) {
                echo json_encode(['success' => false, 'message' => 'Order ID required']);
                return;
            }
            
            // Get order details
            $order_query = "SELECT o.*, p.payment_status, p.transaction_id 
                           FROM orders o 
                           LEFT JOIN payments p ON o.order_id = p.order_id 
                           WHERE o.order_id = :order_id AND o.user_id = :user_id";
            $order_stmt = $functions->db->prepare($order_query);
            $order_stmt->bindValue(':order_id', $order_id);
            $order_stmt->bindValue(':user_id', $user_id);
            $order_stmt->execute();
            $order = $order_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$order) {
                echo json_encode(['success' => false, 'message' => 'Order not found']);
                return;
            }
            
            // Get order items
            $items_query = "SELECT oi.*, p.image_url 
                           FROM order_items oi 
                           LEFT JOIN products p ON oi.product_id = p.product_id 
                           WHERE oi.order_id = :order_id";
            $items_stmt = $functions->db->prepare($items_query);
            $items_stmt->bindValue(':order_id', $order_id);
            $items_stmt->execute();
            $items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'order' => $order,
                'items' => $items
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

function handlePostRequest($action, $functions) {
    $input = json_decode(file_get_contents('php://input'), true);
    $user_id = $_SESSION['user_id'];
    
    switch ($action) {
        case 'create':
            $cart_items = $input['cart_items'] ?? [];
            $shipping_address = Security::sanitizeInput($input['shipping_address'] ?? '');
            $billing_address = Security::sanitizeInput($input['billing_address'] ?? $shipping_address);
            $payment_method = Security::sanitizeInput($input['payment_method'] ?? '');
            $customer_notes = Security::sanitizeInput($input['customer_notes'] ?? '');
            
            if (empty($cart_items)) {
                echo json_encode(['success' => false, 'message' => 'Cart is empty']);
                return;
            }
            
            if (empty($shipping_address)) {
                echo json_encode(['success' => false, 'message' => 'Shipping address required']);
                return;
            }
            
            // Calculate totals
            $subtotal = 0;
            foreach ($cart_items as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            
            $tax_amount = $subtotal * 0.1; // 10% tax
            $shipping_amount = 9.99; // Fixed shipping
            $grand_total = $subtotal + $tax_amount + $shipping_amount;
            
            try {
                $functions->db->beginTransaction();
                
                // Generate order number
                $order_number_stmt = $functions->db->query("SELECT CONCAT('ORD-', DATE_FORMAT(NOW(), '%Y%m%d'), '-', LPAD(COALESCE(MAX(SUBSTRING_INDEX(order_number, '-', -1)), 0) + 1, 4, '0')) as new_order_number FROM orders WHERE DATE(order_date) = CURDATE()");
                $order_number = $order_number_stmt->fetch(PDO::FETCH_ASSOC)['new_order_number'];
                
                if (!$order_number) {
                    $order_number = 'ORD-' . date('Ymd') . '-0001';
                }
                
                // Create order
                $order_query = "INSERT INTO orders (order_number, user_id, total_amount, tax_amount, shipping_amount, grand_total, shipping_address, billing_address, payment_method, customer_notes, ip_address, user_agent) 
                               VALUES (:order_number, :user_id, :total_amount, :tax_amount, :shipping_amount, :grand_total, :shipping_address, :billing_address, :payment_method, :customer_notes, :ip_address, :user_agent)";
                $order_stmt = $functions->db->prepare($order_query);
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
                $order_stmt->bindValue(':ip_address', $_SERVER['REMOTE_ADDR']);
                $order_stmt->bindValue(':user_agent', $_SERVER['HTTP_USER_AGENT']);
                $order_stmt->execute();
                
                $order_id = $functions->db->lastInsertId();
                
                // Add order items and update inventory
                foreach ($cart_items as $item) {
                    $item_query = "INSERT INTO order_items (order_id, product_id, product_name, product_sku, quantity, unit_price, total_price) 
                                  VALUES (:order_id, :product_id, :product_name, :product_sku, :quantity, :unit_price, :total_price)";
                    $item_stmt = $functions->db->prepare($item_query);
                    $item_stmt->bindValue(':order_id', $order_id);
                    $item_stmt->bindValue(':product_id', $item['product_id']);
                    $item_stmt->bindValue(':product_name', $item['product_name']);
                    $item_stmt->bindValue(':product_sku', $item['sku']);
                    $item_stmt->bindValue(':quantity', $item['quantity']);
                    $item_stmt->bindValue(':unit_price', $item['price']);
                    $item_stmt->bindValue(':total_price', $item['price'] * $item['quantity']);
                    $item_stmt->execute();
                    
                    // Update inventory
                    $inventory_query = "CALL UpdateProductStock(:product_id, :quantity, 'sale', :order_id, 'Order placement', :user_id)";
                    $inventory_stmt = $functions->db->prepare($inventory_query);
                    $inventory_stmt->bindValue(':product_id', $item['product_id']);
                    $inventory_stmt->bindValue(':quantity', $item['quantity']);
                    $inventory_stmt->bindValue(':order_id', $order_id);
                    $inventory_stmt->bindValue(':user_id', $user_id);
                    $inventory_stmt->execute();
                }
                
                // Create payment record
                $payment_query = "INSERT INTO payments (order_id, payment_method, amount, payment_status) 
                                 VALUES (:order_id, :payment_method, :amount, 'pending')";
                $payment_stmt = $functions->db->prepare($payment_query);
                $payment_stmt->bindValue(':order_id', $order_id);
                $payment_stmt->bindValue(':payment_method', $payment_method);
                $payment_stmt->bindValue(':amount', $grand_total);
                $payment_stmt->execute();
                
                // Clear cart
                $clear_cart_query = "DELETE FROM shopping_cart WHERE user_id = :user_id OR session_id = :session_id";
                $clear_cart_stmt = $functions->db->prepare($clear_cart_query);
                $clear_cart_stmt->bindValue(':user_id', $user_id);
                $clear_cart_stmt->bindValue(':session_id', session_id());
                $clear_cart_stmt->execute();
                
                $functions->db->commit();
                
                // Send notification
                $functions->sendNotification($user_id, 'Order Placed', "Your order #{$order_number} has been placed successfully. Total: $" . number_format($grand_total, 2), 'success');
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Order created successfully',
                    'order_id' => $order_id,
                    'order_number' => $order_number
                ]);
                
            } catch (Exception $e) {
                $functions->db->rollBack();
                echo json_encode(['success' => false, 'message' => 'Order creation failed: ' . $e->getMessage()]);
            }
            break;
            
        case 'cancel':
            $order_id = $input['order_id'] ?? 0;
            
            if (!$order_id) {
                echo json_encode(['success' => false, 'message' => 'Order ID required']);
                return;
            }
            
            // Verify order belongs to user and can be cancelled
            $verify_query = "SELECT order_id, status FROM orders WHERE order_id = :order_id AND user_id = :user_id";
            $verify_stmt = $functions->db->prepare($verify_query);
            $verify_stmt->bindValue(':order_id', $order_id);
            $verify_stmt->bindValue(':user_id', $user_id);
            $verify_stmt->execute();
            $order = $verify_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$order) {
                echo json_encode(['success' => false, 'message' => 'Order not found']);
                return;
            }
            
            if (!in_array($order['status'], ['pending', 'confirmed'])) {
                echo json_encode(['success' => false, 'message' => 'Order cannot be cancelled in its current status']);
                return;
            }
            
            try {
                $functions->db->beginTransaction();
                
                // Update order status
                $update_query = "UPDATE orders SET status = 'cancelled' WHERE order_id = :order_id";
                $update_stmt = $functions->db->prepare($update_query);
                $update_stmt->bindValue(':order_id', $order_id);
                $update_stmt->execute();
                
                // Restore inventory
                $items_query = "SELECT product_id, quantity FROM order_items WHERE order_id = :order_id";
                $items_stmt = $functions->db->prepare($items_query);
                $items_stmt->bindValue(':order_id', $order_id);
                $items_stmt->execute();
                $items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($items as $item) {
                    $inventory_query = "CALL UpdateProductStock(:product_id, :quantity, 'return', :order_id, 'Order cancellation', :user_id)";
                    $inventory_stmt = $functions->db->prepare($inventory_query);
                    $inventory_stmt->bindValue(':product_id', $item['product_id']);
                    $inventory_stmt->bindValue(':quantity', $item['quantity']);
                    $inventory_stmt->bindValue(':order_id', $order_id);
                    $inventory_stmt->bindValue(':user_id', $user_id);
                    $inventory_stmt->execute();
                }
                
                $functions->db->commit();
                
                echo json_encode(['success' => true, 'message' => 'Order cancelled successfully']);
                
            } catch (Exception $e) {
                $functions->db->rollBack();
                echo json_encode(['success' => false, 'message' => 'Order cancellation failed: ' . $e->getMessage()]);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}
?>