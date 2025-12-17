<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'login');
    exit;
}
// Verificar que sea admin o Dueno
$allowedRoles = ['admin', 'Dueno'];
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowedRoles)) {
    header('Location: ' . BASE_URL . '?error=Acceso+denegado.+No+tienes+permisos+para+esta+sección.');
    exit;
}
