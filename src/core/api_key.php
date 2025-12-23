<?php
// api_key.php - utilidades para API Key y logging de uso

function get_user_api_key($user_id, $conn) {
    $stmt = $conn->prepare("SELECT api_key FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $api_key = null;
    $stmt->bind_result($api_key);
    if ($stmt->fetch()) {
        return $api_key;
    }
    return null;
}

function validate_api_key($key, $conn) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE api_key = ? LIMIT 1");
    if (!$stmt) {
        error_log('[API_KEY] SQL prepare failed: ' . $conn->error);
        if (defined('API_MODE') && API_MODE) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'SQL error: ' . $conn->error]);
            exit;
        }
        return null;
    }
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $user_id = null;
    $stmt->bind_result($user_id);
    if ($stmt->fetch()) {
        return ['user_id' => $user_id];
    }
    return null;
}

function log_api_key_usage($user_id, $endpoint, $ip, $conn) {
    error_log("DEBUG log_api_key_usage: user_id=$user_id, endpoint=$endpoint, ip=$ip");
    $stmt = $conn->prepare("INSERT INTO api_key_usage (user_id, endpoint, ip, used_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param('iss', $user_id, $endpoint, $ip);
    $stmt->execute();
    if ($stmt->affected_rows < 1) {
        error_log("ERROR: No se insertó log de uso de API Key");
    }
}
