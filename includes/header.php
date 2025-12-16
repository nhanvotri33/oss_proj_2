<?php
if (!isset($_SESSION)) {
    session_start();
}
$is_admin = isset($_SESSION['customer_id']) && ($_SESSION['role'] ?? 'user') === 'admin';
$is_in_admin_folder = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/') !== false);
$base_path = $is_in_admin_folder ? '../' : '';
?>
<header class="<?php echo ($is_admin || $is_in_admin_folder) ? 'admin-header' : ''; ?>">
    <div class="container">
        <div class="header-brand">
            <h1><a href="<?php echo $is_in_admin_folder ? 'index.php' : ($base_path ? $base_path . 'index.php' : 'index.php'); ?>">
                <?php echo $is_in_admin_folder ? 'Admin' : 'Nhà Sách'; ?>
            </a></h1>
        </div>
        <nav>
            <?php if (isset($_SESSION['customer_id'])): ?>
                <?php if (($_SESSION['role'] ?? 'user') === 'admin'): ?>
                    <!-- Admin Menu -->
                    <?php if ($is_in_admin_folder): ?>
                        <!-- In admin folder - show full admin menu -->
                        <a href="<?php echo $base_path; ?>index.php">Xem Cửa Hàng</a>
                        <a href="index.php">Bảng Điều Khiển</a>
                        <a href="books.php">Quản Lý Sách</a>
                        <a href="<?php echo $base_path; ?>authors.php">Tác Giả</a>
                        <a href="<?php echo $base_path; ?>categories.php">Danh Mục</a>
                        <a href="orders.php">Đơn Hàng</a>
                        <a href="customers.php">Khách Hàng</a>
                    <?php else: ?>
                        <!-- Not in admin folder - show simplified admin menu -->
                        <a href="index.php">Trang Chủ</a>
                        <a href="admin/index.php">Bảng Điều Khiển</a>
                        <a href="admin/books.php">Quản Lý Sách</a>
                        <a href="authors.php">Tác Giả</a>
                        <a href="categories.php">Danh Mục</a>
                    <?php endif; ?>
                    <span class="user-welcome">Quản trị: <?php echo htmlspecialchars($_SESSION['customer_name']); ?></span>
                <?php else: ?>
                    <!-- User Menu -->
                    <a href="<?php echo $base_path; ?>index.php">Trang Chủ</a>
                    <a href="<?php echo $base_path; ?>cart.php">Giỏ Hàng (<span id="cart-count">0</span>)</a>
                    <span class="user-welcome">Xin chào, <?php echo htmlspecialchars($_SESSION['customer_name']); ?>!</span>
                    <a href="<?php echo $base_path; ?>orders.php">Đơn Hàng Của Tôi</a>
                <?php endif; ?>
                <a href="<?php echo $base_path; ?>logout.php">Đăng Xuất</a>
            <?php else: ?>
                <!-- Guest Menu -->
                <a href="<?php echo $base_path; ?>index.php">Trang Chủ</a>
                <a href="<?php echo $base_path; ?>cart.php">Giỏ Hàng (<span id="cart-count">0</span>)</a>
                <a href="<?php echo $base_path; ?>register.php">Đăng Ký</a>
                <a href="<?php echo $base_path; ?>login.php">Đăng Nhập</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

