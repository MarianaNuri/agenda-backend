<?php
// CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// responde al preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once "../config/database.php";
require_once "../config/auth.php";

//$data = json_decode(file_get_contents("php://input"), true);

//usuario autenticado
$usuario = verificarToken();

// Obtener ID desde GET
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "ID inválido"
    ]);
    exit;
}

try {
    $stmt = $conn->prepare("
        DELETE FROM contactos
        WHERE id = ? AND usuario_id = ?
    ");

    $stmt->execute([$id, $usuario['id']]);

    echo json_encode([
        "success" => true,
        "message" => "Eliminado"
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error al eliminar"
    ]);
}
?>