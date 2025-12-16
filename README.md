# Book Store - PHP Project

A complete book store application built with PHP, featuring author management, categories, customer accounts, shopping cart, and order processing.

## Features

- 📚 **Book Management**
  - Display books with filtering by author and category
  - Add new books with author and category selection
  - Stock management

- 👤 **Author Management**
  - View all authors
  - Add new authors with biography
  - Filter books by author

- 📂 **Category Management**
  - View all book categories
  - Add new categories
  - Filter books by category

- 🛒 **Shopping Cart**
  - Add books to cart using localStorage
  - Update quantities
  - Remove items
  - View cart total

- 👥 **Customer Management**
  - User registration
  - Login/Logout
  - Customer profiles

- 📦 **Order Processing**
  - Place orders
  - View order history
  - Order status tracking
  - Automatic stock updates

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher (or MariaDB)
- Apache/Nginx web server
- PHP PDO extension enabled
- PHP Session support

## Installation

1. **Clone or download this project** to your web server directory (e.g., `htdocs`, `www`, or `public_html`)

2. **Create the database:**
   
   **Cách 1: Sử dụng PHP Setup Script (Khuyến nghị - Không cần SQL)**
   - Mở trình duyệt và truy cập: `http://localhost/book-store/database/setup.php`
   - Script sẽ tự động tạo database, các bảng và dữ liệu mẫu
   - Sau khi chạy xong, bạn sẽ có tài khoản admin mặc định:
     - Email: `admin@bookstore.com`
     - Password: `admin123`
   
   **Cách 2: Sử dụng file SQL**
   ```bash
   mysql -u root -p < database/schema.sql
   ```
   Hoặc import `database/schema.sql` thủ công bằng phpMyAdmin hoặc MySQL client.

3. **Configure database connection:**
   Edit `config/database.php` and update the database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'book_store');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

4. **Set proper permissions:**
   Make sure your web server has read permissions for all files.

5. **Access the application:**
   Open your browser and navigate to:
   ```
   http://localhost/book-store/
   ```

## Project Structure

```
book-store/
├── assets/
│   ├── css/
│   │   └── style.css          # Main stylesheet
│   └── js/
│       └── cart.js            # Shopping cart functionality (localStorage)
├── config/
│   └── database.php           # Database configuration
├── database/
│   ├── schema.sql             # Database schema with sample data (SQL format)
│   └── setup.php              # PHP setup script (tự động tạo database - không cần SQL)
├── index.php                  # Home page (book listing with filters)
├── add_book.php              # Add new book page
├── authors.php                # List all authors
├── add_author.php            # Add new author
├── categories.php            # List all categories
├── add_category.php          # Add new category
├── cart.php                  # Shopping cart page
├── checkout.php              # Checkout and order processing
├── register.php              # Customer registration
├── login.php                 # Customer login
├── logout.php                # Logout
├── orders.php                # View customer orders
└── README.md                 # This file
```

## Database Schema

The database includes the following tables:
- **authors** - Author information
- **categories** - Book categories
- **books** - Book details (linked to authors and categories)
- **customers** - Customer accounts
- **orders** - Order information
- **order_items** - Individual items in each order

## Usage

### For Customers:
1. **Browse Books:** View all books on the home page
2. **Filter:** Use category and author filters to find specific books
3. **Add to Cart:** Click "Add to Cart" on any book
4. **View Cart:** Click "Cart" in navigation to see your items
5. **Register/Login:** Create an account or login to place orders
6. **Checkout:** Complete your order with shipping address
7. **View Orders:** Check your order history in "My Orders"

### For Administrators:
1. **Add Authors:** Go to Authors → Add Author
2. **Add Categories:** Go to Categories → Add Category
3. **Add Books:** Go to Add Book and fill in the form
4. **Manage:** Books are automatically linked to authors and categories

## Shopping Cart (localStorage)

The shopping cart uses browser localStorage to store cart items. This means:
- Cart persists across page refreshes
- Cart is browser-specific (not shared across devices)
- No server-side session required for cart
- Cart is cleared after successful order placement

## Security Notes

This is a basic implementation. For production use, consider:
- Input validation and sanitization (basic implementation included)
- Prepared statements (already implemented)
- Password hashing (already implemented using PHP password_hash)
- CSRF protection
- XSS prevention (basic escaping implemented)
- SQL injection prevention (PDO prepared statements)
- Session security enhancements

## Features in Detail

### Author Management
- Create authors with name and biography
- View all authors with book count
- Filter books by specific author

### Category Management
- Create categories with name and description
- View all categories with book count
- Filter books by category

### Shopping Cart
- Add/remove items
- Update quantities
- Real-time total calculation
- Stock validation
- Persistent storage (localStorage)

### Order System
- Order creation with customer information
- Order items tracking
- Automatic stock deduction
- Order status management
- Order history for customers

## License

This project is open source and available for educational purposes.
