// Cart management using localStorage
const CART_KEY = 'book_store_cart';

// Initialize cart count on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
});

// Get cart from localStorage
function getCart() {
    const cart = localStorage.getItem(CART_KEY);
    return cart ? JSON.parse(cart) : [];
}

// Save cart to localStorage
function saveCart(cart) {
    localStorage.setItem(CART_KEY, JSON.stringify(cart));
    updateCartCount();
}

// Update cart count in header
function updateCartCount() {
    const cart = getCart();
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    const cartCountElement = document.getElementById('cart-count');
    if (cartCountElement) {
        cartCountElement.textContent = totalItems;
    }
}

// Add item to cart
function addToCart(bookId, title, price, stock) {
    const cart = getCart();
    const existingItem = cart.find(item => item.id === bookId);
    
    if (existingItem) {
        if (existingItem.quantity >= stock) {
            alert('Cannot add more. Stock limit reached!');
            return;
        }
        existingItem.quantity += 1;
    } else {
        cart.push({
            id: bookId,
            title: title,
            price: price,
            quantity: 1,
            stock: stock
        });
    }
    
    saveCart(cart);
    alert('Book added to cart!');
}

// Remove item from cart
function removeFromCart(bookId) {
    const cart = getCart();
    const newCart = cart.filter(item => item.id !== bookId);
    saveCart(newCart);
    location.reload(); // Reload to update display
}

// Update quantity in cart
function updateQuantity(bookId, newQuantity) {
    const cart = getCart();
    const item = cart.find(item => item.id === bookId);
    
    if (item) {
        if (newQuantity <= 0) {
            removeFromCart(bookId);
            return;
        }
        if (newQuantity > item.stock) {
            alert('Cannot add more. Stock limit reached!');
            return;
        }
        item.quantity = newQuantity;
        saveCart(cart);
        location.reload(); // Reload to update display
    }
}

// Clear cart
function clearCart() {
    if (confirm('Are you sure you want to clear the cart?')) {
        localStorage.removeItem(CART_KEY);
        location.reload();
    }
}

// Get cart total
function getCartTotal() {
    const cart = getCart();
    return cart.reduce((total, item) => total + (item.price * item.quantity), 0);
}

// Export functions for use in other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        getCart,
        saveCart,
        addToCart,
        removeFromCart,
        updateQuantity,
        clearCart,
        getCartTotal
    };
}

