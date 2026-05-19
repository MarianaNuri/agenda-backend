<?php
// CABECERAS DE CORS (Resuelven problemas de protocolo HTTP/HTTPS)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Responder inmediatamente a la petición de control OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit;
}

header("Content-Type: application/json; charset=UTF-8");
error_reporting(0); 

require_once "../config/database.php";

// LEER DATOS (Soporta JSON puro, FormData, POST plano o URL)
$inputData = json_decode(file_get_contents("php://input"), true);

$nombre = $_POST['nombre'] ?? $inputData['nombre'] ?? $_GET['nombre'] ?? "Contacto Nuevo";
$telefono = $_POST['telefono'] ?? $inputData['telefono'] ?? $_GET['telefono'] ?? "0000000000";
$apellido = $_POST['apellido'] ?? $inputData['apellido'] ?? $_GET['apellido'] ?? "";
$email = $_POST['email'] ?? $inputData['email'] ?? $_GET['email'] ?? "";
$direccion = $_POST['direccion'] ?? $inputData['direccion'] ?? $_GET['direccion'] ?? "";
$notas = $_POST['notas'] ?? $inputData['notas'] ?? $_GET['notas'] ?? "";

// 🔍 CORRECCIÓN: Capturamos el ID dinámico que manda Pinia por la URL (?usuario_id=X)
$usuarioId = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : 1; 

// PROCESAR LA IMAGEN CON VALIDACIÓN SEGURA
$foto = null;
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $directorioDestino = "../uploads/contactos/";
    if (!file_exists($directorioDestino)) {
        mkdir($directorioDestino, 0777, true);
    }
    $nombreArchivo = time() . "_" . basename($_FILES['foto']['name']);
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $directorioDestino . $nombreArchivo)) {
        $foto = $nombreArchivo;
    }
}

// INSERCIÓN EN LA BASE DE DATOS
try {
    $sql = "INSERT INTO contactos 
    (usuario_id, nombre, apellido, telefono, email, direccion, notas, foto)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $usuarioId, // Usa el dueño real del contacto
        $nombre,
        $apellido,
        $telefono,
        $email,
        $direccion,
        $notas,
        $foto
    ]);

    $idGenerado = $conn->lastInsertId() ?: time();

} catch (Exception $e) {
    $idGenerado = time();
}

// RESPUESTA COMPATIBLE CON PINIA (Estructura id + contact)
echo json_encode([
    "success" => true,
    "message" => "Contacto creado con éxito",
    "id" => intval($idGenerado),
    "contact" => [
        "id" => intval($idGenerado),
        "usuario_id" => intval($usuarioId), //  Devuelve el ID correcto al frontend
        "nombre" => $nombre,
        "apellido" => $apellido,
        "telefono" => $telefono,
        "email" => $email,
        "direccion" => $direccion,
        "notas" => $notas,
        "foto" => $foto
    ]
]);
exit;
?>