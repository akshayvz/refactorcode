<?php
// Fetchin exchange rates
@$exchangeRates = json_decode(file_get_contents('https://api.exchangeratesapi.io/latest'), true)['rates'];
// API calling is failing because of API Access Key, after providing access key also, facing error response, below provided is a sample response from api.exchangeratesapi.io
$exchangeRates = array(
    "AUD" => 1.566015,
    "CAD" => 1.560132,
    "CHF" => 1.154727,
    "CNY" => 7.827874,
    "GBP" => 0.882047,
    "JPY" => 132.360679,
    "USD" => 1.23396
);
$rows = explode("\n", file_get_contents($argv[1]));

// to check if a country is in the EU
function isEu($c)
{
    $euCountries = [
        'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI',
        'FR', 'GR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT',
        'NL', 'PO', 'PT', 'RO', 'SE', 'SI', 'SK'
    ];
    return in_array($c, $euCountries) ? 'yes' : 'no';
}

$processedResults = [];

foreach ($rows as $row) {
    if (empty($row)) continue;

    $p = explode(",", $row);
    $p2 = explode(':', $p[0]);
    $value[0] = trim($p2[1], '"');
    $p2 = explode(':', $p[1]);
    $value[1] = trim($p2[1], '"');
    $p2 = explode(':', $p[2]);
    $value[2] = trim($p2[1], '"}');

    $binResults = file_get_contents('https://lookup.binlist.net/' . $value[0]);
    if (!$binResults) continue;

    $r = json_decode($binResults);
    $isEu = isEu($r->country->alpha2);

    $rate = $exchangeRates[$value[2]] ?? 0;
    $amntFixed = $value[1] / ($value[2] == 'EUR' || $rate == 0 ? 1 : $rate);

    $processedResults[] = $amntFixed * ($isEu == 'yes' ? 0.01 : 0.02);
}

foreach ($processedResults as $result) {
    echo $result . "\n";
}
