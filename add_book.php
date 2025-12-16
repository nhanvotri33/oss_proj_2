<?php
session_start();
require_once 'config/database.php';

// Check if user is admin
if (!isset($_SESSION['customer_id']) || ($_SESSION['role'] ?? 'user') !== 'admin') {
    header('Location: index.php');
    exit;
}

// Fetch authors and categories
try {
    $authors = $pdo->query("SELECT * FROM authors ORDER BY name")->fetchAll();
    $categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
} catch (PDOException $e) {
    $authors = [];
    $categories = [];
}

$message = '';
$error = '';
$upload_dir = __DIR__ . '/assets/image/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $author_id = $_POST['author_id'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $image_path = null;

    // Validation
    if (empty($title) || empty($author_id) || empty($category_id)) {
        $error = "Tiêu đề, Tác giả và Danh mục là các trường bắt buộc.";
    } else {
        // Handle image upload if provided
        if (!empty($_FILES['image_file']['name']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $mime = mime_content_type($_FILES['image_file']['tmp_name']);

            if (!in_array($mime, $allowed_types, true)) {
                $error = "Ảnh bìa phải là JPG, PNG, WEBP hoặc GIF.";
            } else {
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $ext = pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION);
                $filename = uniqid('book_', true) . ($ext ? "." . strtolower($ext) : "");
                $dest = $upload_dir . $filename;

                if (move_uploaded_file($_FILES['image_file']['tmp_name'], $dest)) {
                    $image_path = 'assets/image/' . $filename;
                } else {
                    $error = "Không thể lưu ảnh bìa. Vui lòng thử lại.";
                }
            }
        }

        if (!$error) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO books (title, author_id, category_id, description, price, stock, image_url) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$title, $author_id, $category_id, $description, $price, $stock, $image_path]);
                $message = "Thêm sách thành công!";
                $_POST = [];
            } catch (PDOException $e) {
                $error = "Lỗi khi thêm sách: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Sách - Cửa Hàng Sách</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-page">
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <section class="form-section">
            <h2>Thêm Sách Mới</h2>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (empty($authors) || empty($categories)): ?>
                <div class="alert alert-error">
                    Vui lòng thêm ít nhất một <a href="add_author.php">Tác giả</a> và một <a href="add_category.php">Danh mục</a> trước.
                </div>
            <?php else: ?>
                <form method="POST" action="add_book.php" class="book-form" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title">Tiêu Đề *</label>
                        <input type="text" id="title" name="title" required 
                               value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="image_file">Ảnh bìa (tải lên)</label>
                        <input type="file" id="image_file" name="image_file" accept="image/*">
                        <small>Hỗ trợ JPG, PNG, WEBP, GIF. Không bắt buộc.</small>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="author_id">Tác Giả *</label>
                            <select id="author_id" name="author_id" required>
                                <option value="">Chọn Tác Giả</option>
                                <?php foreach ($authors as $author): ?>
                                    <option value="<?php echo $author['id']; ?>" 
                                            <?php echo (isset($_POST['author_id']) && $_POST['author_id'] == $author['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($author['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="category_id">Danh Mục *</label>
                            <select id="category_id" name="category_id" required>
                                <option value="">Chọn Danh Mục</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                            <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Mô Tả</label>
                        <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="price">Giá (VNĐ)</label>
                            <input type="number" id="price" name="price" step="0.01" min="0" 
                                   value="<?php echo htmlspecialchars($_POST['price'] ?? '0'); ?>">
                        </div>

                        <div class="form-group">
                            <label for="stock">Tồn Kho</label>
                            <input type="number" id="stock" name="stock" min="0" 
                                   value="<?php echo htmlspecialchars($_POST['stock'] ?? '0'); ?>">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Thêm Sách</button>
                        <a href="<?php echo (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? 'admin/index.php' : 'index.php'; ?>" class="btn btn-secondary">Hủy</a>
                    </div>
                </form>
            <?php endif; ?>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
