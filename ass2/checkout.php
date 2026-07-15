<?php
require_once 'dbconfig.php.inc';

if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}

$basket = $_SESSION['basket'] ?? [];
if (empty($basket)) {
    header('Location: basket.php');
    exit;
}

$errors = [];
$current_year = date('Y');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cardholder = trim($_POST['cardholder_name'] ?? '');
    $card_num = trim($_POST['card_number'] ?? '');
    $exp_month = trim($_POST['expiry_month'] ?? '');
    $exp_year = trim($_POST['expiry_year'] ?? '');
    $cvv = trim($_POST['cvv'] ?? '');

    if (!$cardholder) $errors[] = 'Cardholder Name is required.';
    if (!$card_num || !ctype_digit($card_num) || strlen($card_num) !== 16) {
        $errors[] = 'Card Number must be 16 digits.';
    }
    if (!$exp_month) $errors[] = 'Expiry Month is required.';
    if (!$exp_year) $errors[] = 'Expiry Year is required.';
    if (!$cvv || !ctype_digit($cvv) || strlen($cvv) !== 3) {
        $errors[] = 'CVV must be 3 digits.';
    }

    if (!$errors) {
        // Calculate order total
        $total = 0;
        foreach ($basket as $item) {
            $total += $item['unit_price'] * $item['quantity'];
        }

        // Generate 10-digit order ID
        $order_id_raw = substr(time() . rand(10000, 99999), -10);

        try {
            $pdo->beginTransaction();

            // Lock and validate stock for each item
            $stockStmt = $pdo->prepare('
                SELECT quantity
                FROM products
                WHERE product_id = :product_id
                FOR UPDATE
            ');
            foreach ($basket as $item) {
                $stockStmt->execute([':product_id' => $item['product_id']]);
                $row = $stockStmt->fetch(PDO::FETCH_ASSOC);
                if (!$row) {
                    throw new Exception('One or more products no longer exist.');
                }
                $available = (int)$row['quantity'];
                $requested = (int)$item['quantity'];
                if ($requested <= 0) {
                    throw new Exception('Invalid quantity in basket.');
                }
                if ($requested > $available) {
                    throw new Exception('Insufficient stock for ' . $item['product_name'] . '.');
                }
            }

            // Insert order
            $stmt = $pdo->prepare('
                INSERT INTO orders (user_id, total_amount)
                VALUES (:user_id, :total)
            ');
            $stmt->execute([
                ':user_id' => $_SESSION['user_id'],
                ':total' => $total
            ]);
            $db_order_id = $pdo->lastInsertId();

            $updateStockStmt = $pdo->prepare('
                UPDATE products
                SET quantity = quantity - :qty
                WHERE product_id = :product_id
            ');
            $insertItemStmt = $pdo->prepare('
                INSERT INTO order_items (order_id, product_id, quantity, unit_price)
                VALUES (:order_id, :product_id, :qty, :price)
            ');

            // Update stock and insert order items
            foreach ($basket as $item) {
                $updateStockStmt->execute([
                    ':product_id' => $item['product_id'],
                    ':qty' => $item['quantity']
                ]);
                $insertItemStmt->execute([
                    ':order_id' => $db_order_id,
                    ':product_id' => $item['product_id'],
                    ':qty' => $item['quantity'],
                    ':price' => $item['unit_price']
                ]);
            }

            $pdo->commit();

            // Clear basket
            unset($_SESSION['basket']);
            ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Palestinian Souvenir Store</title>
</head>
<body>
    <?php require_once 'header.inc.php'; ?>
    <main>
        <article>
            <h2>Order Confirmation</h2>
            <p>Thank you for your order!</p>
            <p>Your Order ID: <strong><?= str_pad($db_order_id, 10, '0', STR_PAD_LEFT) ?></strong></p>
            <p>Order Total: <strong><?= number_format($total, 2) ?></strong></p>
            <p><a href="products.php">Continue Shopping</a></p>
        </article>
    </main>
    <?php require_once 'footer.inc.php'; ?>
</body>
</html>
            <?php
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = $e->getMessage() ?: 'An error occurred while processing your order.';
        }
    }
}

// Calculate total
$total = 0;
foreach ($basket as $item) {
    $total += $item['unit_price'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Palestinian Souvenir Store</title>
</head>
<body>
    <?php require_once 'header.inc.php'; ?>
    <main>
        <article>
            <h2>Checkout</h2>

            <section>
                <h3>Order Summary</h3>
                <table border="1" cellpadding="5">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Unit Price</th>
                            <th>Quantity</th>
                            <th>Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($basket as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['product_name']) ?></td>
                                <td><?= number_format($item['unit_price'], 2) ?></td>
                                <td><?= $item['quantity'] ?></td>
                                <td><?= number_format($item['unit_price'] * $item['quantity'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p><strong>Total: <?= number_format($total, 2) ?></strong></p>
            </section>

            <?php if ($errors): ?>
                <section>
                    <h3>Errors:</h3>
                    <ul>
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>

            <section>
                <h3>Payment Information</h3>
                <form method="post" action="checkout.php">
                    <p>
                        <label>Cardholder Name (required):</label><br>
                        <input type="text" name="cardholder_name" required>
                    </p>
                    <p>
                        <label>Card Number - 16 digits (required):</label><br>
                        <input type="text" name="card_number" maxlength="16" required>
                    </p>
                    <p>
                        <label>Expiry Month (required):</label><br>
                        <select name="expiry_month" required>
                            <option value="">Select Month</option>
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>"><?= str_pad($m, 2, '0', STR_PAD_LEFT) ?></option>
                            <?php endfor; ?>
                        </select>
                    </p>
                    <p>
                        <label>Expiry Year (required):</label><br>
                        <select name="expiry_year" required>
                            <option value="">Select Year</option>
                            <?php for ($y = $current_year; $y <= $current_year + 10; $y++): ?>
                                <option value="<?= $y ?>"><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </p>
                    <p>
                        <label>CVV - 3 digits (required):</label><br>
                        <input type="text" name="cvv" maxlength="3" required>
                    </p>
                    <p>
                        <button type="submit">Place Order</button>
                    </p>
                </form>
            </section>
        </article>
    </main>
    <?php require_once 'footer.inc.php'; ?>
</body>
</html>
