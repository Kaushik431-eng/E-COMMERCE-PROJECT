<?php
session_start();
require "config/db.php";

$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>

<head>
    <title>My Shop</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .nav {
            margin-bottom: 20px;
        }

        .grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .card {
            border: 1px solid #ccc;
            padding: 15px;
            width: 200px;
            border-radius: 8px;
        }

        .card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            background: #eee;
        }

        .price {
            color: green;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="nav">
        <?php if (isset($_SESSION["user_id"])): ?>
            Welcome, <?= htmlspecialchars($_SESSION["user_name"]) ?> |
            <a href="auth/logout.php">Logout</a>
        <?php else: ?>
            <a href="auth/login.php">Login</a> |
            <a href="auth/register.php">Register</a>
        <?php endif; ?>
    </div>

    <h1>Our Products</h1>

    <div class="grid">
        <?php foreach ($products as $p): ?>
            <div class="card">
                <img src="images/<?= htmlspecialchars($p["image"]) ?>" alt="" onerror="this.style.visibility='hidden'">
                <h3><?= htmlspecialchars($p["name"]) ?></h3>
                <p><?= htmlspecialchars($p["description"]) ?></p>
                <p class="price">₹<?= number_format($p["price"], 2) ?></p>
                <p>Stock: <?= (int)$p["stock"] ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</body>

</html>