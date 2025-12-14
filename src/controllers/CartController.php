<?php
// src/controllers/CartController.php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../core/db.php';

class CartController
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

    private function getCartItems(int $userId): array
    {
        $sql = "SELECT c.id as cart_id, c.product_id, c.quantity, p.nombre, p.precio, p.imagen
                FROM cart c
                JOIN products p ON c.product_id = p.id
                WHERE c.user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        $items = [];
        while ($row = $res->fetch_assoc()) {
            $row['subtotal'] = (float)$row['precio'] * (int)$row['quantity'];
            $items[] = $row;
        }
        return $items;
    }

    public function view(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = $_SESSION['user_id'] ?? null;
        $items = $userId ? $this->getCartItems((int)$userId) : [];
        $total = array_sum(array_column($items, 'subtotal'));
        $data = [
            'items' => $items,
            'total' => $total,
            'page_css' => 'cart.css',
            'page_js' => 'cart.js'
        ];
        require_once __DIR__ . '/../core/View.php';
        View::render('cart/index', $data, 'public');
    }

    public function add(): void
    {
        header('Content-Type: application/json');
        $userId = $this->requireUser();
        if (!$userId) return;
        $productId = (int)($_POST['product_id'] ?? 0);
        $qty = max(1, (int)($_POST['quantity'] ?? 1));
        if ($productId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'invalid_product']);
            return;
        }
        // upsert
        $sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iii', $userId, $productId, $qty);
        $ok = $stmt->execute();
        echo json_encode(['success' => $ok]);
    }

    public function update(): void
    {
        header('Content-Type: application/json');
        $userId = $this->requireUser();
        if (!$userId) return;
        $productId = (int)($_POST['product_id'] ?? 0);
        $qty = max(0, (int)($_POST['quantity'] ?? 0));
        if ($productId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'invalid_product']);
            return;
        }
        if ($qty === 0) {
            $stmt = $this->conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param('ii', $userId, $productId);
            $ok = $stmt->execute();
            echo json_encode(['success' => $ok]);
            return;
        }
        $stmt = $this->conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param('iii', $qty, $userId, $productId);
        $ok = $stmt->execute();
        echo json_encode(['success' => $ok]);
    }

    public function remove(): void
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
        $stmt = $this->conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param('ii', $userId, $productId);
        $ok = $stmt->execute();
        echo json_encode(['success' => $ok]);
    }
}
