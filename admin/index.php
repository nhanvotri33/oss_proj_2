<?php
session_start();
require_once '../config/database.php';

// Check admin authentication
if (!isset($_SESSION['customer_id']) || ($_SESSION['role'] ?? 'user') !== 'admin') {
    header('Location: login.php');
    exit;
}

// Get statistics
try {
    $stats = [];
    
    // Total books
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM books");
    $stats['books'] = $stmt->fetch()['count'];
    
    // Total authors
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM authors");
    $stats['authors'] = $stmt->fetch()['count'];
    
    // Total categories
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
    $stats['categories'] = $stmt->fetch()['count'];
    
    // Total customers
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM customers");
    $stats['customers'] = $stmt->fetch()['count'];
    
    // Total orders
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
    $stats['orders'] = $stmt->fetch()['count'];
    
    // Total revenue
    $stmt = $pdo->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed'");
    $stats['revenue'] = $stmt->fetch()['total'] ?? 0;
    
    // Recent orders
    $stmt = $pdo->query("
        SELECT o.*, c.name as customer_name 
        FROM orders o 
        LEFT JOIN customers c ON o.customer_id = c.id 
        ORDER BY o.created_at DESC 
        LIMIT 10
    ");
    $recent_orders = $stmt->fetchAll();
    
    // Low stock books
    $stmt = $pdo->query("SELECT * FROM books WHERE stock < 10 ORDER BY stock ASC LIMIT 10");
    $low_stock = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Error loading statistics: " . $e->getMessage();
    $stats = ['books' => 0, 'authors' => 0, 'categories' => 0, 'customers' => 0, 'orders' => 0, 'revenue' => 0];
    $recent_orders = [];
    $low_stock = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảng Điều Khiển - Cửa Hàng Sách</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-page">
    <?php include '../includes/header.php'; ?>

    <main class="container">
        <div class="admin-dashboard">
            <h1>Bảng Điều Khiển</h1>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon books-icon">SÁCH</div>
                    <div class="stat-content">
                        <h3><?php echo $stats['books']; ?></h3>
                        <p>Tổng Sách</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon authors-icon">TG</div>
                    <div class="stat-content">
                        <h3><?php echo $stats['authors']; ?></h3>
                        <p>Tác Giả</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon categories-icon">DM</div>
                    <div class="stat-content">
                        <h3><?php echo $stats['categories']; ?></h3>
                        <p>Danh Mục</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon customers-icon">KH</div>
                    <div class="stat-content">
                        <h3><?php echo $stats['customers']; ?></h3>
                        <p>Khách Hàng</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon orders-icon">ĐH</div>
                    <div class="stat-content">
                        <h3><?php echo $stats['orders']; ?></h3>
                        <p>Tổng Đơn Hàng</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon revenue-icon">₫</div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['revenue'], 0, ',', '.'); ?> ₫</h3>
                        <p>Tổng Doanh Thu</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h2>Thao Tác Nhanh</h2>
                <div class="action-buttons">
                    <a href="../add_book.php" class="btn btn-primary">Thêm Sách Mới</a>
                    <a href="../add_author.php" class="btn btn-primary">Thêm Tác Giả Mới</a>
                    <a href="../add_category.php" class="btn btn-primary">Thêm Danh Mục Mới</a>
                    <a href="orders.php" class="btn btn-secondary">Quản Lý Đơn Hàng</a>
                    <a href="customers.php" class="btn btn-secondary">Xem Khách Hàng</a>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="dashboard-section">
                <h2>Đơn Hàng Gần Đây</h2>
                <?php if (empty($recent_orders)): ?>
                    <div class="no-data">Chưa có đơn hàng nào.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Mã Đơn</th>
                                    <th>Khách Hàng</th>
                                    <th>Tổng Tiền</th>
                                    <th>Trạng Thái</th>
                                    <th>Ngày</th>
                                    <th>Thao Tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order): ?>
                                    <tr>
                                        <td>#<?php echo $order['id']; ?></td>
                                        <td><?php echo htmlspecialchars($order['customer_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> ₫</td>
                                        <td>
                                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                                <?php 
                                                $status_vn = [
                                                    'pending' => 'Chờ Xử Lý',
                                                    'processing' => 'Đang Xử Lý',
                                                    'shipped' => 'Đã Giao',
                                                    'delivered' => 'Đã Nhận',
                                                    'cancelled' => 'Đã Hủy'
                                                ];
                                                echo $status_vn[$order['status']] ?? ucfirst($order['status']);
                                                ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">Xem</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Low Stock Alert -->
            <?php if (!empty($low_stock)): ?>
                <div class="dashboard-section alert-section">
                    <h2>Cảnh Báo Tồn Kho Thấp</h2>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Tên Sách</th>
                                    <th>Tồn Kho Hiện Tại</th>
                                    <th>Thao Tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($low_stock as $book): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                                        <td><span class="stock-warning"><?php echo $book['stock']; ?></span></td>
                                        <td>
                                            <a href="../add_book.php?edit=<?php echo $book['id']; ?>" class="btn btn-sm btn-warning">Cập Nhật</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>

