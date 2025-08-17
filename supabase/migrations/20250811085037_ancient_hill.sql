-- E-commerce Database Schema
-- Drop existing database and create new one
DROP DATABASE IF EXISTS ecommerce_db;
CREATE DATABASE ecommerce_db;
USE ecommerce_db;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_admin TINYINT DEFAULT 0,
    phone VARCHAR(15),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Brands table
CREATE TABLE brands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category_id INT,
    brand_id INT,
    stock_quantity INT DEFAULT 0,
    image_url VARCHAR(300),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (brand_id) REFERENCES brands(id)
);

-- Orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('Pending', 'Delivered', 'Cancelled') DEFAULT 'Pending',
    payment_method VARCHAR(50),
    shipping_address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Order items table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Reviews table
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Contact messages table
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200),
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample data

-- Insert admin user
INSERT INTO users (name, email, password, is_admin) VALUES 
('Admin User', 'admin@ecommerce.com', 'admin123', 1),
('John Doe', 'john@example.com', 'user123', 0),
('Jane Smith', 'jane@example.com', 'user123', 0);

-- Insert categories
INSERT INTO categories (name) VALUES 
('Electronics'),
('Clothing'),
('Home & Garden'),
('Sports'),
('Books'),
('Beauty');

-- Insert brands
INSERT INTO brands (name) VALUES 
('Samsung'),
('Apple'),
('Nike'),
('Adidas'),
('Sony'),
('LG');

-- Insert sample products
INSERT INTO products (name, description, price, category_id, brand_id, stock_quantity, image_url) VALUES 
('Samsung Galaxy S23', 'Latest Samsung smartphone with advanced camera features', 75999.00, 1, 1, 25, 'https://images.pexels.com/photos/404280/pexels-photo-404280.jpeg'),
('iPhone 15 Pro', 'Apple iPhone 15 Pro with A17 chip', 134999.00, 1, 2, 15, 'https://images.pexels.com/photos/788946/pexels-photo-788946.jpeg'),
('Nike Air Max', 'Comfortable running shoes for daily exercise', 8999.00, 4, 3, 50, 'https://images.pexels.com/photos/2529148/pexels-photo-2529148.jpeg'),
('Adidas Ultraboost', 'Premium running shoes with boost technology', 12999.00, 4, 4, 30, 'https://images.pexels.com/photos/1464625/pexels-photo-1464625.jpeg'),
('Sony Headphones', 'Noise-cancelling wireless headphones', 15999.00, 1, 5, 40, 'https://images.pexels.com/photos/3394650/pexels-photo-3394650.jpeg'),
('LG Smart TV', '55 inch 4K Smart TV with webOS', 45999.00, 1, 6, 12, 'https://images.pexels.com/photos/1201996/pexels-photo-1201996.jpeg'),
('Cotton T-Shirt', 'Premium quality cotton t-shirt', 1299.00, 2, 3, 100, 'https://images.pexels.com/photos/996329/pexels-photo-996329.jpeg'),
('Denim Jeans', 'Classic blue denim jeans', 2499.00, 2, 4, 75, 'https://images.pexels.com/photos/1598505/pexels-photo-1598505.jpeg');

-- Insert sample reviews
INSERT INTO reviews (product_id, user_id, rating, comment) VALUES 
(1, 2, 5, 'Excellent phone with great camera quality!'),
(1, 3, 4, 'Good phone but battery could be better'),
(2, 2, 5, 'Amazing iPhone, worth every penny'),
(3, 3, 4, 'Very comfortable shoes for running'),
(5, 2, 5, 'Best headphones I have ever used!');