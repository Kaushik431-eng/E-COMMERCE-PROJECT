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
<html>

<head>
    <title>Register</title>
</head>

<body>
    <h2>Register</h2>

    <?php foreach ($errors as $error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endforeach; ?>

    <form method="POST">
        <input type="text" name="name" placeholder="Name" value="<?= htmlspecialchars($name) ?>"><br><br>
        <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($email) ?>"><br><br>
        <input type="password" name="password" placeholder="Password"><br><br>
        <input type="password" name="confirm_password" placeholder="Confirm Password"><br><br>
        <button type="submit">Register</button>
    </form>

    <p>Already account hai? <a href="login.php">Login</a></p>
</body>

</html>