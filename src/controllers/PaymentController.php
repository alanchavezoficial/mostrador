<?php
// src/controllers/PaymentController.php

class PaymentController
{
    public function index(): void
    {
        // Implementación pendiente
        error_log('[PaymentController::index] Método no implementado');
        http_response_code(501); // Not Implemented
        echo 'Funcionalidad en construcción';
    }
    public function crear(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log('[PaymentController::crear] Método incorrecto: ' . $_SERVER['REQUEST_METHOD']);
            http_response_code(405);
            echo 'Método no permitido';
            return;
        }

        // ... resto de tu lógica actual sin cambios
    }
    public function editar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log('[PaymentController::editar] Método incorrecto: ' . $_SERVER['REQUEST_METHOD']);
            http_response_code(405);
            echo 'Método no permitido';
            return;
        }
    }
    public function eliminar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            error_log('[PaymentController::eliminar] Método incorrecto: ' . $_SERVER['REQUEST_METHOD']);
            http_response_code(405);
            echo 'Método no permitido';
            return;
        }

        // ... resto de tu lógica actual sin cambios
    }
}
