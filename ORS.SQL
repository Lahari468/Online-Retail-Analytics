-- Create the database
DROP DATABASE IF EXISTS retail_insight;
CREATE DATABASE retail_insight;
USE retail_insight;

-- Create customers table
CREATE TABLE customers (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE,
    join_date DATE NOT NULL,
    region VARCHAR(50),
    customer_segment VARCHAR(30) DEFAULT 'Standard'
);

-- Create products table
CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    subcategory VARCHAR(50),
    price DECIMAL(10, 2) NOT NULL,
    cost DECIMAL(10, 2) NOT NULL,
    stock_quantity INT NOT NULL
);

-- Create orders table
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    order_date DATETIME NOT NULL,
    status VARCHAR(20) DEFAULT 'Completed',
    payment_method VARCHAR(30),
    shipping_region VARCHAR(50),
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
);

-- Create order_items table
CREATE TABLE order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

-- Insert sample customers
INSERT INTO customers (first_name, last_name, email, join_date, region, customer_segment) VALUES
('John', 'Smith', 'john.smith@email.com', '2022-01-15', 'North America', 'Premium'),
('Emma', 'Johnson', 'emma.j@email.com', '2022-02-20', 'Europe', 'Standard'),
('Michael', 'Brown', 'michael.b@email.com', '2022-03-10', 'North America', 'Standard'),
('Sarah', 'Davis', 'sarah.d@email.com', '2022-04-05', 'Asia', 'Premium'),
('David', 'Wilson', 'david.w@email.com', '2022-05-12', 'Europe', 'Standard');

-- Insert sample products
INSERT INTO products (product_name, category, subcategory, price, cost, stock_quantity) VALUES
('Wireless Headphones', 'Electronics', 'Audio', 99.99, 45.00, 150),
('Smartphone X', 'Electronics', 'Phones', 899.99, 450.00, 75),
('Running Shoes', 'Apparel', 'Footwear', 79.99, 35.00, 200),
('Yoga Mat', 'Sports', 'Fitness', 29.99, 12.50, 180),
('Blender Pro', 'Home', 'Kitchen', 59.99, 25.00, 90),
('Desk Lamp', 'Home', 'Office', 39.99, 18.00, 120),
('Backpack', 'Accessories', 'Bags', 49.99, 22.00, 160);

-- Insert sample orders
INSERT INTO orders (customer_id, order_date, status, payment_method, shipping_region) VALUES
(1, '2023-01-05 10:30:00', 'Completed', 'Credit Card', 'North America'),
(2, '2023-01-10 14:15:00', 'Completed', 'PayPal', 'Europe'),
(3, '2023-01-15 09:45:00', 'Completed', 'Credit Card', 'North America'),
(4, '2023-01-20 16:20:00', 'Completed', 'Bank Transfer', 'Asia'),
(1, '2023-02-01 11:10:00', 'Completed', 'Credit Card', 'North America'),
(5, '2023-02-05 13:25:00', 'Completed', 'PayPal', 'Europe'),
(2, '2023-02-10 15:30:00', 'Completed', 'Credit Card', 'Europe'),
(3, '2023-02-15 10:45:00', 'Completed', 'Credit Card', 'North America'),
(4, '2023-02-20 12:00:00', 'Completed', 'Bank Transfer', 'Asia'),
(1, '2023-03-01 14:15:00', 'Completed', 'Credit Card', 'North America');

-- Insert sample order items
INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES
(1, 1, 1, 99.99),
(1, 3, 1, 79.99),
(2, 2, 1, 899.99),
(3, 4, 2, 29.99),
(3, 5, 1, 59.99),
(4, 6, 1, 39.99),
(4, 7, 1, 49.99),
(5, 1, 2, 99.99),
(6, 3, 1, 79.99),
(7, 2, 1, 899.99),
(8, 4, 1, 29.99),
(8, 5, 1, 59.99),
(9, 6, 1, 39.99),
(10, 7, 2, 49.99);

-- ===================================================================
-- CORRECTED ANALYTICAL QUERIES (ALL AMBIGUITIES FIXED)
-- ===================================================================

