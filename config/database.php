<?php
$host = "localhost";
$db = "agenda_app";
$user = "root";
$pass = "root"; 

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error de conexión"
    ]);
}

$host = "sql123.infinityfree.com";
$db = "if0_12345678_agenda";
$user = "if0_12345678";
$pass = "c22sr0TTwX";

?>