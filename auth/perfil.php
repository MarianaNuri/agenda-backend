// aqui va el perfil del usuario, con su informacion y sus contactos, etc.

<?php
// CABECERAS DE CORS 
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

// Recibimos el ID desde la URL. Si no viene, usamos el 1 por respaldo.
$usuarioId = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : 1;

try {
    // Buscamos los datos reales del usuario en la tabla de usuarios
    $stmt = $conn->prepare("SELECT id, nombre_de_usuario, foto FROM usuarios WHERE id = ?");
    $stmt->execute([$usuarioId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Construimos la URL completa
        $foto_url = !empty($user['foto']) 
            ? "https://sistemas-agenda.alwaysdata.net/api/uploads/usuarios/" . $user['foto'] 
            : null;

        echo json_encode([
            "success" => true,
            "user" => [
                "id" => intval($user['id']),
                "nombre_de_usuario" => $user['nombre_de_usuario'],
                "foto" => $foto_url // Enviamos la URL completa construida arriba
            ]
        ]);
    } else {
        // Respaldo por si el ID no existe en la BD
        echo json_encode([
            "success" => true,
            "user" => [
                "id" => $usuarioId,
                "nombre_de_usuario" => "Usuario Agenda",
                "foto" => null
            ]
        ]);
    }
    exit;

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error al obtener perfil"
    ]);
    exit;
}
?>