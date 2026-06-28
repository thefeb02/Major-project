<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$servername = "localhost";
$port = 3307;
$username = "root";
$password = "";
$database = "tour_travel_db";

$conn = null;

try {
    $conn = new mysqli($servername, $username, $password, $database, $port);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    // Don’t fatal-crash pages. Let calling code decide what to do.
    // You can still echo $e->getMessage() from the page if needed.
    $conn = null;
}
?>
