<?php
require_once "../config/cors.php";
header("Content-Type: application/json");
require_once "../config/database.php";

$nombre = $_POST['nombre_de_usuario'] ?? null;
$password = $_POST['password'] ?? null;

if(!$nombre || !$password){

    echo json_encode([
        "success" => false,
        "message" => "Campos requeridos"
    ]);

    exit;
}

$query = "SELECT * FROM usuarios WHERE nombre_de_usuario = ?";

$stmt = $conn->prepare($query);

$stmt->execute([$nombre]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if(
    !$usuario ||
    !password_verify($password, $usuario['password'])
){

    echo json_encode([
        "success" => false,
        "message" => "Credenciales inválidas"
    ]);

    exit;
}

$token = bin2hex(random_bytes(32));

$expiracion = date('Y-m-d H:i:s', strtotime('+1 day'));

$update = "
UPDATE usuarios
SET token = ?, token_expiracion = ?
WHERE id = ?
";

$stmt = $conn->prepare($update);

$stmt->execute([
    $token,
    $expiracion,
    $usuario['id']
]);

echo json_encode([
    "success" => true,
    "token" => $token,
    "usuario" => [
        "id" => $usuario['id'],
        "nombre_de_usuario" => $usuario['nombre_de_usuario'],
        "foto" => $usuario['foto']
    ]
]);