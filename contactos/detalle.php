<?php

require_once "../config/cors.php";

header("Content-Type: application/json");

require_once "../config/database.php";
require_once "../config/auth.php";

$usuario = verificarToken();

if (!isset($_GET['id'])) {
    echo json_encode([
        "success" => false,
        "message" => "ID requerido"
    ]);
    exit;
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("
    SELECT *
    FROM contactos
    WHERE id = ?
    AND usuario_id = ?
");

$stmt->execute([$id, $usuario['id']]);

$contacto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$contacto) {
    echo json_encode([
        "success" => false,
        "message" => "Contacto no encontrado"
    ]);
    exit;
}

echo json_encode([
    "success" => true,
    "data" => $contacto
]);