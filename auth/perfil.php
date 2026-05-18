// aqui va el perfil del usuario, con su informacion y sus contactos, etc.

<?php

header("Content-Type: application/json");

require_once "../config/database.php";
require_once "../config/cors.php";
require_once "../config/auth.php";

// Validar token
$usuario = verificarToken();

echo json_encode([
    "success" => true,
    "user" => [
        "id" => $usuario['id'],
        "nombre_de_usuario" => $usuario['nombre_de_usuario'],
        "foto" => $usuario['foto']
    ]
]);

?>