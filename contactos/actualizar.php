<?php
// 1. INCLUIMOS EL CORS CORREGIDO (Ya maneja OPTIONS y cabeceras limpias)
require_once "../config/cors.php";
header("Content-Type: application/json; charset=UTF-8");
error_reporting(0);

require_once "../config/database.php";

// 2. RECUPERAR EL ID DEL DUEÑO DESDE LA URL (?usuario_id=X)
$usuarioId = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : null;

if (!$usuarioId) {
    echo json_encode([
        "success" => false,
        "message" => "Falta el ID del usuario autenticado."
    ]);
    exit;
}

// LEER DATOS (Soporta FormData o JSON puro)
$inputData = json_decode(file_get_contents("php://input"), true);

// Capturamos el ID del contacto que se va a editar
$contactoId = $_POST['id'] ?? $inputData['id'] ?? null;

if (!$contactoId) {
    echo json_encode([
        "success" => false,
        "message" => "Falta el ID del contacto a actualizar."
    ]);
    exit;
}

$nombre    = $_POST['nombre'] ?? $inputData['nombre'] ?? "Sin Nombre";
$apellido  = $_POST['apellido'] ?? $inputData['apellido'] ?? "";
$telefono  = $_POST['telefono'] ?? $inputData['telefono'] ?? "";
$email     = $_POST['email'] ?? $inputData['email'] ?? "";
$direccion = $_POST['direccion'] ?? $inputData['direccion'] ?? "";
$notas     = $_POST['notas'] ?? $inputData['notas'] ?? "";

// 3. PROCESAR LA IMAGEN POR SI EDITARON LA FOTO (Opcional)
// Jalamos la foto actual por si no se subió una nueva
$stmtFoto = $conn->prepare("SELECT foto FROM contactos WHERE id = ? AND usuario_id = ?");
$stmtFoto->execute([$contactoId, $usuarioId]);
$contactoActual = $stmtFoto->fetch(PDO::FETCH_ASSOC);
$foto = $contactoActual ? $contactoActual['foto'] : null;

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
    // 4. EJECUTAR LA ACTUALIZACIÓN SEGURA
    $sql = "UPDATE contactos SET 
            nombre = ?, apellido = ?, telefono = ?, email = ?, direccion = ?, notas = ?, foto = ?
            WHERE id = ? AND usuario_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $nombre,
        $apellido,
        $telefono,
        $email,
        $direccion,
        $notas,
        $foto,
        intval($contactoId),
        intval($usuarioId) // Seguridad: solo el dueño puede editarlo
    ]);

    // 5. RESPUESTA COMPATIBLE PARA QUE PINIA ACTUALICE EL ESTADO LOCAL
    echo json_encode([
        "success" => true,
        "message" => "Contacto actualizado con éxito",
        "contact" => [
            "id" => intval($contactoId),
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
        "message" => "Error interno al actualizar el contacto."
    ]);
    exit;
}
?>