<?php
// src/controllers/CatalogController.php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/View.php';

class CatalogController
{
    private mysqli $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    public function index(): void
    {
        $filters = [];
        $params = [];
        $types  = '';

        if (!empty($_GET['q'])) {
            $filters[] = "(p.nombre LIKE CONCAT('%', ?, '%') OR p.descripcion LIKE CONCAT('%', ?, '%'))";
            $params[] = $_GET['q'];
            $params[] = $_GET['q'];
            $types   .= 'ss';
        }
        if (!empty($_GET['categoria'])) {
            $filters[] = "c.nombre = ?";
            $params[] = $_GET['categoria'];
            $types   .= 's';
        }
        if (!empty($_GET['precio_min'])) {
            $filters[] = "p.precio >= ?";
            $params[] = (float)$_GET['precio_min'];
            $types   .= 'd';
        }
        if (!empty($_GET['precio_max'])) {
            $filters[] = "p.precio <= ?";
            $params[] = (float)$_GET['precio_max'];
            $types   .= 'd';
        }

        $order = $_GET['orden'] ?? 'recientes';
        $orderBy = match ($order) {
            'precio_asc'  => 'p.precio ASC',
            'precio_desc' => 'p.precio DESC',
            'nombre'      => 'p.nombre ASC',
            default       => 'p.creado_en DESC'
        };

        $where = $filters ? ('WHERE ' . implode(' AND ', $filters)) : '';
        $sql = "SELECT p.*, c.nombre AS categoria_nombre
                FROM products p
                LEFT JOIN categories c ON p.categoria_id = c.id
                $where
                ORDER BY $orderBy
                LIMIT 50";
        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $catsRes = $this->conn->query("SELECT nombre FROM categories ORDER BY nombre ASC");
        $cats = $catsRes ? $catsRes->fetch_all(MYSQLI_ASSOC) : [];

        View::render('productos/catalog', [
            'products' => $products,
            'cats'     => $cats,
            'page_css' => 'product.css',
            'page_js'  => 'cart.js',
        ], 'public');
    }
}
