
<?php

$host = "localhost";
$dbname = "studentsphere";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database connection failed. Please try again later.");
}

$conn->set_charset("utf8mb4");

?>
