<?php
session_start();
require_once 'classes/DB.php';
require_once 'classes/TradeManager.php';

header('Content-Type: text/plain');

if (!isset($_SESSION['user_id'])) {
    echo "❌ not logged in";
    exit;
}

$db = DB::getInstance();
$trade = new TradeManager($db);

// === ОТЛАДКА ===
file_put_contents(__DIR__ . '/trade_debug.log', 
    date('Y-m-d H:i:s') . " POST: " . print_r($_POST, true) . "\n\n", FILE_APPEND);

$type   = $_POST['type'] ?? '';
$coin   = $_POST['coin'] ?? '';
$amount = $_POST['amount'] ?? 0;
$price  = $_POST['price'] ?? 0;

$result = $trade->executeOrderSimple(
    $_SESSION['user_id'],
    $type,
    $coin,
    $amount,
    $price
);

if ($result['success']) {
    echo "✔ " . $result['message'];
} else {
    echo "❌ " . $result['message'];
}