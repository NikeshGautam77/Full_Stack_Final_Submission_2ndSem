-- ================================
-- DATABASE CREATION
-- ================================
CREATE DATABASE IF NOT EXISTS final_assignment;
USE final_assignment;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ================================
-- USERS TABLE
-- ================================
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('customer','admin') DEFAULT 'customer',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (id, username, password_hash, role) VALUES
(1, 'arvindsir1', '$2y$10$tRyuDKP1TuiHhqXxxePGV.oFVCAIu66ia0ymcd7mj3E5gsd/Mt30e', 'admin'),
(2, 'rohit7', '$2y$10$p36.dOxb5YsN9aOuX0S.zOBsg83WrQfBrUl1Ujm38G5ArskqWSnmC', 'customer'),
(3, 'nik7', '$2y$10$6sKRFIklPFuM1/HIsJ9mP./xh0i2UlrDD.17RAII0teb7YxnyuITy', 'customer');

-- ================================
-- MENU ITEMS TABLE
-- ================================
CREATE TABLE menu_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  category ENUM('veg','nonveg','drinks','desserts') NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  description TEXT,
  available TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO menu_items (name, category, price, description) VALUES
('Paneer Butter Masala','veg',250,'Rich tomato gravy with paneer'),
('Veg Biryani','veg',100,'Fragrant rice with vegetables'),
('Chole Bhature','veg',70,'Spicy chickpeas with fried bread'),
('Veg Momo','veg',90,'Steamed dumplings with chutney'),
('Samosa','veg',30,'Crispy pastry with spiced filling'),
('Dal Makhani','veg',290,'Creamy lentils cooked overnight'),
('Masala Dosa','veg',200,'Crispy dosa with potato filling'),
('Palak Paneer','veg',250,'Spinach curry with paneer cubes'),
('Chicken Biryani','nonveg',280,'Rice with chicken and spices'),
('Mutton Biryani','nonveg',320,'Rice with mutton pieces'),
('Chicken Sekuwa','nonveg',160,'Grilled chicken skewers'),
('Mutton Sekuwa','nonveg',200,'Grilled mutton skewers'),
('Chicken Curry','nonveg',200,'Spicy chicken curry'),
('Chicken Momo','nonveg',120,'Steamed chicken dumplings'),
('Milk Tea','drinks',30,'Classic milk tea'),
('Black Coffee','drinks',25,'Plain black coffee'),
('Cold Coffee','drinks',80,'Iced coffee'),
('Coca-Cola','drinks',50,'Soft drink'),
('Gulab Jamun','desserts',40,'Sweet milk balls'),
('Chocolate Ice Cream','desserts',95,'Chocolate flavored ice cream');

-- ================================
-- ORDERS TABLE
-- ================================
CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  items LONGTEXT NOT NULL CHECK (JSON_VALID(items)),
  total_price DECIMAL(10,2) NOT NULL,
  status ENUM('pending','preparing','completed') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT INTO orders (customer_id, items, total_price, status) VALUES
(2, '[{"name":"Dal Makhani","price":290,"qty":1}]', 290, 'completed'),
(3, '[{"name":"Chole Bhature","price":70,"qty":1}]', 70, 'preparing'),
(1, '[{"name":"Samosa","price":30,"qty":2}]', 60, 'pending'),
(3, '[{"name":"Cold Coffee","price":80,"qty":2}]', 160, 'completed');

-- ================================
-- END OF FILE
-- ================================
