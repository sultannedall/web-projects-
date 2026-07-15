<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Handle remove from basket
if (isset($_GET['remove'])) {
    $product_id = (int)$_GET['remove'];
    if (isset($_SESSION['basket'][$product_id])) {
        unset($_SESSION['basket'][$product_id]);
    }
    header('Location: basket.php');
    exit;
}

$basket = $_SESSION['basket'] ?? [];
$total = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Basket - Palestinian Souvenir Store</title>
</head>
<body>
    <?php require_once 'header.inc.php'; ?>
    <main>
        <article>
            <h2>Shopping Basket</h2>
            <?php if (empty($basket)): ?>
                <p>Your basket is empty.</p>
                <p><a href="products.php">Continue Shopping</a></p>
            <?php else: ?>
                <table border="1" cellpadding="5">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Unit Price</th>
                            <th>Quantity</th>
                            <th>Line Total</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($basket as $item): ?>
                            <?php 
                            $line_total = $item['unit_price'] * $item['quantity'];
                            $total += $line_total;
                            ?>
                            <tr>
                                <td><img src="images/<?= htmlspecialchars($item['photo']) ?>" alt="Product" width="100"></td>
                                <td><?= htmlspecialchars($item['product_name']) ?></td>
                                <td><?= number_format($item['unit_price'], 2) ?></td>
                                <td><?= $item['quantity'] ?></td>
                                <td><?= number_format($line_total, 2) ?></td>
                                <td><a href="basket.php?remove=<?= $item['product_id'] ?>">Remove</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p><strong>Basket Total: <?= number_format($total, 2) ?></strong></p>
                <p>
                    <a href="products.php">Continue Shopping</a> | 
                    <a href="checkout.php">Proceed to Checkout</a>
                </p>
            <?php endif; ?>
        </article>
    </main>
    <?php require_once 'footer.inc.php'; ?>
</body>
</html>
