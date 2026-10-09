<?php
session_start();
require "../config/db.php";

$errors = [];
$name = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm = $_POST["confirm_password"];

    if ($name === "" || $email === "" || $password === "") {
        $errors[] = "Saari fields bharna zaroori hai.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email valid nahi hai.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password kam se kam 6 characters ka hona chahiye.";
    }
    if ($password !== $confirm) {
        $errors[] = "Dono passwords match nahi karte.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $errors[] = "Ye email pehle se registered hai.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $hash]);

            header("Location: login.php?registered=1");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body>
    <nav class="navbar">
        <a href="../index.php" class="brand">🛒 My Shop</a>
        <div class="nav-links">
            <a href="login.php">Login</a>
        </div>
    </nav>

    <div class="auth-box">
        <h2>Create Account</h2>

        <?php foreach ($errors as $error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endforeach; ?>

        <form method="POST">
            <input type="text" name="name" placeholder="Full Name" value="<?= htmlspecialchars($name) ?>">
            <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($email) ?>">
            <input type="password" name="password" placeholder="Password">
            <input type="password" name="confirm_password" placeholder="Confirm Password">
            <button type="submit" class="btn btn-full">Register</button>
        </form>

        <p class="auth-link">Already account hai? <a href="login.php">Login</a></p>
    </div>
</body>

</html>