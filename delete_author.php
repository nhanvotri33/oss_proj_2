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
    header('Location: authors.php');
    exit;
}

// Check if author has books
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM books WHERE author_id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] > 0) {
        $_SESSION['error'] = "Không thể xóa tác giả này vì có " . $result['count'] . " cuốn sách đang sử dụng.";
        header('Location: authors.php');
        exit;
    }
    
    // Delete author
    $stmt = $pdo->prepare("DELETE FROM authors WHERE id = ?");
    $stmt->execute([$id]);
    
    $_SESSION['message'] = "Xóa tác giả thành công!";
    header('Location: authors.php');
    exit;
} catch (PDOException $e) {
    $_SESSION['error'] = "Lỗi khi xóa tác giả: " . $e->getMessage();
    header('Location: authors.php');
    exit;
}
?>

