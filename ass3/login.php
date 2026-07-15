<?php
require_once 'dbconfig.php.inc';

if (session_status() === PHP_SESSION_NONE) session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Email and Password are required.';
    } else {
        $stmt = $pdo->prepare('SELECT user_id, first_name, last_name, password, role FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['role'] = $user['role'];

            // Redirect to return URL if saved, otherwise to products
            $return_url = $_SESSION['return_url'] ?? 'products.php';
            unset($_SESSION['return_url']);
            header('Location: ' . $return_url);
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Login - Palestinian Souvenir Store</title>
</head>
<body>
    <?php require_once 'header.inc.php'; ?>
    <div class="page-layout page-layout--full">
    <main class="page-main">
        <article class="form-card">
            <h2>Login</h2>
            <?php if ($error): ?>
                <section class="error-list">
                    <p><?= htmlspecialchars($error) ?></p>
                </section>
            <?php endif; ?>
            <form class="styled-form" method="post" action="login.php">
                <fieldset>
                    <legend>Login Credentials</legend>
                    <p class="form-row">
                        <label for="email">Email:</label>
                        <input id="email" type="email" name="email" required>
                    </p>
                    <p class="form-row">
                        <label for="password">Password:</label>
                        <input id="password" type="password" name="password" required>
                    </p>
                </fieldset>
                <p class="form-actions">
                    <button class="button button-green" type="submit">Login</button>
                </p>
            </form>
            <p><a href="register.php">Don't have an account? Register here.</a></p>
        </article>
    </main>
    </div>
    <?php require_once 'footer.inc.php'; ?>
</body>
</html>
