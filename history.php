<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'classes/DB.php';
require_once 'classes/Translator.php';

$db = DB::getInstance();
$translator = new Translator($_SESSION['lang'] ?? 'ua');

$userId = $_SESSION['user_id'];

$typeFilter = $_GET['type'] ?? 'all';
$sort = $_GET['sort'] ?? 'desc';

/* ===================== ICON HELPER ===================== */
function coinIcon($coin) {
    $coin = strtoupper(trim($coin));

    $map = [
        'BTCUSDT' => 'btc',
        'ETHUSDT' => 'eth',
        'SOLUSDT' => 'sol',
        'BNBUSDT' => 'bnb',
        'XRPUSDT' => 'xrp',
    ];

    return $map[$coin] ?? strtolower(str_replace('USDT', '', $coin));
}

/* ===================== TRANSACTIONS ===================== */
$sql = "SELECT * FROM transactions WHERE user_id=?";

if ($typeFilter !== 'all') {
    $sql .= " AND type=?";
}

$sql .= " ORDER BY id " . ($sort === 'asc' ? "ASC" : "DESC");

$stmt = $db->prepare($sql);

if ($typeFilter !== 'all') {
    $stmt->bind_param("is", $userId, $typeFilter);
} else {
    $stmt->bind_param("i", $userId);
}

$stmt->execute();
$result = $stmt->get_result();

/* ===================== PORTFOLIO ===================== */
$pstmt = $db->prepare("
    SELECT coin,
    SUM(CASE WHEN type='buy' THEN amount ELSE -amount END) AS amount
    FROM transactions
    WHERE user_id=?
    GROUP BY coin
");

$pstmt->bind_param("i", $userId);
$pstmt->execute();
$portfolio = $pstmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Transaction History | Crypto Exchange</title>

<meta name="description" content="View your crypto trading history. Track BTC, ETH, SOL transactions and performance.">

<link rel="icon" href="favicon.png" type="image/png">

<link rel="stylesheet" href="history.css">
</head>

<body>

<div class="container">

<!-- HEADER -->
<div class="header">
    <b><?= $translator->get('history') ?></b>
    <a class="btn" href="index.php">← <?= $translator->get('Back') ?></a>
</div>

<div class="box">
    <h3>💼 <?= $translator->get('Portfolio') ?></h3>
    
    <div class="portfolio-header">
        <div><?= $translator->get('Asset') ?></div>
        <div><?= $translator->get('Amount') ?></div>
        <div><?= $translator->get('Value') ?></div>
    </div>

    <?php while ($p = $portfolio->fetch_assoc()): ?>
        <?php if ($p['amount'] <= 0) continue; ?>

        <div class="portfolio-item"
             data-coin="<?= $p['coin'] ?>"
             data-amount="<?= $p['amount'] ?>">

            <div style="display:flex; align-items:center; gap:6px;">
                <img src="https://assets.coincap.io/assets/icons/<?= coinIcon($p['coin']) ?>@2x.png">
                <?= $p['coin'] ?>
            </div>

            <div><?= number_format($p['amount'], 6) ?></div>

            <div class="value price"><?= $translator->get('loading') ?>...</div>

        </div>
    <?php endwhile; ?>
</div>

<div class="box">

<form method="GET" class="filters">

    <select name="type">
        <option value="all"><?= $translator->get('All') ?></option>
        <option value="buy" <?= $typeFilter=='buy'?'selected':'' ?>><?= $translator->get('Buy') ?></option>
        <option value="sell" <?= $typeFilter=='sell'?'selected':'' ?>><?= $translator->get('Sell') ?></option>
    </select>

    <select name="sort">
        <option value="desc" <?= $sort=='desc'?'selected':'' ?>><?= $translator->get('Newest') ?></option>
        <option value="asc" <?= $sort=='asc'?'selected':'' ?>><?= $translator->get('Oldest') ?></option>
    </select>

    <button><?= $translator->get('Apply') ?></button>

</form>

<div class="table-header">
    <div class="col"><?= $translator->get('Type') ?></div>
    <div class="col"><?= $translator->get('Coin') ?></div>
    <div class="col center"><?= $translator->get('Amount') ?></div>
    <div class="col center"><?= $translator->get('Price') ?></div>
    <div class="col right"><?= $translator->get('Total') ?></div>
</div>
<?php while ($row = $result->fetch_assoc()): ?>

<div class="table-row">

    <div class="col <?= $row['type']=='buy'?'buy':'sell' ?>">
        <?= strtoupper($translator->get($row['type'])) ?>
    </div>

    <div class="col" style="display:flex; align-items:center; gap:6px;">
        <img src="https://assets.coincap.io/assets/icons/<?= coinIcon($row['coin']) ?>@2x.png">
        <?= $row['coin'] ?>
    </div>

    <div class="col center"><?= $row['amount'] ?></div>

    <div class="col center"><?= $row['price'] ?></div>

    <div class="col right usdt">
        <?= number_format($row['amount'] * $row['price'], 2) ?>
    </div>

</div>

<?php endwhile; ?>

</div>

</div>

<!-- WS -->
<script>

window.prices = {};

/* =========================
   1. PRELOAD (СРАЗУ ЦЕНЫ)
========================= */
async function preloadPrices() {
    const coins = ["BTCUSDT","ETHUSDT","SOLUSDT","BNBUSDT","XRPUSDT"];

    for (let coin of coins) {
        try {
            const res = await fetch(`https://api.binance.com/api/v3/ticker/price?symbol=${coin}`);
            const data = await res.json();

            window.prices[coin] = parseFloat(data.price);
        } catch (e) {
            console.log("preload error", coin);
        }
    }

    updatePortfolio(); // 💥 мгновенно рисуем UI
}

/* =========================
   2. WEBSOCKET PRICE STREAM
========================= */
function connect(symbol) {
    const ws = new WebSocket(
        `wss://stream.binance.com:9443/ws/${symbol.toLowerCase()}@trade`
    );

    ws.onmessage = (e) => {
        const data = JSON.parse(e.data);
        window.prices[symbol] = parseFloat(data.p);
    };
}

/* =========================
   3. PORTFOLIO CALC
========================= */
function updatePortfolio() {
    document.querySelectorAll(".portfolio-item").forEach(el => {
        const coin = el.dataset.coin;
        const amount = parseFloat(el.dataset.amount);
        const price = window.prices[coin];
        const valueEl = el.querySelector(".value");

        if (price && valueEl) {
            const total = amount * price;
            valueEl.innerText = total.toFixed(2) + " USDT";
        }
    });
}

/* =========================
   4. TIMER (5 SEC UPDATE LOOP)
========================= */
let countdown = 5;

function tick() {
    const timer = document.getElementById("timer");

    if (timer) {
        timer.innerText = countdown;
    }

    if (countdown === 0) {
        updatePortfolio();
        countdown = 5;
    } else {
        countdown--;
    }
}

setInterval(tick, 1000);

/* =========================
   5. START APP
========================= */
preloadPrices();

["BTCUSDT","ETHUSDT","SOLUSDT","BNBUSDT","XRPUSDT"]
    .forEach(connect);
</script>

</body>
</html>