<?php
if (!function_exists('generate_api_key')) {
    function generate_api_key() {
        return bin2hex(random_bytes(32));
    }
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['user_id'])) {
    if (defined('API_MODE') && API_MODE) {
        if (!headers_sent()) header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['error' => 'No autenticado']);
        exit;
    } else {
        header('Location: ' . BASE_URL . 'login');
        exit;
    }
}
// Verificar que sea admin o Dueno
$allowedRoles = ['admin', 'Dueno'];
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowedRoles)) {
    if (defined('API_MODE') && API_MODE) {
        if (!headers_sent()) header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode(['error' => 'No autorizado']);
        exit;
    } else {
        header('Location: ' . BASE_URL . '?error=Acceso+denegado.+No+tienes+permisos+para+esta+sección.');
        exit;
    }
}
