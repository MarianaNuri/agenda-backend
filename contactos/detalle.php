<?php
// INCLUIMOS EL CORS (Ya maneja OPTIONS y cabeceras)
require_once "../config/cors.php";
header("Content-Type: application/json; charset=UTF-8");
error_reporting(0);

require_once "../config/database.php";
// 1. IMPORTANTE: Traer la función que protege la ruta y valida el token
require_once "../config/auth.php"; 

try {
    // 2. PROTECCIÓN DE RUTA: Validamos el token y obtenemos el usuario autenticado
    // Si el token falta o no sirve, la función frena todo en seco.
    $usuarioAutenticado = verificarToken(); 
    $usuarioId = $usuarioAutenticado['id']; // ID real y blindado desde el token

    // 3. VALIDAR QUE VENGA EL ID DEL CONTACTO QUE SE QUIERE VER (?id=X)
    // Según tu contrato (Sección 5), este parámetro Sí viaja por GET: /api/contactos/detalle.php?id=X
    if (!isset($_GET['id'])) {
        echo json_encode([
            "success" => false,
            "message" => "ID de contacto requerido"
        ]);
        exit;
    }

    $id = intval($_GET['id']);

    // 4. BUSCAR EL CONTACTO (Seguridad: debe coincidir el id y pertenecer al usuario logueado)
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

    // 5. RESPUESTA COMPATIBLE Y SEGURA
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