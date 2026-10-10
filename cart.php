<?php
session_start();
require "config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: auth/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = (int)($_POST["product_id"] ?? 0);
    $action = $_POST["action"] ?? "";

    if ($action === "remove") {
        unset($_SESSION["cart"][$id]);
    } elseif ($action === "update") {
        $qty = (int)($_POST["quantity"] ?? 1);

        if ($qty <= 0) {
            unset($_SESSION["cart"][$id]);
        } else {
            $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $product = $stmt->fetch();

            if ($product) {
                $_SESSION["cart"][$id] = min($qty, (int)$product["stock"]);
            }
        }
    }

    header("Location: cart.php");
    exit;
}

$items = [];
$total = 0;
$cart = $_SESSION["cart"] ?? [];

if (!empty($cart)) {
    $ids = array_keys($cart);
    $placeholders = implode(",", array_fill(0, count($ids), "?"));

    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);

    foreach ($stmt->fetchAll() as $p) {
        $p["qty"] = $cart[$p["id"]];
        $p["subtotal"] = $p["qty"] * $p["price"];
        $total += $p["subtotal"];
        $items[] = $p;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Cart</title>
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
        <h1 class="page-title">Your Cart</h1>

        <?php if (empty($items)): ?>
            <div class="empty-box">
                <p>Aapka cart khali hai.</p>
                <a href="index.php" class="btn">Shopping Karo</a>
            </div>
        <?php else: ?>
            <table class="cart-table">
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item["name"]) ?></td>
                        <td>₹<?= number_format($item["price"], 2) ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?= (int)$item["id"] ?>">
                                <input type="number" name="quantity" class="qty-input"
                                    value="<?= (int)$item["qty"] ?>" min="0" max="<?= (int)$item["stock"] ?>">
                                <button type="submit" class="btn btn-sm">Update</button>
                            </form>
                        </td>
                        <td>₹<?= number_format($item["subtotal"], 2) ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?= (int)$item["id"] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <div class="cart-summary">
                <h2>Total: ₹<?= number_format($total, 2) ?></h2>
                <a href="checkout.php" class="btn">Checkout</a>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>