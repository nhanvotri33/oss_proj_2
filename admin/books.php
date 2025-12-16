<?php
session_start();
require_once '../config/database.php';

// Admin guard
if (!isset($_SESSION['customer_id']) || ($_SESSION['role'] ?? 'user') !== 'admin') {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';
$upload_dir = __DIR__ . '/../assets/image/';

// Load authors and categories for selectors
try {
    $authors = $pdo->query("SELECT * FROM authors ORDER BY name")->fetchAll();
} catch (PDOException $e) {
    $authors = [];
    $error = "Không thể tải danh sách tác giả: " . $e->getMessage();
}

try {
    $categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
} catch (PDOException $e) {
    $categories = [];
    $error = $error ?: "Không thể tải danh sách danh mục: " . $e->getMessage();
}

// Handle CRUD actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if (in_array($action, ['create', 'update'], true)) {
        $title = trim($_POST['title'] ?? '');
        $author_id = (int)($_POST['author_id'] ?? 0);
        $category_id = (int)($_POST['category_id'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
        $stock = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
        $image_url = trim($_POST['current_image'] ?? '');

        if ($image_url && !preg_match('~^https?://~', $image_url)) {
            $image_url = ltrim(preg_replace('#^\.\./#', '', $image_url), '/');
        }

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
                    $image_url = 'assets/image/' . $filename;
                } else {
                    $error = "Không thể lưu ảnh bìa. Vui lòng thử lại.";
                }
            }
        }

        if ($title === '' || $author_id <= 0 || $category_id <= 0) {
            $error = "Tiêu đề, Tác giả và Danh mục là bắt buộc.";
        } else {
            try {
                if ($action === 'create') {
                    $stmt = $pdo->prepare("
                        INSERT INTO books (title, author_id, category_id, description, price, stock, image_url)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$title, $author_id, $category_id, $description, $price, $stock, $image_url ?: null]);
                    $message = "Đã thêm sách mới.";
                } else {
                    $book_id = (int)($_POST['book_id'] ?? 0);
                    if ($book_id <= 0) {
                        $error = "Không tìm thấy sách để cập nhật.";
                    } else {
                        $stmt = $pdo->prepare("
                            UPDATE books 
                            SET title = ?, author_id = ?, category_id = ?, description = ?, price = ?, stock = ?, image_url = ?
                            WHERE id = ?
                        ");
                        $stmt->execute([$title, $author_id, $category_id, $description, $price, $stock, $image_url ?: null, $book_id]);
                        $message = "Đã cập nhật thông tin sách.";
                    }
                }
            } catch (PDOException $e) {
                $error = "Không thể lưu sách: " . $e->getMessage();
            }
        }
    } elseif ($action === 'delete') {
        $book_id = (int)($_POST['book_id'] ?? 0);
        if ($book_id <= 0) {
            $error = "Không tìm thấy sách để xóa.";
        } else {
            try {
                $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
                $stmt->execute([$book_id]);
                $message = "Đã xóa sách.";
            } catch (PDOException $e) {
                $error = "Không thể xóa sách: " . $e->getMessage();
            }
        }
    }
}

