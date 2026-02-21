<?php
require_once 'security.php';

class Auth {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    public function register($user_data) {
        // Validate input
        if (empty($user_data['username']) || empty($user_data['email']) || empty($user_data['password'])) {
            return ['success' => false, 'message' => 'All fields are required'];
        }
        
        if (!Security::validateEmail($user_data['email'])) {
            return ['success' => false, 'message' => 'Invalid email format'];
        }
        
        if (strlen($user_data['password']) < 6) {
            return ['success' => false, 'message' => 'Password must be at least 6 characters long'];
        }
        
        // Check if user already exists
        try {
            $query = "SELECT user_id FROM users WHERE email = :email OR username = :username";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':email', $user_data['email']);
            $stmt->bindValue(':username', $user_data['username']);
            $stmt->execute();
            
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Username or email already exists'];
            }
        } catch (PDOException $e) {
            error_log("User check error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error during registration'];
        }
        
        // Create user with error handling for missing columns
        try {
            $query = "INSERT INTO users (username, email, password_hash, first_name, last_name, phone, address) 
                      VALUES (:username, :email, :password_hash, :first_name, :last_name, :phone, :address)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':username', Security::sanitizeInput($user_data['username']));
            $stmt->bindValue(':email', Security::sanitizeInput($user_data['email']));
            $stmt->bindValue(':password_hash', Security::hashPassword($user_data['password']));
            $stmt->bindValue(':first_name', Security::sanitizeInput($user_data['first_name']));
            $stmt->bindValue(':last_name', Security::sanitizeInput($user_data['last_name']));
            $stmt->bindValue(':phone', Security::sanitizeInput($user_data['phone'] ?? ''));
            $stmt->bindValue(':address', Security::sanitizeInput($user_data['address'] ?? ''));
            
            if ($stmt->execute()) {
                $user_id = $this->db->lastInsertId();
                
                // Log the user in
                $this->login($user_data['email'], $user_data['password']);
                
                return ['success' => true, 'message' => 'Registration successful'];
            } else {
                return ['success' => false, 'message' => 'Registration failed'];
            }
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            
            // Try without optional fields if there's a column error
            if (strpos($e->getMessage(), 'phone') !== false || strpos($e->getMessage(), 'address') !== false) {
                try {
                    $query = "INSERT INTO users (username, email, password_hash, first_name, last_name) 
                              VALUES (:username, :email, :password_hash, :first_name, :last_name)";
                    
                    $stmt = $this->db->prepare($query);
                    $stmt->bindValue(':username', Security::sanitizeInput($user_data['username']));
                    $stmt->bindValue(':email', Security::sanitizeInput($user_data['email']));
                    $stmt->bindValue(':password_hash', Security::hashPassword($user_data['password']));
                    $stmt->bindValue(':first_name', Security::sanitizeInput($user_data['first_name']));
                    $stmt->bindValue(':last_name', Security::sanitizeInput($user_data['last_name']));
                    
                    if ($stmt->execute()) {
                        $user_id = $this->db->lastInsertId();
                        $this->login($user_data['email'], $user_data['password']);
                        return ['success' => true, 'message' => 'Registration successful'];
                    }
                } catch (PDOException $e2) {
                    error_log("Fallback registration error: " . $e2->getMessage());
                }
            }
            
            return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
        }
    }
    
    public function login($email, $password) {
        try {
            $query = "SELECT * FROM users WHERE email = :email AND is_active = 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && Security::verifyPassword($password, $user['password_hash'])) {
                // Try to update last_login, but don't fail if column doesn't exist
                try {
                    $update_query = "UPDATE users SET last_login = NOW() WHERE user_id = :user_id";
                    $update_stmt = $this->db->prepare($update_query);
                    $update_stmt->bindValue(':user_id', $user['user_id']);
                    $update_stmt->execute();
                } catch (PDOException $e) {
                    // Ignore error if last_login column doesn't exist
                    error_log("last_login update failed (non-critical): " . $e->getMessage());
                }
                
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'] ?? 'customer';
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                
                // Merge cart items from session to user
                $this->mergeCart($user['user_id']);
                
                return ['success' => true, 'message' => 'Login successful'];
            } else {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Login failed: Database error'];
        }
    }
    
    public function logout() {
        // Clear all session variables
        $_SESSION = array();
        
        // Destroy the session
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        
        return ['success' => true, 'message' => 'Logout successful'];
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
    
    private function mergeCart($user_id) {
        try {
            $session_id = session_id();
            
            // Get session cart items
            $query = "SELECT * FROM shopping_cart WHERE session_id = :session_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':session_id', $session_id);
            $stmt->execute();
            $session_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($session_items as $item) {
                // Check if user already has this product in cart
                $check_query = "SELECT * FROM shopping_cart WHERE user_id = :user_id AND product_id = :product_id";
                $check_stmt = $this->db->prepare($check_query);
                $check_stmt->bindValue(':user_id', $user_id);
                $check_stmt->bindValue(':product_id', $item['product_id']);
                $check_stmt->execute();
                $existing_item = $check_stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($existing_item) {
                    // Update quantity
                    $new_quantity = $existing_item['quantity'] + $item['quantity'];
                    $update_query = "UPDATE shopping_cart SET quantity = :quantity WHERE cart_id = :cart_id";
                    $update_stmt = $this->db->prepare($update_query);
                    $update_stmt->bindValue(':quantity', $new_quantity);
                    $update_stmt->bindValue(':cart_id', $existing_item['cart_id']);
                    $update_stmt->execute();
                    
                    // Delete session item
                    $delete_query = "DELETE FROM shopping_cart WHERE cart_id = :cart_id";
                    $delete_stmt = $this->db->prepare($delete_query);
                    $delete_stmt->bindValue(':cart_id', $item['cart_id']);
                    $delete_stmt->execute();
                } else {
                    // Update session item to user item
                    $update_query = "UPDATE shopping_cart SET user_id = :user_id, session_id = NULL WHERE cart_id = :cart_id";
                    $update_stmt = $this->db->prepare($update_query);
                    $update_stmt->bindValue(':user_id', $user_id);
                    $update_stmt->bindValue(':cart_id', $item['cart_id']);
                    $update_stmt->execute();
                }
            }
        } catch (PDOException $e) {
            error_log("Cart merge error: " . $e->getMessage());
            // Don't fail login if cart merge fails
        }
    }

    /**
     * Change user password
     */
    public function changePassword($user_id, $current_password, $new_password) {
        try {
            // Get current password hash
            $query = "SELECT password_hash FROM users WHERE user_id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':user_id', $user_id);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || !Security::verifyPassword($current_password, $user['password_hash'])) {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }
            
            if (strlen($new_password) < 6) {
                return ['success' => false, 'message' => 'New password must be at least 6 characters long'];
            }
            
            $new_password_hash = Security::hashPassword($new_password);
            $update_query = "UPDATE users SET password_hash = :password_hash WHERE user_id = :user_id";
            $update_stmt = $this->db->prepare($update_query);
            $update_stmt->bindValue(':password_hash', $new_password_hash);
            $update_stmt->bindValue(':user_id', $user_id);
            
            if ($update_stmt->execute()) {
                return ['success' => true, 'message' => 'Password changed successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to change password'];
            }
            
        } catch (PDOException $e) {
            error_log("Change password error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Password change failed: Database error'];
        }
    }

    /**
     * Get user by ID
     */
    public function getUserById($user_id) {
        try {
            $query = "SELECT user_id, username, email, first_name, last_name, phone, address, role, created_at 
                      FROM users WHERE user_id = :user_id AND is_active = 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':user_id', $user_id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get user error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile($user_id, $profile_data) {
        try {
            $query = "UPDATE users SET first_name = :first_name, last_name = :last_name, 
                      phone = :phone, address = :address, updated_at = NOW() 
                      WHERE user_id = :user_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':first_name', Security::sanitizeInput($profile_data['first_name']));
            $stmt->bindValue(':last_name', Security::sanitizeInput($profile_data['last_name']));
            $stmt->bindValue(':phone', Security::sanitizeInput($profile_data['phone'] ?? ''));
            $stmt->bindValue(':address', Security::sanitizeInput($profile_data['address'] ?? ''));
            $stmt->bindValue(':user_id', $user_id);
            
            if ($stmt->execute()) {
                // Update session data
                $_SESSION['first_name'] = $profile_data['first_name'];
                $_SESSION['last_name'] = $profile_data['last_name'];
                
                return ['success' => true, 'message' => 'Profile updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update profile'];
            }
        } catch (PDOException $e) {
            error_log("Update profile error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Profile update failed: Database error'];
        }
    }
}
?>