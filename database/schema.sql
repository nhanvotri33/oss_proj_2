-- Book Store Database Schema
-- 
-- IMPORTANT: Run this entire file at once for best results.
-- If you need to drop tables individually, run this first:
-- SET FOREIGN_KEY_CHECKS = 0;
-- Then drop your table, then run:
-- SET FOREIGN_KEY_CHECKS = 1;

CREATE DATABASE IF NOT EXISTS book_store_1;
USE book_store_1;

-- ============================================
-- STEP 1: Drop all existing tables safely
-- ============================================
-- IMPORTANT: Disable foreign key checks BEFORE dropping tables
SET FOREIGN_KEY_CHECKS = 0;

-- Drop tables if they exist (in reverse order of dependencies)
-- Must drop child tables first, then parent tables
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS books;
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS authors;

-- ============================================
-- STEP 2: Create tables
-- ============================================
-- Foreign key checks are still disabled, will be re-enabled after creating all tables

-- Authors table
CREATE TABLE authors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    bio TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Books table (updated with foreign keys)
CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author_id INT NOT NULL,
    category_id INT NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) DEFAULT 0.00,
    stock INT DEFAULT 0,
    image_url VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES authors(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customers table
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20),
    address TEXT,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order items table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    book_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Insert sample authors
INSERT INTO authors (id, name, bio) VALUES
(1, 'F. Scott Fitzgerald', 'American novelist and short story writer, best known for The Great Gatsby.'),
(2, 'Harper Lee', 'American novelist best known for To Kill a Mockingbird.'),
(3, 'George Orwell', 'English novelist, essayist, and critic, best known for 1984 and Animal Farm.'),
(4, 'Jane Austen', 'English novelist known primarily for her six major novels.'),
(5, 'J.D. Salinger', 'American writer known for The Catcher in the Rye.');

-- Reset AUTO_INCREMENT for authors
ALTER TABLE authors AUTO_INCREMENT = 6;

-- Insert sample categories
INSERT INTO categories (id, name, description) VALUES
(1, 'Fiction', 'Works of imaginative narration, especially in prose form.'),
(2, 'Classic Literature', 'Literary works of recognized and established value.'),
(3, 'Dystopian', 'Fictional societies that are undesirable or frightening.'),
(4, 'Romance', 'Fictional stories with a focus on romantic relationships.'),
(5, 'Coming of Age', 'Stories about the growth of a protagonist from youth to adulthood.');

-- Reset AUTO_INCREMENT for categories
ALTER TABLE categories AUTO_INCREMENT = 6;

-- Insert sample books
INSERT INTO books (title, author_id, category_id, description, price, stock, image_url) VALUES
('The Great Gatsby', 1, 2, 'A classic American novel set in the Jazz Age, following the mysterious millionaire Jay Gatsby and his obsession with Daisy Buchanan.', 12.99, 25, NULL),
('To Kill a Mockingbird', 2, 2, 'A gripping tale of racial injustice and childhood innocence in the American South.', 11.99, 30, NULL),
('1984', 3, 3, 'A dystopian novel about totalitarianism, surveillance, and thought control in a future society.', 13.99, 20, NULL),
('Pride and Prejudice', 4, 4, 'A romantic novel about Elizabeth Bennet and Mr. Darcy in 19th century England.', 10.99, 35, NULL),
('The Catcher in the Rye', 5, 5, 'A controversial novel following teenager Holden Caulfield as he navigates adolescence in New York City.', 11.99, 15, NULL);

-- Insert default admin user
-- Email: admin@bookstore.com
-- Password: admin123
INSERT INTO customers (name, email, password, role) VALUES
('Administrator', 'admin@bookstore.com', '$2y$10$2NU4X3KGMLs1ZT5ujQVdIOtDPtvyBKRnHtCDfsyxgZEB3aWrQCqRG', 'admin');
