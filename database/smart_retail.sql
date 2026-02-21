-- Smart Retail System - Fixed Database Schema with last_login column

SET FOREIGN_KEY_CHECKS=0;
DROP DATABASE IF EXISTS smart_retail_system;
CREATE DATABASE smart_retail_system;
USE smart_retail_system;

-- Users Table (FIXED: added last_login column)
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('customer', 'admin', 'manager') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL, -- ADDED THIS COLUMN
    is_active BOOLEAN DEFAULT TRUE
);

-- Categories Table
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    image_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products Table
CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(255) NOT NULL,
    description TEXT,
    short_description VARCHAR(500),
    price DECIMAL(10,2) NOT NULL,
    compare_price DECIMAL(10,2) NULL,
    cost_price DECIMAL(10,2) NULL,
    sku VARCHAR(100) UNIQUE NOT NULL,
    stock_quantity INT NOT NULL DEFAULT 0,
    low_stock_threshold INT DEFAULT 10,
    category_id INT,
    brand VARCHAR(100),
    image_url VARCHAR(500),
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    views_count INT DEFAULT 0,
    sales_count INT DEFAULT 0,
    rating_avg DECIMAL(3,2) DEFAULT 0,
    review_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
);

-- Product Attributes Table
CREATE TABLE product_attributes (
    attribute_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    attribute_name VARCHAR(100) NOT NULL,
    attribute_value TEXT NOT NULL,
    display_order INT DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);

-- Shopping Cart Table
CREATE TABLE shopping_cart (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    session_id VARCHAR(100),
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

-- Orders Table
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2) NOT NULL,
    tax_amount DECIMAL(10,2) DEFAULT 0,
    shipping_amount DECIMAL(10,2) DEFAULT 0,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    grand_total DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    shipping_address TEXT,
    billing_address TEXT,
    shipping_method VARCHAR(100),
    tracking_number VARCHAR(100),
    customer_notes TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Order Items Table
CREATE TABLE order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    product_sku VARCHAR(100) NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

-- Payments Table
CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    transaction_id VARCHAR(255),
    payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id)
);

-- Product Reviews Table
CREATE TABLE product_reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating TINYINT NOT NULL,
    title VARCHAR(255),
    review_text TEXT,
    is_approved BOOLEAN DEFAULT TRUE,
    helpful_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Wishlist Table
CREATE TABLE wishlist (
    wishlist_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    UNIQUE KEY unique_wishlist (user_id, product_id)
);

-- Insert Sample Data

-- Insert admin user (password: admin123)
INSERT INTO users (username, email, password_hash, first_name, last_name, role) VALUES 
('admin', 'admin@smartretail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator', 'admin'),
('john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe', 'customer');

-- Insert categories
INSERT INTO categories (category_name, description) VALUES 
('Electronics', 'Latest electronic devices and gadgets'),
('Smartphones', 'Mobile phones and accessories'),
('Laptops', 'Laptops and computing devices'),
('Home Appliances', 'Home and kitchen appliances'),
('Books', 'Educational and entertainment books'),
('Clothing', 'Fashion and apparel');

-- Update the products section with real image files
INSERT INTO products (product_name, description, short_description, price, compare_price, sku, stock_quantity, category_id, brand, is_featured, image_url) VALUES 
('iPhone 15 Pro', 'Latest Apple iPhone with advanced camera and A17 Pro chip', '6.1-inch Super Retina XDR display, Titanium design', 999.99, 1099.99, 'IP15P-256', 50, 2, 'Apple', 1, 'images/iphone15.jpg'),
('Samsung Galaxy S24', 'AI-powered smartphone with advanced features', '6.2-inch Dynamic AMOLED, Snapdragon 8 Gen 3', 849.99, 899.99, 'SGS24-256', 75, 2, 'Samsung', 1, 'images/s24.jpg'),
('MacBook Pro 16"', 'Professional laptop with M3 Max chip', '16-inch Liquid Retina XDR display, 32GB RAM', 2499.99, 2799.99, 'MBP16-M3', 25, 3, 'Apple', 1, 'images/macbook.png'),
('Dell XPS 15', 'Powerful Windows laptop for professionals', '15.6-inch OLED display, Intel Core i9', 1899.99, 1999.99, 'DXPS15-1T', 30, 3, 'Dell', 0, 'images/dell.jpg'),
('Wireless Headphones', 'Noise-cancelling wireless headphones with premium sound', '30-hour battery life, comfortable over-ear design', 199.99, 249.99, 'WH-1000XM4', 100, 1, 'Sony', 1, 'images/headphone.jpg'),
('Smart Watch Series 8', 'Advanced smartwatch with health monitoring', 'Always-on display, GPS, heart rate monitor', 399.99, 429.99, 'SW-S8-44', 60, 1, 'Apple', 1, 'images/watch.jpg'),
('Programming Book', 'Complete guide to web development', 'Learn HTML, CSS, JavaScript and PHP', 49.99, 59.99, 'BOOK-PG-001', 200, 5, 'Tech Publications', 1, 'images/book.jpg'),
('Cotton T-Shirt', 'Comfortable cotton t-shirt for everyday wear', '100% cotton, available in multiple colors', 24.99, 29.99, 'CLOTH-TS-001', 150, 6, 'Fashion Co', 0, 'images/cotton.jpg');
-- Insert product attributes
INSERT INTO product_attributes (product_id, attribute_name, attribute_value, display_order) VALUES 
(1, 'Color', 'Natural Titanium', 1),
(1, 'Storage', '256GB', 2),
(1, 'Display', '6.1-inch Super Retina XDR', 3),
(2, 'Color', 'Phantom Black', 1),
(2, 'Storage', '256GB', 2),
(2, 'Display', '6.2-inch Dynamic AMOLED', 3),
(3, 'Processor', 'Apple M3 Max', 1),
(3, 'Memory', '32GB Unified Memory', 2),
(3, 'Storage', '1TB SSD', 3);

-- Insert sample reviews
INSERT INTO product_reviews (product_id, user_id, rating, title, review_text, is_approved) VALUES 
(1, 2, 5, 'Excellent phone!', 'The iPhone 15 Pro is amazing. The camera quality is outstanding and the performance is super smooth.', 1),
(1, 2, 4, 'Great but expensive', 'Love the phone but it''s quite expensive. The titanium build feels premium though.', 1),
(2, 2, 5, 'Best Android phone', 'Samsung has outdone themselves with the S24. The AI features are incredibly useful.', 1);

SET FOREIGN_KEY_CHECKS=1;