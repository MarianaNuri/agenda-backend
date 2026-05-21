<?php

require_once "../config/cors.php"; // CABECERAS DE CORS 

// Responder inmediatamente a la petición de control OPTIONS
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
    // PROTECCIÓN DE RUTA: Validamos el token y obtenemos el usuario autenticado
    // Ya no usamos $_GET['usuario_id'] ni respaldos manuales. El token manda.
    $usuarioAutenticado = verificarToken(); 
    $usuarioId = $usuarioAutenticado['id']; 

    // LEER DATOS (Soporta JSON puro, FormData, POST plano o URL)
    $inputData = json_decode(file_get_contents("php://input"), true);

    $nombre = $_POST['nombre'] ?? $inputData['nombre'] ?? $_GET['nombre'] ?? "Contacto Nuevo";
    $telefono = $_POST['telefono'] ?? $inputData['telefono'] ?? $_GET['telefono'] ?? "0000000000";
    $apellido = $_POST['apellido'] ?? $inputData['apellido'] ?? $_GET['apellido'] ?? "";
    $email = $_POST['email'] ?? $inputData['email'] ?? $_GET['email'] ?? "";
    $direccion = $_POST['direccion'] ?? $inputData['direccion'] ?? $_GET['direccion'] ?? "";
    $notas = $_POST['notas'] ?? $inputData['notas'] ?? $_GET['notas'] ?? "";

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
    $sql = "INSERT INTO contactos 
    (usuario_id, nombre, apellido, telefono, email, direccion, notas, foto)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $usuarioId, // Se asigna de forma segura al ID extraído del Token
        $nombre,
        $apellido,
        $telefono,
        $email,
        $direccion,
        $notas,
        $foto
    ]);

    $idGenerado = $conn->lastInsertId();

    // RESPUESTA COMPATIBLE CON PINIA (Devuelve el contacto recién creado con su ID para actualizar el estado en el Front)
    echo json_encode([
        "success" => true,
        "message" => "Contacto creado con éxito",
        "id" => intval($idGenerado),
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
    exit;

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error al crear el contacto"
    ]);
    exit;
}
?>