<?php
session_start();
require_once 'config/database.php';

$customer_id = $_SESSION['customer_id'] ?? null;

if (!$customer_id) {
    header('Location: login.php');
    exit;
}

// Fetch orders for this customer
try {
    $stmt = $pdo->prepare("
        SELECT o.*, 
               COUNT(oi.id) as item_count
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.customer_id = ?
        GROUP BY o.id
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$customer_id]);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $orders = [];
    $error = "Lỗi khi tải danh sách đơn hàng: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn Hàng Của Tôi - Cửa Hàng Sách</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <section class="orders-section">
            <h2>Đơn Hàng Của Tôi</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (empty($orders)): ?>
                <div class="no-books">
                    <p>Bạn chưa có đơn hàng nào. <a href="index.php">Bắt đầu mua sắm</a></p>
                </div>
            <?php else: ?>
                <div class="orders-list">
                    <?php 
                    $statusMap = [
                        'pending' => 'Đang chờ',
                        'processing' => 'Đang xử lý',
                        'shipped' => 'Đã giao hàng',
                        'delivered' => 'Đã nhận hàng',
                        'cancelled' => 'Đã hủy'
                    ];
                    foreach ($orders as $order): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <h3>Đơn hàng #<?php echo $order['id']; ?></h3>
                                <span class="order-status status-<?php echo $order['status']; ?>">
                                    <?php echo $statusMap[$order['status']] ?? ucfirst($order['status']); ?>
                                </span>
                            </div>
                            <div class="order-details">
                                <p><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                                <p><strong>Số sản phẩm:</strong> <?php echo $order['item_count']; ?></p>
                                <p><strong>Tổng tiền:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
                                <?php if ($order['shipping_address']): ?>
                                    <p><strong>Địa chỉ giao hàng:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="order-items">
                                <?php
                                try {
                                    $itemsStmt = $pdo->prepare("
                                        SELECT oi.*, b.title 
                                        FROM order_items oi
                                        INNER JOIN books b ON oi.book_id = b.id
                                        WHERE oi.order_id = ?
                                    ");
                                    $itemsStmt->execute([$order['id']]);
                                    $items = $itemsStmt->fetchAll();
                                    
                                    foreach ($items as $item) {
                                        echo "<p>- " . htmlspecialchars($item['title']) . " (Số lượng: " . $item['quantity'] . ") - $" . number_format($item['price'] * $item['quantity'], 2) . "</p>";
                                    }
                                } catch (PDOException $e) {
                                    echo "<p>Lỗi khi tải chi tiết đơn hàng</p>";
                                }
                                ?>
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

