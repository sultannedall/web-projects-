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
    $product = $stmt->fetchObject('Product');
    if (!$product) {
        $error = 'Product not found.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details - Palestinian Souvenir Store</title>
</head>
<body>
    <?php require_once 'header.inc.php'; ?>
    <main>
        <?php if ($error): ?>
            <article>
                <p><?= htmlspecialchars($error) ?></p>
                <p><a href="products.php">Back to Products</a></p>
            </article>
        <?php elseif ($product): ?>
            <?= $product->displayProductPage() ?>
        <?php endif; ?>
    </main>
    <?php require_once 'footer.inc.php'; ?>
</body>
</html>
