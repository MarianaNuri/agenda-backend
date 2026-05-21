<?php
// 1. INCLUIMOS EL CORS CENTRALIZADO
require_once "../config/cors.php";

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit;
}

header("Content-Type: application/json; charset=UTF-8");
error_reporting(0);

require_once "../config/database.php";
// 2. IMPORTANTE: Traer la función que protege la ruta y valida el token
require_once "../config/auth.php"; 

try {
    // 3. PROTECCIÓN DE RUTA: Validamos el token y obtenemos el usuario autenticado
    // Ya no dependemos de que el frontend mande un 'usuario_id' expuesto.
    $usuarioAutenticado = verificarToken(); 
    $usuarioId = intval($usuarioAutenticado['id']);

    // 4. LIMPIAR EL TOKEN EN LA BASE DE DATOS (Invalidación real en el servidor)
    $stmt = $conn->prepare("
        UPDATE usuarios
        SET token = NULL, token_expiracion = NULL
        WHERE id = ?
    ");
    $stmt->execute([$usuarioId]);

} catch (Exception $e) {
    // Si la base de datos falla, dejamos que pase para que el frontend limpie su localStorage
}

// 5. RESPUESTA EXITOSA OBLIGATORIA SEGÚN EL CONTRATO
echo json_encode([
    "success" => true,
    "message" => "Operación realizada correctamente"
]);
exit;
?>