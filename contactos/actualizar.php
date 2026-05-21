<?php
// cors corregido
require_once "../config/cors.php";
header("Content-Type: application/json; charset=UTF-8");
error_reporting(0);

require_once "../config/database.php";
// 1. IMPORTANTE: Traer la función que protege la ruta y valida el token
require_once "../config/auth.php"; 

try {
    // 2. PROTECCIÓN DE RUTA: Validamos el token y obtenemos el usuario autenticado
    // Ya no dependemos de parámetros por la URL ni corremos riesgo de suplantación.
    $usuarioAutenticado = verificarToken(); 
    $usuarioId = $usuarioAutenticado['id']; // Obtenemos el ID real y blindado desde el token

    // LEER DATOS 
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

    // PROCESAR LA IMAGEN POR SI EDITARON LA FOTO (Opcional)
    // Jalamos la foto actual por si no se subió una nueva (Doble seguridad: validando id y dueño)
    $stmtFoto = $conn->prepare("SELECT foto FROM contactos WHERE id = ? AND usuario_id = ?");
    $stmtFoto->execute([$contactoId, $usuarioId]);
    $contactoActual = $stmtFoto->fetch(PDO::FETCH_ASSOC);
    
    // Si el contacto no existe o no le pertenece a este usuario, frenamos el proceso
    if (!$contactoActual) {
        echo json_encode([
            "success" => false,
            "message" => "Contacto no encontrado o no tienes permisos para editarlo."
        ]);
        exit;
    }
    
    $foto = $contactoActual['foto'];

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

    // EJECUTAR LA ACTUALIZACIÓN SEGURA
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
        intval($usuarioId) // Seguridad absoluta: solo el dueño real puede aplicar el cambio
    ]);

    // RESPUESTA COMPATIBLE PARA QUE PINIA ACTUALICE EL ESTADO LOCAL
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