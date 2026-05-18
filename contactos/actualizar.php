<?php
require_once "../config/database.php";
require_once "../config/auth.php";
require_once "../config/cors.php";

$usuario = verificarToken();

$data = $_POST;

$sql = "UPDATE contactos SET 
nombre=?, apellido=?, telefono=?, email=?, direccion=?, notas=?
WHERE id=? AND usuario_id=?";

$stmt = $conn->prepare($sql);
$stmt->execute([
    $data['nombre'],
    $data['apellido'],
    $data['telefono'],
    $data['email'],
    $data['direccion'],
    $data['notas'],
    $data['id'],
    $usuario['id']
]);

echo json_encode([
    "success" => true,
    "message" => "Actualizado"
]);
?>