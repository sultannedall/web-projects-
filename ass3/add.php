<?php
require_once 'dbconfig.php.inc';

if (session_status() === PHP_SESSION_NONE) session_start();

// Check employee permission
if (empty($_SESSION['logged_in']) || $_SESSION['role'] !== 'Employee') {
    header('Location: login.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['product_name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $quantity = trim($_POST['quantity'] ?? '');
    $rating = trim($_POST['rating'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $default_photo = trim($_POST['default_photo'] ?? '');

    // Validation
    if (!$name) $errors[] = 'Product Name is required.';
    if (!$category) $errors[] = 'Category is required.';
    if (!$price || (float)$price <= 0) $errors[] = 'Price must be a positive number.';
    if ($quantity === '' || (int)$quantity < 0) $errors[] = 'Quantity must be a non-negative integer.';
    if (!$rating || (int)$rating < 1 || (int)$rating > 5) $errors[] = 'Rating must be between 1 and 5.';
    if (!$description) $errors[] = 'Description is required.';

    // Check file uploads
    $photos = [];
    for ($i = 1; $i <= 3; $i++) {
        if (empty($_FILES["photo$i"]['tmp_name'])) {
            $errors[] = "Photo $i is required.";
        } elseif ($_FILES["photo$i"]['type'] !== 'image/jpeg') {
            $errors[] = "Photo $i must be a JPEG image.";
        }
    }

    if (!$errors) {
        // Insert product
        $stmt = $pdo->prepare('
            INSERT INTO products (product_name, category, price, quantity, rating, description, default_photo)
            VALUES (:name, :cat, :price, :qty, :rating, :desc, :default)
        ');
        $stmt->execute([
            ':name' => $name,
            ':cat' => $category,
            ':price' => (float)$price,
            ':qty' => (int)$quantity,
            ':rating' => (int)$rating,
            ':desc' => $description,
            ':default' => $default_photo
        ]);
        $product_id = $pdo->lastInsertId();

        // Create images folder if needed
        if (!is_dir('images')) mkdir('images', 0755, true);

        // Handle photo uploads
        $photo_names = [];
        for ($i = 1; $i <= 3; $i++) {
            $filename = $product_id . '_' . $i . '.jpeg';
            $filepath = 'images/' . $filename;
            if (move_uploaded_file($_FILES["photo$i"]['tmp_name'], $filepath)) {
                $photo_names[$i] = $filename;
            }
        }

        // Update product with photo names
        $stmt = $pdo->prepare('
            UPDATE products 
            SET photo1 = :p1, photo2 = :p2, photo3 = :p3, default_photo = :default
            WHERE product_id = :id
        ');
        $stmt->execute([
            ':p1' => $photo_names[1],
            ':p2' => $photo_names[2],
            ':p3' => $photo_names[3],
            ':default' => $photo_names[(int)$default_photo] ?? $photo_names[1],
            ':id' => $product_id
        ]);

        header('Location: products.php');
        exit;
    }
}

$categories = ['Religious', 'Clothing', 'Home Decor', 'Decorative', 'Food', 'Personal Care', 'Kitchenware', 'Jewelry'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Add Product - Palestinian Souvenir Store</title>
</head>
<body>
    <?php require_once 'header.inc.php'; ?>
    <div class="page-layout page-layout--full">
    <main class="page-main">
        <article class="form-card">
            <h2>Add New Product</h2>
            <?php if ($errors): ?>
                <section class="error-list">
                    <h3>Errors:</h3>
                    <ul>
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>
            <form class="styled-form <?= $errors ? 'has-errors' : '' ?>" method="post" action="add.php" enctype="multipart/form-data">
                <fieldset>
                    <legend>Product Information</legend>
                    <p class="form-row">
                        <label for="product_name">Product Name (required):</label>
                        <input id="product_name" type="text" name="product_name" required>
                    </p>
                    <p class="form-row">
                        <label for="category">Category (required):</label>
                        <select id="category" name="category" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </p>
                    <p class="form-row">
                        <label for="price">Price (required):</label>
                        <input id="price" type="number" step="0.01" name="price" required>
                    </p>
                    <p class="form-row">
                        <label for="quantity">Quantity (required):</label>
                        <input id="quantity" type="number" name="quantity" value="0" required>
                    </p>
                    <p class="form-row">
                        <label for="rating">Rating 1-5 (required):</label>
                        <input id="rating" type="number" name="rating" min="1" max="5" required>
                    </p>
                    <p class="form-row">
                        <label for="description">Description (required):</label>
                        <textarea id="description" name="description" rows="6" required></textarea>
                    </p>
                </fieldset>
                <fieldset>
                    <legend>Photos (all required, JPEG only)</legend>
                    <p class="form-row">
                        <label for="photo1">Photo 1:</label>
                        <input id="photo1" type="file" name="photo1" accept="image/jpeg" required>
                    </p>
                    <p class="form-row">
                        <label for="photo2">Photo 2:</label>
                        <input id="photo2" type="file" name="photo2" accept="image/jpeg" required>
                    </p>
                    <p class="form-row">
                        <label for="photo3">Photo 3:</label>
                        <input id="photo3" type="file" name="photo3" accept="image/jpeg" required>
                    </p>
                    <p class="form-row radio-row">
                        <label>Default Photo:</label>
                        <span class="radio-group">
                            <label><input type="radio" name="default_photo" value="1" checked> Photo 1</label>
                            <label><input type="radio" name="default_photo" value="2"> Photo 2</label>
                            <label><input type="radio" name="default_photo" value="3"> Photo 3</label>
                        </span>
                    </p>
                </fieldset>
                <p class="form-actions">
                    <button class="button button-green" type="submit">Insert Product</button>
                </p>
            </form>
        </article>
    </main>
    </div>
    <?php require_once 'footer.inc.php'; ?>
</body>
</html>
