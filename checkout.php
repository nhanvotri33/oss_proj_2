<?php
session_start();
require_once 'config/database.php';

$customer_id = $_SESSION['customer_id'] ?? null;

if (!$customer_id) {
    header('Location: login.php');
    exit;
}

// Get customer info
try {
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->execute([$customer_id]);
    $customer = $stmt->fetch();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_address = $_POST['shipping_address'] ?? '';
    
    if (empty($shipping_address)) {
        $error = "Shipping address is required.";
    } else {
        // Get cart from localStorage (we'll need to pass it via form or use AJAX)
        // For now, we'll use a simple approach with session
        $cart_json = $_POST['cart_data'] ?? '[]';
        $cart = json_decode($cart_json, true);
        
        if (empty($cart)) {
            $error = "Your cart is empty.";
        } else {
            try {
                $pdo->beginTransaction();
                
                // Calculate total
                $total = 0;
                foreach ($cart as $item) {
                    $total += $item['price'] * $item['quantity'];
                }
                
                // Create order
                $stmt = $pdo->prepare("
                    INSERT INTO orders (customer_id, total_amount, status, shipping_address) 
                    VALUES (?, ?, 'pending', ?)
                ");
                $stmt->execute([$customer_id, $total, $shipping_address]);
                $order_id = $pdo->lastInsertId();
                
                // Create order items
                $stmt = $pdo->prepare("
                    INSERT INTO order_items (order_id, book_id, quantity, price) 
                    VALUES (?, ?, ?, ?)
                ");
                
                foreach ($cart as $item) {
                    $stmt->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
                    
                    // Update book stock
                    $updateStmt = $pdo->prepare("UPDATE books SET stock = stock - ? WHERE id = ?");
                    $updateStmt->execute([$item['quantity'], $item['id']]);
                }
                
                $pdo->commit();
                
                // Clear cart
                echo "<script>localStorage.removeItem('book_store_cart');</script>";
                
                $message = "Order placed successfully! Order ID: #" . $order_id;
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error = "Error placing order: " . $e->getMessage();
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
    <title>Thanh Toán - Cửa Hàng Sách</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <section class="form-section">
            <h2>Checkout</h2>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                <a href="orders.php" class="btn btn-primary">View Orders</a>
            <?php elseif ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php else: ?>
                <div class="checkout-info">
                    <h3>Customer Information</h3>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($customer['name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($customer['email']); ?></p>
                </div>

                <div id="checkout-cart-summary"></div>

                <form method="POST" action="checkout.php" id="checkout-form" onsubmit="return submitCheckout(event)">
                    <div class="form-group">
                        <label for="shipping_address">Shipping Address *</label>
                        <textarea id="shipping_address" name="shipping_address" rows="4" required><?php echo htmlspecialchars($customer['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <input type="hidden" name="cart_data" id="cart-data">
                    
                    <div class="form-actions">
                        <a href="cart.php" class="btn btn-secondary">Back to Cart</a>
                        <button type="submit" class="btn btn-primary">Place Order</button>
                    </div>
                </form>
            <?php endif; ?>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/cart.js"></script>
    <script>
        function loadCheckoutSummary() {
            const cart = getCart();
            const summaryDiv = document.getElementById('checkout-cart-summary');
            const cartDataInput = document.getElementById('cart-data');
            
            if (cart.length === 0) {
                summaryDiv.innerHTML = '<div class="alert alert-error">Your cart is empty!</div>';
                window.location.href = 'cart.php';
                return;
            }
            
            let html = '<h3>Order Summary</h3><table class="cart-table"><thead><tr><th>Book</th><th>Price</th><th>Quantity</th><th>Total</th></tr></thead><tbody>';
            let total = 0;
            
            cart.forEach(item => {
                const itemTotal = item.price * item.quantity;
                total += itemTotal;
                html += `
                    <tr>
                        <td>${item.title}</td>
                        <td>$${item.price.toFixed(2)}</td>
                        <td>${item.quantity}</td>
                        <td>$${itemTotal.toFixed(2)}</td>
                    </tr>
                `;
            });
            
            html += '</tbody></table>';
            html += `<div class="cart-total"><strong>Total: $${total.toFixed(2)}</strong></div>`;
            summaryDiv.innerHTML = html;
            
            // Set cart data in hidden input
            cartDataInput.value = JSON.stringify(cart);
        }
        
        function submitCheckout(event) {
            const cart = getCart();
            if (cart.length === 0) {
                alert('Your cart is empty!');
                event.preventDefault();
                return false;
            }
            return true;
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            loadCheckoutSummary();
        });
    </script>
</body>
</html>