// Fetch books list
try {
    $stmt = $pdo->query("
        SELECT b.*, a.name AS author_name, c.name AS category_name 
        FROM books b
        INNER JOIN authors a ON b.author_id = a.id
        INNER JOIN categories c ON b.category_id = c.id
        ORDER BY b.id DESC
    ");
    $books = $stmt->fetchAll();
} catch (PDOException $e) {
    $books = [];
    $error = $error ?: "Không thể tải danh sách sách: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Sách</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-page">
    <?php include '../includes/header.php'; ?>

    <main class="container">
        <section class="dashboard-section">
            <div class="section-heading">
                <div>
                    <h1>Quản Lý Sách</h1>
                    <p>Thêm, sửa, xóa hoặc xem nhanh thông tin sách.</p>
                </div>
                <div class="section-actions">
                    <button class="btn btn-primary" id="open-create-modal"<?php echo (empty($authors) || empty($categories)) ? ' disabled' : ''; ?>>
                        + Thêm Sách
                    </button>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (empty($authors) || empty($categories)): ?>
                <div class="alert alert-warning">
                    Vui lòng tạo ít nhất một <a href="../add_author.php">Tác giả</a> và một <a href="../add_category.php">Danh mục</a> trước khi thêm hoặc chỉnh sửa sách.
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tiêu Đề</th>
                            <th>Tác Giả</th>
                            <th>Danh Mục</th>
                            <th>Giá</th>
                            <th>Tồn Kho</th>
                            <th>Ngày Tạo</th>
                            <th>Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($books)): ?>
                            <tr>
                                <td colspan="8" class="no-data">Chưa có sách nào.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($books as $book): ?>
                                <tr>
                                    <td>#<?php echo $book['id']; ?></td>
                                    <td><?php echo htmlspecialchars($book['title']); ?></td>
                                    <td><?php echo htmlspecialchars($book['author_name']); ?></td>
                                    <td><?php echo htmlspecialchars($book['category_name']); ?></td>
                                    <td><?php echo number_format($book['price'], 2, ',', '.'); ?> ₫</td>
                                    <td><?php echo (int)$book['stock']; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($book['created_at'])); ?></td>
                                    <td>
                                        <div class="table-actions">
                                            <button 
                                                class="btn btn-sm btn-secondary view-book-btn"
                                                data-id="<?php echo $book['id']; ?>"
                                                data-title="<?php echo htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8'); ?>"
                                                data-author="<?php echo (int)$book['author_id']; ?>"
                                                data-authorname="<?php echo htmlspecialchars($book['author_name'], ENT_QUOTES, 'UTF-8'); ?>"
                                                data-category="<?php echo (int)$book['category_id']; ?>"
                                                data-categoryname="<?php echo htmlspecialchars($book['category_name'], ENT_QUOTES, 'UTF-8'); ?>"
                                                data-price="<?php echo $book['price']; ?>"
                                                data-stock="<?php echo (int)$book['stock']; ?>"
                                                data-description="<?php echo htmlspecialchars($book['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                data-created="<?php echo htmlspecialchars($book['created_at'], ENT_QUOTES, 'UTF-8'); ?>"
                                                data-image="<?php echo htmlspecialchars($book['image_url'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            >
                                                Xem
                                            </button>
                                            <button 
                                                class="btn btn-sm btn-warning edit-book-btn"
                                                data-id="<?php echo $book['id']; ?>"
                                                data-title="<?php echo htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8'); ?>"
                                                data-author="<?php echo (int)$book['author_id']; ?>"
                                                data-category="<?php echo (int)$book['category_id']; ?>"
                                                data-price="<?php echo $book['price']; ?>"
                                                data-stock="<?php echo (int)$book['stock']; ?>"
                                                data-description="<?php echo htmlspecialchars($book['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                data-image="<?php echo htmlspecialchars($book['image_url'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            >
                                                Sửa
                                            </button>
                                            <button 
                                                class="btn btn-sm btn-danger delete-book-btn"
                                                data-id="<?php echo $book['id']; ?>"
                                                data-title="<?php echo htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8'); ?>"
                                            >
                                                Xóa
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <!-- Create / Edit Modal -->
    <div class="modal-overlay" id="book-modal">
        <div class="modal">
            <div class="modal-header">
                <h3 id="book-modal-title">Thêm Sách</h3>
                <button type="button" class="close-modal" data-close="book-modal">&times;</button>
            </div>
            <?php if (!empty($authors) && !empty($categories)): ?>
                <form method="POST" class="modal-form" id="book-form" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="create">
                    <input type="hidden" name="book_id" value="">
                    <input type="hidden" name="current_image" value="">
                    
                    <div class="form-group">
                        <label for="book-title">Tiêu Đề *</label>
                        <input type="text" id="book-title" name="title" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="book-author">Tác Giả *</label>
                            <select id="book-author" name="author_id" required>
                                <option value="">Chọn Tác Giả</option>
                                <?php foreach ($authors as $author): ?>
                                    <option value="<?php echo $author['id']; ?>"><?php echo htmlspecialchars($author['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="book-category">Danh Mục *</label>
                            <select id="book-category" name="category_id" required>
                                <option value="">Chọn Danh Mục</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="book-image-url">Ảnh bìa (URL)</label>
                        <input type="url" id="book-image-url" name="image_url" placeholder="https://...">
                        <small>Hoặc nhập URL ảnh từ internet. Không bắt buộc.</small>
                    </div>

                    <div class="form-group">
                        <label for="book-image-file">Ảnh bìa (tải lên)</label>
                        <input type="file" id="book-image-file" name="image_file" accept="image/*">
                        <small>Hỗ trợ JPG, PNG, WEBP, GIF. Không bắt buộc. Nếu tải lên file, URL sẽ bị bỏ qua.</small>
                    </div>

                    <div class="form-group">
                        <label for="book-description">Mô Tả</label>
                        <textarea id="book-description" name="description" rows="3"></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="book-price">Giá (VNĐ)</label>
                            <input type="number" id="book-price" name="price" step="0.01" min="0" value="0">
                        </div>
                        <div class="form-group">
                            <label for="book-stock">Tồn Kho</label>
                            <input type="number" id="book-stock" name="stock" min="0" value="0">
                        </div>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" data-close="book-modal">Hủy</button>
                        <button type="submit" class="btn btn-primary" id="book-submit-btn">Lưu</button>
                    </div>
                </form>
            <?php else: ?>
                <p class="no-data">Không thể hiển thị biểu mẫu khi thiếu tác giả hoặc danh mục.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal-overlay" id="delete-modal">
        <div class="modal">
            <div class="modal-header">
                <h3>Xóa Sách</h3>
                <button type="button" class="close-modal" data-close="delete-modal">&times;</button>
            </div>
            <form method="POST" class="modal-form" id="delete-form">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="book_id" id="delete-book-id">
                <p id="delete-message">Bạn có chắc muốn xóa sách này?</p>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" data-close="delete-modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xóa</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal-overlay" id="detail-modal">
        <div class="modal modal-detail">
            <div class="modal-header">
                <h3 id="detail-title">Chi Tiết Sách</h3>
                <button type="button" class="close-modal" data-close="detail-modal">&times;</button>
            </div>
            <div class="detail-grid">
                <div>
                    <p><strong>Tác Giả:</strong> <span id="detail-author"></span></p>
                    <p><strong>Danh Mục:</strong> <span id="detail-category"></span></p>
                    <p><strong>Giá:</strong> <span id="detail-price"></span></p>
                </div>
                <div>
                    <p><strong>Tồn Kho:</strong> <span id="detail-stock"></span></p>
                    <p><strong>Ngày Tạo:</strong> <span id="detail-created"></span></p>
                </div>
            </div>
                    <div class="detail-description">
                <h4>Mô Tả</h4>
                <p id="detail-description"></p>
            </div>
                    <div class="detail-image" id="detail-image-wrap" style="display:none;">
                        <h4>Ảnh bìa</h4>
                        <img id="detail-image" src="" alt="Ảnh bìa" style="max-width:100%;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.1);">
                    </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-primary edit-from-detail" data-close="detail-modal">Sửa</button>
                <button type="button" class="btn btn-secondary" data-close="detail-modal">Đóng</button>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const bookModal = document.getElementById('book-modal');
            const deleteModal = document.getElementById('delete-modal');
            const detailModal = document.getElementById('detail-modal');
            const bookForm = document.getElementById('book-form');
            const deleteForm = document.getElementById('delete-form');
            const openCreateBtn = document.getElementById('open-create-modal');

            const openModal = (modal) => {
                if (modal) {
                    modal.classList.add('active');
                }
            };

            const closeModal = (modal) => {
                if (modal) {
                    modal.classList.remove('active');
                }
            };

            document.querySelectorAll('[data-close]').forEach(btn => {
                btn.addEventListener('click', () => {
                    closeModal(document.getElementById(btn.dataset.close));
                });
            });

            window.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    [bookModal, deleteModal, detailModal].forEach(closeModal);
                }
            });

            if (openCreateBtn && bookForm) {
                openCreateBtn.addEventListener('click', () => {
                    setFormMode('create');
                    openModal(bookModal);
                });
            }

            const setFormMode = (mode, data = {}) => {
                if (!bookForm) return;
                bookForm.querySelector('input[name="action"]').value = mode === 'edit' ? 'update' : 'create';
                bookForm.querySelector('input[name="book_id"]').value = mode === 'edit' ? (data.id || '') : '';
                bookForm.querySelector('input[name="current_image"]').value = mode === 'edit' ? (data.image || '') : '';
                document.getElementById('book-modal-title').textContent = mode === 'edit' ? 'Cập Nhật Sách' : 'Thêm Sách';
                document.getElementById('book-submit-btn').textContent = mode === 'edit' ? 'Lưu Thay Đổi' : 'Thêm Sách';

                document.getElementById('book-title').value = data.title || '';
                document.getElementById('book-description').value = data.description || '';
                document.getElementById('book-price').value = data.price ?? 0;
                document.getElementById('book-stock').value = data.stock ?? 0;
                document.getElementById('book-author').value = data.author || '';
                document.getElementById('book-category').value = data.category || '';
                // Set image URL nếu có, hoặc để trống
                document.getElementById('book-image-url').value = data.image || '';
                // Reset file input (không thể set value cho file input)
                document.getElementById('book-image-file').value = '';
            };

            document.querySelectorAll('.edit-book-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const data = btn.dataset;
                    setFormMode('edit', {
                        id: data.id,
                        title: data.title,
                        author: data.author,
                        category: data.category,
                        price: data.price,
                        stock: data.stock,
                        description: data.description,
                        image: data.image || ''
                    });
                    openModal(bookModal);
                });
            });

            document.querySelectorAll('.delete-book-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    if (!deleteForm) return;
                    deleteForm.querySelector('#delete-book-id').value = btn.dataset.id;
                    document.getElementById('delete-message').textContent = `Bạn có chắc muốn xóa "${btn.dataset.title}"?`;
                    openModal(deleteModal);
                });
            });

            document.querySelectorAll('.view-book-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const data = btn.dataset;
                    document.getElementById('detail-title').textContent = data.title || 'Chi Tiết Sách';
                    document.getElementById('detail-author').textContent = data.authorname || '';
                    document.getElementById('detail-category').textContent = data.categoryname || '';
                    document.getElementById('detail-price').textContent = `${parseFloat(data.price || 0).toLocaleString('vi-VN')} ₫`;
                    document.getElementById('detail-stock').textContent = data.stock || 0;
                    document.getElementById('detail-created').textContent = data.created ? new Date(data.created).toLocaleDateString('vi-VN') : '';
                    document.getElementById('detail-description').textContent = data.description || 'Không có mô tả.';

                    const imgWrap = document.getElementById('detail-image-wrap');
                    const imgEl = document.getElementById('detail-image');
                    if (data.image) {
                        const resolved = data.image.startsWith('http') ? data.image : `../${data.image}`;
                        imgEl.src = resolved;
                        imgWrap.style.display = 'block';
                    } else {
                        imgEl.src = '';
                        imgWrap.style.display = 'none';
                    }

                    // Allow jump to edit from detail modal
                    document.querySelector('.edit-from-detail').onclick = () => {
                        closeModal(detailModal);
                        setFormMode('edit', {
                            id: data.id,
                            title: data.title,
                            author: data.author,
                            category: data.category,
                            price: data.price,
                            stock: data.stock,
                                description: data.description,
                                image: data.image
                        });
                        openModal(bookModal);
                    };

                    openModal(detailModal);
                });
            });
        })();
    </script>
</body>
</html>
