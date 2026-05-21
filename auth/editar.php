<?php
// INCLUIMOS EL CORS CENTRALIZADO (Ya maneja OPTIONS y las cabeceras limpias)
require_once "../config/cors.php";

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit;
}

header("Content-Type: application/json; charset=UTF-8");
error_reporting(0);

require_once "../config/database.php";
// 1. IMPORTANTE: Traer la función que protege la ruta y valida el token
require_once "../config/auth.php"; 

try {
    // 2. PROTECCIÓN DE RUTA: Validamos el token y obtenemos el usuario autenticado
    // Ya no usamos $_GET['id'] ni $_GET['usuario_id']. El token identifica al usuario de forma segura.
    $usuarioAutenticado = verificarToken(); 
    $usuarioId = intval($usuarioAutenticado['id']);

    // LEER DATOS 
    $inputData = json_decode(file_get_contents("php://input"), true);

    $nombre = $_POST['nombre_de_usuario'] ?? $inputData['nombre_de_usuario'] ?? null;
    $password = $_POST['password'] ?? $inputData['password'] ?? null; // Opcional por si cambian contraseña

    if (!$nombre) {
        echo json_encode([
            "success" => false,
            "message" => "El nombre de usuario es requerido"
        ]);
        exit;
    }

    $nombre = htmlspecialchars(trim($nombre));

    // OBTENER LOS DATOS ACTUALES (Para conservar la foto o contraseña si no se envían cambios)
    $stmtActual = $conn->prepare("SELECT foto, password FROM usuarios WHERE id = ?");
    $stmtActual->execute([$usuarioId]);
    $usuarioExistente = $stmtActual->fetch(PDO::FETCH_ASSOC);

    if (!$usuarioExistente) {
        echo json_encode([
            "success" => false,
            "message" => "Usuario no encontrado"
        ]);
        exit;
    }

    $foto = $usuarioExistente['foto'];
    $passwordHash = $usuarioExistente['password'];

    // PROCESAR LA IMAGEN SI SUBIERON UNA NUEVA FOTO
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $permitidos = ['image/jpeg', 'image/png', 'image/webp'];

        if (in_array($_FILES['foto']['type'], $permitidos)) {
            $directorioDestino = "../uploads/usuarios/";
            if (!file_exists($directorioDestino)) {
                mkdir($directorioDestino, 0777, true);
            }

            $nombreArchivo = time() . "_" . basename($_FILES['foto']['name']);
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $directorioDestino . $nombreArchivo)) {
                $foto = $nombreArchivo;
            }
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Formato de imagen no permitido (solo JPEG, PNG y WEBP)"
            ]);
            exit;
        }
    }

    // PROCESAR NUEVA CONTRASEÑA (Solo si el usuario escribió algo en ese campo)
    if (!empty($password)) {
        // Encriptamos utilizando el algoritmo estándar compatible con password_verify()
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    }

    // ACTUALIZAR EN LA BASE DE DATOS
    $sql = "UPDATE usuarios 
            SET nombre_de_usuario = ?, foto = ?, password = ? 
            WHERE id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $nombre,
        $foto,
        $passwordHash,
        $usuarioId
    ]);

    // Construimos la URL completa de la foto para el Front-End
    $fotoUrl = $foto ? "https://sistemas-agenda.alwaysdata.net/api/uploads/usuarios/" . $foto : null;

    // RESPUESTA COMPATIBLE CON EL CONTRATO (Devuelve las claves necesarias)
    echo json_encode([
        "success" => true,
        "message" => "Operación realizada correctamente",
        "usuario" => [
            "id" => $usuarioId,
            "nombre_de_usuario" => $nombre,
            "foto" => $fotoUrl
        ]
        "user" => [ 
            "id" => $usuarioId,
            "nombre_de_usuario" => $nombre,
            "foto" => $fotoUrl
        ]
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error interno en el servidor al actualizar"
    ]);
    exit;
}
?>