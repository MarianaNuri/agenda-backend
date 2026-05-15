<?php
require_once "../config/database.php";
require_once "../config/auth.php";

$data = json_decode(file_get_contents("php://input"), true);
$usuario = verificarToken();

$stmt = $conn->prepare("DELETE FROM contactos WHERE id=? AND usuario_id=?");
$stmt->execute([$data['id'], $usuario['id']]);

echo json_encode([
    "success" => true,
    "message" => "Eliminado"
]);
?>