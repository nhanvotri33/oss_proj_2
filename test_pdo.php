<?php
// try {
//     $pdo = new PDO(
//         "mysql:host=sql100.infinityfree.com;dbname=if0_40694250_book_store_1;charset=utf8mb4",
//         "if0_40694250",
//         "tfjeocw0xnz"
//     );
//     echo "✅ Database connected successfully";
// } catch (PDOException $e) {
//     echo $e->getMessage();
// }


$conn = mysqli_connect(
    "sql100.infinityfree.com",
    "if0_40694250",
    "tfjeocw0xnz",
    "if0_40694250_book_store_1"
);

if ($conn) {
    echo "✅ Database connected successfully";
} else {
    echo "❌ Failed";
}


?>