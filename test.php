<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Debug Price API</title>

<style>
body {
  margin:0;
  background:#0b0f1a;
  color:white;
  font-family: Arial;
  display:flex;
  justify-content:center;
  align-items:center;
  height:100vh;
}

.box {
  width:500px;
  background:#111827;
  padding:20px;
  border-radius:10px;
}

h3 { margin-top:0; }

#price {
  font-size:32px;
  color:#00ff99;
  margin:10px 0;
}

.log {
  background:#0b0f1a;
  padding:10px;
  height:200px;
  overflow:auto;
  font-size:12px;
  border:1px solid #333;
}
</style>
</head>

<body>

<div class="box">
  <h3>PRICE DEBUG TEST</h3>

  <div id="price">---</div>
  <div id="time">---</div>

  <div class="log" id="log"></div>
</div>

<script>

let count = 0;

function log(msg) {
  const el = document.getElementById("log");
  el.innerHTML += msg + "<br>";
  el.scrollTop = el.scrollHeight;
}

async function run() {

  count++;

  log("➡️ REQUEST #" + count + " START");

  try {
    const res = await fetch("price.php", { cache: "no-store" });

    log("⬅️ STATUS: " + res.status);

    const text = await res.text();

    log("📦 RAW: " + text);

    let data;

    try {
      data = JSON.parse(text);
    } catch (e) {
      log("❌ JSON ERROR");
      document.getElementById("price").innerText = "BAD JSON";
      return;
    }

    if (!data.bitcoin) {
      log("❌ NO BITCOIN FIELD");
      document.getElementById("price").innerText = "NO DATA";
      return;
    }

    const price = data.bitcoin.usd;

    log("✅ PRICE: " + price);

    document.getElementById("price").innerText =
      price.toLocaleString() + " USD";

    document.getElementById("time").innerText =
      "updated: " + new Date().toLocaleTimeString();

  } catch (e) {
    log("❌ FETCH ERROR: " + e);
    document.getElementById("price").innerText = "ERROR";
  }
}

// старт
run();

// каждые 10 сек
setInterval(run, 10000);

</script>

</body>
</html>