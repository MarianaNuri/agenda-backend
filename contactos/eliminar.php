<?php
// INCLUIMOS EL CORS CENTRALIZADO (Ya maneja OPTIONS y cabeceras)
require_once "../config/cors.php";
header("Content-Type: application/json; charset=UTF-8");
error_reporting(0);

require_once "../config/database.php";
// 1. IMPORTANTE: Traer la función que protege la ruta y valida el token
require_once "../config/auth.php"; 

try {
    // 2. PROTECCIÓN DE RUTA: Validamos el token y obtenemos el usuario autenticado
    // Si el token es inválido o no viene, la función corta la ejecución y manda el error automáticamente
    $usuarioAutenticado = verificarToken(); 
    $usuarioId = $usuarioAutenticado['id']; // Obtenemos el ID real y seguro desde el token

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

    // 4. ELIMINACIÓN SEGURA: Solo borra si el contacto pertenece al usuario logueado
    $stmt = $conn->prepare("
        DELETE FROM contactos
        WHERE id = ? AND usuario_id = ?
    ");

    $stmt->execute([$id, $usuarioId]);

    // Verificamos si realmente se eliminó una fila (por si intentaron meter el ID de un contacto ajeno)
    if ($stmt->rowCount() === 0) {
        echo json_encode([
            "success" => false,
            "message" => "No tienes permisos para eliminar este contacto o no existe."
        ]);
        exit;
    }

    // 5. FORMATO DE RESPUESTA OBLIGATORIO SEGÚN EL CONTRATO
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