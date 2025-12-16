<?php
session_start();
require_once 'config/database.php';

// ===== GET FILTER PARAMETERS =====
$category_id = $_GET['category'] ?? null;
$author_id   = $_GET['author'] ?? null;

// ===== BUILD QUERY =====
$query = "SELECT b.*, a.name AS author_name, c.name AS category_name
          FROM books b
          INNER JOIN authors a ON b.author_id = a.id
          INNER JOIN categories c ON b.category_id = c.id
          WHERE 1=1";

$params = [];

if (!empty($category_id)) {
    $query .= " AND b.category_id = ?";
    $params[] = $category_id;
}

if (!empty($author_id)) {
    $query .= " AND b.author_id = ?";
    $params[] = $author_id;
}

$query .= " ORDER BY b.id DESC";

// ===== FETCH BOOKS =====
try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $books = [];
    $error = "Lỗi khi tải danh sách sách: " . $e->getMessage();
}

// ===== FETCH CATEGORIES =====
try {
    $categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}

// ===== FETCH AUTHORS =====
try {
    $authors = $pdo->query("SELECT * FROM authors ORDER BY name")->fetchAll();
} catch (PDOException $e) {
    $authors = [];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cửa Hàng Sách</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>

<?php include 'includes/header.php'; ?>

<main class="container">

    <?php if (isset($error)): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <section class="hero">
        <h2>Chào Mừng Đến Nhan's BookStore!</h2>
        <p>Khám phá cuốn sách yêu thích tiếp theo của bạn</p>
    </section>

    <!-- ========================
           FILTER SECTION
    ========================= -->
    <section class="filters">
        <h3>Lọc Sách</h3>
        <form method="GET" action="index.php" class="filter-form">

            <div class="filter-group">
                <label for="category">Danh Mục:</label>
                <select name="category" id="category">
                    <option value="">Tất Cả Danh Mục</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"
                            <?php echo ($category_id == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="author">Tác Giả:</label>
                <select name="author" id="author">
                    <option value="">Tất Cả Tác Giả</option>
                    <?php foreach ($authors as $auth): ?>
                        <option value="<?php echo $auth['id']; ?>"
                            <?php echo ($author_id == $auth['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($auth['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Lọc</button>
            <a href="index.php" class="btn btn-secondary">Xóa</a>
        </form>
    </section>

    <!-- ========================
           BOOK LIST
    ========================= -->
    <section class="books-section">
        <h2>Sách Của Chúng Tôi</h2>

        <?php if (empty($books)): ?>

            <div class="no-books">
                <p>Hiện chưa có sách nào. <a href="add_book.php">Thêm sách</a></p>
            </div>

        <?php else: ?>

            <div class="books-grid">

                <?php foreach ($books as $book): ?>

                    <?php
                    // ==========================================
                    //      FIX IMAGE PATH (NO DOUBLE PATH)
                    // ==========================================
                    $imgSrc = $book['image_url'] ?? '';

                    if (!empty($imgSrc)) {

                        // Nếu DB đã chứa 'assets/image/...'
                        if (strpos($imgSrc, 'assets/image/') === 0) {
                            // giữ nguyên
                        }
                        // Nếu URL tuyệt đối (http/https)
                        elseif (preg_match('~^https?://~', $imgSrc)) {
                            // giữ nguyên
                        }
                        // Nếu chỉ có tên file → thêm thư mục đúng
                        else {
                            $imgSrc = 'assets/image/' . ltrim($imgSrc, '/');
                        }
                    }
                    ?>

                    <div class="book-card">

                        <div class="book-cover">
                            <?php if (!empty($imgSrc)): ?>
                                <img src="<?php echo htmlspecialchars($imgSrc); ?>"
                                     alt="<?php echo htmlspecialchars($book['title']); ?>"
                                     class="book-thumb">
                            <?php else: ?>
                                <span class="book-icon">NO IMAGE</span>
                            <?php endif; ?>
                        </div>

                        <div class="book-info">
                            <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                            <p class="book-author">by <?php echo htmlspecialchars($book['author_name']); ?></p>
                            <p class="book-category">Category: <?php echo htmlspecialchars($book['category_name']); ?></p>
                            <p class="book-price">$<?php echo number_format($book['price'], 2); ?></p>
                            <p class="book-description"><?php echo htmlspecialchars($book['description']); ?></p>

                            <div class="book-actions">
                                <span class="book-stock">Tồn kho: <?php echo $book['stock']; ?></span>

                                <?php if ($book['stock'] > 0): ?>
                                    <button
                                        class="btn btn-primary btn-sm"
                                        onclick="addToCart(
                                            <?php echo $book['id']; ?>,
                                            '<?php echo htmlspecialchars($book['title']); ?>',
                                            <?php echo $book['price']; ?>,
                                            <?php echo $book['stock']; ?>
                                        )">
                                        Thêm Vào Giỏ
                                    </button>
                                <?php else: ?>
                                    <span class="out-of-stock">Hết Hàng</span>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>
    </section>

</main>

<?php include 'includes/footer.php'; ?>

<script src="assets/js/cart.js"></script>
</body>
</html>
