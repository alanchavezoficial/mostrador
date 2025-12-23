<?php
define('BASE_URL', '/');
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

    // Asegurar que el resultado esté disponible y evitar warnings si no hay filas
    $stmt->store_result();
    if ($stmt->num_rows === 0) {
      $stmt->close();
      return null; // clave no encontrada
    }

    $valor = null; // inicializar para evitar notices
    $stmt->bind_result($valor);

    if ($stmt->fetch()) {
      $cache[$clave] = $valor; // guarda en cache
      $stmt->close();
      return $valor;
    }

    $stmt->close();
    return null; // clave no encontrada
  }
}
