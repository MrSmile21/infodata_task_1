<?php 

$servername = "localhost";
$username = "root";
$password = " "; // your password
$dbname = "customer_db"; // your database

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
