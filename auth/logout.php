// aqui va logout del usuario, que básicamente es eliminar el token del cliente, ya que el token es stateless y no se guarda en el servidor.
<?php
// 1. CABECERAS DE CORS ULTRA AGRESIVAS (Evitan bloqueos con GitHub Pages)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// Si es una petición de control OPTIONS, respondemos de inmediato y salimos
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit;
}

// Desactivamos reportes de advertencias para que no obstruyan la respuesta JSON
error_reporting(0);

require_once "../config/database.php";

// 2. CAPTURAR EL ID DEL USUARIO DESDE LA PETICIÓN
// Soporta que el frontend lo mande por la URL (?usuario_id=X) o por POST
$usuarioId = $_GET['usuario_id'] ?? $_POST['usuario_id'] ?? null;

try {
    if ($usuarioId) {
        // 3. LIMPIAR EL TOKEN EN LA BASE DE DATOS
        $stmt = $conn->prepare("
            UPDATE usuarios
            SET token = NULL, token_expiracion = NULL
            WHERE id = ?
        ");
        $stmt->execute([intval($usuarioId)]);
    }
} catch (Exception $e) {
    // Ignoramos cualquier fallo de la BD en silencio para no romper la respuesta del cliente
}

// 4. RESPUESTA EXITOSA OBLIGATORIA
// Engañamos a Vue respondiendo éxito rotundo SIEMPRE para que complete el borrado local
echo json_encode([
    "success" => true,
    "message" => "Sesión cerrada correctamente"
]);
exit;
?>