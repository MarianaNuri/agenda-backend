// aqui va logout del usuario, que básicamente es eliminar el token del cliente, ya que el token es stateless y no se guarda en el servidor.

<?php
require_once "../config/cors.php";
header("Content-Type: application/json");
require_once "../config/database.php";
require_once "../config/auth.php";

// Validar token
$usuario = verificarToken();

// Eliminar token
$stmt = $conn->prepare("
    UPDATE usuarios
    SET token = NULL, token_expiracion = NULL
    WHERE id = ?
");

$stmt->execute([$usuario['id']]);

echo json_encode([
    "success" => true,
    "message" => "Sesión cerrada correctamente"
]);

?>