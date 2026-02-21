// Smart Retail System - Main JavaScript File

class SmartRetailSystem {
    constructor() {
        this.cart = new ShoppingCart();
        this.search = new SearchSystem();
        this.notifications = new NotificationSystem();
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.loadCartCount();
        this.setupForms();
        this.setupProductInteractions();
    }
    
    setupEventListeners() {
        // Global click handler for dynamic elements
        document.addEventListener('click', (e) => {
            // Add to cart buttons
            if (e.target.closest('.add-to-cart-btn')) {
                this.handleAddToCart(e.target.closest('.add-to-cart-btn'));
            }
            
            // Quantity controls
            if (e.target.closest('.quantity-btn')) {
                this.handleQuantityChange(e.target.closest('.quantity-btn'));
            }
            
            // Remove from cart
            if (e.target.closest('.remove-from-cart')) {
                this.handleRemoveFromCart(e.target.closest('.remove-from-cart'));
            }
        });
        
        // Search functionality
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', this.debounce((e) => {
                this.search.handleSearch(e.target.value);
            }, 300));
        }
        
        // Form submissions
        document.addEventListener('submit', (e) => {
            this.handleFormSubmission(e);
        });
    }
    
    setupForms() {
        // Real-time form validation
        const forms = document.querySelectorAll('form[needs-validation]');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!this.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                this.classList.add('was-validated');
            });
            
            // Real-time validation
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.addEventListener('blur', () => this.validateField(input));
                input.addEventListener('input', () => this.clearFieldError(input));
            });
        });
    }
    
    validateField(field) {
        const value = field.value.trim();
        const fieldName = field.name;
        
        // Clear previous errors
        this.clearFieldError(field);
        
        // Required field validation
        if (field.required && !value) {
            this.showFieldError(field, 'This field is required');
            return false;
        }
        
        // Email validation
        if (field.type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                this.showFieldError(field, 'Please enter a valid email address');
                return false;
            }
        }
        
        // Password strength
        if (field.type === 'password' && value) {
            if (value.length < 6) {
                this.showFieldError(field, 'Password must be at least 6 characters long');
                return false;
            }
        }
        
        // Phone validation
        if (fieldName === 'phone' && value) {
            const phoneRegex = /^\d{10}$/;
            if (!phoneRegex.test(value.replace(/\D/g, ''))) {
                this.showFieldError(field, 'Please enter a valid phone number');
                return false;
            }
        }
        
        return true;
    }
    
    showFieldError(field, message) {
        field.classList.add('is-invalid');
        
        let errorElement = field.parentNode.querySelector('.invalid-feedback');
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'invalid-feedback';
            field.parentNode.appendChild(errorElement);
        }
        
        errorElement.textContent = message;
    }
    
    clearFieldError(field) {
        field.classList.remove('is-invalid');
        const errorElement = field.parentNode.querySelector('.invalid-feedback');
        if (errorElement) {
            errorElement.textContent = '';
        }
    }
    
    async handleAddToCart(button) {
        const productId = button.dataset.productId;
        const quantity = parseInt(button.dataset.quantity || 1);
        
        if (!productId) {
            this.notifications.show('Product ID missing', 'error');
            return;
        }
        
        try {
            button.disabled = true;
            button.innerHTML = '<span class="loading"></span> Adding...';
            
            const result = await this.cart.addItem(productId, quantity);
            
            if (result.success) {
                this.notifications.show('Product added to cart', 'success');
                this.loadCartCount();
                
                // Add animation effect
                this.animateAddToCart(button);
            } else {
                this.notifications.show(result.message || 'Failed to add product to cart', 'error');
            }
        } catch (error) {
            console.error('Add to cart error:', error);
            this.notifications.show('An error occurred', 'error');
        } finally {
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-shopping-cart"></i> Add to Cart';
        }
    }
    
    animateAddToCart(button) {
        button.classList.add('adding-to-cart');
        setTimeout(() => {
            button.classList.remove('adding-to-cart');
        }, 600);
    }
    
    handleQuantityChange(button) {
        const action = button.dataset.action;
        const productId = button.dataset.productId;
        const quantityElement = document.getElementById(`quantity-${productId}`);
        
        if (!quantityElement) return;
        
        let quantity = parseInt(quantityElement.textContent);
        
        if (action === 'increase') {
            const maxStock = parseInt(button.dataset.maxStock || 999);
            if (quantity < maxStock) {
                quantity++;
            } else {
                this.notifications.show('Maximum stock reached', 'warning');
            }
        } else if (action === 'decrease' && quantity > 0) {
            quantity--;
        }
        
        quantityElement.textContent = quantity;
        
        // Update any related buttons
        const addButton = document.querySelector(`.add-to-cart-btn[data-product-id="${productId}"]`);
        if (addButton) {
            addButton.dataset.quantity = quantity;
        }
    }
    
    async handleRemoveFromCart(button) {
        const cartId = button.dataset.cartId;
        
        if (!cartId) {
            this.notifications.show('Cart item ID missing', 'error');
            return;
        }
        
        try {
            const result = await this.cart.removeItem(cartId);
            
            if (result.success) {
                this.notifications.show('Item removed from cart', 'success');
                this.loadCartCount();
                
                // Remove from UI
                const cartItem = button.closest('.cart-item');
                if (cartItem) {
                    cartItem.style.opacity = '0';
                    setTimeout(() => cartItem.remove(), 300);
                }
                
                // Update totals
                this.updateCartTotals();
            } else {
                this.notifications.show('Failed to remove item', 'error');
            }
        } catch (error) {
            console.error('Remove from cart error:', error);
            this.notifications.show('An error occurred', 'error');
        }
    }
    
    async handleFormSubmission(e) {
        const form = e.target;
        const formData = new FormData(form);
        
        // Prevent default for AJAX forms
        if (form.classList.contains('ajax-form')) {
            e.preventDefault();
            
            try {
                const submitButton = form.querySelector('button[type="submit"]');
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<span class="loading"></span> Processing...';
                }
                
                const response = await fetch(form.action, {
                    method: form.method,
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    this.notifications.show(result.message, 'success');
                    
                    // Redirect if specified
                    if (result.redirect) {
                        setTimeout(() => {
                            window.location.href = result.redirect;
                        }, 1500);
                    }
                    
                    // Reset form if needed
                    if (result.resetForm) {
                        form.reset();
                        form.classList.remove('was-validated');
                    }
                } else {
                    this.notifications.show(result.message, 'error');
                    
                    // Show field errors if provided
                    if (result.errors) {
                        this.showFormErrors(form, result.errors);
                    }
                }
            } catch (error) {
                console.error('Form submission error:', error);
                this.notifications.show('An error occurred', 'error');
            } finally {
                const submitButton = form.querySelector('button[type="submit"]');
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = submitButton.dataset.originalText || 'Submit';
                }
            }
        }
    }
    
    showFormErrors(form, errors) {
        Object.keys(errors).forEach(fieldName => {
            const field = form.querySelector(`[name="${fieldName}"]`);
            if (field) {
                this.showFieldError(field, errors[fieldName]);
            }
        });
    }
    
    async loadCartCount() {
        try {
            const count = await this.cart.getItemCount();
            const cartCountElement = document.getElementById('cartCount');
            if (cartCountElement) {
                cartCountElement.textContent = count;
                cartCountElement.style.display = count > 0 ? 'block' : 'none';
            }
        } catch (error) {
            console.error('Failed to load cart count:', error);
        }
    }
    
    updateCartTotals() {
        // This would update cart totals on the cart page
        const cartItems = document.querySelectorAll('.cart-item');
        let subtotal = 0;
        
        cartItems.forEach(item => {
            const price = parseFloat(item.dataset.price) || 0;
            const quantity = parseInt(item.querySelector('.quantity-display').textContent) || 0;
            subtotal += price * quantity;
        });
        
        const subtotalElement = document.getElementById('cartSubtotal');
        const totalElement = document.getElementById('cartTotal');
        
        if (subtotalElement) subtotalElement.textContent = `$${subtotal.toFixed(2)}`;
        if (totalElement) totalElement.textContent = `$${subtotal.toFixed(2)}`;
    }
    
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}

