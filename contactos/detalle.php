<?php
require_once "../config/database.php";
require_once "../config/auth.php";

$usuario = verificarToken();

$id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM contactos WHERE id=? AND usuario_id=?");
$stmt->execute([$id, $usuario['id']]);

echo json_encode([
    "success" => true,
    "data" => $stmt->fetch(PDO::FETCH_ASSOC)
]);
?>