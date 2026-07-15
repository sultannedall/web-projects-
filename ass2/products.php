<?php
require_once 'dbconfig.php.inc';
require_once 'Product.class.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Initialize search state
$search_state = [
    'name' => '',
    'max_price' => '',
    'category' => '',
    'sort_col' => 'product_id',
    'sort_dir' => 'ASC',
    'current_page' => 1,
    'per_page' => 5
];

// Decode session state if it exists
if (!empty($_SESSION['product_search_state'])) {
    $saved_state = json_decode($_SESSION['product_search_state'], true);
    if (is_array($saved_state)) {
        $search_state = array_merge($search_state, $saved_state);
    }
}

// Handle reset
if (isset($_GET['reset'])) {
    $search_state = [
        'name' => '',
        'max_price' => '',
        'category' => '',
        'sort_col' => 'product_id',
        'sort_dir' => 'ASC',
        'current_page' => 1,
        'per_page' => 5
    ];
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submission
    $search_state['name'] = trim($_POST['name'] ?? '');
    $search_state['max_price'] = trim($_POST['max_price'] ?? '');
    $search_state['category'] = trim($_POST['category'] ?? '');
    $search_state['sort_col'] = 'product_id';
    $search_state['sort_dir'] = 'ASC';
    $search_state['current_page'] = 1;
}

// Handle sort column clicks
if (isset($_GET['sort'])) {
    $sort_col = $_GET['sort'];
    if (in_array($sort_col, ['product_id', 'price', 'category'])) {
        if ($search_state['sort_col'] === $sort_col) {
            $search_state['sort_dir'] = ($search_state['sort_dir'] === 'ASC') ? 'DESC' : 'ASC';
        } else {
            $search_state['sort_col'] = $sort_col;
            $search_state['sort_dir'] = 'ASC';
        }
    }
}

// Handle per_page changes
if (isset($_GET['per_page'])) {
    $per_page = (int)$_GET['per_page'];
    if (in_array($per_page, [5, 10, 15, 20, 0])) {
        $search_state['per_page'] = $per_page;
        $search_state['current_page'] = 1;
    }
}

// Handle pagination
if (isset($_GET['page'])) {
    $search_state['current_page'] = max(1, (int)$_GET['page']);
}

// Save to session
$_SESSION['product_search_state'] = json_encode($search_state);

// Get categories for dropdown
$categories = [];
$stmt = $pdo->prepare('SELECT DISTINCT category FROM products ORDER BY category');
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Build query
$where_clauses = [];
$params = [];

if ($search_state['name']) {
    $where_clauses[] = 'product_name LIKE :name';
    $params[':name'] = '%' . $search_state['name'] . '%';
}

if ($search_state['max_price']) {
    $where_clauses[] = 'price <= :max_price';
    $params[':max_price'] = (float)$search_state['max_price'];
}

if ($search_state['category']) {
    $where_clauses[] = 'category = :category';
    $params[':category'] = $search_state['category'];
}

$where_sql = $where_clauses ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

// Count total results
$count_sql = "SELECT COUNT(*) FROM products $where_sql";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_records = $stmt->fetchColumn();

// Calculate pagination
$per_page = $search_state['per_page'] > 0 ? $search_state['per_page'] : $total_records;
$total_pages = $per_page > 0 ? ceil($total_records / $per_page) : 1;
$current_page = max(1, min($search_state['current_page'], $total_pages));
$offset = ($current_page - 1) * $per_page;
$limit_sql = $per_page > 0 ? "LIMIT :offset, :limit" : '';

// Get products
$sort_col = $search_state['sort_col'];
$sort_dir = $search_state['sort_dir'];
$sql = "SELECT * FROM products $where_sql ORDER BY $sort_col $sort_dir $limit_sql";
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
if ($per_page > 0) {
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
}
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $products = [];
    foreach ($rows as $row) {
        $products[] = new Product(
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

function build_query_string($search_state, $exclude = []) {
    $params = [];
    if ($search_state['name'] && !in_array('name', $exclude)) $params[] = 'name=' . urlencode($search_state['name']);
    if ($search_state['max_price'] && !in_array('max_price', $exclude)) $params[] = 'max_price=' . urlencode($search_state['max_price']);
    if ($search_state['category'] && !in_array('category', $exclude)) $params[] = 'category=' . urlencode($search_state['category']);
    $params[] = 'sort=' . $search_state['sort_col'];
    $params[] = 'per_page=' . $search_state['per_page'];
    return implode('&', $params);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Palestinian Souvenir Store</title>
</head>
<body>
    <?php require_once 'header.inc.php'; ?>
    <main>
        <article>
            <h2>Products</h2>

            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Employee'): ?>
                <p><a href="add.php">Add Product</a></p>
            <?php endif; ?>

            <section>
                <h3>Advanced Product Search</h3>
                <form method="post" action="products.php">
                    <p>
                        <label>Product Name:</label><br>
                        <input type="text" name="name" placeholder="Product Name" value="<?= htmlspecialchars($search_state['name']) ?>">
                    </p>
                    <p>
                        <label>Maximum Price:</label><br>
                        <input type="number" step="0.01" name="max_price" placeholder="Max Price" value="<?= htmlspecialchars($search_state['max_price']) ?>">
                    </p>
                    <p>
                        <label>Category:</label><br>
                        <select name="category">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>" <?= $search_state['category'] === $cat ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </p>
                    <p>
                        <button type="submit">Filter</button>
                        <a href="products.php?reset=1&per_page=0">Show All</a>
                    </p>
                </form>
            </section>

            <section>
                <h3>Results (<?= $total_records ?> products)</h3>

                <form method="get" action="products.php">
                    <p>
                        <label>Items per page:</label>
                        <select name="per_page" onchange="this.form.submit()">
                            <option value="5" <?= $search_state['per_page'] === 5 ? 'selected' : '' ?>>5</option>
                            <option value="10" <?= $search_state['per_page'] === 10 ? 'selected' : '' ?>>10</option>
                            <option value="15" <?= $search_state['per_page'] === 15 ? 'selected' : '' ?>>15</option>
                            <option value="20" <?= $search_state['per_page'] === 20 ? 'selected' : '' ?>>20</option>
                            <option value="0" <?= $search_state['per_page'] === 0 ? 'selected' : '' ?>>All</option>
                        </select>
                    </p>
                </form>

                <?php if ($total_records > 0): ?>
                    <table border="1" cellpadding="5">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th><a href="products.php?sort=product_id&<?= build_query_string($search_state, ['sort']) ?>">Product ID</a></th>
                                <th>Product Name</th>
                                <th><a href="products.php?sort=category&<?= build_query_string($search_state, ['sort']) ?>">Category</a></th>
                                <th><a href="products.php?sort=price&<?= build_query_string($search_state, ['sort']) ?>">Price</a></th>
                                <th>Quantity</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <?= $product->displayInTable() ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <p>Page <?= $current_page ?> of <?= $total_pages ?></p>

                    <?php if ($current_page > 1): ?>
                        <a href="products.php?page=<?= $current_page - 1 ?>&<?= build_query_string($search_state) ?>">Previous</a>
                    <?php endif; ?>

                    <?php if ($current_page < $total_pages): ?>
                        <a href="products.php?page=<?= $current_page + 1 ?>&<?= build_query_string($search_state) ?>">Next</a>
                    <?php endif; ?>
                <?php else: ?>
                    <p>No products found.</p>
                <?php endif; ?>
            </section>
        </article>
    </main>
    <?php require_once 'footer.inc.php'; ?>
</body>
</html>
