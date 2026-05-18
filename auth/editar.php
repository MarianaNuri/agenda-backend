// aqui se puede editar el perfil del usuario, cambiar su nombre, contraseña, etc.

<?php
require_once "../config/cors.php";
header("Content-Type: application/json");
require_once "../config/database.php";
require_once "../config/auth.php";

// Validar token
$usuario = verificarToken();

$nombre = $_POST['nombre_de_usuario'] ?? null;

if (!$nombre) {
    echo json_encode([
        "success" => false,
        "message" => "Nombre requerido"
    ]);
    exit;
}

// Sanitizar
$nombre = htmlspecialchars(trim($nombre));

// Imagen opcional
$foto = $usuario['foto'];

if (isset($_FILES['foto'])) {

    $permitidos = ['image/jpeg', 'image/png', 'image/webp'];

    if (!in_array($_FILES['foto']['type'], $permitidos)) {
        echo json_encode([
            "success" => false,
            "message" => "Formato no permitido"
        ]);
        exit;
    }

    $nombreArchivo = time() . "_" . $_FILES['foto']['name'];

    move_uploaded_file(
        $_FILES['foto']['tmp_name'],
        "../uploads/usuarios/" . $nombreArchivo
    );

    $foto = $nombreArchivo;
}

// Actualizar
$stmt = $conn->prepare("
    UPDATE usuarios
    SET nombre_de_usuario = ?, foto = ?
    WHERE id = ?
");

$stmt->execute([
    $nombre,
    $foto,
    $usuario['id']
]);

echo json_encode([
    "success" => true,
    "message" => "Perfil actualizado",
    "user" => [
        "id" => $usuario['id'],
        "nombre_de_usuario" => $nombre,
        "foto" => $foto
    ]
]);

?>