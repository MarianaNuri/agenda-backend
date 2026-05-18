<?php
require_once "../config/database.php";
require_once "../config/auth.php";
require_once "../config/cors.php";

$usuario = verificarToken();

$nombre = $_POST['nombre'] ?? null;
$telefono = $_POST['telefono'] ?? null;

if (!$nombre || !$telefono) {
    echo json_encode([
        "success" => false,
        "message" => "Campos obligatorios faltantes"
    ]);
    exit;
}

$foto = null;

if (isset($_FILES['foto'])) {
    $nombreArchivo = time() . "_" . $_FILES['foto']['name'];
    move_uploaded_file($_FILES['foto']['tmp_name'], "../uploads/contactos/" . $nombreArchivo);
    $foto = $nombreArchivo;
}

$sql = "INSERT INTO contactos 
(usuario_id, nombre, apellido, telefono, email, direccion, notas, foto)
VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->execute([
    $usuario['id'],
    $nombre,
    $_POST['apellido'] ?? null,
    $telefono,
    $_POST['email'] ?? null,
    $_POST['direccion'] ?? null,
    $_POST['notas'] ?? null,
    $foto
]);

echo json_encode([
    "success" => true,
    "message" => "Contacto creado"
]);
?>