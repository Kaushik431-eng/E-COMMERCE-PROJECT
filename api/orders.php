<?php
header("Content-Type: application/json; charset=utf-8");
session_start();
require "../config/db.php";

function respond($data, $code = 200)
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

if (!isset($_SESSION["user_id"])) {
    respond(["success" => false, "error" => "Pehle login karo."], 401);
}

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    respond(["success" => false, "error" => "Sirf GET method allowed hai."], 405);
}

$userId = (int)$_SESSION["user_id"];

function getItems($pdo, $orderId)
{
    $stmt = $pdo->prepare(
        "SELECT oi.product_id, p.name, oi.quantity, oi.price
         FROM order_items oi
         JOIN products p ON p.id = oi.product_id
         WHERE oi.order_id = ?"
    );
    $stmt->execute([$orderId]);

    $items = [];
    foreach ($stmt->fetchAll() as $row) {
        $items[] = [
            "product_id" => (int)$row["product_id"],
            "name"       => $row["name"],
            "quantity"   => (int)$row["quantity"],
            "price"      => (float)$row["price"],
            "subtotal"   => (float)$row["price"] * (int)$row["quantity"],
        ];
    }
    return $items;
}

function formatOrder($pdo, $o)
{
    return [
        "id"         => (int)$o["id"],
        "total"      => (float)$o["total"],
        "status"     => $o["status"],
        "created_at" => $o["created_at"],
        "items"      => getItems($pdo, $o["id"]),
    ];
}

if (isset($_GET["id"])) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([(int)$_GET["id"], $userId]);
    $order = $stmt->fetch();

    if (!$order) {
        respond(["success" => false, "error" => "Order nahi mila."], 404);
    }

    respond(["success" => true, "data" => formatOrder($pdo, $order)]);
}

$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$userId]);

$orders = [];
foreach ($stmt->fetchAll() as $o) {
    $orders[] = formatOrder($pdo, $o);
}

respond([
    "success" => true,
    "count"   => count($orders),
    "data"    => $orders,
]);
