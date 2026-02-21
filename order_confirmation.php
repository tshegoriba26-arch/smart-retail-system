<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$database = new Database();
$db = $database->getConnection();
$functions = new Functions($db);
$auth = new Auth($db);

// Redirect if not logged in
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$order_id = $_GET['order_id'] ?? 0;

if (!$order_id) {
    header('Location: profile.php');
    exit;
}

// Get order details
$order_query = "SELECT o.*, p.payment_status, p.transaction_id 
                FROM orders o 
                LEFT JOIN payments p ON o.order_id = p.order_id 
                WHERE o.order_id = :order_id AND o.user_id = :user_id";
$order_stmt = $db->prepare($order_query);
$order_stmt->bindValue(':order_id', $order_id);
$order_stmt->bindValue(':user_id', $_SESSION['user_id']);
$order_stmt->execute();
$order = $order_stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: profile.php');
    exit;
}

// Get order items
$items_query = "SELECT oi.*, p.image_url 
                FROM order_items oi 
                LEFT JOIN products p ON oi.product_id = p.product_id 
                WHERE oi.order_id = :order_id";
$items_stmt = $db->prepare($items_query);
$items_stmt->bindValue(':order_id', $order_id);
$items_stmt->execute();
$items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Smart Retail System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="confirmation-page">
            <div class="confirmation-header">
                <div class="confirmation-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1>Order Confirmed!</h1>
                <p>Thank you for your purchase. Your order has been received.</p>
            </div>

            <div class="confirmation-details">
                <div class="order-summary-card">
                    <h3>Order Summary</h3>
                    <div class="summary-row">
                        <span>Order Number:</span>
                        <strong><?php echo $order['order_number']; ?></strong>
                    </div>
                    <div class="summary-row">
                        <span>Order Date:</span>
                        <span><?php echo date('F j, Y g:i A', strtotime($order['order_date'])); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Order Status:</span>
                        <span class="status-badge <?php echo $order['status']; ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </div>
                    <div class="summary-row">
                        <span>Payment Status:</span>
                        <span class="payment-status <?php echo $order['payment_status']; ?>">
                            <?php echo ucfirst($order['payment_status']); ?>
                        </span>
                    </div>
                    <div class="summary-row total">
                        <span>Total Amount:</span>
                        <strong>$<?php echo number_format($order['grand_total'], 2); ?></strong>
                    </div>
                </div>

                <div class="order-items">
                    <h3>Order Items</h3>
                    <?php foreach ($items as $item): ?>
                        <div class="order-item">
                            <img src="<?php echo $item['image_url'] ?? 'images/placeholder.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                 class="item-image">
                            <div class="item-details">
                                <h4><?php echo htmlspecialchars($item['product_name']); ?></h4>
                                <p>SKU: <?php echo $item['product_sku']; ?></p>
                                <p>Quantity: <?php echo $item['quantity']; ?></p>
                            </div>
                            <div class="item-price">
                                $<?php echo number_format($item['total_price'], 2); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="shipping-details">
                    <h3>Shipping Information</h3>
                    <div class="address-card">
                        <p><strong>Shipping Address:</strong></p>
                        <p><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                    </div>
                    
                    <?php if ($order['tracking_number']): ?>
                        <div class="tracking-info">
                            <p><strong>Tracking Number:</strong> <?php echo $order['tracking_number']; ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="confirmation-actions">
                <a href="profile.php" class="btn btn-outline">
                    <i class="fas fa-user"></i> View My Account
                </a>
                <a href="order_history.php" class="btn btn-outline">
                    <i class="fas fa-history"></i> Order History
                </a>
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fas fa-print"></i> Print Receipt
                </button>
            </div>

            <div class="next-steps">
                <h3>What's Next?</h3>
                <div class="steps-grid">
                    <div class="step">
                        <div class="step-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h4>Order Confirmation</h4>
                        <p>You'll receive an email confirmation shortly</p>
                    </div>
                    <div class="step">
                        <div class="step-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <h4>Order Processing</h4>
                        <p>We'll prepare your order for shipment</p>
                    </div>
                    <div class="step">
                        <div class="step-icon">
                            <i class="fas fa-shipping-fast"></i>
                        </div>
                        <h4>Order Shipped</h4>
                        <p>You'll receive tracking information via email</p>
                    </div>
                    <div class="step">
                        <div class="step-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <h4>Order Delivered</h4>
                        <p>Your order will arrive at your doorstep</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <style>
        .confirmation-page {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .confirmation-header {
            text-align: center;
            padding: 2rem;
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            color: white;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        
        .confirmation-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        .confirmation-details {
            display: grid;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .order-summary-card, .shipping-details {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .confirmation-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-bottom: 2rem;
        }
        
        .next-steps {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .steps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .step {
            text-align: center;
            padding: 1rem;
        }
        
        .step-icon {
            font-size: 2rem;
            color: #3498db;
            margin-bottom: 1rem;
        }
        
        @media (max-width: 768px) {
            .confirmation-actions {
                flex-direction: column;
            }
            
            .confirmation-details {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>