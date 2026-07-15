<?php
require_once 'dbconfig.php.inc';
require_once 'Product.class.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$product = null;
$error = '';

if (empty($_GET['id'])) {
    $error = 'Product ID is required.';
} else {
    $product_id = (int)$_GET['id'];
    $stmt = $pdo->prepare('SELECT * FROM products WHERE product_id = :id');
    $stmt->execute([':id' => $product_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        $error = 'Product not found.';
    } else {
        $product = new Product(
            $row['product_id'] ?? null,
            $row['product_name'] ?? null,
            $row['category'] ?? null,
            $row['description'] ?? null,
            $row['price'] ?? null,
            $row['quantity'] ?? null,
            $row['rating'] ?? null,
            $row['photo1'] ?? null,
            $row['photo2'] ?? null,
            $row['photo3'] ?? null,
            $row['default_photo'] ?? null
        );
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Product Details - Palestinian Souvenir Store</title>
</head>
<body>
    <?php require_once 'header.inc.php'; ?>
    <div class="page-layout page-layout--full">
        <main class="page-main">
            <?php if ($error): ?>
                <article class="message-card">
                    <p><?= htmlspecialchars($error) ?></p>
                    <p><a class="button button-neutral" href="products.php">Back to Products</a></p>
                </article>
            <?php elseif ($product): ?>
                <?= $product->displayProductPage() ?>
            <?php endif; ?>
        </main>
    </div>
    <?php require_once 'footer.inc.php'; ?>
</body>
</html>
