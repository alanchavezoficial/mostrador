<?php
/**
 * Front Controller - Punto de entrada principal
 * Redirige todas las peticiones a public/index.php
 */

// Cargar headers de seguridad
require_once __DIR__ . '/src/core/security-headers.php';

// Cargar configuración segura de sesiones
require_once __DIR__ . '/src/core/session.php';

// Cargar el router principal
require_once __DIR__ . '/src/core/router.php';