class ShoppingCart {
    constructor() {
        this.items = [];
        this.loadFromStorage();
    }
    
    loadFromStorage() {
        const stored = localStorage.getItem('smart_retail_cart');
        if (stored) {
            this.items = JSON.parse(stored);
        }
    }
    
    saveToStorage() {
        localStorage.setItem('smart_retail_cart', JSON.stringify(this.items));
    }
    
    async addItem(productId, quantity = 1, attributes = {}) {
        try {
            const response = await fetch('api/cart.php?action=add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: quantity,
                    attributes: attributes
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Update local storage
                const existingItem = this.items.find(item => item.product_id == productId);
                if (existingItem) {
                    existingItem.quantity += quantity;
                } else {
                    this.items.push({
                        product_id: productId,
                        quantity: quantity,
                        attributes: attributes,
                        added_at: new Date().toISOString()
                    });
                }
                this.saveToStorage();
            }
            
            return result;
        } catch (error) {
            console.error('Add to cart API error:', error);
            return { success: false, message: 'Network error' };
        }
    }
    
    async removeItem(cartId) {
        try {
            const response = await fetch('api/cart.php?action=remove', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ cart_id: cartId })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.items = this.items.filter(item => item.cart_id != cartId);
                this.saveToStorage();
            }
            
            return result;
        } catch (error) {
            console.error('Remove from cart API error:', error);
            return { success: false, message: 'Network error' };
        }
    }
    
    async getItemCount() {
        try {
            const response = await fetch('api/cart.php?action=count');
            const result = await response.json();
            return result.count || 0;
        } catch (error) {
            console.error('Get cart count error:', error);
            return this.items.reduce((total, item) => total + item.quantity, 0);
        }
    }
    
    clear() {
        this.items = [];
        this.saveToStorage();
    }
}

