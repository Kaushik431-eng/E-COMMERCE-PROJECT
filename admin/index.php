<?php
session_start();
require "../config/db.php";

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: ../index.php");
    exit;
}

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "delete") {
    $id = (int)($_POST["product_id"] ?? 0);

    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $message = "Product delete ho gaya.";
    } catch (PDOException $e) {
        $error = "Ye product kisi order me use hua hai, isliye delete nahi ho sakta.";
    }
}

$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Products</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body>
    <nav class="navbar">
        <a href="index.php" class="brand">⚙️ Admin Panel</a>
        <div class="nav-links">
            <span>Hi, <?= htmlspecialchars($_SESSION["user_name"]) ?></span>
            <a href="../index.php">View Shop</a>
            <a href="../auth/logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="admin-top">
            <h1 class="page-title">Manage Products</h1>
            <a href="add_product.php" class="btn">+ Add Product</a>
        </div>

        <?php if ($message !== ""): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error !== ""): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <table class="cart-table">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($products as $p): ?>
                <tr>
                    <td><?= (int)$p["id"] ?></td>
                    <td><?= htmlspecialchars($p["name"]) ?></td>
                    <td>₹<?= number_format($p["price"], 2) ?></td>
                    <td><?= (int)$p["stock"] ?></td>
                    <td>
                        <div class="actions">
                            <a href="edit_product.php?id=<?= (int)$p["id"] ?>" class="btn btn-sm btn-edit">Edit</a>
                            <form method="POST" onsubmit="return confirm('Pakka delete karna hai?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="product_id" value="<?= (int)$p["id"] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>

</html>