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

// Fetch category
try {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$category) {
        header('Location: categories.php');
        exit;
    }
} catch (PDOException $e) {
    header('Location: categories.php');
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
            $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
            $stmt->execute([$name, $description, $id]);
            $message = "Cập nhật danh mục thành công!";
            // Refresh category data
            $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            $category = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $error = "Lỗi khi cập nhật danh mục: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Danh Mục - Cửa Hàng Sách</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-page">
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <section class="form-section">
            <h2>Sửa Danh Mục</h2>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="edit_category.php?id=<?php echo $id; ?>" class="book-form">
                <div class="form-group">
                    <label for="name">Tên Danh Mục *</label>
                    <input type="text" id="name" name="name" required 
                           value="<?php echo htmlspecialchars($category['name']); ?>">
                </div>

                <div class="form-group">
                    <label for="description">Mô Tả</label>
                    <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($category['description'] ?? ''); ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Cập Nhật</button>
                    <a href="categories.php" class="btn btn-secondary">Hủy</a>
                </div>
            </form>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>

