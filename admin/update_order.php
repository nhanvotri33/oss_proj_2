<?php
session_start();
require_once '../config/database.php';

// Check admin authentication
if (!isset($_SESSION['customer_id']) || ($_SESSION['role'] ?? 'user') !== 'admin') {
    header('Location: login.php');
    exit;
}

$order_id = $_GET['id'] ?? null;
$new_status = $_GET['status'] ?? null;

if (!$order_id || !$new_status) {
    header('Location: orders.php');
    exit;
}

// Validate status
$valid_statuses = ['pending', 'processing', 'completed', 'cancelled'];
if (!in_array($new_status, $valid_statuses)) {
    header('Location: orders.php');
    exit;
}

// Update order status
try {
    $stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->execute([$new_status, $order_id]);
    
    header('Location: order_detail.php?id=' . $order_id);
    exit;
} catch (PDOException $e) {
    header('Location: orders.php?error=' . urlencode("Error updating order: " . $e->getMessage()));
    exit;
}
?>

