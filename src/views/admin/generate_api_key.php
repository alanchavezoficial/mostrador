<?php
require_once __DIR__ . '/../../core/auth.php';
if (!function_exists('generate_api_key')) {
    function generate_api_key() { return bin2hex(random_bytes(32)); }
}
require_once __DIR__ . '/../../core/db.php';

session_start();
echo '<pre>DEBUG SESSION: ' . print_r($_SESSION, true) . '</pre>';
if (!isset($_SESSION['user_id']) || !in_array(strtolower($_SESSION['role'] ?? ''), ['owner', 'dueno'], true)) {
    http_response_code(403);
    exit('No autorizado. Solo el dueño puede generar keys.');
}

$conn = db();
$user_id = intval($_GET['user_id'] ?? 0);
if (!$user_id) {
    exit('Falta user_id');
}

// Generar y asignar nueva key
$api_key = generate_api_key();
$stmt = $conn->prepare("UPDATE users SET api_key = ? WHERE id = ?");
$stmt->bind_param('si', $api_key, $user_id);
$stmt->execute();

if ($stmt->affected_rows) {
    echo "API Key generada para usuario $user_id: <br><code>$api_key</code>";
} else {
    echo "No se pudo asignar la key (¿usuario inexistente?)";
}
