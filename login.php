<?php
session_start();

require_once 'classes/DB.php';
require_once 'classes/AuthService.php';

$db = DB::getInstance();
$auth = new AuthService($db);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $res = $auth->login($_POST['username'], $_POST['password']);

    if ($res['success']) {
        $_SESSION['user_id'] = $res['user_id'];
        header("Location: index.php");
        exit;
    } else {
        $error = $res['message'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Login</title>
<link rel="stylesheet" href="style.css">
</head>

<body>

<div class="auth-box">

    <h2>Login</h2>

    <form method="POST">
        <input class="input" name="username" placeholder="Username">
        <input class="input" type="password" name="password" placeholder="Password">
        <button class="btn buy">Login</button>
    </form>

    <div style="color:red; text-align:center;">
        <?= $error ?>
    </div>

    <a href="register.php" style="color:#00ff99; display:block; text-align:center; margin-top:10px;">
        Register
    </a>

</div>

</body>
</html>