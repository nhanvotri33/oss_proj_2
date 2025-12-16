<?php
session_start();
require_once 'config/database.php';

// Check if user is admin
if (!isset($_SESSION['customer_id']) || ($_SESSION['role'] ?? 'user') !== 'admin') {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: categories.php');
    exit;
}

// Check if category has books
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM books WHERE category_id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] > 0) {
        $_SESSION['error'] = "Không thể xóa danh mục này vì có " . $result['count'] . " cuốn sách đang sử dụng.";
        header('Location: categories.php');
        exit;
    }
    
    // Delete category
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    
    $_SESSION['message'] = "Xóa danh mục thành công!";
    header('Location: categories.php');
    exit;
} catch (PDOException $e) {
    $_SESSION['error'] = "Lỗi khi xóa danh mục: " . $e->getMessage();
    header('Location: categories.php');
    exit;
}
?>

