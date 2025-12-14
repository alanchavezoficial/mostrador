<?php
define('BASE_URL', '/mostrador/');
require_once __DIR__ . '/../src/core/db.php';
function db() {
    global $conn;
    return $conn;
}

class Config {
  public static function get($clave) {
    static $cache = [];

    if (isset($cache[$clave])) {
      return $cache[$clave];
    }

    $conn = db();
    // Prepara consulta
    $stmt = $conn->prepare("SELECT valor FROM settings WHERE clave = ? LIMIT 1");
    if (!$stmt) return null;

    $stmt->bind_param("s", $clave);
    $stmt->execute();
    $stmt->bind_result($valor);

    if ($stmt->fetch()) {
      $cache[$clave] = $valor; // guarda en cache
      return $valor;
    }

    return null; // clave no encontrada
  }
}
