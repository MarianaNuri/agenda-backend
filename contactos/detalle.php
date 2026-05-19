<?php
// INCLUIMOS EL CORS (Ya maneja OPTIONS y cabeceras)
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

// VALIDAR QUE VENGA EL ID DEL CONTACTO (?id=X)
if (!isset($_GET['id'])) {
    echo json_encode([
        "success" => false,
        "message" => "ID de contacto requerido"
    ]);
    exit;
}

$id = intval($_GET['id']);

try {
    // BUSCAR EL CONTACTO (Seguridad: debe coincidir el id y el dueño)
    $stmt = $conn->prepare("
        SELECT *
        FROM contactos
        WHERE id = ?
        AND usuario_id = ?
    ");

    $stmt->execute([$id, $usuarioId]);
    $contacto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$contacto) {
        echo json_encode([
            "success" => false,
            "message" => "Contacto no encontrado o no tienes permisos"
        ]);
        exit;
    }

    // RESPUESTA COMPATIBLE  FUNCIÓN EN FRONTEND (data?.data || data)
    echo json_encode([
        "success" => true,
        "data" => $contacto
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error interno al obtener el detalle"
    ]);
    exit;
}
?>