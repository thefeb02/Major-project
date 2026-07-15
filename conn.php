<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$servername = "localhost";
$ports = [3307, 3306];
$username = "tour_user";
$password = "tour_pass_2026";
$database = "tour_travel_db";

$conn = null;

foreach ($ports as $port) {
    try {
        $conn = new mysqli($servername, $username, $password, $database, $port);
        $conn->set_charset('utf8mb4');
        break;
    } catch (mysqli_sql_exception $e) {
        // Let calling code decide what to do if all connection attempts fail.
        $conn = null;
    }
}
?>
