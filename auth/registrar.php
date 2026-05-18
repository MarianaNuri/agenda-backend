<?php

// Permitir solicitudes desde el origen de tu frontend en GitHub Pages
header("Access-Control-Allow-Origin: https://mariananuri.github.io");

// Permitir los métodos HTTP que uses (POST, GET, OPTIONS, etc.)
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");

// Permitir las cabeceras que el cliente pueda enviar (como Content-Type o Authorization)
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Manejar las peticiones de tipo OPTIONS (Preflight)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// require_once "../config/cors.php";
header("Content-Type: application/json");
require_once "../config/database.php";

// Leer JSON
$input = json_decode(
    file_get_contents("php://input"),
    true
);

// Obtener datos
$nombre = $input['nombre_de_usuario'] ?? null;
$password = $input['password'] ?? null;

// Validar campos
if (
    !$nombre ||
    !$password
) {

    echo json_encode([
        "success" => false,
        "message" => "Campos obligatorios"
    ]);
    exit;
}

// Sanitizar
$nombre = htmlspecialchars(
    trim($nombre)
);

// Verificar usuario existente
$stmt = $conn->prepare("
    SELECT id
    FROM usuarios
    WHERE nombre_de_usuario = ?
");

$stmt->execute([
    $nombre
]);

if ($stmt->fetch()) {

    echo json_encode([
        "success" => false,
        "message" => "Usuario ya existe"
    ]);
    exit;
}

// Encriptar contraseña
$passwordHash = password_hash(
    $password,
    PASSWORD_DEFAULT
);

// Foto opcional
$foto = null;

// Insertar usuario
$stmt = $conn->prepare("
    INSERT INTO usuarios (
        nombre_de_usuario,
        password,
        foto
    )
    VALUES (?, ?, ?)
");

$stmt->execute([
    $nombre,
    $passwordHash,
    $foto
]);

echo json_encode([
    "success" => true,
    "message" => "Usuario registrado correctamente"
]);

?>