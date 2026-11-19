<?php

header('Content-Type: application/json');

$cacheFile = __DIR__ . '/price_cache.json';
$cacheTime = 10; // секунд

// 💾 1. если кеш свежий — отдаём его
if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
    echo file_get_contents($cacheFile);
    exit;
}

// 💰 2. список монет
$ids = "bitcoin,ethereum,solana,binancecoin,ripple";
$url = "https://api.coingecko.com/api/v3/simple/price?ids=$ids&vs_currencies=usd";

// 🌐 3. CURL запрос (стабильнее чем file_get_contents)
$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 5,
    CURLOPT_USERAGENT => "Mozilla/5.0"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// ❌ 4. если API сломался или заблокировал
if ($httpCode !== 200 || !$response) {

    // пробуем отдать старый кеш
    if (file_exists($cacheFile)) {
        echo file_get_contents($cacheFile);
        exit;
    }

    echo json_encode([
        "error" => "api_blocked"
    ]);
    exit;
}

// 💾 5. сохраняем кеш
file_put_contents($cacheFile, $response);

// 📤 6. отдаём данные
echo $response;