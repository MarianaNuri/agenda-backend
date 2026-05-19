<?php

// CONFIGURACIÓN DE CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit;
}

// Configuración de cabeceras de respuesta y errores
header("Content-Type: application/json; charset=UTF-8");
error_reporting(0);

require_once "../config/database.php";
require_once "../config/auth.php";
//require_once "../config/cors.php";

$usuario = verificarToken();

if (!$usuario || !isset($usuario['id'])) {
    echo json_encode([
        "success" => false,
        "message" => "Usuario no autenticado o token inválido"
    ]);
    exit;
}


$inputData = json_decode(file_get_contents("php://input"), true);

$nombre = $_POST['nombre'] ?? $inputData['nombre'] ?? null;
$telefono = $_POST['telefono'] ?? $inputData['telefono'] ?? null;
$apellido = $_POST['apellido'] ?? $inputData['apellido'] ?? null;
$email = $_POST['email'] ?? $inputData['email'] ?? null;
$direccion = $_POST['direccion'] ?? $inputData['direccion'] ?? null;
$notas = $_POST['notas'] ?? $inputData['notas'] ?? null;


if (empty($nombre) || empty($telefono)) {
    echo json_encode([
        "success" => false,
        "message" => "Campos obligatorios faltantes (Nombre y Teléfono)"
    ]);
    exit;
}

$foto = null;

if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $directorioDestino = "../uploads/contactos/";
    
    // Crear la estructura de carpetas automáticamente si no existe en AwardSpace
    if (!file_exists($directorioDestino)) {
        mkdir($directorioDestino, 0777, true);
    }
    
    // Generar un nombre único para evitar que se sobreescriban fotos del mismo nombre
    $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $nombreArchivo = time() . "_" . uniqid() . "." . $extension;
    
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $directorioDestino . $nombreArchivo)) {
        $foto = $nombreArchivo;
    }
}

try {
    $sql = "INSERT INTO contactos 
    (usuario_id, nombre, apellido, telefono, email, direccion, notas, foto)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $resultado = $stmt->execute([
        $usuario['id'],
        $nombre,
        $apellido,
        $telefono,
        $email,
        $direccion,
        $notas,
        $foto
    ]);

    if ($resultado) {
        echo json_encode([
            "success" => true,
            "message" => "Contacto creado exitosamente"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "No se pudo insertar el contacto en la base de datos"
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error interno en la base de datos"
    ]);
}
?>