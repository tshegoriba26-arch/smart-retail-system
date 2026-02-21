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
    header('Location: login.php?redirect=checkout.php');
    exit;
}

// Get cart items and total
$cart_items = $functions->getCartItems();
$cart_total = $functions->getCartTotal();

// If cart is empty, redirect to cart page
if (empty($cart_items)) {
    header('Location: cart.php');
    exit;
}

// Calculate totals
$subtotal = $cart_total;
$tax_rate = 0.10; // 10% tax
$tax_amount = $subtotal * $tax_rate;
$shipping_amount = 9.99; // Fixed shipping
$grand_total = $subtotal + $tax_amount + $shipping_amount;

// Get user details
$user_query = "SELECT * FROM users WHERE user_id = :user_id";
$user_stmt = $db->prepare($user_query);
$user_stmt->bindValue(':user_id', $_SESSION['user_id']);
$user_stmt->execute();
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);

// Handle checkout form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_address = $_POST['shipping_address'] ?? '';
    $billing_address = $_POST['billing_address'] ?? $shipping_address;
    $payment_method = $_POST['payment_method'] ?? '';
    $customer_notes = $_POST['customer_notes'] ?? '';
    
    // Validate required fields
    $errors = [];
    
    if (empty($shipping_address)) {
        $errors[] = "Shipping address is required";
    }
    
    if (empty($payment_method)) {
        $errors[] = "Payment method is required";
    }
    
    if (empty($errors)) {
        // Process order via API
        $order_data = [
            'cart_items' => $cart_items,
            'shipping_address' => $shipping_address,
            'billing_address' => $billing_address,
            'payment_method' => $payment_method,
            'customer_notes' => $customer_notes
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://' . $_SERVER['HTTP_HOST'] . '/smart-retail-system/api/orders.php?action=create');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($order_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Requested-With: XMLHttpRequest'
        ]);
        
        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            $response = json_decode($result, true);
            if ($response['success']) {
                header('Location: order_confirmation.php?order_id=' . $response['order_id']);
                exit;
            } else {
                $error = $response['message'] ?? 'Order creation failed';
            }
        } else {
            $error = 'Server error occurred. Please try again.';
        }
    } else {
        $error = implode('<br>', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Smart Retail System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="checkout-page">
            <h1>Checkout</h1>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="checkout-layout">
                <div class="checkout-form">
                    <form method="POST" id="checkoutForm" class="ajax-form">
                        <!-- Shipping Address -->
                        <div class="form-section">
                            <h2>
                                <i class="fas fa-shipping-fast"></i>
                                Shipping Address
                            </h2>
                            <div class="form-group">
                                <label for="shipping_address" class="form-label">Address *</label>
                                <textarea id="shipping_address" name="shipping_address" class="form-control" rows="4" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <!-- Billing Address -->
                        <div class="form-section">
                            <h2>
                                <i class="fas fa-file-invoice-dollar"></i>
                                Billing Address
                            </h2>
                            <div class="form-check">
                                <input type="checkbox" id="same_as_shipping" checked>
                                <label for="same_as_shipping">Same as shipping address</label>
                            </div>
                            <div class="form-group" id="billing_address_group" style="display: none;">
                                <label for="billing_address" class="form-label">Billing Address</label>
                                <textarea id="billing_address" name="billing_address" class="form-control" rows="4"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="form-section">
                            <h2>
                                <i class="fas fa-credit-card"></i>
                                Payment Method
                            </h2>
                            <div class="payment-methods">
                                <div class="payment-method">
                                    <input type="radio" id="credit_card" name="payment_method" value="credit_card" required>
                                    <label for="credit_card">
                                        <i class="fas fa-credit-card"></i> Credit Card
                                    </label>
                                </div>
                                <div class="payment-method">
                                    <input type="radio" id="paypal" name="payment_method" value="paypal">
                                    <label for="paypal">
                                        <i class="fab fa-paypal"></i> PayPal
                                    </label>
                                </div>
                                <div class="payment-method">
                                    <input type="radio" id="bank_transfer" name="payment_method" value="bank_transfer">
                                    <label for="bank_transfer">
                                        <i class="fas fa-university"></i> Bank Transfer
                                    </label>
                                </div>
                                <div class="payment-method">
                                    <input type="radio" id="cash_on_delivery" name="payment_method" value="cash_on_delivery">
                                    <label for="cash_on_delivery">
                                        <i class="fas fa-money-bill-wave"></i> Cash on Delivery
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Order Notes -->
                        <div class="form-section">
                            <h2>
                                <i class="fas fa-sticky-note"></i>
                                Additional Information
                            </h2>
                            <div class="form-group">
                                <label for="customer_notes" class="form-label">Order Notes</label>
                                <textarea id="customer_notes" name="customer_notes" class="form-control" rows="4" placeholder="Any special instructions for your order..."></textarea>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="order-summary">
                    <div class="summary-card">
                        <h3>Order Summary</h3>
                        <div class="order-items">
                            <?php foreach ($cart_items as $item): ?>
                                <div class="order-item">
                                    <div class="item-image">
                                        <img src="<?php echo $item['image_url'] ?? 'images/placeholder.jpg'; ?>" 
                                             alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                    </div>
                                    <div class="item-details">
                                        <h4><?php echo htmlspecialchars($item['product_name']); ?></h4>
                                        <p>Qty: <?php echo $item['quantity']; ?></p>
                                        <p class="item-price">$<?php echo number_format($item['price'], 2); ?> each</p>
                                    </div>
                                    <div class="item-total">
                                        $<?php echo number_format($item['item_total'], 2); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="summary-totals">
                            <div class="summary-row">
                                <span>Subtotal:</span>
                                <span>$<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            <div class="summary-row">
                                <span>Shipping:</span>
                                <span>$<?php echo number_format($shipping_amount, 2); ?></span>
                            </div>
                            <div class="summary-row">
                                <span>Tax (10%):</span>
                                <span>$<?php echo number_format($tax_amount, 2); ?></span>
                            </div>
                            <div class="summary-row total">
                                <span>Total:</span>
                                <span>$<?php echo number_format($grand_total, 2); ?></span>
                            </div>
                        </div>

                        <button type="submit" form="checkoutForm" class="btn btn-primary btn-block btn-lg">
                            <i class="fas fa-lock"></i> Place Order
                        </button>
                        
                        <div class="security-notice">
                            <p>
                                <i class="fas fa-shield-alt"></i>
                                Your payment information is secure and encrypted
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script>
        // Same as shipping address toggle
        const sameAsShipping = document.getElementById('same_as_shipping');
        const billingAddressGroup = document.getElementById('billing_address_group');
        const billingAddress = document.getElementById('billing_address');
        const shippingAddress = document.getElementById('shipping_address');

        sameAsShipping.addEventListener('change', function() {
            if (this.checked) {
                billingAddressGroup.style.display = 'none';
                billingAddress.value = shippingAddress.value;
            } else {
                billingAddressGroup.style.display = 'block';
            }
        });

        // Copy shipping address to billing address when shipping address changes
        shippingAddress.addEventListener('input', function() {
            if (sameAsShipping.checked) {
                billingAddress.value = this.value;
            }
        });

        // Form validation
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
            if (!paymentMethod) {
                e.preventDefault();
                alert('Please select a payment method.');
                return;
            }
            
            // Additional validation can be added here
            const shippingAddress = document.getElementById('shipping_address').value.trim();
            if (!shippingAddress) {
                e.preventDefault();
                alert('Please enter your shipping address.');
                return;
            }
        });

        // Real-time form validation
        document.querySelectorAll('#checkoutForm input, #checkoutForm textarea').forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
        });

        function validateField(field) {
            const value = field.value.trim();
            
            if (field.required && !value) {
                field.classList.add('error');
                return false;
            }
            
            field.classList.remove('error');
            return true;
        }
    </script>
</body>
</html>