<?php
session_start();
require_once 'config/database.php';

$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message']);
unset($_SESSION['error']);

// Fetch all categories
try {
    $categories = $pdo->query("SELECT c.*, COUNT(b.id) as book_count 
                              FROM categories c 
                              LEFT JOIN books b ON c.id = b.category_id 
                              GROUP BY c.id 
                              ORDER BY c.name")->fetchAll();
} catch (PDOException $e) {
    $categories = [];
    $error = "Lỗi khi tải danh sách danh mục: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Mục - Cửa Hàng Sách</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <?php if (isset($_SESSION['customer_id']) && ($_SESSION['role'] ?? 'user') === 'admin'): ?>
        <link rel="stylesheet" href="assets/css/admin.css">
    <?php endif; ?>
</head>
<body class="<?php echo (isset($_SESSION['customer_id']) && ($_SESSION['role'] ?? 'user') === 'admin') ? 'admin-page' : ''; ?>">
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <section class="categories-section">
            <h2>Danh Mục Sách</h2>
            <?php if (isset($_SESSION['customer_id']) && ($_SESSION['role'] ?? 'user') === 'admin'): ?>
                <a href="add_category.php" class="btn btn-primary">Thêm Danh Mục Mới</a>
            <?php endif; ?>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (empty($categories)): ?>
                <div class="no-books">
                    <p>Chưa có danh mục nào. <a href="add_category.php">Thêm danh mục đầu tiên</a></p>
                </div>
            <?php else: ?>
                <div class="categories-grid">
                    <?php foreach ($categories as $category): ?>
                        <div class="category-card">
                            <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                            <p class="category-description"><?php echo htmlspecialchars($category['description'] ?? 'Chưa có mô tả.'); ?></p>
                            <p class="category-books">Số sách: <?php echo $category['book_count']; ?></p>
                            <div class="category-actions">
                                <a href="index.php?category=<?php echo $category['id']; ?>" class="btn btn-primary btn-sm">Xem Sách</a>
                                <?php if (isset($_SESSION['customer_id']) && ($_SESSION['role'] ?? 'user') === 'admin'): ?>
                                    <a href="edit_category.php?id=<?php echo $category['id']; ?>" class="btn btn-warning btn-sm">Sửa</a>
                                    <a href="delete_category.php?id=<?php echo $category['id']; ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirm('Bạn có chắc chắn muốn xóa danh mục này?');">Xóa</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>

