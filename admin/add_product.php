<?php
session_start();
require "../config/db.php";

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: ../index.php");
    exit;
}

$errors = [];
$name = "";
$description = "";
$price = "";
$stock = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $price = trim($_POST["price"] ?? "");
    $stock = trim($_POST["stock"] ?? "");
    $imageName = "";

    if ($name === "") {
        $errors[] = "Product ka naam zaroori hai.";
    }
    if (!is_numeric($price) || $price <= 0) {
        $errors[] = "Price 0 se zyada hona chahiye.";
    }
    if (!ctype_digit($stock)) {
        $errors[] = "Stock ek whole number hona chahiye (0 ya zyada).";
    }

    if (isset($_FILES["image"]) && $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES["image"]["error"] !== UPLOAD_ERR_OK) {
            $errors[] = "Image upload nahi ho payi.";
        } else {
            $ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
            $allowed = ["jpg", "jpeg", "png", "webp"];

            if (!in_array($ext, $allowed)) {
                $errors[] = "Sirf jpg, jpeg, png ya webp image allowed hai.";
            } elseif ($_FILES["image"]["size"] > 2 * 1024 * 1024) {
                $errors[] = "Image 2MB se chhoti honi chahiye.";
            } else {
                $imageName = uniqid("p_", true) . "." . $ext;
            }
        }
    }

    if (empty($errors)) {
        if ($imageName !== "") {
            $folder = __DIR__ . "/../images/";
            if (!is_dir($folder)) {
                mkdir($folder, 0777, true);
            }
            move_uploaded_file($_FILES["image"]["tmp_name"], $folder . $imageName);
        }

        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, image, stock) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $imageName, (int)$stock]);

        header("Location: index.php?added=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Product</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body>
    <nav class="navbar">
        <a href="index.php" class="brand">⚙️ Admin Panel</a>
        <div class="nav-links">
            <span>Hi, <?= htmlspecialchars($_SESSION["user_name"]) ?></span>
            <a href="index.php">Products</a>
            <a href="../index.php">View Shop</a>
            <a href="../auth/logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="form-box">
            <h2>Add New Product</h2>

            <?php foreach ($errors as $error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>

            <form method="POST" enctype="multipart/form-data">
                <label>Product Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($name) ?>">

                <label>Description</label>
                <textarea name="description" rows="4"><?= htmlspecialchars($description) ?></textarea>

                <label>Price (₹)</label>
                <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($price) ?>">

                <label>Stock</label>
                <input type="number" name="stock" value="<?= htmlspecialchars($stock) ?>">

                <label>Image (optional, max 2MB)</label>
                <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp">

                <button type="submit" class="btn btn-full">Add Product</button>
            </form>
            <p class="auth-link"><a href="index.php">← Wapas jao</a></p>
        </div>
    </div>
</body>

</html>