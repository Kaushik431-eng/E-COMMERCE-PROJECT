<?php
session_start();
require "../config/db.php";

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: ../index.php");
    exit;
}

$id = (int)($_GET["id"] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: index.php");
    exit;
}

$errors = [];
$name = $product["name"];
$description = $product["description"];
$price = $product["price"];
$stock = $product["stock"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $price = trim($_POST["price"] ?? "");
    $stock = trim($_POST["stock"] ?? "");
    $newImage = "";

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
                $newImage = uniqid("p_", true) . "." . $ext;
            }
        }
    }

    if (empty($errors)) {
        $imageToSave = $product["image"];
        $folder = __DIR__ . "/../images/";

        if ($newImage !== "") {
            if (!is_dir($folder)) {
                mkdir($folder, 0777, true);
            }
            move_uploaded_file($_FILES["image"]["tmp_name"], $folder . $newImage);

            if ($product["image"] !== "") {
                $oldPath = $folder . basename($product["image"]);
                if (is_file($oldPath)) {
                    unlink($oldPath);
                }
            }
            $imageToSave = $newImage;
        }

        $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, image = ?, stock = ? WHERE id = ?");
        $stmt->execute([$name, $description, $price, $imageToSave, (int)$stock, $id]);

        header("Location: index.php?updated=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Product</title>
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
            <h2>Edit Product</h2>

            <?php foreach ($errors as $error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>

            <?php if ($product["image"] !== ""): ?>
                <p style="margin-bottom:6px; font-weight:600; font-size:14px;">Current Image</p>
                <img src="../images/<?= htmlspecialchars($product["image"]) ?>" alt="" class="current-img" onerror="this.style.display='none'">
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <label>Product Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($name) ?>">

                <label>Description</label>
                <textarea name="description" rows="4"><?= htmlspecialchars($description) ?></textarea>

                <label>Price (₹)</label>
                <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($price) ?>">

                <label>Stock</label>
                <input type="number" name="stock" value="<?= htmlspecialchars($stock) ?>">

                <label>Nayi Image (optional, purani badalne ke liye)</label>
                <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp">

                <button type="submit" class="btn btn-full">Update Product</button>
            </form>
            <p class="auth-link"><a href="index.php">← Wapas jao</a></p>
        </div>
    </div>
</body>

</html>