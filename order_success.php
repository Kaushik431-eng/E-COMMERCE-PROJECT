<?php
session_start();
require "config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: auth/login.php");
    exit;
}

$id = (int)($_GET["id"] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION["user_id"]]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order Placed</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <nav class="navbar">
        <a href="index.php" class="brand">🛒 My Shop</a>
        <div class="nav-links">
            <span>Hi, <?= htmlspecialchars($_SESSION["user_name"]) ?></span>
            <a href="index.php">Products</a>
            <a href="auth/logout.php">Logout</a>
        </div>
    </nav>

    <div class="success-box">
        <div class="tick">✅</div>
        <h2>Order Placed!</h2>
        <p>
            Order #<?= (int)$order["id"] ?> |
            Total: ₹<?= number_format($order["total"], 2) ?> |
            Status: <?= htmlspecialchars($order["status"]) ?>
        </p>
        <a href="index.php" class="btn">Aur Shopping Karo</a>
    </div>
</body>

</html>