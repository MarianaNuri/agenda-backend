<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit;
}

header("Content-Type: application/json; charset=UTF-8");

error_reporting(0);

require_once "../config/database.php";
require_once "../config/auth.php";

// Obtener usuario autenticado desde el token
$usuario = verificarToken();

$inputData = json_decode(file_get_contents("php://input"), true);

$nombre = $_POST['nombre'] ?? $inputData['nombre'] ?? '';
$telefono = $_POST['telefono'] ?? $inputData['telefono'] ?? '';
$apellido = $_POST['apellido'] ?? $inputData['apellido'] ?? '';
$email = $_POST['email'] ?? $inputData['email'] ?? '';
$direccion = $_POST['direccion'] ?? $inputData['direccion'] ?? '';
$notas = $_POST['notas'] ?? $inputData['notas'] ?? '';

// ESTE ES EL ID REAL DEL USUARIO LOGUEADO
$usuarioId = $usuario['id'];

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

try {

    $sql = "INSERT INTO contactos
    (usuario_id, nombre, apellido, telefono, email, direccion, notas, foto)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        $usuarioId,
        $nombre,
        $apellido,
        $telefono,
        $email,
        $direccion,
        $notas,
        $foto
    ]);

    $idGenerado = $conn->lastInsertId();

    echo json_encode([
        "success" => true,
        "message" => "Contacto creado con éxito",
        "contact" => [
            "id" => intval($idGenerado),
            "usuario_id" => intval($usuarioId),
            "nombre" => $nombre,
            "apellido" => $apellido,
            "telefono" => $telefono,
            "email" => $email,
            "direccion" => $direccion,
            "notas" => $notas,
            "foto" => $foto
        ]
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>