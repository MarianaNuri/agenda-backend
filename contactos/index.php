<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


require_once "../config/database.php";
require_once "../config/auth.php";
require_once "../config/cors.php";



$usuario = verificarToken();

$stmt = $conn->prepare("SELECT * FROM contactos WHERE usuario_id = ?");
$stmt->execute([$usuario['id']]);

echo json_encode([
    "success" => true,
    "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)
]);

//prueba
?>