<?php
require_once "../config/cors.php";
header("Content-Type: application/json; charset=UTF-8");
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