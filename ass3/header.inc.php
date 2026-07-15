<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<header>
  <div class="brand">
    <img src="images/logo.jpg" alt="Store Logo">
    <h1>Palestinian Souvenir Store</h1>
  </div>
  <nav>
    <a href="index.html">Home page</a>
    <a href="products.php">Products</a>
    <?php if (empty($_SESSION['logged_in'])): ?>
      <a href="register.php">Register</a>
      <a href="login.php">Login</a>
    <?php else: ?>
      <a href="logout.php">Logout</a>
    <?php endif; ?>
    <a href="basket.php">Basket</a>
  </nav>
  <?php if (!empty($_SESSION['logged_in'])): ?>
    <p class="user-status">Welcome, <?= htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']) ?> (<?= htmlspecialchars($_SESSION['role']) ?>)</p>
  <?php endif; ?>
</header>
