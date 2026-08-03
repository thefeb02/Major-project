<?php
$pdo = new PDO('mysql:host=localhost;port=3306;dbname=nepal_travel_db;charset=utf8mb4', 'root', '');
$tables = $pdo->query("SHOW TABLES LIKE 'service_bookings'")->fetchAll();
var_dump($tables);
$cols = $pdo->query('SHOW COLUMNS FROM service_bookings')->fetchAll();
var_dump($cols);
