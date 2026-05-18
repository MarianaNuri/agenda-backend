<?php
// Permitir solicitudes desde cualquier origen (GitHub Pages)
header("Access-Control-Allow-Origin: *");

// Métodos permitidos
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

// Headers permitidos
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Manejar preflight (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}
?>