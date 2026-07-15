<?php
require_once 'dbconfig.php.inc';

if (session_status() === PHP_SESSION_NONE) session_start();

// Check login
if (empty($_SESSION['logged_in'])) {
    $_SESSION['return_url'] = 'add_to_basket.php' . (isset($_GET['id']) ? '?id=' . $_GET['id'] : '');
    header('Location: login.php');
    exit;
}

$errors = [];
$product = null;

if (empty($_GET['id'])) {
    $errors[] = 'Product ID is required.';
} else {
    $product_id = (int)$_GET['id'];
    $stmt = $pdo->prepare('SELECT product_id, product_name, price, quantity, default_photo, photo1, photo2, photo3 FROM products WHERE product_id = :id');
    $stmt->execute([':id' => $product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        $errors[] = 'Product not found.';
    }
}

// Handle POST (add to basket)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $product && !$errors) {
    $requested_qty = (int)($_POST['quantity'] ?? 0);
    
    if ($requested_qty <= 0) {
        $errors[] = 'Quantity must be a positive integer.';
    } elseif ($requested_qty > $product['quantity']) {
        $errors[] = 'Requested quantity exceeds available stock.';
    }

    if (!$errors) {
        if (!isset($_SESSION['basket'])) {
            $_SESSION['basket'] = [];
        }

        $pid = $product['product_id'];
        if (isset($_SESSION['basket'][$pid])) {
            $_SESSION['basket'][$pid]['quantity'] += $requested_qty;
        } else {
            $_SESSION['basket'][$pid] = [
                'product_id' => $pid,
                'product_name' => $product['product_name'],
                'unit_price' => $product['price'],
                'quantity' => $requested_qty,
                'photo' => $product['default_photo'] ?? $product['photo1']
            ];
        }

        header('Location: basket.php');
        exit;
    }
}

$qty_to_add = 1;
$photo = $product ? ($product['default_photo'] ?? $product['photo1']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add to Basket - Palestinian Souvenir Store</title>
</head>
<body>
    <?php require_once 'header.inc.php'; ?>
    <main>
        <article>
            <h2>Add to Basket</h2>
            <?php if ($errors): ?>
                <section>
                    <h3>Errors:</h3>
                    <ul>
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <p><a href="products.php">Back to Products</a></p>
                </section>
            <?php elseif ($product): ?>
                <form method="post" action="add_to_basket.php?id=<?= $product['product_id'] ?>">
                    <p>
                        <label>Product ID:</label><br>
                        <input type="text" value="<?= $product['product_id'] ?>" disabled>
                    </p>
                    <p>
                        <label>Product Name:</label><br>
                        <input type="text" value="<?= htmlspecialchars($product['product_name']) ?>" disabled>
                    </p>
                    <p>
                        <label>Unit Price:</label><br>
                        <input type="text" value="<?= number_format($product['price'], 2) ?>" disabled>
                    </p>
                    <p>
                        <label>Quantity:</label><br>
                        <input type="number" name="quantity" value="<?= $qty_to_add ?>" min="1" required>
                    </p>
                    <p>
                        <button type="submit">Add to Basket</button>
                    </p>
                </form>
            <?php endif; ?>
        </article>
    </main>
    <?php require_once 'footer.inc.php'; ?>
</body>
</html>
