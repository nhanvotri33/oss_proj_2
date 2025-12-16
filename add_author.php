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
    $bio = $_POST['bio'] ?? '';

    // Validation
    if (empty($name)) {
        $error = "Tên tác giả là bắt buộc.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO authors (name, bio) VALUES (?, ?)");
            $stmt->execute([$name, $bio]);
            $message = "Thêm tác giả thành công!";
            $_POST = [];
        } catch (PDOException $e) {
            $error = "Lỗi khi thêm tác giả: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Tác Giả - Cửa Hàng Sách</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-page">
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <section class="form-section">
            <h2>Thêm Tác Giả Mới</h2>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="add_author.php" class="book-form">
                <div class="form-group">
                    <label for="name">Tên Tác Giả *</label>
                    <input type="text" id="name" name="name" required 
                           value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="bio">Tiểu Sử</label>
                    <textarea id="bio" name="bio" rows="6"><?php echo htmlspecialchars($_POST['bio'] ?? ''); ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Thêm Tác Giả</button>
                    <a href="authors.php" class="btn btn-secondary">Hủy</a>
                </div>
            </form>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>

