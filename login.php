<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    $redirect = $_GET['redirect'] ?? 'index.php';
    header('Location: ' . $redirect);
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    $result = $auth->login($email, $password);
    if ($result['success']) {
        $redirect = $_GET['redirect'] ?? 'index.php';
        header('Location: ' . $redirect);
        exit;
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Smart Retail System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .auth-page {
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .auth-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 3rem;
            width: 100%;
            max-width: 450px;
            margin: 2rem;
            position: relative;
            overflow: hidden;
        }

        .auth-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #3498db, #2c3e50);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .auth-header h1 {
            color: #2c3e50;
            margin-bottom: 0.5rem;
            font-size: 2.2rem;
            font-weight: 700;
        }

        .auth-header p {
            color: #7f8c8d;
            font-size: 1.1rem;
        }

        .alert {
            padding: 1rem 1.25rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-left: 4px solid;
        }

        .alert-error {
            background: #fee;
            color: #c53030;
            border-left-color: #c53030;
        }

        .alert i {
            font-size: 1.2rem;
        }

        .auth-form {
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.95rem;
        }

        .input-with-icon {
            position: relative;
        }

        .input-with-icon i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
            z-index: 2;
        }

        .input-with-icon input {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 3rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #fff;
        }

        .input-with-icon input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
            background: #fff;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #7f8c8d;
            cursor: pointer;
            padding: 0.25rem;
            z-index: 2;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: #3498db;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            font-size: 0.95rem;
            color: #4a5568;
        }

        .checkbox-label input {
            display: none;
        }

        .checkmark {
            width: 20px;
            height: 20px;
            border: 2px solid #cbd5e0;
            border-radius: 4px;
            position: relative;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .checkbox-label input:checked + .checkmark {
            background: #3498db;
            border-color: #3498db;
        }

        .checkbox-label input:checked + .checkmark::after {
            content: '?';
            color: white;
            font-size: 14px;
            font-weight: bold;
        }

        .forgot-password {
            color: #3498db;
            text-decoration: none;
            font-size: 0.95rem;
            transition: color 0.3s ease;
        }

        .forgot-password:hover {
            color: #2980b9;
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(52, 152, 219, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .auth-divider {
            text-align: center;
            margin: 2rem 0;
            position: relative;
        }

        .auth-divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e2e8f0;
        }

        .auth-divider span {
            background: white;
            padding: 0 1.5rem;
            color: #7f8c8d;
            font-size: 0.95rem;
            position: relative;
        }

        .social-auth {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .btn-social {
            padding: 0.875rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            background: white;
            color: #4a5568;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }

        .btn-social:hover {
            border-color: #3498db;
            color: #3498db;
            transform: translateY(-1px);
        }

        .btn-google {
            border-color: #e2e8f0;
        }

        .btn-google:hover {
            border-color: #db4437;
            color: #db4437;
        }

        .btn-facebook {
            border-color: #e2e8f0;
        }

        .btn-facebook:hover {
            border-color: #4267B2;
            color: #4267B2;
        }

        .auth-footer {
            text-align: center;
            padding-top: 1.5rem;
            border-top: 1px solid #e2e8f0;
        }

        .auth-footer p {
            color: #7f8c8d;
            margin-bottom: 0;
        }

        .auth-link {
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .auth-link:hover {
            color: #2980b9;
            text-decoration: underline;
        }

        /* Animation for form */
        .auth-card {
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 480px) {
            .auth-card {
                margin: 1rem;
                padding: 2rem 1.5rem;
            }

            .auth-header h1 {
                font-size: 1.8rem;
            }

            .form-options {
                flex-direction: column;
                align-items: flex-start;
            }

            .social-auth {
                grid-template-columns: 1fr;
            }

            .btn-social {
                padding: 0.75rem 1rem;
            }
        }

        /* Loading state */
        .btn-loading {
            position: relative;
            color: transparent;
        }

        .btn-loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Enhanced focus styles */
        .input-with-icon input:focus + i {
            color: #3498db;
        }

        /* Placeholder styling */
        .input-with-icon input::placeholder {
            color: #a0aec0;
        }
    </style>
</head>
<body>
    <!-- Simple header for login page -->
    <header class="header" style="background: transparent; box-shadow: none; position: absolute; width: 100%;">
        <div class="header-container">
            <a href="index.php" class="logo" style="color: white;">Smart<span style="color: #3498db;">Retail</span></a>
            <nav>
                <ul class="nav-menu">
                    <li><a href="index.php" style="color: white;">Home</a></li>
                    <li><a href="products.php" style="color: white;">Products</a></li>
                    <li><a href="register.php" style="color: white;">Sign Up</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="auth-page">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Welcome Back</h1>
                <p>Sign in to your account to continue shopping</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form" id="loginForm">
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                               required placeholder="Enter your email">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" class="form-control" 
                               required placeholder="Enter your password">
                        <button type="button" class="password-toggle" id="passwordToggle">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" id="remember">
                        <span class="checkmark"></span>
                        Remember me
                    </label>
                    <a href="forgot_password.php" class="forgot-password">Forgot password?</a>
                </div>

                <button type="submit" class="btn-login" id="loginButton">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>

            <div class="auth-divider">
                <span>Or continue with</span>
            </div>

            <div class="social-auth">
                <button type="button" class="btn-social btn-google">
                    <i class="fab fa-google"></i> Google
                </button>
                <button type="button" class="btn-social btn-facebook">
                    <i class="fab fa-facebook"></i> Facebook
                </button>
            </div>

            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php" class="auth-link">Sign up here</a></p>
            </div>
        </div>
    </main>

    <script>
        // Password toggle functionality
        const passwordToggle = document.getElementById('passwordToggle');
        const passwordInput = document.getElementById('password');
        
        passwordToggle.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });

        // Form validation and submission
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value.trim();
            const loginButton = document.getElementById('loginButton');
            
            if (!email || !password) {
                e.preventDefault();
                showNotification('Please fill in all required fields.', 'error');
                return;
            }

            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                showNotification('Please enter a valid email address.', 'error');
                return;
            }

            // Show loading state
            loginButton.innerHTML = '<div class="btn-loading"></div>';
            loginButton.disabled = true;
        });

        // Enhanced input validation
        document.getElementById('email').addEventListener('blur', function() {
            validateEmail(this);
        });

        document.getElementById('password').addEventListener('blur', function() {
            validatePassword(this);
        });

        function validateEmail(input) {
            const value = input.value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (value && !emailRegex.test(value)) {
                input.style.borderColor = '#e74c3c';
                showFieldError(input, 'Please enter a valid email address');
            } else {
                input.style.borderColor = value ? '#27ae60' : '#e2e8f0';
                clearFieldError(input);
            }
        }

        function validatePassword(input) {
            const value = input.value.trim();
            
            if (value && value.length < 6) {
                input.style.borderColor = '#e74c3c';
                showFieldError(input, 'Password must be at least 6 characters');
            } else if (value) {
                input.style.borderColor = '#27ae60';
                clearFieldError(input);
            } else {
                input.style.borderColor = '#e2e8f0';
                clearFieldError(input);
            }
        }

        function showFieldError(input, message) {
            // Remove existing error
            clearFieldError(input);
            
            // Create error element
            const errorElement = document.createElement('div');
            errorElement.className = 'field-error';
            errorElement.style.cssText = `
                color: #e74c3c;
                font-size: 0.875rem;
                margin-top: 0.5rem;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            `;
            errorElement.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
            
            input.parentNode.appendChild(errorElement);
        }

        function clearFieldError(input) {
            const existingError = input.parentNode.querySelector('.field-error');
            if (existingError) {
                existingError.remove();
            }
        }

        // Notification system
        function showNotification(message, type = 'info') {
            // Remove existing notifications
            const existingNotifications = document.querySelectorAll('.notification');
            existingNotifications.forEach(notification => notification.remove());

            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 1rem 1.5rem;
                border-radius: 8px;
                color: white;
                z-index: 10000;
                max-width: 400px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.2);
                animation: slideInRight 0.3s ease;
                display: flex;
                align-items: center;
                gap: 0.75rem;
            `;

            const bgColor = type === 'error' ? '#e74c3c' : type === 'success' ? '#27ae60' : '#3498db';
            notification.style.background = bgColor;

            notification.innerHTML = `
                <i class="fas fa-${type === 'error' ? 'exclamation-triangle' : type === 'success' ? 'check-circle' : 'info-circle'}"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.remove()" style="background: none; border: none; color: white; cursor: pointer; margin-left: auto;">
                    <i class="fas fa-times"></i>
                </button>
            `;

            document.body.appendChild(notification);

            // Auto remove after 5 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.style.animation = 'slideOutRight 0.3s ease forwards';
                    setTimeout(() => notification.remove(), 300);
                }
            }, 5000);
        }

        // Add CSS for animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOutRight {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);

        // Demo credentials helper
        document.addEventListener('DOMContentLoaded', function() {
            // Add demo credentials note for testing
            const demoNote = document.createElement('div');
            demoNote.style.cssText = `
                position: fixed;
                bottom: 20px;
                left: 20px;
                background: rgba(255,255,255,0.9);
                padding: 1rem;
                border-radius: 8px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                font-size: 0.875rem;
                color: #2c3e50;
                max-width: 300px;
                z-index: 1000;
            `;
            demoNote.innerHTML = `
                <strong>Demo Credentials:</strong><br>
                Admin: admin@smartretail.com / admin123<br>
                User: john@example.com / password
            `;
            document.body.appendChild(demoNote);

            // Auto-fill demo credentials for testing
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('demo') === 'admin') {
                document.getElementById('email').value = 'admin@smartretail.com';
                document.getElementById('password').value = 'admin123';
            } else if (urlParams.get('demo') === 'user') {
                document.getElementById('email').value = 'john@example.com';
                document.getElementById('password').value = 'password';
            }
        });

        // Social login buttons (placeholder functionality)
        document.querySelector('.btn-google').addEventListener('click', function() {
            showNotification('Google login integration coming soon!', 'info');
        });

        document.querySelector('.btn-facebook').addEventListener('click', function() {
            showNotification('Facebook login integration coming soon!', 'info');
        });
    </script>
</body>
</html>