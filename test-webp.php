<?php
header('Content-Type: application/json');

// Test simple para verificar funcionalidad
$response = [
    'php_version' => phpversion(),
    'webp_support' => function_exists('imagewebp'),
    'gd_version' => function_exists('gd_info') ? gd_info()['GD Version'] : 'No GD',
    'temp_dir_exists' => is_dir(__DIR__ . '/assets/images/temp/'),
    'temp_dir_writable' => is_writable(__DIR__ . '/assets/images/temp/'),
    'server_method' => $_SERVER['REQUEST_METHOD'],
    'timestamp' => date('Y-m-d H:i:s')
];

echo json_encode($response, JSON_PRETTY_PRINT);
?>