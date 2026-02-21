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

$user_id = $_SESSION['user_id'];

// Get user details with error handling
$user_query = "SELECT * FROM users WHERE user_id = :user_id";
$user_stmt = $db->prepare($user_query);
$user_stmt->bindValue(':user_id', $user_id);
$user_stmt->execute();
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);

// If user not found, redirect to login
if (!$user) {
    header('Location: logout.php');
    exit;
}

// Get recent orders with error handling
$recent_orders = [];
try {
    $orders_query = "SELECT * FROM orders WHERE user_id = :user_id ORDER BY order_date DESC LIMIT 5";
    $orders_stmt = $db->prepare($orders_query);
    $orders_stmt->bindValue(':user_id', $user_id);
    $orders_stmt->execute();
    $recent_orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (PDOException $e) {
    error_log("Orders query error: " . $e->getMessage());
    $recent_orders = [];
}

// Get order statistics with error handling
$stats = [
    'total_orders' => 0,
    'total_spent' => 0,
    'avg_order_value' => 0
];

try {
    $stats_query = "SELECT 
        COUNT(*) as total_orders,
        COALESCE(SUM(grand_total), 0) as total_spent,
        COALESCE(AVG(grand_total), 0) as avg_order_value
        FROM orders 
        WHERE user_id = :user_id AND status != 'cancelled'";
    
    $stats_stmt = $db->prepare($stats_query);
    $stats_stmt->bindValue(':user_id', $user_id);
    $stats_stmt->execute();
    $stats_result = $stats_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($stats_result) {
        $stats = array_merge($stats, $stats_result);
    }
} catch (PDOException $e) {
    error_log("Stats query error: " . $e->getMessage());
}

// Handle profile update
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    
    // Validate required fields
    if (empty($first_name) || empty($last_name)) {
        $error = "First name and last name are required.";
    } else {
        $update_query = "UPDATE users SET first_name = :first_name, last_name = :last_name, 
                        phone = :phone, address = :address, updated_at = NOW() 
                        WHERE user_id = :user_id";
        $update_stmt = $db->prepare($update_query);
        $update_stmt->bindValue(':first_name', $first_name);
        $update_stmt->bindValue(':last_name', $last_name);
        $update_stmt->bindValue(':phone', $phone);
        $update_stmt->bindValue(':address', $address);
        $update_stmt->bindValue(':user_id', $user_id);
        
        if ($update_stmt->execute()) {
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name'] = $last_name;
            $success = "Profile updated successfully!";
            // Refresh user data
            $user_stmt->execute();
            $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = "Failed to update profile. Please try again.";
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password)) {
        $error = "Current password is required.";
    } elseif (!password_verify($current_password, $user['password_hash'])) {
        $error = "Current password is incorrect.";
    } elseif (empty($new_password)) {
        $error = "New password is required.";
    } elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters long.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } else {
        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $password_query = "UPDATE users SET password_hash = :password_hash WHERE user_id = :user_id";
        $password_stmt = $db->prepare($password_query);
        $password_stmt->bindValue(':password_hash', $new_password_hash);
        $password_stmt->bindValue(':user_id', $user_id);
        
        if ($password_stmt->execute()) {
            $success = "Password changed successfully!";
        } else {
            $error = "Failed to change password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Smart Retail System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .profile-page {
            padding: 2rem 0;
        }

        .dashboard {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 2rem;
            min-height: 70vh;
        }

        .sidebar {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 100px;
        }

        .user-info {
            text-align: center;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .user-avatar {
            font-size: 4rem;
            color: #3498db;
            margin-bottom: 1rem;
        }

        .user-info h3 {
            margin-bottom: 0.5rem;
            color: #2c3e50;
        }

        .user-info p {
            color: #7f8c8d;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin-bottom: 0.5rem;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.25rem;
            color: #2c3e50;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: #3498db;
            color: white;
        }

        .dashboard-content {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .section-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f8f9fa;
        }

        .section-header h2 {
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 4px solid #3498db;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #3498db;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .recent-orders {
            margin-top: 2rem;
        }

        .recent-orders h3 {
            margin-bottom: 1.5rem;
            color: #2c3e50;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: #7f8c8d;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #bdc3c7;
        }

        .orders-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .order-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-left: 4px solid #3498db;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .order-info strong {
            display: block;
            margin-bottom: 0.25rem;
        }

        .order-date {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .status-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-badge.pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-badge.confirmed {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-badge.processing {
            background: #cce7ff;
            color: #004085;
        }

        .status-badge.shipped {
            background: #d4edda;
            color: #155724;
        }

        .status-badge.delivered {
            background: #d1edff;
            color: #0c5460;
        }

        .status-badge.cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .order-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .order-total {
            font-size: 1.2rem;
            font-weight: bold;
            color: #3498db;
        }

        .view-all {
            text-align: center;
            margin-top: 2rem;
        }

        .settings-form,
        .security-form {
            max-width: 600px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .form-text {
            font-size: 0.875rem;
            color: #7f8c8d;
            margin-top: 0.25rem;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        @media (max-width: 768px) {
            .dashboard {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: static;
                margin-bottom: 2rem;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .order-header,
            .order-details {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="profile-page">
            <h1>My Account</h1>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="dashboard">
                <!-- Sidebar -->
                <aside class="sidebar">
                    <div class="user-info">
                        <div class="user-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <h3><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
                        <p><?php echo htmlspecialchars($user['email']); ?></p>
                        <p>Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
                    </div>
                    
                    <ul class="sidebar-menu">
                        <li><a href="#profile" class="active" data-tab="profile"><i class="fas fa-user"></i> Profile</a></li>
                        <li><a href="#orders" data-tab="orders"><i class="fas fa-shopping-bag"></i> Orders</a></li>
                        <li><a href="#wishlist" data-tab="wishlist"><i class="fas fa-heart"></i> Wishlist</a></li>
                        <li><a href="#security" data-tab="security"><i class="fas fa-shield-alt"></i> Security</a></li>
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </aside>

                <!-- Main Content -->
                <div class="dashboard-content">
                    <!-- Profile Overview -->
                    <section id="profile" class="tab-content active">
                        <div class="section-header">
                            <h2>Profile Overview</h2>
                        </div>

                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-number"><?php echo $stats['total_orders']; ?></div>
                                <div class="stat-label">Total Orders</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-number">$<?php echo number_format($stats['total_spent'], 2); ?></div>
                                <div class="stat-label">Total Spent</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-number">$<?php echo number_format($stats['avg_order_value'], 2); ?></div>
                                <div class="stat-label">Avg. Order Value</div>
                            </div>
                        </div>

                        <!-- Recent Orders -->
                        <div class="recent-orders">
                            <h3>Recent Orders</h3>
                            <?php if (empty($recent_orders)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-shopping-bag"></i>
                                    <p>No orders yet</p>
                                    <a href="products.php" class="btn btn-primary">Start Shopping</a>
                                </div>
                            <?php else: ?>
                                <div class="orders-list">
                                    <?php foreach ($recent_orders as $order): ?>
                                        <div class="order-card">
                                            <div class="order-header">
                                                <div class="order-info">
                                                    <strong>Order #<?php echo htmlspecialchars($order['order_number'] ?? 'N/A'); ?></strong>
                                                    <span class="order-date"><?php echo date('M j, Y', strtotime($order['order_date'])); ?></span>
                                                </div>
                                                <div class="order-status">
                                                    <span class="status-badge <?php echo $order['status'] ?? 'pending'; ?>">
                                                        <?php echo ucfirst($order['status'] ?? 'Pending'); ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="order-details">
                                                <div class="order-total">$<?php echo number_format($order['grand_total'], 2); ?></div>
                                                <a href="order_details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-outline btn-sm">
                                                    View Details
                                                </a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="view-all">
                                    <a href="order_history.php" class="btn btn-outline">View All Orders</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>

                    <!-- Profile Settings -->
                    <section id="settings" class="tab-content">
                        <div class="section-header">
                            <h2>Profile Settings</h2>
                        </div>

                        <div class="settings-form">
                            <form method="POST">
                                <input type="hidden" name="update_profile" value="1">
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="first_name" class="form-label">First Name</label>
                                        <input type="text" id="first_name" name="first_name" class="form-control" 
                                               value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="last_name" class="form-label">Last Name</label>
                                        <input type="text" id="last_name" name="last_name" class="form-control" 
                                               value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                    <small class="form-text">Email cannot be changed. Contact support if needed.</small>
                                </div>

                                <div class="form-group">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" id="phone" name="phone" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea id="address" name="address" class="form-control" rows="4"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary">Update Profile</button>
                            </form>
                        </div>
                    </section>

                    <!-- Security Settings -->
                    <section id="security" class="tab-content">
                        <div class="section-header">
                            <h2>Security Settings</h2>
                        </div>

                        <div class="security-form">
                            <form method="POST">
                                <input type="hidden" name="change_password" value="1">
                                
                                <div class="form-group">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="new_password" class="form-label">New Password</label>
                                        <input type="password" id="new_password" name="new_password" class="form-control" required minlength="6">
                                    </div>
                                    <div class="form-group">
                                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">Change Password</button>
                            </form>
                        </div>
                    </section>

                    <!-- Orders Tab -->
                    <section id="orders" class="tab-content">
                        <div class="section-header">
                            <h2>Order History</h2>
                        </div>
                        <div class="empty-state">
                            <i class="fas fa-shopping-bag"></i>
                            <p>Order history will be displayed here</p>
                            <a href="products.php" class="btn btn-primary">Continue Shopping</a>
                        </div>
                    </section>

                    <!-- Wishlist Tab -->
                    <section id="wishlist" class="tab-content">
                        <div class="section-header">
                            <h2>My Wishlist</h2>
                        </div>
                        <div class="empty-state">
                            <i class="fas fa-heart"></i>
                            <p>Your wishlist is empty</p>
                            <a href="products.php" class="btn btn-primary">Browse Products</a>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script>
        // Tab functionality
        document.querySelectorAll('.sidebar-menu a[data-tab]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Update active link
                document.querySelectorAll('.sidebar-menu a').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
                
                // Show corresponding tab
                const targetId = this.getAttribute('data-tab');
                document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
                document.getElementById(targetId).classList.add('active');
            });
        });

        // Password validation
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');
        
        if (newPassword && confirmPassword) {
            function validatePasswords() {
                const newPass = newPassword.value;
                const confirmPass = confirmPassword.value;
                
                if (confirmPass && newPass !== confirmPass) {
                    confirmPassword.style.borderColor = '#e74c3c';
                } else if (confirmPass) {
                    confirmPassword.style.borderColor = '#27ae60';
                } else {
                    confirmPassword.style.borderColor = '#e2e8f0';
                }
            }
            
            newPassword.addEventListener('input', validatePasswords);
            confirmPassword.addEventListener('input', validatePasswords);
        }

        // Form validation
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const requiredFields = form.querySelectorAll('[required]');
                let valid = true;
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        valid = false;
                        field.style.borderColor = '#e74c3c';
                    }
                });
                
                if (!valid) {
                    e.preventDefault();
                    alert('Please fill in all required fields.');
                }
            });
        });
    </script>
</body>
</html>