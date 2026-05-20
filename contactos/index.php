<?php
// 1. CONFIGURACIÓN DE CORS ABIERTA (Evita bloqueos con GitHub Pages)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// Si es una petición de control OPTIONS, respondemos de inmediato y salimos
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit;
}

// Desactivamos reportes de advertencias para que no ensucien ni rompan el JSON
error_reporting(0);

require_once "../config/database.php";

// 2. RECUPERAR EL ID DEL USUARIO DESDE LA URL
// Lee el parámetro ?usuario_id=X que le manda Vue. Si no viene, usa el 1 por respaldo.
$usuarioId = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : 1; 

try {
    // 3. CONSULTA FILTRADA A LA BASE DE DATOS
    $stmt = $conn->prepare("
        SELECT id, usuario_id, nombre, apellido, telefono, email, direccion, notas, foto
        FROM contactos
        WHERE usuario_id = ?
        ORDER BY id DESC
    ");
    $stmt->execute([$usuarioId]);
    $contactos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formateamos los IDs a valores numéricos para que JavaScript los procese bien
    foreach ($contactos as &$c) {
        $c['id'] = intval($c['id']);
        $c['usuario_id'] = intval($c['usuario_id']);

        // Si el contacto tiene una foto, convertimos el nombre del archivo en una URL completa
        if (!empty($c['foto'])) {
            // Esto crea la URL completa apuntando a carpeta en AlwaysData
            $c['foto'] = "https://sistemas-agenda.alwaysdata.net/api/uploads/contactos/" . $c['foto'];
        } else {
            // Opcional: una imagen por defecto si no tiene foto
            $c['foto'] = null; 
        }
    }

    // 4. RESPUESTA DIRECTA (El array limpio que Pinia espera mapear)
    echo json_encode($contactos);
    exit;

} catch (PDOException $e) {
    // Si la base de datos falla, mandamos un array vacío para que el frontend no colapse
    echo json_encode([]);
    exit;
}
?>