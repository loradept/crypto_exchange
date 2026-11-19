<?php
session_start();
require_once 'classes/DB.php';
require_once 'classes/user.php';

$db = DB::getInstance();

$userId = $_SESSION['user_id'];

$stmt = $db->prepare("SELECT balance FROM users WHERE id=?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

echo json_encode([
    "balance" => $user['balance']
]);