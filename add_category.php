<?php
session_start();
require_once 'config/database.php';

// Check if user is admin
if (!isset($_SESSION['customer_id']) || ($_SESSION['role'] ?? 'user') !== 'admin') {
    header('Location: index.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';

    // Validation
    if (empty($name)) {
        $error = "Tên danh mục là bắt buộc.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            $stmt->execute([$name, $description]);
            $message = "Thêm danh mục thành công!";
            $_POST = [];
        } catch (PDOException $e) {
            $error = "Lỗi khi thêm danh mục: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Danh Mục - Cửa Hàng Sách</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-page">
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <section class="form-section">
            <h2>Thêm Danh Mục Mới</h2>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="add_category.php" class="book-form">
                <div class="form-group">
                    <label for="name">Tên Danh Mục *</label>
                    <input type="text" id="name" name="name" required 
                           value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="description">Mô Tả</label>
                    <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Thêm Danh Mục</button>
                    <a href="categories.php" class="btn btn-secondary">Hủy</a>
                </div>
            </form>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>

