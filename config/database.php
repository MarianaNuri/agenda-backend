
<?php
// este es para las pribas locales 
/*$host = "localhost";
$db = "agenda_app";
$user = "root";
$pass = "root"; */

//$host = "sql211.infinityfree.com";
//$db = "if0_41931788_agenda";
//$user = "if0_41931788";
//$pass = "GC9RT6QC4F";

$host = "fdb1032.awardspace.net";
$db = "4760450_agenda";
$user = "4760450_agenda";
$pass = "AgendaContactos1234";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    header("Content-Type: application/json");
    echo json_encode([
        "success" => false,
        "message" => "Error de conexión"
    ]);
}

?>
