<?php
session_start();

require_once 'classes/DB.php';
require_once 'classes/AuthService.php';

$db = DB::getInstance();
$auth = new AuthService($db);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $result = $auth->register($username, $password);

    if ($result['success']) {
        header("Location: login.php");
        exit;
    } else {
        $message = $result['message'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Register</title>

<link rel="stylesheet" href="style.css">

<style>
.auth-box {
    max-width: 400px;
    margin: 120px auto;
    padding: 20px;
    background: #111827;
    border-radius: 10px;
}

.auth-title {
    text-align:center;
    margin-bottom:15px;
}

.auth-link {
    display:block;
    margin-top:10px;
    text-align:center;
    color:#00ff99;
    text-decoration:none;
    font-size:14px;
}

.auth-link:hover {
    text-decoration:underline;
}

.error {
    color:red;
    text-align:center;
    margin-top:10px;
}
</style>

</head>

<body>

<div class="auth-box">

    <h2 class="auth-title">Create Account</h2>

    <form method="POST">

        <input type="text" name="username" placeholder="Username" required>

        <input type="password" name="password" placeholder="Password" required>

        <button class="buy">Register</button>

    </form>

    <?php if ($message): ?>
        <div class="error"><?= $message ?></div>
    <?php endif; ?>

    <a href="login.php" class="auth-link">
        Already have an account? Sign in
    </a>

</div>

</body>
</html>