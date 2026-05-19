<?php
// INCLUIMOS EL CORS  (Ya maneja OPTIONS y las cabeceras)
require_once "../config/cors.php";
header("Content-Type: application/json; charset=UTF-8");
error_reporting(0);

require_once "../config/database.php";

// RECUPERAR EL ID DEL DUEÑO DESDE LA URL (?usuario_id=X)
$usuarioId = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : null;

if (!$usuarioId) {
    echo json_encode([
        "success" => false,
        "message" => "Falta el ID del usuario autenticado."
    ]);
    exit;
}

// OBTENER EL ID DEL CONTACTO DESDE LA URL (?id=X)
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "ID de contacto inválido"
    ]);
    exit;
}

try {
    //ELIMINACIÓN SEGURA (Solo borra si el contacto pertenece al usuario logueado)
    $stmt = $conn->prepare("
        DELETE FROM contactos
        WHERE id = ? AND usuario_id = ?
    ");

    $stmt->execute([$id, $usuarioId]);

    echo json_encode([
        "success" => true,
        "message" => "Contacto eliminado correctamente"
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error al eliminar el contacto"
    ]);
    exit;
}
?>