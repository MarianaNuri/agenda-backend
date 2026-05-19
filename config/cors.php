<?php
// 1. Permitir solicitudes desde cualquier origen (Esencial para GitHub Pages)
header("Access-Control-Allow-Origin: *");

// 2. Métodos HTTP permitidos para todo tu CRUD
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

// 3. Headers permitidos en las peticiones
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Al usar el comodín (*), no se permite enviar credenciales nativas (como cookies automáticas).
// Como ustedes manejan todo de forma manual por localStorage, esta línea no se necesita y causa errores si se activa junto al *.
// header("Access-Control-Allow-Credentials: true"); 

// 4. Manejar de forma limpia la petición de control preflight (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit();
}
?>