-- 1. Sales Trend Over Time (Line Chart)
SELECT 
    DATE_FORMAT(o.order_date, '%Y-%m') AS month,
    SUM(oi.quantity * oi.unit_price) AS total_sales,
    COUNT(DISTINCT o.order_id) AS order_count
FROM orders o
JOIN order_items oi ON o.order_id = oi.order_id
GROUP BY DATE_FORMAT(o.order_date, '%Y-%m')
ORDER BY month;

-- 2. Sales by Product Category (Bar Chart)
SELECT 
    p.category,
    SUM(oi.quantity * oi.unit_price) AS total_sales,
    SUM(oi.quantity) AS total_quantity
FROM order_items oi
JOIN products p ON oi.product_id = p.product_id
GROUP BY p.category
ORDER BY total_sales DESC;

-- 3. Top Selling Products (Horizontal Bar Chart)
SELECT 
    p.product_name,
    SUM(oi.quantity) AS total_quantity,
    SUM(oi.quantity * oi.unit_price) AS total_revenue
FROM order_items oi
JOIN products p ON oi.product_id = p.product_id
GROUP BY p.product_name
ORDER BY total_revenue DESC
LIMIT 5;

-- 4. Customer Segmentation (Pie/Doughnut Chart) - FIXED
SELECT 
    c.customer_segment,
    COUNT(DISTINCT c.customer_id) AS customer_count,
    SUM(oi.quantity * oi.unit_price) AS total_spend
FROM customers c
JOIN orders o ON c.customer_id = o.customer_id
JOIN order_items oi ON o.order_id = oi.order_id
GROUP BY c.customer_segment;

-- 5. RFM Analysis (Scatter Plot) - FIXED
WITH rfm_data AS (
    SELECT 
        c.customer_id,
        c.first_name,
        c.last_name,
        DATEDIFF(CURRENT_DATE, MAX(o.order_date)) AS recency,
        COUNT(DISTINCT o.order_id) AS frequency,
        SUM(oi.quantity * oi.unit_price) AS monetary
    FROM customers c
    JOIN orders o ON c.customer_id = o.customer_id
    JOIN order_items oi ON o.order_id = oi.order_id
    GROUP BY c.customer_id, c.first_name, c.last_name
)
SELECT * FROM rfm_data;

-- 6. Monthly Sales Comparison (YoY Growth) - FIXED
SELECT 
    DATE_FORMAT(o.order_date, '%m') AS month_num,
    DATE_FORMAT(o.order_date, '%M') AS month_name,
    SUM(CASE WHEN YEAR(o.order_date) = 2023 THEN oi.quantity * oi.unit_price ELSE 0 END) AS sales_2023,
    SUM(CASE WHEN YEAR(o.order_date) = 2022 THEN oi.quantity * oi.unit_price ELSE 0 END) AS sales_2022
FROM orders o
JOIN order_items oi ON o.order_id = oi.order_id
WHERE YEAR(o.order_date) IN (2022, 2023)
GROUP BY DATE_FORMAT(o.order_date, '%m'), DATE_FORMAT(o.order_date, '%M')
ORDER BY month_num;

-- 7. Geographic Sales Distribution (Map/Bar Chart) - FIXED
SELECT 
    c.region,
    SUM(oi.quantity * oi.unit_price) AS total_sales,
    COUNT(DISTINCT o.order_id) AS order_count
FROM orders o
JOIN customers c ON o.customer_id = c.customer_id
JOIN order_items oi ON o.order_id = oi.order_id
GROUP BY c.region
ORDER BY total_sales DESC;

-- 8. Product Performance by Margin (Bubble Chart) - FIXED
SELECT 
    p.product_name,
    SUM(oi.quantity) AS total_quantity,
    SUM(oi.quantity * oi.unit_price) AS total_revenue,
    SUM(oi.quantity * (oi.unit_price - p.cost)) AS total_profit,
    AVG(oi.unit_price) AS avg_price
FROM order_items oi
JOIN products p ON oi.product_id = p.product_id
GROUP BY p.product_name;