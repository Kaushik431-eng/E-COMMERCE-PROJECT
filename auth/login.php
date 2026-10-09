<?php
session_start();
require "../config/db.php";

$errors = [];
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if ($email === "" || $password === "") {
        $errors[] = "Email aur password dono bharo.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user["password"])) {
            session_regenerate_id(true);
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_name"] = $user["name"];
            $_SESSION["role"] = $user["role"];

            header("Location: ../index.php");
            exit;
        } else {
            $errors[] = "Email ya password galat hai.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body>
    <nav class="navbar">
        <a href="../index.php" class="brand">🛒 My Shop</a>
        <div class="nav-links">
            <a href="register.php">Register</a>
        </div>
    </nav>

    <div class="auth-box">
        <h2>Welcome Back</h2>

        <?php if (isset($_GET["registered"])): ?>
            <div class="alert alert-success">Registration successful! Ab login karo.</div>
        <?php endif; ?>

        <?php foreach ($errors as $error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endforeach; ?>

        <form method="POST">
            <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($email) ?>">
            <input type="password" name="password" placeholder="Password">
            <button type="submit" class="btn btn-full">Login</button>
        </form>

        <p class="auth-link">Account nahi hai? <a href="register.php">Register</a></p>
    </div>
</body>

</html>