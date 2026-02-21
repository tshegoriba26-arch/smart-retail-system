<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$database = new Database();
$db = $database->getConnection();
$functions = new Functions($db);
$auth = new Auth($db);

// Get cart items
$cart_items = $functions->getCartItems();
$cart_total = $functions->getCartTotal();

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update':
            $cart_id = $_POST['cart_id'] ?? 0;
            $quantity = $_POST['quantity'] ?? 1;
            
            if ($cart_id && $quantity > 0) {
                $update_query = "UPDATE shopping_cart SET quantity = :quantity WHERE cart_id = :cart_id";
                $stmt = $db->prepare($update_query);
                $stmt->bindValue(':quantity', $quantity);
                $stmt->bindValue(':cart_id', $cart_id);
                $stmt->execute();
            }
            break;
            
        case 'remove':
            $cart_id = $_POST['cart_id'] ?? 0;
            
            if ($cart_id) {
                $delete_query = "DELETE FROM shopping_cart WHERE cart_id = :cart_id";
                $stmt = $db->prepare($delete_query);
                $stmt->bindValue(':cart_id', $cart_id);
                $stmt->execute();
            }
            break;
            
        case 'clear':
            $user_id = $_SESSION['user_id'] ?? null;
            $session_id = session_id();
            
            $delete_query = "DELETE FROM shopping_cart WHERE ";
            if ($user_id) {
                $delete_query .= "user_id = :user_id";
                $params = [':user_id' => $user_id];
            } else {
                $delete_query .= "session_id = :session_id";
                $params = [':session_id' => $session_id];
            }
            
            $stmt = $db->prepare($delete_query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            break;
    }
    
    // Refresh the page
    header('Location: cart.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Smart Retail System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="cart-page">
            <h1>Shopping Cart</h1>

            <?php if (empty($cart_items)): ?>
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart fa-3x"></i>
                    <h2>Your cart is empty</h2>
                    <p>Browse our products and add items to your cart</p>
                    <a href="products.php" class="btn btn-primary">Start Shopping</a>
                </div>
            <?php else: ?>
                <div class="cart-layout">
                    <div class="cart-items">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item" data-cart-id="<?php echo $item['cart_id']; ?>">
                                <img src="<?php echo $item['image_url'] ?? 'images/placeholder.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                     class="cart-item-image">
                                
                                <div class="cart-item-details">
                                    <h3 class="cart-item-title">
                                        <a href="product.php?id=<?php echo $item['product_id']; ?>">
                                            <?php echo htmlspecialchars($item['product_name']); ?>
                                        </a>
                                    </h3>
                                    <p class="cart-item-price">$<?php echo number_format($item['price'], 2); ?></p>
                                    <p class="cart-item-stock">
                                        <?php if ($item['stock_quantity'] > 0): ?>
                                            <span class="in-stock">In Stock (<?php echo $item['stock_quantity']; ?> available)</span>
                                        <?php else: ?>
                                            <span class="out-of-stock">Out of Stock</span>
                                        <?php endif; ?>
                                    </p>
                                </div>

                                <div class="cart-item-controls">
                                    <div class="quantity-controls">
                                        <button type="button" class="quantity-btn decrease-btn" 
                                                data-cart-id="<?php echo $item['cart_id']; ?>"
                                                data-current="<?php echo $item['quantity']; ?>">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <span class="quantity-display"><?php echo $item['quantity']; ?></span>
                                        <button type="button" class="quantity-btn increase-btn" 
                                                data-cart-id="<?php echo $item['cart_id']; ?>"
                                                data-current="<?php echo $item['quantity']; ?>"
                                                data-max="<?php echo $item['stock_quantity']; ?>">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>

                                    <button type="button" class="btn btn-danger btn-sm remove-btn" 
                                            data-cart-id="<?php echo $item['cart_id']; ?>">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                </div>

                                <div class="cart-item-total">
                                    $<?php echo number_format($item['item_total'], 2); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div class="cart-actions">
                            <button type="button" id="clearCartBtn" class="btn btn-outline">
                                <i class="fas fa-trash"></i> Clear Cart
                            </button>
                            <a href="products.php" class="btn btn-outline">
                                <i class="fas fa-shopping-bag"></i> Continue Shopping
                            </a>
                        </div>
                    </div>

                    <div class="cart-summary">
                        <div class="summary-card">
                            <h3>Order Summary</h3>
                            <div class="summary-row">
                                <span>Subtotal:</span>
                                <span>$<?php echo number_format($cart_total, 2); ?></span>
                            </div>
                            <div class="summary-row">
                                <span>Shipping:</span>
                                <span>$9.99</span>
                            </div>
                            <div class="summary-row">
                                <span>Tax (10%):</span>
                                <span>$<?php echo number_format($cart_total * 0.1, 2); ?></span>
                            </div>
                            <div class="summary-row total">
                                <span>Total:</span>
                                <span>$<?php echo number_format($cart_total + 9.99 + ($cart_total * 0.1), 2); ?></span>
                            </div>
                            
                            <?php if ($auth->isLoggedIn()): ?>
                                <a href="checkout.php" class="btn btn-primary btn-block">
                                    Proceed to Checkout
                                </a>
                            <?php else: ?>
                                <div class="login-prompt">
                                    <p>Please log in to proceed with checkout</p>
                                    <a href="login.php?redirect=checkout.php" class="btn btn-primary btn-block">
                                        Login to Checkout
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <div class="security-badges">
                                <div class="security-item">
                                    <i class="fas fa-shield-alt"></i>
                                    <span>Secure Checkout</span>
                                </div>
                                <div class="security-item">
                                    <i class="fas fa-lock"></i>
                                    <span>SSL Encrypted</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script src="js/main.js"></script>
    <script>
        // Quantity controls
        document.querySelectorAll('.quantity-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const cartId = this.dataset.cartId;
                const currentQty = parseInt(this.dataset.current);
                const maxStock = parseInt(this.dataset.max || 999);
                const isIncrease = this.classList.contains('increase-btn');
                
                let newQty = isIncrease ? currentQty + 1 : currentQty - 1;
                
                if (newQty < 1) newQty = 1;
                if (newQty > maxStock) {
                    alert('Maximum available stock is ' + maxStock);
                    return;
                }
                
                // Update via AJAX
                updateCartQuantity(cartId, newQty);
            });
        });
        
        // Remove items
        document.querySelectorAll('.remove-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const cartId = this.dataset.cartId;
                if (confirm('Are you sure you want to remove this item from your cart?')) {
                    removeCartItem(cartId);
                }
            });
        });
        
        // Clear cart
        document.getElementById('clearCartBtn')?.addEventListener('click', function() {
            if (confirm('Are you sure you want to clear your entire cart?')) {
                clearCart();
            }
        });
        
        function updateCartQuantity(cartId, quantity) {
            fetch('api/cart.php?action=update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    cart_id: cartId,
                    quantity: quantity
                })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    location.reload();
                } else {
                    alert('Failed to update cart: ' + result.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the cart');
            });
        }
        
        function removeCartItem(cartId) {
            fetch('api/cart.php?action=remove', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    cart_id: cartId
                })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    location.reload();
                } else {
                    alert('Failed to remove item: ' + result.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while removing the item');
            });
        }
        
        function clearCart() {
            fetch('api/cart.php?action=clear', {
                method: 'DELETE'
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    location.reload();
                } else {
                    alert('Failed to clear cart: ' + result.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while clearing the cart');
            });
        }
    </script>
</body>
</html>