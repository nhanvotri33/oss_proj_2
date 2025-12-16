<?php
try {
    $pdo = new PDO(
        "mysql:host=sql100.infinityfree.com;dbname=if0_40694250_book_store_1;charset=utf8mb4",
        "if0_40694250",
        "tfjeocw0xnz"
    );
    echo "✅ Database connected successfully";
} catch (PDOException $e) {
    echo $e->getMessage();
}
?>