<?php
// 1. CONFIGURACIÓN DE CORS ABIERTA
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
// Cumplimos con el punto 7 del documento: Validar el token en el Backend
require_once "../config/auth.php"; 


try {
    // Identificamos al usuario autenticado de forma segura a través del Token
    $usuarioAutenticado = verificarToken(); 
    $usuarioId = $usuarioAutenticado['id']; 

    // 3. CONSULTA FILTRADA A LA BASE DE DATOS
    $stmt = $conn->prepare("
        SELECT id, usuario_id, nombre, apellido, telefono, email, direccion, notas, foto
        FROM contactos
        WHERE usuario_id = ?
        ORDER BY id DESC
    ");
    $stmt->execute([$usuarioId]);
    $contactos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formateamos los datos para cumplir con el contrato estricto
    foreach ($contactos as &$c) {
        $c['id'] = intval($c['id']);
        $c['usuario_id'] = intval($c['usuario_id']);

        if (!empty($c['foto'])) {
            $c['foto'] = "https://sistemas-agenda.alwaysdata.net/api/uploads/contactos/" . $c['foto'];
        } else {
            $c['foto'] = null; 
        }
    }

    // Devolvemos exactamente la estructura JSON que el contrato exige
    echo json_encode($contactos);
    exit;

} catch (PDOException $e) {
    echo json_encode([]);
    exit;
}
?>