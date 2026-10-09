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
<html>

<head>
    <title>Login</title>
</head>

<body>
    <h2>Login</h2>

    <?php if (isset($_GET["registered"])): ?>
        <p style="color:green;">Registration successful! Ab login karo.</p>
    <?php endif; ?>

    <?php foreach ($errors as $error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endforeach; ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($email) ?>"><br><br>
        <input type="password" name="password" placeholder="Password"><br><br>
        <button type="submit">Login</button>
    </form>

    <p>Account nahi hai? <a href="register.php">Register</a></p>
</body>

</html>