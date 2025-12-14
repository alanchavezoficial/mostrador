<?php
/**
 * Security Headers
 * Configura headers HTTP de seguridad para proteger la aplicación
 */

// Prevenir que la página se muestre en iframes (protección contra clickjacking)
header("X-Frame-Options: SAMEORIGIN");

// Prevenir MIME type sniffing
header("X-Content-Type-Options: nosniff");

// Habilitar protección XSS del navegador
header("X-XSS-Protection: 1; mode=block");

// Política de referencia (no enviar información sensible en el referrer)
header("Referrer-Policy: strict-origin-when-cross-origin");

// Política de permisos (Feature Policy)
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");

// Content Security Policy (CSP) - básico
// Ajusta según necesites más recursos externos
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://translate.googleapis.com; style-src 'self' 'unsafe-inline' https://www.gstatic.com https://translate.googleapis.com; img-src 'self' data: https://www.gstatic.com; font-src 'self' data: https://www.gstatic.com; connect-src 'self' https://cdn.jsdelivr.net https://translate.googleapis.com;");

// Forzar HTTPS en producción (solo si tienes certificado SSL)
// Descomenta la siguiente línea cuando tengas HTTPS configurado:
// header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
