<?php
require_once "database.php";

function verificarToken() {

    //Obtener headers (compatible con MAMP)
    $headers = [];

    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $headers['Authorization'] = $_SERVER['HTTP_AUTHORIZATION'];
    } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $headers['Authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    } elseif (function_exists('apache_request_headers')) {
        $apacheHeaders = apache_request_headers();
        if (isset($apacheHeaders['Authorization'])) {
            $headers['Authorization'] = $apacheHeaders['Authorization'];
        }
    }



    //Si no hay token
   /* if (!isset($headers['Authorization'])) {
        echo json_encode([
            "success" => false,
            "message" => "Token requerido"
        ]);
        exit;
    }*/

                //TEMPORAL: MAMP sin token :)
    if (!isset($headers['Authorization'])) {
    $headers['Authorization'] = "Bearer TOKEN123";
} // esto hay que cambiarlo por el código de arriba para producción

    // limpiar token
    $token = str_replace('Bearer ', '', $headers['Authorization']);

    global $conn;

    //  Buscar usuario por token
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE token = ?");
    $stmt->execute([$token]);

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    
    if (!$usuario) {
        echo json_encode([
            "success" => false,
            "message" => "Token inválido"
        ]);
        exit;
    }

    // Verificar expiración
    if (strtotime($usuario['token_expiracion']) < time()) {
        echo json_encode([
            "success" => false,
            "message" => "Token expirado"
        ]);
        exit;
    }

    // Retornar usuario autenticado
    return $usuario;
}
?>


