<?php
session_start();
require "config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: auth/login.php");
    exit;
}

$cart = $_SESSION["cart"] ?? [];
if (empty($cart)) {
    header("Location: cart.php");
    exit;
}

$error = "";
$ids = array_keys($cart);
$placeholders = implode(",", array_fill(0, count($ids), "?"));

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders) FOR UPDATE");
        $stmt->execute($ids);
        $products = $stmt->fetchAll();

        if (count($products) !== count($cart)) {
            throw new Exception("Cart ka koi product ab available nahi hai.");
        }

        $total = 0;
        foreach ($products as $p) {
            $qty = $cart[$p["id"]];
            if ($qty > $p["stock"]) {
                throw new Exception($p["name"] . " ka stock kam hai. Cart update karo.");
            }
            $total += $qty * $p["price"];
        }

        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total) VALUES (?, ?)");
        $stmt->execute([$_SESSION["user_id"], $total]);
        $order_id = $pdo->lastInsertId();

        $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stockStmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

        foreach ($products as $p) {
            $qty = $cart[$p["id"]];
            $itemStmt->execute([$order_id, $p["id"], $qty, $p["price"]]);
            $stockStmt->execute([$qty, $p["id"]]);
        }

        $pdo->commit();
        unset($_SESSION["cart"]);

        header("Location: order_success.php?id=" . $order_id);
        exit;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = $e->getMessage();
    }
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->execute($ids);
$items = [];
$grand = 0;
foreach ($stmt->fetchAll() as $p) {
    $p["qty"] = $cart[$p["id"]];
    $p["subtotal"] = $p["qty"] * $p["price"];
    $grand += $p["subtotal"];
    $items[] = $p;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <nav class="navbar">
        <a href="index.php" class="brand">🛒 My Shop</a>
        <div class="nav-links">
            <span>Hi, <?= htmlspecialchars($_SESSION["user_name"]) ?></span>
            <a href="index.php">Products</a>
            <a href="cart.php">Cart (<?= array_sum($_SESSION["cart"] ?? []) ?>)</a>
            <a href="auth/logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <h1 class="page-title">Checkout</h1>

        <?php if ($error !== ""): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="checkout-box">
            <h3>Order Summary</h3>

            <?php foreach ($items as $item): ?>
                <div class="checkout-row">
                    <span><?= htmlspecialchars($item["name"]) ?> × <?= (int)$item["qty"] ?></span>
                    <span>₹<?= number_format($item["subtotal"], 2) ?></span>
                </div>
            <?php endforeach; ?>

            <div class="checkout-total">
                <span>Total</span>
                <span>₹<?= number_format($grand, 2) ?></span>
            </div>

            <form method="POST">
                <button type="submit" class="btn btn-full">Place Order</button>
            </form>
            <p class="auth-link"><a href="cart.php">← Cart me wapas jao</a></p>
        </div>
    </div>
</body>

</html>