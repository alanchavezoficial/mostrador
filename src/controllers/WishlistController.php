<?php
// src/controllers/WishlistController.php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/View.php';

class WishlistController
{
    private mysqli $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function requireUser(): ?int
    {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'auth_required']);
            return null;
        }
        return (int)$_SESSION['user_id'];
    }

    public function toggle(): void
    {
        header('Content-Type: application/json');
        $userId = $this->requireUser();
        if (!$userId) return;

        $productId = (int)($_POST['product_id'] ?? 0);
        if ($productId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'invalid_product']);
            return;
        }

        $existsStmt = $this->conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
        $existsStmt->bind_param('ii', $userId, $productId);
        $existsStmt->execute();
        $exists = $existsStmt->get_result()->fetch_assoc();

        if ($exists) {
            $del = $this->conn->prepare("DELETE FROM wishlist WHERE id = ?");
            $del->bind_param('i', $exists['id']);
            $del->execute();
            echo json_encode(['success' => true, 'state' => 'removed']);
            return;
        }

        $ins = $this->conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $ins->bind_param('ii', $userId, $productId);
        $ok = $ins->execute();
        echo json_encode(['success' => $ok, 'state' => 'added']);
    }

    public function view(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'login');
            return;
        }
        $userId = (int)$_SESSION['user_id'];
        $stmt = $this->conn->prepare("SELECT p.* FROM wishlist w JOIN products p ON w.product_id = p.id WHERE w.user_id = ?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        View::render('wishlist/index', ['products' => $products, 'page_css' => 'product.css'], 'public');
    }
}
