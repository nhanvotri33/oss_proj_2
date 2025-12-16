<?php
session_start();
require_once '../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = "Email and Password are required.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM customers WHERE email = ? AND role = 'admin'");
            $stmt->execute([$email]);
            $customer = $stmt->fetch();
            
            if ($customer && password_verify($password, $customer['password'])) {
                $_SESSION['customer_id'] = $customer['id'];
                $_SESSION['customer_name'] = $customer['name'];
                $_SESSION['customer_email'] = $customer['email'];
                $_SESSION['role'] = 'admin';
                
                header('Location: index.php');
                exit;
            } else {
                $error = "Invalid admin credentials.";
            }
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// If already logged in as admin, redirect
if (isset($_SESSION['customer_id']) && ($_SESSION['role'] ?? 'user') === 'admin') {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập Quản Trị - Cửa Hàng Sách</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-login-page">
    <div class="login-container">
        <div class="login-box">
            <h1>Admin Login</h1>
            <p class="login-subtitle">Book Store Management System</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php" class="login-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required 
                           placeholder="admin@bookstore.com"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Enter password">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-block">Login</button>
                    <a href="../index.php" class="btn btn-secondary btn-block">Back to Store</a>
                </div>
            </form>
            
            <div class="login-info">
                <p><small>Use your admin account to login</small></p>
                <p><small><a href="../login.php" style="color: #667eea;">Customer Login</a></small></p>
            </div>
        </div>
    </div>
</body>
</html>

