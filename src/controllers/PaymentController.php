<?php
// src/controllers/PaymentController.php

class PaymentController
{
    public function index(): void
    {
        // Implementación pendiente
        echo "<pre>Listado de categorías en construcción...</pre>";
    }
    public function crear(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo "<pre>El método crear() requiere solicitud POST.</pre>";
            return;
        }

        // ... resto de tu lógica actual sin cambios
    }
    public function editar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo "<pre>El método editar() requiere solicitud POST.</pre>";
            return;
        }
    }
    public function eliminar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            echo "<pre>El método eliminar() requiere solicitud GET con parámetro id.</pre>";
            return;
        }

        // ... resto de tu lógica actual sin cambios
    }
}
