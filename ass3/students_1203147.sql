
CREATE DATABASE IF NOT EXISTS web1203147_souvenirStore;
USE web1203147_souvenirStore;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mobile VARCHAR(15) NOT NULL,
    date_of_birth DATE NOT NULL,
    flat_unit VARCHAR(50),
    street_address VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    country VARCHAR(100) NOT NULL,
    postal_code VARCHAR(6) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('Customer', 'Employee') DEFAULT 'Customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Products table
CREATE TABLE IF NOT EXISTS products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    quantity INT NOT NULL,
    rating INT,
    photo1 VARCHAR(255),
    photo2 VARCHAR(255),
    photo3 VARCHAR(255),
    default_photo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Order Items table
CREATE TABLE IF NOT EXISTS order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Test data: Users (2 customers + 1 employee)
INSERT INTO users (first_name, last_name, email, mobile, date_of_birth, flat_unit, street_address, city, country, postal_code, password, role) VALUES
('Ahmed', 'Ibrahim', 'ahmed@example.com', '0590123456', '1990-05-15', '101', '42 Main Street', 'Ramallah', 'Palestine', '123456', '$2y$10$rRXvpb9K9.2J3a6b7c8d9J2K3L4M5N6O7P8Q9R0S1T2U3V4W5X6Y7Z', 'Customer'),
('Fatima', 'Hassan', 'fatima@example.com', '0590234567', '1995-08-22', '205', '88 King Street', 'Bethlehem', 'Palestine', '234567', '$2y$10$rRXvpb9K9.2J3a6b7c8d9J2K3L4M5N6O7P8Q9R0S1T2U3V4W5X6Y7Z', 'Customer'),
('Noor', 'Abbas', 'noor@example.com', '0590345678', '1988-03-10', '302', '15 Commerce Road', 'Nablus', 'Palestine', '345678', '$2y$10$rRXvpb9K9.2J3a6b7c8d9J2K3L4M5N6O7P8Q9R0S1T2U3V4W5X6Y7Z', 'Employee');

INSERT INTO products
(product_name, category, description, price, quantity, rating, photo1, photo2, photo3, default_photo)
VALUES

(
'Traditional Keffiyeh',
'Clothing',
'Authentic Palestinian black and white keffiyeh scarf.',
29.99,
50,
5,
'1_1.jpeg',
'1_2.jpeg',
'1_3.jpeg',
'1_1.jpeg'
),

(
'Palestine Flag T-Shirt',
'Clothing',
'Comfortable cotton T-shirt featuring the Palestinian flag.',
24.99,
40,
4,
'2_1.jpeg',
'2_2.jpeg',
'2_3.jpeg',
'2_1.jpeg'
),

(
'Palestinian Hoodie',
'Clothing',
'Warm hoodie printed with Palestinian cultural artwork.',
39.99,
25,
5,
'3_1.jpeg',
'3_2.jpeg',
'3_3.jpeg',
'3_1.jpeg'
),

(
'Olive Wood Camel',
'Handicrafts',
'Hand-carved camel made from genuine olive wood.',
19.99,
30,
4,
'4_1.jpeg',
'4_2.jpeg',
'4_3.jpeg',
'4_1.jpeg'
),

(
'Olive Wood Cross',
'Religious Gifts',
'Traditional Bethlehem olive wood cross.',
14.99,
35,
5,
'5_1.jpeg',
'5_2.jpeg',
'5_3.jpeg',
'5_1.jpeg'
),

(
'Palestine Coffee Mug',
'Kitchenware',
'Ceramic mug printed with Palestinian flag design.',
12.99,
60,
4,
'6_1.jpeg',
'6_2.jpeg',
'6_3.jpeg',
'6_1.jpeg'
),

(
'Palestinian Map Keychain',
'Accessories',
'Metal keychain featuring the map of Palestine.',
8.99,
100,
4,
'7_1.jpeg',
'7_2.jpeg',
'7_3.jpeg',
'7_1.jpeg'
),

(
'Ceramic Jerusalem Plate',
'Home Decor',
'Decorative ceramic plate inspired by Jerusalem landmarks.',
34.99,
20,
5,
'8_1.jpeg',
'8_2.jpeg',
'8_3.jpeg',
'8_1.jpeg'
),

(
'Palestinian Embroidered Bag',
'Accessories',
'Traditional embroidered shoulder bag.',
27.99,
15,
5,
'9_1.jpeg',
'9_2.jpeg',
'9_3.jpeg',
'9_1.jpeg'
),

(
'Palestine Flag',
'Flags',
'High-quality Palestinian national flag.',
15.99,
80,
4,
'10_1.jpeg',
'10_2.jpeg',
'10_3.jpeg',
'10_1.jpeg'
); 