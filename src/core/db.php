<?php
// Conexión a base de datos con variables de entorno y manejo de errores

// Cargar variables de entorno desde .env
$envFile = __DIR__ . '/../../config/.env';
if (file_exists($envFile)) {
    $envVars = parse_ini_file($envFile);
    if (is_array($envVars)) {
        foreach ($envVars as $key => $value) {
            putenv("$key=$value");
        }
    } else {
        error_log('Error parsing .env file: ' . $envFile);
    }
}

$host     = getenv('DB_HOST') ?: 'localhost';
$user     = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';
$database = getenv('DB_NAME') ?: 'tienda_props';
$port     = getenv('DB_PORT') ?: 3306;

$conn = @new mysqli($host, $user, $password, $database, $port);
if ($conn->connect_errno) {
    error_log('DB connection error: ' . $conn->connect_error);
    http_response_code(500);
    exit('Error de conexión a la base de datos.');
}


$conn->set_charset('utf8mb4');

global $conn;
return $conn;