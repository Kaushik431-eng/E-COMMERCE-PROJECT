<?php
header("Content-Type: application/json; charset=utf-8");
require "../config/db.php";

function respond($data, $code = 200)
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function formatProduct($p)
{
    $base = (isset($_SERVER["HTTPS"]) ? "https" : "http") . "://" . $_SERVER["HTTP_HOST"];
    $root = rtrim(dirname(dirname($_SERVER["SCRIPT_NAME"])), "/\\");

    return [
        "id"          => (int)$p["id"],
        "name"        => $p["name"],
        "description" => $p["description"],
        "price"       => (float)$p["price"],
        "stock"       => (int)$p["stock"],
        "image_url"   => $p["image"] !== "" ? $base . $root . "/images/" . $p["image"] : null,
    ];
}

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    respond(["success" => false, "error" => "Sirf GET method allowed hai."], 405);
}

if (isset($_GET["id"])) {
    $id = (int)$_GET["id"];

    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if (!$product) {
        respond(["success" => false, "error" => "Product nahi mila."], 404);
    }

    respond(["success" => true, "data" => formatProduct($product)]);
}

$search = trim($_GET["search"] ?? "");

if ($search !== "") {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ? ORDER BY id DESC");
    $stmt->execute(["%" . $search . "%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
}

$products = array_map("formatProduct", $stmt->fetchAll());

respond([
    "success" => true,
    "count"   => count($products),
    "data"    => $products,
]);
