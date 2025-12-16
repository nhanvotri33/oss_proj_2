<?php
session_start();
require_once '../config/database.php';

// Check admin authentication
if (!isset($_SESSION['customer_id']) || ($_SESSION['role'] ?? 'user') !== 'admin') {
    header('Location: login.php');
    exit;
}

// Fetch all customers
try {
    $stmt = $pdo->query("
        SELECT c.*, COUNT(o.id) as order_count 
        FROM customers c 
        LEFT JOIN orders o ON c.id = o.customer_id 
        GROUP BY c.id 
        ORDER BY c.created_at DESC
    ");
    $customers = $stmt->fetchAll();
} catch (PDOException $e) {
    $customers = [];
    $error = "Error loading customers: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Customers - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-page">
    <?php include '../includes/header.php'; ?>

    <main class="container">
        <div class="admin-dashboard">
            <h1>View Customers</h1>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="dashboard-section">
                <?php if (empty($customers)): ?>
                    <div class="no-data">No customers found.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Role</th>
                                    <th>Orders</th>
                                    <th>Registered</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customers as $customer): ?>
                                    <tr>
                                        <td><?php echo $customer['id']; ?></td>
                                        <td><?php echo htmlspecialchars($customer['name']); ?></td>
                                        <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                        <td><?php echo htmlspecialchars($customer['phone'] ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $customer['role'] ?? 'user'; ?>">
                                                <?php echo ucfirst($customer['role'] ?? 'user'); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $customer['order_count']; ?></td>
                                        <td><?php echo date('M j, Y', strtotime($customer['created_at'])); ?></td>
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

