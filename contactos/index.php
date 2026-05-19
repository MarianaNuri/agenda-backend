<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit;
}

error_reporting(0);

require_once "../config/database.php";
require_once "../config/auth.php";

// Obtener usuario autenticado desde el token
$usuario = verificarToken();

try {

    $stmt = $conn->prepare("
        SELECT id, usuario_id, nombre, apellido, telefono, email, direccion, notas, foto
        FROM contactos
        WHERE usuario_id = ?
        ORDER BY id DESC
    ");

    // Usar el ID REAL del usuario autenticado
    $stmt->execute([$usuario['id']]);

    $contactos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($contactos as &$c) {
        $c['id'] = intval($c['id']);
        $c['usuario_id'] = intval($c['usuario_id']);
    }

    echo json_encode([
        "success" => true,
        "data" => $contactos
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>