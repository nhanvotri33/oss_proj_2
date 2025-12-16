# Hướng dẫn thiết lập phân quyền Admin

## Bước 1: Thêm cột role vào database

Chạy file SQL sau để thêm cột role:
```sql
book-store/database/add_role_column.sql
```

Hoặc chạy trực tiếp trong phpMyAdmin:
```sql
USE book_store_1;

ALTER TABLE customers 
ADD COLUMN role ENUM('user', 'admin') DEFAULT 'user' AFTER password;

UPDATE customers SET role = 'user' WHERE role IS NULL;
```

## Bước 2: Tạo tài khoản Admin

Có 2 cách:

### Cách 1: Tạo qua SQL
```sql
INSERT INTO customers (name, email, password, role) 
VALUES ('Administrator', 'admin@bookstore.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
```
Password mặc định: `admin123`

### Cách 2: Đăng ký tài khoản thường, sau đó cập nhật role
```sql
UPDATE customers SET role = 'admin' WHERE email = 'your-email@example.com';
```

## Bước 3: Đăng nhập

- **Admin**: Đăng nhập tại `login.php` hoặc `admin/login.php` → Tự động chuyển đến Admin Dashboard
- **User**: Đăng nhập tại `login.php` → Vào trang chủ bình thường

## Phân quyền

### Admin có thể:
- Truy cập Admin Dashboard (`admin/index.php`)
- Thêm/Sửa/Xóa sách, tác giả, danh mục
- Xem thống kê, đơn hàng, khách hàng
- Quản lý toàn bộ hệ thống

### User có thể:
- Xem sách, thêm vào giỏ hàng
- Đặt hàng
- Xem đơn hàng của mình
- Quản lý thông tin cá nhân

## Menu tự động thay đổi

Header sẽ tự động hiển thị menu phù hợp:
- **Admin**: Admin Dashboard, Add Book, Authors, Categories
- **User**: Cart, My Orders
- **Guest**: Cart, Register, Login

