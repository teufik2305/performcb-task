<?php

$url = "http://localhost/aggregate/method?dt_start=2020-11-23+19:39:07&dt_end=2020-11-23+19:39:08";

$response = curl_init($url);
curl_setopt($response, CURLOPT_RETURNTRANSFER, true);
$responseJson = curl_exec($response);
curl_close($response);;
echo $responseJson;