class SearchSystem {
    constructor() {
        this.suggestionsContainer = document.getElementById('searchSuggestions');
        this.resultsContainer = document.getElementById('searchResults');
    }
    
    async handleSearch(query) {
        if (query.length < 2) {
            this.hideSuggestions();
            return;
        }
        
        try {
            const response = await fetch(`api/search.php?q=${encodeURIComponent(query)}`);
            const results = await response.json();
            
            if (this.suggestionsContainer) {
                this.showSuggestions(results);
            } else if (this.resultsContainer) {
                this.showResults(results);
            }
        } catch (error) {
            console.error('Search error:', error);
        }
    }
    
    showSuggestions(suggestions) {
        if (!suggestions.length) {
            this.hideSuggestions();
            return;
        }
        
        this.suggestionsContainer.innerHTML = suggestions.map(product => `
            <div class="suggestion-item" data-product-id="${product.product_id}">
                <img src="${product.image_url || 'images/placeholder.jpg'}" alt="${product.product_name}">
                <div>
                    <div class="suggestion-title">${this.escapeHtml(product.product_name)}</div>
                    <div class="suggestion-price">$${product.price}</div>
                </div>
            </div>
        `).join('');
        
        this.suggestionsContainer.style.display = 'block';
        
        // Add click handlers
        this.suggestionsContainer.querySelectorAll('.suggestion-item').forEach(item => {
            item.addEventListener('click', () => {
                const productId = item.dataset.productId;
                window.location.href = `product.php?id=${productId}`;
            });
        });
    }
    
    showResults(results) {
        if (!this.resultsContainer) return;
        
        if (results.length === 0) {
            this.resultsContainer.innerHTML = `
                <div class="no-results">
                    <h3>No products found</h3>
                    <p>Try adjusting your search terms</p>
                </div>
            `;
            return;
        }
        
        this.resultsContainer.innerHTML = results.map(product => `
            <div class="product-card">
                <img src="${product.image_url || 'images/placeholder.jpg'}" alt="${product.product_name}" class="product-image">
                <h3 class="product-title">${this.escapeHtml(product.product_name)}</h3>
                <p class="product-description">${this.escapeHtml(product.short_description || '')}</p>
                <div class="product-price">
                    <span class="current-price">$${product.price}</span>
                    ${product.compare_price ? `<span class="original-price">$${product.compare_price}</span>` : ''}
                </div>
                <button class="btn btn-primary add-to-cart-btn" data-product-id="${product.product_id}">
                    <i class="fas fa-shopping-cart"></i> Add to Cart
                </button>
            </div>
        `).join('');
    }
    
    hideSuggestions() {
        if (this.suggestionsContainer) {
            this.suggestionsContainer.style.display = 'none';
        }
    }
    
    escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
}

class NotificationSystem {
    show(message, type = 'info') {
        // Remove existing notifications
        this.clear();
        
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <span class="notification-message">${message}</span>
                <button class="notification-close">&times;</button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Add close handler
        notification.querySelector('.notification-close').addEventListener('click', () => {
            this.remove(notification);
        });
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            this.remove(notification);
        }, 5000);
    }
    
    remove(notification) {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }
    
    clear() {
        const notifications = document.querySelectorAll('.notification');
        notifications.forEach(notification => this.remove(notification));
    }
}

// Initialize the system when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.smartRetail = new SmartRetailSystem();
});

// Utility functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { SmartRetailSystem, ShoppingCart, SearchSystem, NotificationSystem };
}