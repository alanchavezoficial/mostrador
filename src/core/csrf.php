<?php
// CSRF helper functions
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Obtain or create the CSRF token for the current session.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Hidden input field for embedding the CSRF token in HTML forms.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Validate a provided CSRF token against the session token.
 */
function csrf_validate(?string $token): bool
{
    if (empty($_SESSION['csrf_token']) || $token === null) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Enforce CSRF protection for incoming POST requests.
 */
function csrf_require(): void
{
    $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
    if (!csrf_validate($token)) {
        http_response_code(400);
        exit('CSRF token inválido.');
    }
}