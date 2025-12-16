<?php
/**
 * Migration Script: Thêm cột image_url vào bảng books
 * Chạy file này một lần để sửa lỗi thiếu cột image_url
 */

require_once __DIR__ . '/../config/database.php';

try {
    // Kiểm tra xem cột image_url đã tồn tại chưa
    $checkColumn = $pdo->query("
        SELECT COUNT(*) as count 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = '" . DB_NAME . "' 
        AND TABLE_NAME = 'books' 
        AND COLUMN_NAME = 'image_url'
    ")->fetch();
    
    if ($checkColumn['count'] > 0) {
        echo "<h2 style='color: green;'>✓ Cột 'image_url' đã tồn tại trong bảng 'books'</h2>";
        echo "<p>Không cần thực hiện migration.</p>";
    } else {
        // Thêm cột image_url vào bảng books
        $pdo->exec("
            ALTER TABLE books 
            ADD COLUMN image_url VARCHAR(500) DEFAULT NULL 
            AFTER stock
        ");
        
        echo "<h2 style='color: green;'>✓ Đã thêm cột 'image_url' vào bảng 'books' thành công!</h2>";
        echo "<p>Bây giờ bạn có thể thêm sách với ảnh bìa.</p>";
    }
    
    echo "<p><a href='../index.php'>Về trang chủ</a> | <a href='../add_book.php'>Thêm sách</a></p>";
    
} catch (PDOException $e) {
    echo "<h2 style='color: red;'>Lỗi khi thực hiện migration:</h2>";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Vui lòng kiểm tra lại cấu hình database trong file <code>config/database.php</code></p>";
}
?>
