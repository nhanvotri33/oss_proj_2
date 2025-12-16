<?php
/**
 * Database Setup Script
 * Chạy file này để tự động tạo database và các bảng
 * Không cần chạy file SQL thủ công
 */

require_once __DIR__ . '/../config/database.php';

// Kết nối MySQL mà không chọn database (để tạo database nếu chưa có)
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    // Tạo database nếu chưa có
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE " . DB_NAME);
    
    echo "<h2>Đang thiết lập database...</h2>";
    echo "<p>✓ Database '" . DB_NAME . "' đã được tạo/chọn</p>";
    
    // Tắt kiểm tra foreign key tạm thời
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Xóa các bảng cũ nếu có (theo thứ tự ngược lại)
    $tables = ['order_items', 'orders', 'books', 'customers', 'categories', 'authors'];
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS `$table`");
        echo "<p>✓ Đã xóa bảng cũ: $table</p>";
    }
    
    // Tạo bảng authors
    $pdo->exec("
        CREATE TABLE authors (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            bio TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p>✓ Đã tạo bảng: authors</p>";
    
    // Tạo bảng categories
    $pdo->exec("
        CREATE TABLE categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p>✓ Đã tạo bảng: categories</p>";
    
    // Tạo bảng books
    $pdo->exec("
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p>✓ Đã tạo bảng: books</p>";
    
    // Tạo bảng customers
    $pdo->exec("
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p>✓ Đã tạo bảng: customers</p>";
    
    // Tạo bảng orders
    $pdo->exec("
        CREATE TABLE orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT NOT NULL,
            total_amount DECIMAL(10, 2) NOT NULL,
            status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
            shipping_address TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p>✓ Đã tạo bảng: orders</p>";
    
    // Tạo bảng order_items
    $pdo->exec("
        CREATE TABLE order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            book_id INT NOT NULL,
            quantity INT NOT NULL,
            price DECIMAL(10, 2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p>✓ Đã tạo bảng: order_items</p>";
    
    // Bật lại kiểm tra foreign key
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    // Thêm dữ liệu mẫu - Authors
    $authors = [
        [1, 'F. Scott Fitzgerald', 'American novelist and short story writer, best known for The Great Gatsby.'],
        [2, 'Harper Lee', 'American novelist best known for To Kill a Mockingbird.'],
        [3, 'George Orwell', 'English novelist, essayist, and critic, best known for 1984 and Animal Farm.'],
        [4, 'Jane Austen', 'English novelist known primarily for her six major novels.'],
        [5, 'J.D. Salinger', 'American writer known for The Catcher in the Rye.']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO authors (id, name, bio) VALUES (?, ?, ?)");
    foreach ($authors as $author) {
        $stmt->execute($author);
    }
    $pdo->exec("ALTER TABLE authors AUTO_INCREMENT = 6");
    echo "<p>✓ Đã thêm dữ liệu mẫu: authors</p>";
    
    // Thêm dữ liệu mẫu - Categories
    $categories = [
        [1, 'Fiction', 'Works of imaginative narration, especially in prose form.'],
        [2, 'Classic Literature', 'Literary works of recognized and established value.'],
        [3, 'Dystopian', 'Fictional societies that are undesirable or frightening.'],
        [4, 'Romance', 'Fictional stories with a focus on romantic relationships.'],
        [5, 'Coming of Age', 'Stories about the growth of a protagonist from youth to adulthood.']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO categories (id, name, description) VALUES (?, ?, ?)");
    foreach ($categories as $category) {
        $stmt->execute($category);
    }
    $pdo->exec("ALTER TABLE categories AUTO_INCREMENT = 6");
    echo "<p>✓ Đã thêm dữ liệu mẫu: categories</p>";
    
    // Thêm dữ liệu mẫu - Books
    $books = [
        ['The Great Gatsby', 1, 2, 'A classic American novel set in the Jazz Age, following the mysterious millionaire Jay Gatsby and his obsession with Daisy Buchanan.', 12.99, 25, NULL],
        ['To Kill a Mockingbird', 2, 2, 'A gripping tale of racial injustice and childhood innocence in the American South.', 11.99, 30, NULL],
        ['1984', 3, 3, 'A dystopian novel about totalitarianism, surveillance, and thought control in a future society.', 13.99, 20, NULL],
        ['Pride and Prejudice', 4, 4, 'A romantic novel about Elizabeth Bennet and Mr. Darcy in 19th century England.', 10.99, 35, NULL],
        ['The Catcher in the Rye', 5, 5, 'A controversial novel following teenager Holden Caulfield as he navigates adolescence in New York City.', 11.99, 15, NULL]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO books (title, author_id, category_id, description, price, stock, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($books as $book) {
        $stmt->execute($book);
    }
    echo "<p>✓ Đã thêm dữ liệu mẫu: books</p>";
    
    // Thêm tài khoản admin mặc định
    // Email: admin@bookstore.com
    // Password: admin123
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO customers (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute(['Administrator', 'admin@bookstore.com', $adminPassword, 'admin']);
    echo "<p>✓ Đã tạo tài khoản admin mặc định</p>";
    echo "<p style='background: #e8f5e9; padding: 10px; border-left: 4px solid #4caf50; margin-top: 10px;'>";
    echo "<strong>Thông tin đăng nhập Admin:</strong><br>";
    echo "Email: <strong>admin@bookstore.com</strong><br>";
    echo "Password: <strong>admin123</strong>";
    echo "</p>";
    
    echo "<h2 style='color: green; margin-top: 20px;'>✓ Hoàn tất thiết lập database!</h2>";
    echo "<p><a href='../index.php'>Về trang chủ</a> | <a href='../admin/login.php'>Đăng nhập Admin</a></p>";
    
} catch (PDOException $e) {
    echo "<h2 style='color: red;'>Lỗi khi thiết lập database:</h2>";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Vui lòng kiểm tra lại cấu hình database trong file <code>config/database.php</code></p>";
}
?>
