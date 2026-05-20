<?php
// INCLUIMOS EL CORS CENTRALIZADO (Ya maneja OPTIONS y cabeceras)
require_once "../config/cors.php";
header("Content-Type: application/json; charset=UTF-8");
error_reporting(0);

require_once "../config/database.php";

// 2. RECUPERAR EL ID DEL DUEÑO DESDE LA URL (?usuario_id=X)
$usuarioId = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : null;

if (!$usuarioId) {
    echo json_encode([
        "success" => false,
        "message" => "Falta el ID del usuario autenticado."
    ]);
    exit;
}

// LEER DATOS: Soporta $_POST tradicional, $_GET o JSON plano de otros equipos
$inputData = json_decode(file_get_contents("php://input"), true);
$id = $_POST['id'] ?? $inputData['id'] ?? $_GET['id'] ?? 0;
$id = intval($id);

// 3. VALIDAR QUE EL ID DEL CONTACTO SEA VÁLIDO
if ($id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "ID de contacto inválido"
    ]);
    exit;
}

try {
    // 4. ELIMINACIÓN SEGURA: Solo borra si el contacto pertenece al usuario logueado
    $stmt = $conn->prepare("
        DELETE FROM contactos
        WHERE id = ? AND usuario_id = ?
    ");

    $stmt->execute([$id, $usuarioId]);

    // 5. FORMATO DE RESPUESTA 
    echo json_encode([
        "success" => true,
        "message" => "Operación realizada correctamente"
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