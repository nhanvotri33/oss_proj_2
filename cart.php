<?php
session_start();
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ Hàng - Cửa Hàng Sách</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <section class="cart-section">
            <h2>Giỏ Hàng</h2>
            
            <div id="cart-items">
                <!-- Cart items will be loaded here by JavaScript -->
            </div>

            <div class="cart-summary">
                <div class="cart-total">
                    <strong>Tổng Tiền: <span id="cart-total">0</span> VNĐ</strong>
                </div>
                <div class="cart-actions">
                    <button onclick="clearCart()" class="btn btn-secondary">Xóa Giỏ Hàng</button>
                    <a href="index.php" class="btn btn-secondary">Tiếp Tục Mua Sắm</a>
                    <button onclick="checkout()" class="btn btn-primary">Thanh Toán</button>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/cart.js"></script>
    <script>
        // Load and display cart items
        function loadCart() {
            const cart = getCart();
            const cartItemsDiv = document.getElementById('cart-items');
            const cartTotalSpan = document.getElementById('cart-total');
            
            if (cart.length === 0) {
                cartItemsDiv.innerHTML = '<div class="no-books"><p>Giỏ hàng của bạn đang trống. <a href="index.php">Bắt đầu mua sắm</a></p></div>';
                cartTotalSpan.textContent = '0.00';
                return;
            }
            
            let html = '<table class="cart-table"><thead><tr><th>Sách</th><th>Giá</th><th>Số lượng</th><th>Thành tiền</th><th>Thao tác</th></tr></thead><tbody>';
            let total = 0;
            
            cart.forEach(item => {
                const itemTotal = item.price * item.quantity;
                total += itemTotal;
                
                html += `
                    <tr>
                        <td>${item.title}</td>
                        <td>$${item.price.toFixed(2)}</td>
                        <td>
                            <input type="number" min="1" max="${item.stock}" value="${item.quantity}" 
                                   onchange="updateQuantity(${item.id}, parseInt(this.value))" 
                                   class="qty-input">
                        </td>
                        <td>$${itemTotal.toFixed(2)}</td>
                        <td>
                            <button onclick="removeFromCart(${item.id})" class="btn btn-danger btn-sm">Xóa</button>
                        </td>
                    </tr>
                `;
            });
            
            html += '</tbody></table>';
            cartItemsDiv.innerHTML = html;
            cartTotalSpan.textContent = total.toFixed(2);
        }
        
        // Checkout function
        function checkout() {
            const cart = getCart();
            if (cart.length === 0) {
                alert('Giỏ hàng trống!');
                return;
            }
            
            // Check if user is logged in (will be checked on server side in checkout.php)
            // Redirect to checkout - server will verify session
            
            // Redirect to checkout page
            window.location.href = 'checkout.php';
        }
        
        // Load cart on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadCart();
            updateCartCount();
        });
    </script>
</body>
</html>

