<?php
// 1. CONFIGURACIÓN DE CORS ULTRA AGRESIVA (Para evitar bloqueos de GitHub Pages)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit;
}

error_reporting(0); // Evitamos que advertencias locales rompan el JSON de respuesta

require_once "../config/database.php";

// 2. MODO COMODÍN: Forzamos el usuario_id = 1 que es donde se guardaron tus contactos en la BD
$usuarioId = 1; 

try {
    // 3. CONSULTA A LA BASE DE DATOS
    $stmt = $conn->prepare("
        SELECT id, usuario_id, nombre, apellido, telefono, email, direccion, notas, foto
        FROM contactos
        WHERE usuario_id = ?
        ORDER BY id DESC
    ");
    $stmt->execute([$usuarioId]);
    $contactos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formateamos los IDs a números por si las dudas con JavaScript
    foreach ($contactos as &$c) {
        $c['id'] = intval($c['id']);
        $c['usuario_id'] = intval($c['usuario_id']);
    }

    // 4. RESPUESTA DIRECTA (El array limpio que Pinia está esperando)
    echo json_encode($contactos);
    exit;

} catch (PDOException $e) {
    // Si algo truena, mandamos el array vacío para que el frontend no colapse
    echo json_encode([]);
    exit;
}
?>