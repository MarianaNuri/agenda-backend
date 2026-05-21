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
// IMPORTANTE: Traer la función que protege la ruta y valida el token
require_once "../config/auth.php"; 

try {
    //PROTECCIÓN DE RUTA: Validamos el token y obtenemos el usuario autenticado
    // Si el token es inválido o no viene, la función corta la ejecución y manda el error automáticamente
    $usuarioAutenticado = verificarToken(); 
    
    // Construimos la URL completa de la foto si el usuario tiene una asignada
    $foto_url = null;
    if (!empty($usuarioAutenticado['foto'])) {
        $foto_url = "https://sistemas-agenda.alwaysdata.net/api/uploads/usuarios/" . $usuarioAutenticado['foto'];
    }

    // 4. RESPUESTA ESTÁNDAR (Cambiado 'user' por 'usuario' para cumplir el contrato)
    echo json_encode([
        "success" => true,
        "usuario" => [
            "id" => intval($usuarioAutenticado['id']),
            "nombre_de_usuario" => $usuarioAutenticado['nombre_de_usuario'],
            "foto" => $foto_url 
        ]
         "user" => [ 
            "id" => $usuarioId,
            "nombre_de_usuario" => $nombre,
            "foto" => $fotoUrl
        ]

    ]);
    exit;

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error al obtener perfil"
    ]);
    exit;
}
?>