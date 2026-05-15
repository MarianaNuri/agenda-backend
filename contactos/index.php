<?php

echo json_encode(getallheaders());
require_once "../config/database.php";
require_once "../config/auth.php";



$usuario = verificarToken();

$stmt = $conn->prepare("SELECT * FROM contactos WHERE usuario_id = ?");
$stmt->execute([$usuario['id']]);

echo json_encode([
    "success" => true,
    "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)
]);
?>