<?php
session_start();
require "config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: auth/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$product_id = (int)($_POST["product_id"] ?? 0);

$stmt = $pdo->prepare("SELECT id, stock FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if ($product) {
    $current = $_SESSION["cart"][$product_id] ?? 0;
    if ($current < $product["stock"]) {
        $_SESSION["cart"][$product_id] = $current + 1;
    }
}

header("Location: index.php");
exit;
