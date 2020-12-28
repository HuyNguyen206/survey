<?php

/*
 * Controlers kết nối tới API của ISC
 * 
 */

$uri = '118.69.241.22/cam/api/get_call_histories.php';
$headers = [
    'Content-Type: application/json'
];
$method = 'POST';
$params = [
    'contract' => 'LDFD16481',
];

$ch = curl_init();
if (strtoupper($method) == 'POST') {
    if (!empty($params)) {
        $params = json_encode($params);
    }
}

if (strtoupper($method) == 'POST') {
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
} else if ($method == 'DELETE') {
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
}

if (!empty($headers)) {
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
}

curl_setopt($ch, CURLOPT_URL, $uri);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 90);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
//		curl_setopt($ch, CURLOPT_PROXY, "");

$result = curl_exec($ch);
var_dump($result);