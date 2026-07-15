<?php
require_once 'dbconfig.php.inc';
require_once 'Product.class.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Check employee permission
if (empty($_SESSION['logged_in']) || $_SESSION['role'] !== 'Employee') {
    header('Location: login.php');
    exit;
}

$product = null;
$error = '';

if (empty($_GET['id']) && empty($_POST['product_id'])) {
    $error = 'Product ID is required.';
} else {
    $product_id = (int)($_GET['id'] ?? $_POST['product_id']);
    $stmt = $pdo->prepare('SELECT * FROM products WHERE product_id = :id');
    $stmt->execute([':id' => $product_id]);
    $product = $stmt->fetchObject('Product');
    
    if (!$product) {
        $error = 'Product not found.';
    }
}

// Handle POST (save changes)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $product_id = (int)$_POST['product_id'];
    $price = trim($_POST['price'] ?? '');
    $quantity = trim($_POST['quantity'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $default_photo = trim($_POST['default_photo'] ?? '');

    $errors = [];
    if (!$price || (float)$price <= 0) $errors[] = 'Price must be a positive number.';
    if ($quantity === '' || (int)$quantity < 0) $errors[] = 'Quantity must be a non-negative integer.';
    if (!$description) $errors[] = 'Description is required.';

    if (!$errors) {
        // Handle photo replacements
        if (!is_dir('images')) mkdir('images', 0755, true);

        for ($i = 1; $i <= 3; $i++) {
            if (!empty($_FILES["photo$i"]['tmp_name'])) {
                if ($_FILES["photo$i"]['type'] === 'image/jpeg') {
                    $filename = $product_id . '_' . $i . '.jpeg';
                    $filepath = 'images/' . $filename;
                    move_uploaded_file($_FILES["photo$i"]['tmp_name'], $filepath);
                }
            }
        }

        // Update product
        $stmt = $pdo->prepare('
            UPDATE products 
            SET price = :price, quantity = :qty, description = :desc, default_photo = :default
            WHERE product_id = :id
        ');
        $stmt->execute([
            ':price' => (float)$price,
            ':qty' => (int)$quantity,
            ':desc' => $description,
            ':default' => $default_photo,
            ':id' => $product_id
        ]);

        header('Location: view.php?id=' . $product_id);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Palestinian Souvenir Store</title>
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
            <article>
                <h2>Edit Product</h2>
                <form method="post" action="edit.php" enctype="multipart/form-data">
                    <input type="hidden" name="product_id" value="<?= $product->getProductId() ?>">
                    
                    <p>
                        <label>Product ID:</label><br>
                        <input type="text" value="<?= $product->getProductId() ?>" disabled>
                    </p>
                    <p>
                        <label>Product Name:</label><br>
                        <input type="text" value="<?= htmlspecialchars($product->getProductName()) ?>" disabled>
                    </p>
                    <p>
                        <label>Category:</label><br>
                        <select disabled>
                            <option><?= htmlspecialchars($product->getCategory()) ?></option>
                        </select>
                    </p>
                    <p>
                        <label>Price:</label><br>
                        <input type="number" step="0.01" name="price" value="<?= $product->getPrice() ?>" required>
                    </p>
                    <p>
                        <label>Quantity:</label><br>
                        <input type="number" name="quantity" value="<?= $product->getQuantity() ?>" required>
                    </p>
                    <p>
                        <label>Description:</label><br>
                        <textarea name="description" rows="6" cols="60" required><?= htmlspecialchars($product->getDescription()) ?></textarea>
                    </p>
                    <p>
                        <label>Rating:</label><br>
                        <input type="text" value="<?= $product->getRating() ?>" disabled>
                    </p>

                    <fieldset>
                        <legend>Default Photo</legend>
                        <p>
                            <img src="images/<?= htmlspecialchars($product->getPhoto1()) ?>" alt="Photo1" width="120">
                            <input type="radio" name="default_photo" value="<?= htmlspecialchars($product->getPhoto1()) ?>" <?= $product->getDefaultPhoto() === $product->getPhoto1() ? 'checked' : '' ?>> Photo 1
                        </p>
                        <p>
                            <img src="images/<?= htmlspecialchars($product->getPhoto2()) ?>" alt="Photo2" width="120">
                            <input type="radio" name="default_photo" value="<?= htmlspecialchars($product->getPhoto2()) ?>" <?= $product->getDefaultPhoto() === $product->getPhoto2() ? 'checked' : '' ?>> Photo 2
                        </p>
                        <p>
                            <img src="images/<?= htmlspecialchars($product->getPhoto3()) ?>" alt="Photo3" width="120">
                            <input type="radio" name="default_photo" value="<?= htmlspecialchars($product->getPhoto3()) ?>" <?= $product->getDefaultPhoto() === $product->getPhoto3() ? 'checked' : '' ?>> Photo 3
                        </p>
                    </fieldset>

                    <fieldset>
                        <legend>Replace Photos (optional, JPEG only)</legend>
                        <p>
                            <label>Photo 1:</label><br>
                            <input type="file" name="photo1" accept="image/jpeg">
                        </p>
                        <p>
                            <label>Photo 2:</label><br>
                            <input type="file" name="photo2" accept="image/jpeg">
                        </p>
                        <p>
                            <label>Photo 3:</label><br>
                            <input type="file" name="photo3" accept="image/jpeg">
                        </p>
                    </fieldset>

                    <p>
                        <button type="submit" name="save">Save Changes</button>
                    </p>
                </form>
            </article>
        <?php endif; ?>
    </main>
    <?php require_once 'footer.inc.php'; ?>
</body>
</html>
