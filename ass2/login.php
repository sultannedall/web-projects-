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
    <title>Login - Palestinian Souvenir Store</title>
</head>
<body>
    <?php require_once 'header.inc.php'; ?>
    <main>
        <article>
            <h2>Login</h2>
            <?php if ($error): ?>
                <section>
                    <p><strong><?= htmlspecialchars($error) ?></strong></p>
                </section>
            <?php endif; ?>
            <form method="post" action="login.php">
                <fieldset>
                    <legend>Login Credentials</legend>
                    <p>
                        <label>Email:</label><br>
                        <input type="email" name="email" required>
                    </p>
                    <p>
                        <label>Password:</label><br>
                        <input type="password" name="password" required>
                    </p>
                </fieldset>
                <p>
                    <button type="submit">Login</button>
                </p>
            </form>
            <p><a href="register.php">Don't have an account? Register here.</a></p>
        </article>
    </main>
    <?php require_once 'footer.inc.php'; ?>
</body>
</html>
