<?php
session_start();
require_once '../config/database.php';

// Check admin authentication
if (!isset($_SESSION['customer_id']) || ($_SESSION['role'] ?? 'user') !== 'admin') {
    header('Location: login.php');
    exit;
}

$order_id = $_GET['id'] ?? null;

if (!$order_id) {
    header('Location: orders.php');
    exit;
}

// Fetch order details
try {
    $stmt = $pdo->prepare("
        SELECT o.*, c.name as customer_name, c.email as customer_email, c.phone as customer_phone, c.address as customer_address
        FROM orders o 
        LEFT JOIN customers c ON o.customer_id = c.id 
        WHERE o.id = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    
    if (!$order) {
        header('Location: orders.php');
        exit;
    }
    
    // Fetch order items
    $stmt = $pdo->prepare("
        SELECT oi.*, b.title, b.description 
        FROM order_items oi 
        INNER JOIN books b ON oi.book_id = b.id 
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Error loading order: " . $e->getMessage();
    $order = null;
    $items = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Detail #<?php echo $order_id; ?> - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-page">
    <?php include '../includes/header.php'; ?>

    <main class="container">
        <div class="admin-dashboard">
            <h1>Order Detail #<?php echo $order_id; ?></h1>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php elseif ($order): ?>
                
                <!-- Order Information -->
                <div class="dashboard-section">
                    <h2>Order Information</h2>
                    <div class="order-details">
                        <p><strong>Order ID:</strong> #<?php echo $order['id']; ?></p>
                        <p><strong>Status:</strong> 
                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </p>
                        <p><strong>Total Amount:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
                        <p><strong>Order Date:</strong> <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></p>
                        <?php if ($order['shipping_address']): ?>
                            <p><strong>Shipping Address:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="dashboard-section">
                    <h2>Customer Information</h2>
                    <div class="order-details">
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name'] ?? 'N/A'); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($order['customer_email'] ?? 'N/A'); ?></p>
                        <?php if ($order['customer_phone']): ?>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?></p>
                        <?php endif; ?>
                        <?php if ($order['customer_address']): ?>
                            <p><strong>Address:</strong> <?php echo htmlspecialchars($order['customer_address']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="dashboard-section">
                    <h2>Order Items</h2>
                    <?php if (empty($items)): ?>
                        <div class="no-data">No items found.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Book</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['title']); ?></td>
                                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
                                        <td><strong>$<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Actions -->
                <div class="quick-actions">
                    <a href="orders.php" class="btn btn-secondary">Back to Orders</a>
                    <?php if ($order['status'] === 'pending'): ?>
                        <a href="update_order.php?id=<?php echo $order['id']; ?>&status=processing" class="btn btn-warning">Mark as Processing</a>
                    <?php elseif ($order['status'] === 'processing'): ?>
                        <a href="update_order.php?id=<?php echo $order['id']; ?>&status=completed" class="btn btn-primary">Mark as Completed</a>
                    <?php endif; ?>
                </div>

            <?php endif; ?>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>

