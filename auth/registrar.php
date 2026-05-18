<?php

header("Content-Type: application/json");

require_once "../config/database.php";
require_once "../config/cors.php";

$input = json_decode(
    file_get_contents("php://input"),
    true
);

$nombre = $input['nombre_de_usuario'] ?? null;
$email = $input['email'] ?? null;
$password = $input['password'] ?? null;

if (
    !$nombre ||
    !$email ||
    !$password
) {

    echo json_encode([
        "success" => false,
        "message" => "Campos obligatorios"
    ]);
    exit;
}

// sanitizar
$nombre = htmlspecialchars(
    trim($nombre)
);
$email = htmlspecialchars(
    trim($email)
);

// verificar si ya existe
$stmt = $conn->prepare("
    SELECT id
    FROM usuarios
    WHERE nombre_de_usuario = ?
");

$stmt->execute([
    $nombre
]);

if ($stmt->fetch()) {

    echo json_encode([
        "success" => false,
        "message" => "Usuario ya existe"
    ]);
    exit;
}

// encriptar password
$passwordHash = password_hash(
    $password,
    PASSWORD_DEFAULT
);

// imagen opcional
$foto = null;

if (isset($_FILES['foto'])) {

    $permitidos = [
        'image/jpeg',
        'image/png',
        'image/webp'
    ];

    if (
        !in_array(
            $_FILES['foto']['type'],
            $permitidos
        )
    ) {

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

$stmt = $conn->prepare("
    INSERT INTO usuarios (
        nombre_de_usuario,
        email,
        password,
        foto
    )
    VALUES (?, ?, ?, ?)
");

$stmt->execute([
    $nombre,
    $email,
    $passwordHash,
    $foto
]);

echo json_encode([
    "success" => true,
    "message" => "Usuario registrado correctamente"
]);

?>