<?php
session_start();
require_once '../config/database.php';

// Check admin authentication
if (!isset($_SESSION['customer_id']) || ($_SESSION['role'] ?? 'user') !== 'admin') {
    header('Location: login.php');
    exit;
}

// Get filter parameters
$status = $_GET['status'] ?? null;

// Build query
$query = "SELECT o.*, c.name as customer_name, c.email as customer_email 
          FROM orders o 
          LEFT JOIN customers c ON o.customer_id = c.id 
          WHERE 1=1";
$params = [];

if ($status) {
    $query .= " AND o.status = ?";
    $params[] = $status;
}

$query .= " ORDER BY o.created_at DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $orders = [];
    $error = "Error loading orders: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-page">
    <?php include '../includes/header.php'; ?>

    <main class="container">
        <div class="admin-dashboard">
            <h1>Manage Orders</h1>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- Filter -->
            <div class="filters">
                <h3>Filter Orders</h3>
                <form method="GET" action="orders.php" class="filter-form">
                    <div class="filter-group">
                        <label for="status">Status:</label>
                        <select id="status" name="status">
                            <option value="">All Status</option>
                            <option value="pending" <?php echo ($status === 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="processing" <?php echo ($status === 'processing') ? 'selected' : ''; ?>>Processing</option>
                            <option value="completed" <?php echo ($status === 'completed') ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo ($status === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="orders.php" class="btn btn-secondary">Clear</a>
                </form>
            </div>

            <!-- Orders List -->
            <div class="dashboard-section">
                <?php if (empty($orders)): ?>
                    <div class="no-data">No orders found.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Email</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#<?php echo $order['id']; ?></td>
                                        <td><?php echo htmlspecialchars($order['customer_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($order['customer_email'] ?? 'N/A'); ?></td>
                                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                            <?php if ($order['status'] === 'pending'): ?>
                                                <a href="update_order.php?id=<?php echo $order['id']; ?>&status=processing" class="btn btn-sm btn-warning">Process</a>
                                            <?php elseif ($order['status'] === 'processing'): ?>
                                                <a href="update_order.php?id=<?php echo $order['id']; ?>&status=completed" class="btn btn-sm btn-primary">Complete</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div class="quick-actions">
                <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>

