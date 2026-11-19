<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

$lang = $_SESSION['lang'] ?? 'ua';

require_once 'classes/DB.php';
require_once 'classes/User.php';
require_once 'classes/Translator.php';

$db = DB::getInstance();
$user = new User($db, $_SESSION['user_id']);
$translator = new Translator($lang);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Crypto Exchange | Trade BTC, ETH, SOL</title>

<meta name="description" content="Trade cryptocurrency with real-time prices. Buy and sell BTC, ETH, SOL easily.">

<!-- Open Graph (Telegram / Discord / Facebook) -->
<meta property="og:title" content="Crypto Exchange">
<meta property="og:description" content="Real-time crypto trading platform">
<meta property="og:image" content="https://yourdomain.com/logo.png">
<meta property="og:type" content="website">

<!-- Favicon -->
<link rel="icon" href="favicon.png" type="image/png">

<link rel="stylesheet" href="style.css">
</head>

<body>

<div class="header" style="display: flex; justify-content: space-between; align-items: center;">

  <div style="flex: 1; display: flex; align-items: center;">
    <div style="display:flex; align-items:center; gap:10px;">
      <img src="https://cdn-icons-png.flaticon.com/512/272/272525.png" width="28">
      <b>Crypto Exchange</b>
    </div>
  </div>

  <div style="flex: 2; display: flex; justify-content: center; align-items: center; gap: 20px;">
    
    <div style="display:flex; align-items:center; gap:6px;">
      <img src="https://cdn-icons-png.flaticon.com/512/2489/2489756.png" width="18">
      <span>
        <?= $translator->get('balance') ?>:
        <b id="balance" style="color:#00ff99;">
          <?= number_format($user->balance, 2) ?> USDT
        </b>
      </span>
    </div>

    <a href="history.php" style="color:#00ff99; text-decoration:none; font-size:14px;">
      📊 <?= $translator->get('History') ?>
    </a>

  </div>

  <div style="flex: 1; display: flex; justify-content: flex-end; align-items: center; gap: 15px;">

    <div style="display:flex; align-items:center; gap:8px;">
      <span style="font-size:12px; color:#aaa;">
        <?= $translator->get('language') ?>:
      </span>

      <a href="?lang=ua">
        <img src="https://flagcdn.com/w20/ua.png"
             style="border-radius:3px; <?= $lang=='ua'?'outline:1px solid #00ff99':'' ?>">
      </a>

      <a href="?lang=en">
        <img src="https://flagcdn.com/w20/us.png"
             style="border-radius:3px; <?= $lang=='en'?'outline:1px solid #00ff99':'' ?>">
      </a>
    </div>

    <a href="logout.php" class="btn btn-sell"
   style="width:auto; padding:6px 10px; text-decoration:none;">
   <?php echo $translator->get('logout'); ?>
</a>

  </div>

</div>

</body>
</html>

<div class="container">

  <div class="box">
    <h3><?= $translator->get('chart') ?></h3>
    <div id="chart" style="height:450px;"></div>
  </div>

  <div class="box">

    <h3><?= $translator->get('trade') ?></h3>

    <!-- DROPDOWN -->
    <div class="dropdown">

      <div class="dropdown-selected" onclick="toggleDropdown()">
        <img id="selectedIcon" width="20" src="https://assets.coincap.io/assets/icons/btc@2x.png">
        <span id="selectedText">BTC</span>
      </div>

      <div id="dropdownList" class="dropdown-list">

        <div class="item" onclick="setCoin('BTCUSDT','btc')">
          <img src="https://assets.coincap.io/assets/icons/btc@2x.png" width="18"> BTC
        </div>

        <div class="item" onclick="setCoin('ETHUSDT','eth')">
          <img src="https://assets.coincap.io/assets/icons/eth@2x.png" width="18"> ETH
        </div>

        <div class="item" onclick="setCoin('SOLUSDT','sol')">
          <img src="https://assets.coincap.io/assets/icons/sol@2x.png" width="18"> SOL
        </div>

        <div class="item" onclick="setCoin('BNBUSDT','bnb')">
          <img src="https://assets.coincap.io/assets/icons/bnb@2x.png" width="18"> BNB
        </div>

        <div class="item" onclick="setCoin('XRPUSDT','xrp')">
          <img src="https://assets.coincap.io/assets/icons/xrp@2x.png" width="18"> XRP
        </div>

      </div>
    </div>

    <div style="margin-top:15px;">
      <?= $translator->get('price') ?>:
      <span id="price">...</span> USDT
    </div>

    <div style="color:#aaa; font-size:14px;">
      <?= $translator->get('update') ?>:
      <span id="timer">10</span> sec
    </div>

