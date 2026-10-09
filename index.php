<?php
session_start();
require "config/db.php";

$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Shop</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <nav class="navbar">
        <a href="index.php" class="brand">🛒 My Shop</a>
        <div class="nav-links">
            <?php if (isset($_SESSION["user_id"])): ?>
                <span>Hi, <?= htmlspecialchars($_SESSION["user_name"]) ?></span>
                <a href="cart.php">Cart (<?= array_sum($_SESSION["cart"] ?? []) ?>)</a>
                <a href="auth/logout.php">Logout</a>
            <?php else: ?>
                <a href="auth/login.php">Login</a>
                <a href="auth/register.php">Register</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">
        <h1 class="page-title">Our Products</h1>

        <div class="grid">
            <?php foreach ($products as $p): ?>
                <div class="card">
                    <img class="card-img" src="images/<?= htmlspecialchars($p["image"]) ?>" alt="<?= htmlspecialchars($p["name"]) ?>" onerror="this.style.visibility='hidden'">
                    <div class="card-body">
                        <h3><?= htmlspecialchars($p["name"]) ?></h3>
                        <p><?= htmlspecialchars($p["description"]) ?></p>
                        <p class="price">₹<?= number_format($p["price"], 2) ?></p>
                        <p>Stock: <?= (int)$p["stock"] ?></p>

                        <?php if ($p["stock"] > 0): ?>
                            <form method="POST" action="add_to_cart.php">
                                <input type="hidden" name="product_id" value="<?= (int)$p["id"] ?>">
                                <button type="submit" class="btn btn-full">Add to Cart</button>
                            </form>
                        <?php else: ?>
                            <p class="out">Out of stock</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>

</html>