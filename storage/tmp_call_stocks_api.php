<?php
// Minimal test to call the local route via built-in PHP stream (assumes site is accessible at http://localhost)
$url = 'http://localhost/pharmacy/stocks-reference?search=para';
$options = [
    'http' => [
        'method' => 'GET',
        'header' => "Accept: application/json\r\n"
    ]
];
$context = stream_context_create($options);
try {
    $res = @file_get_contents($url, false, $context);
    if ($res === false) {
        echo "Request failed or returned empty.\n";
        var_dump($http_response_header ?? null);
    } else {
        echo "Response:\n";
        echo $res . "\n";
    }
} catch (Exception $e) {
    echo "Error calling $url: " . $e->getMessage() . "\n";
}
