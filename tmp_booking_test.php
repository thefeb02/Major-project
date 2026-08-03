<?php
require 'C:/xampp/htdocs/tour and travelling/Major-project/Backend/database.php';
$ch = curl_init('http://localhost/tour%20and%20travelling/Major-project/Backend/book_service.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'service_category=Tour&service_name=Test+Package&full_name=Test+User&email=test@example.com&phone=9800000000&travel_date=2099-01-01&travelers=2&message=Demo&payment_method=online_now&amount=150');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo 'status:' . $status . PHP_EOL;
echo 'body:' . substr($response, 0, 200) . PHP_EOL;
curl_close($ch);
