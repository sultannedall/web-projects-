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
    <link rel="stylesheet" href="style.css">
    <title>Shopping Basket - Palestinian Souvenir Store</title>
</head>
<body>
    <?php require_once 'header.inc.php'; ?>
    <div class="page-layout page-layout--full">
    <main class="page-main">
        <article class="content-card">
            <h2>Shopping Basket</h2>
            <?php if (empty($basket)): ?>
                <p>Your basket is empty.</p>
                <p><a class="button button-green" href="products.php">Continue Shopping</a></p>
            <?php else: ?>
                <table class="basket-table">
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
                                <td><img class="table-image" src="images/<?= htmlspecialchars($item['photo']) ?>" alt="Product"></td>
                                <td><?= htmlspecialchars($item['product_name']) ?></td>
                                <td class="numeric"><?= number_format($item['unit_price'], 2) ?></td>
                                <td class="numeric"><?= $item['quantity'] ?></td>
                                <td class="numeric"><?= number_format($line_total, 2) ?></td>
                                <td><a class="button button-neutral" href="basket.php?remove=<?= $item['product_id'] ?>">Remove</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="basket-total">Basket Total: <?= number_format($total, 2) ?></p>
                <nav class="form-actions" aria-label="Basket actions">
                    <a class="button button-neutral" href="products.php">Continue Shopping</a>
                    <a class="button button-green" href="checkout.php">Proceed to Checkout</a>
                </nav>
            <?php endif; ?>
        </article>
    </main>
    </div>
    <?php require_once 'footer.inc.php'; ?>
</body>
</html>
