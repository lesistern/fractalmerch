<?php
header('Content-Type: application/json');

echo json_encode([
    'status' => 'ok',
    'message' => 'Servidor funcionando correctamente',
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => phpversion(),
    'method' => $_SERVER['REQUEST_METHOD']
]);
?>