<?php
require_once 'config/database.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($name) || empty($email) || empty($password)) {
        $error = "Họ tên, Email và Mật khẩu là các trường bắt buộc.";
    } elseif ($password !== $confirm_password) {
        $error = "Mật khẩu không khớp.";
    } elseif (strlen($password) < 6) {
        $error = "Mật khẩu phải có ít nhất 6 ký tự.";
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM customers WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "Email đã được đăng ký. Vui lòng đăng nhập.";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert customer with default 'user' role
                $stmt = $pdo->prepare("
                    INSERT INTO customers (name, email, phone, address, password, role) 
                    VALUES (?, ?, ?, ?, ?, 'user')
                ");
                $stmt->execute([$name, $email, $phone, $address, $hashed_password]);
                $message = "Đăng ký thành công! Bạn có thể <a href='login.php'>đăng nhập</a> ngay.";
            }
        } catch (PDOException $e) {
            $error = "Lỗi: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký - Cửa Hàng Sách</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <section class="form-section">
            <h2>Đăng Ký Tài Khoản</h2>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="register.php" class="book-form">
                <div class="form-group">
                    <label for="name">Họ và Tên *</label>
                    <input type="text" id="name" name="name" required 
                           value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="phone">Số Điện Thoại</label>
                    <input type="text" id="phone" name="phone" 
                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="address">Địa Chỉ</label>
                    <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="password">Mật Khẩu *</label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Xác Nhận Mật Khẩu *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Đăng Ký</button>
                    <a href="login.php" class="btn btn-secondary">Đã có tài khoản? Đăng Nhập</a>
                </div>
            </form>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>

