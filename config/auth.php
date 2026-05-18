
<?php
require_once "database.php";

function verificarToken() {

    $headers = [];

    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $headers['Authorization'] = $_SERVER['HTTP_AUTHORIZATION'];
    } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $headers['Authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }

    // infinityfree no pasa el header Authorization así que se busca manualmente
    if (!isset($headers['Authorization'])) {
        foreach ($_SERVER as $name => $value) {
            if ($name === 'HTTP_AUTHORIZATION') {
                $headers['Authorization'] = $value;
            }
        }
    }

    if (!isset($headers['Authorization'])) {
        echo json_encode([
            "success" => false,
            "message" => "Token requerido"
        ]);
        exit;
    }

    $token = str_replace('Bearer ', '', $headers['Authorization']);

    global $conn;

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

    if (strtotime($usuario['token_expiracion']) < time()) {
        echo json_encode([
            "success" => false,
            "message" => "Token expirado"
        ]);
        exit;
    }

    return $usuario;
}
?>


