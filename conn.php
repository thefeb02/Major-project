<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$servername = "localhost";
$ports = [3307, 3306];
$credentials = [
    ["user" => "tour_user", "pass" => "tour_pass_2026"],
    ["user" => "root", "pass" => ""],
];
$database = "tour_travel_db";

$conn = null;

foreach ($ports as $port) {
    foreach ($credentials as $credential) {
        try {
            $conn = new mysqli($servername, $credential["user"], $credential["pass"], $database, $port);
            $conn->set_charset('utf8mb4');
            break 2;
        } catch (mysqli_sql_exception $e) {
            // Let calling code decide what to do if all connection attempts fail.
            $conn = null;
        }
    }
}
?>
