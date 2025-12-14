<?php
/**
 * Session Security Configuration
 * Configura las sesiones de forma segura
 */

// Prevenir que se inicie sesión si ya está iniciada
if (session_status() === PHP_SESSION_NONE) {
    // Configuración de cookies seguras
    ini_set('session.cookie_httponly', '1'); // Prevenir acceso a cookies desde JavaScript
    ini_set('session.cookie_samesite', 'Strict'); // Protección CSRF adicional
    ini_set('session.use_strict_mode', '1'); // Solo aceptar IDs de sesión generados por el servidor
    
    // Si tienes HTTPS, descomenta estas líneas:
    // ini_set('session.cookie_secure', '1'); // Solo enviar cookies por HTTPS
    
    // Configuración adicional de seguridad
    ini_set('session.use_only_cookies', '1'); // No permitir session ID en URL
    ini_set('session.use_trans_sid', '0'); // Desactivar session ID en URLs
    
    session_start();
}

/**
 * Regenera el ID de sesión para prevenir session fixation
 * Llamar después de un login exitoso o cambio de privilegios
 */
function session_regenerate_secure(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true); // true = eliminar sesión antigua
    }
}
