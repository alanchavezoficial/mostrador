<?php
// src/controllers/ReviewController.php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../core/db.php';

class ReviewController
{
    private mysqli $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    private function requireUser(): ?int
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'login');
            return null;
        }
        return (int)$_SESSION['user_id'];
    }

    public function add(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL);
            return;
        }
        $userId = $this->requireUser();
        if (!$userId) return;

        $productId = (int)($_POST['product_id'] ?? 0);
        $rating = max(1, min(5, (int)($_POST['rating'] ?? 0)));
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        if ($productId <= 0 || $content === '') {
            http_response_code(400);
            echo 'Datos inválidos';
            return;
        }

        $stmt = $this->conn->prepare("INSERT INTO reviews (product_id, user_id, rating, title, content, is_verified_purchase, is_visible) VALUES (?, ?, ?, ?, ?, 0, 1)");
        $stmt->bind_param('iiiss', $productId, $userId, $rating, $title, $content);
        $stmt->execute();

        header('Location: ' . BASE_URL . 'product?id=' . $productId . '#reviews');
    }
}