<input type="number" id="amount" class="input" placeholder="<?= $translator->get('amount') ?>">

<button class="btn btn-buy" onclick="trade('buy')">
    <?= $translator->get('buy') ?>
</button>

<button class="btn btn-sell" onclick="trade('sell')">
    <?= $translator->get('sell') ?>
</button>
<div id="tradeMessage" style="margin-top:10px; text-align:center; color:#00ff99;"></div>
<script src="https://s3.tradingview.com/tv.js"></script>

<script>
let currentCoin = "BTCUSDT";
let prices = {};
let countdown = 10;

/* WS */
function connect(symbol) {
    const ws = new WebSocket(`wss://stream.binance.com:9443/ws/${symbol.toLowerCase()}@trade`);

    ws.onmessage = (e) => {
        const data = JSON.parse(e.data);
        prices[symbol] = parseFloat(data.p);
    };
}
["BTCUSDT","ETHUSDT","SOLUSDT","BNBUSDT","XRPUSDT"].forEach(connect);
function refreshBalance() {
    fetch("balance.php")
        .then(res => res.json())
        .then(data => {
            document.getElementById("balance").innerText =
                parseFloat(data.balance).toFixed(2)+ " USDT";
        });
}
/* UI */
function updatePrice(){
    let price = prices[currentCoin];

    const el = document.getElementById("price");

    if (!price) {
        el.innerText = "0.00";
        return;
    }

    el.innerText = price.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}


/* TIMER */
setInterval(()=>{
    countdown--;
    document.getElementById("timer").innerText = countdown;

    if(countdown <= 0){
        countdown = 10;
        updatePrice();
    }
},1000);
async function loadInitialPrice(symbol) {
    try {
        const res = await fetch(`https://api.binance.com/api/v3/ticker/price?symbol=${symbol}`);
        const data = await res.json();

        prices[symbol] = parseFloat(data.price);

        updatePrice(); // сразу рисуем норм цену
    } catch (e) {
        console.log("REST fallback error", e);
    }
}
/* dropdown */
function setCoin(coin, short){
    currentCoin = coin;

    document.getElementById("selectedText").innerText = short.toUpperCase();
    document.getElementById("selectedIcon").src =
        `https://assets.coincap.io/assets/icons/${short}@2x.png`;

    countdown = 10;

    updatePrice();
    loadChart(coin);
    toggleDropdown();
}

function toggleDropdown(){
    const el = document.getElementById("dropdownList");
    el.style.display = el.style.display === "block" ? "none" : "block";
}

/* chart */
function loadChart(coin){
    document.getElementById("chart").innerHTML = "";

    new TradingView.widget({
        container_id: "chart",
        symbol: "BINANCE:" + coin,
        interval: "5",
        theme: "dark",
        width: "100%",
        height: 450
    });
}

function trade(type) {
    const amount = document.getElementById("amount").value;
    const msg = document.getElementById("tradeMessage");

    const price = parseFloat(prices[currentCoin]);

    if (!amount || amount <= 0) {
        msg.style.color = "red";
        msg.innerText = "Enter amount";
        return;
    }

    if (!price || isNaN(price) || price <= 0) {
        msg.style.color = "red";
        msg.innerText = "Price not loaded or invalid";
        return;
    }

    fetch("trade.php", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: `type=${type}&coin=${currentCoin}&amount=${amount}&price=${price}`
    })
    .then(res => {
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return res.text();
    })
    .then(text => {
        if (text.includes("✔")) {
            msg.style.color = "#00ff99";
            if (typeof refreshBalance === "function") refreshBalance();
            setTimeout(() => msg.innerText = "", 3000);
        } else {
            msg.style.color = "red";
        }
        msg.innerText = text;
    })
    .catch(err => {
        console.error(err); 
        msg.style.color = "red";
        msg.innerText = "Server error. Check console";
    });
}
/* INIT */
window.onload = () => {
    loadChart(currentCoin);

    connect("BTCUSDT");
    connect("ETHUSDT");
    connect("SOLUSDT");
    connect("BNBUSDT");
    connect("XRPUSDT");

    loadInitialPrice(currentCoin);
};
document.addEventListener("click", function(e) {

    const dropdown = document.getElementById("dropdownList");
    const selected = document.querySelector(".dropdown-selected");

    if (!dropdown || !selected) return;

    if (!selected.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.style.display = "none";
    }
});
</script>

</body>
</html>