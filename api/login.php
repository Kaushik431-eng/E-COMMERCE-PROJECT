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

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    respond(["success" => false, "error" => "Sirf POST method allowed hai."], 405);
}

$input = json_decode(file_get_contents("php://input"), true);
if (!is_array($input)) {
    $input = $_POST;
}

$email = trim($input["email"] ?? "");
$password = $input["password"] ?? "";

if ($email === "" || $password === "") {
    respond(["success" => false, "error" => "Email aur password dono zaroori hain."], 400);
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user["password"])) {
    respond(["success" => false, "error" => "Email ya password galat hai."], 401);
}

session_regenerate_id(true);
$_SESSION["user_id"] = $user["id"];
$_SESSION["user_name"] = $user["name"];
$_SESSION["role"] = $user["role"];

respond([
    "success" => true,
    "message" => "Login successful.",
    "user" => [
        "id"    => (int)$user["id"],
        "name"  => $user["name"],
        "email" => $user["email"],
        "role"  => $user["role"],
    ],
]);
