<?php
session_start();
require_once 'config/database.php';

$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message']);
unset($_SESSION['error']);

// Fetch all authors
try {
    $authors = $pdo->query("SELECT a.*, COUNT(b.id) as book_count 
                           FROM authors a 
                           LEFT JOIN books b ON a.id = b.author_id 
                           GROUP BY a.id 
                           ORDER BY a.name")->fetchAll();
} catch (PDOException $e) {
    $authors = [];
    $error = "Lỗi khi tải danh sách tác giả: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tác Giả - Cửa Hàng Sách</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <?php if (isset($_SESSION['customer_id']) && ($_SESSION['role'] ?? 'user') === 'admin'): ?>
        <link rel="stylesheet" href="assets/css/admin.css">
    <?php endif; ?>
</head>
<body class="<?php echo (isset($_SESSION['customer_id']) && ($_SESSION['role'] ?? 'user') === 'admin') ? 'admin-page' : ''; ?>">
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <section class="authors-section">
            <h2>Tác Giả</h2>
            <?php if (isset($_SESSION['customer_id']) && ($_SESSION['role'] ?? 'user') === 'admin'): ?>
                <a href="add_author.php" class="btn btn-primary">Thêm Tác Giả Mới</a>
            <?php endif; ?>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (empty($authors)): ?>
                <div class="no-books">
                    <p>Chưa có tác giả nào. <a href="add_author.php">Thêm tác giả đầu tiên</a></p>
                </div>
            <?php else: ?>
                <div class="authors-grid">
                    <?php foreach ($authors as $author): ?>
                        <div class="author-card">
                            <h3><?php echo htmlspecialchars($author['name']); ?></h3>
                            <p class="author-bio"><?php echo htmlspecialchars($author['bio'] ?? 'Chưa có tiểu sử.'); ?></p>
                            <p class="author-books">Số sách: <?php echo $author['book_count']; ?></p>
                            <div class="author-actions">
                                <a href="index.php?author=<?php echo $author['id']; ?>" class="btn btn-primary btn-sm">Xem Sách</a>
                                <?php if (isset($_SESSION['customer_id']) && ($_SESSION['role'] ?? 'user') === 'admin'): ?>
                                    <a href="edit_author.php?id=<?php echo $author['id']; ?>" class="btn btn-warning btn-sm">Sửa</a>
                                    <a href="delete_author.php?id=<?php echo $author['id']; ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirm('Bạn có chắc chắn muốn xóa tác giả này?');">Xóa</a>
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

