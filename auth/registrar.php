<?php

header("Content-Type: application/json");

require_once "../config/database.php";

$nombre = $_POST['nombre_de_usuario'] ?? null;
$password = $_POST['password'] ?? null;

if (
    !$nombre ||
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
        password,
        foto
    )
    VALUES (?, ?, ?)
");

$stmt->execute([
    $nombre,
    $passwordHash,
    $foto
]);

echo json_encode([
    "success" => true,
    "message" => "Usuario registrado correctamente"
]);

?>