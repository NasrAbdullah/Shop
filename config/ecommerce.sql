-- =========================================
-- E-COMMERCE DATABASE
-- =========================================

CREATE DATABASE IF NOT EXISTS ecommerce;

USE ecommerce;


-- =========================================
-- 1. USERS
-- =========================================

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
    phone VARCHAR(20),
    -- this is give us now time of the computr
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================================
-- 2. CATEGORIES
-- =========================================

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- =========================================
-- 3. PRODUCTS
-- =========================================

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_products_category
        FOREIGN KEY (category_id)
        REFERENCES categories(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);


-- =========================================
-- 4. CART
-- =========================================

CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_cart_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);


-- =========================================
-- 5. CART ITEMS
-- =========================================

CREATE TABLE IF NOT EXISTS cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cart_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,

    CONSTRAINT fk_cart_items_cart
        FOREIGN KEY (cart_id)
        REFERENCES cart(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_cart_items_product
        FOREIGN KEY (product_id)
        REFERENCES products(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    UNIQUE (cart_id, product_id)
);


-- =========================================
-- 6. ORDERS
-- =========================================

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    status ENUM(
        'pending',
        'processing',
        'shipped',
        'completed',
        'cancelled'
    ) NOT NULL DEFAULT 'pending',
    shipping_address VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    payment_method ENUM('cash_on_delivery')
        NOT NULL DEFAULT 'cash_on_delivery',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_orders_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);


-- =========================================
-- 7. ORDER ITEMS
-- =========================================

CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,

    CONSTRAINT fk_order_items_order
        FOREIGN KEY (order_id)
        REFERENCES orders(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_order_items_product
        FOREIGN KEY (product_id)
        REFERENCES products(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);


-- =========================================
-- SAMPLE DATA
-- =========================================

-- USERS

INSERT INTO users
(name, email, password, role, phone)
VALUES
('Nasr', 'ahmed@gmail.com', '1125444', 'customer', '777111111'),
('Seham', 'ali@gmail.com', 'test123', 'customer', '777222222'),
('Admin', 'admin@gmail.com', 'test123', 'admin', '777333333');


-- CATEGORIES

INSERT INTO categories (name)
VALUES
('Phones'),
('Laptops'),
('Accessories');


-- PRODUCTS

INSERT INTO products
(category_id, name, description, price, quantity, image)
VALUES
(1, 'iPhone 15', 'Apple smartphone', 800.00, 10, 'iphone15.jpg'),
(1, 'Samsung S24', 'Samsung smartphone', 700.00, 15, 's24.jpg'),
(2, 'HP Laptop', 'HP laptop for students', 600.00, 8, 'hp.jpg'),
(3, 'Wireless Mouse', 'Wireless mouse', 25.00, 30, 'mouse.jpg'),
(3, 'Keyboard', 'Mechanical keyboard', 50.00, 20, 'keyboard.jpg');


-- CART

INSERT INTO cart (user_id)
VALUES
(1);


-- CART ITEMS

INSERT INTO cart_items
(cart_id, product_id, quantity)
VALUES
(1, 1, 1),
(1, 4, 2);


-- ORDER

INSERT INTO orders
(user_id, total, status, shipping_address, phone, payment_method)
VALUES
(1, 850.00, 'pending', 'Sana''a, Yemen', '777111111', 'cash_on_delivery');


-- ORDER ITEMS

INSERT INTO order_items
(order_id, product_id, quantity, price)
VALUES
(1, 1, 1, 800.00),
(1, 4, 2, 25.00);