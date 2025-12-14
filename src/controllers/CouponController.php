<?php
// src/controllers/CouponController.php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../core/auth.php';

class CouponController
{
    private mysqli $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public function index(): void
    {
        $view = $_GET['view'] ?? 'table';
        $msg = $_GET['msg'] ?? '';
        $isAjax = ($_GET['ajax'] ?? '') === '1';

        $message = match ($msg) {
            'created' => '✅ Cupón creado correctamente.',
            'edited'  => '✏️ Cupón editado correctamente.',
            'deleted' => '🗑️ Cupón eliminado correctamente.',
            default   => ''
        };

        switch ($view) {
            case 'register':
                $data = ['message' => $message];
                $vista = 'cupones/register';
                break;

            case 'table':
            default:
                $res = $this->conn->query("SELECT * FROM coupons ORDER BY created_at DESC");
                $coupons = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
                $data = ['coupons' => $coupons, 'message' => $message];
                $vista = 'cupones/table';
                break;
        }

        if ($isAjax) {
            View::renderPartial($vista, $data);
        } else {
            View::render($vista, $data, 'admin');
        }
    }

    public function couponCreate(): void
    {
        csrf_require();
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $type = $_POST['discount_type'] === 'fixed' ? 'fixed' : 'percentage';
        $value = (float)($_POST['discount_value'] ?? 0);
        $maxUses = $_POST['max_uses'] === '' ? null : (int)$_POST['max_uses'];
        $expiry = $_POST['expiry_date'] ?? null;
        $minOrder = $_POST['minimum_order'] === '' ? 0 : (float)$_POST['minimum_order'];
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($code === '' || $value <= 0) {
            http_response_code(400);
            die('Código y valor son obligatorios');
        }

        $stmt = $this->conn->prepare("INSERT INTO coupons (code, discount_type, discount_value, max_uses, expiry_date, minimum_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssdisdi', $code, $type, $value, $maxUses, $expiry, $minOrder, $isActive);
        $stmt->execute();

        if ($_GET['ajax'] ?? '' === '1') {
            $_GET['view'] = 'table';
            $_GET['msg'] = 'created';
            $_GET['ajax'] = '1';
            $this->index();
            exit;
        }

        header('Location: ' . BASE_URL . 'admin/cupones?msg=created');
    }

    public function couponEdit(): void
    {
        csrf_require();
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            die('ID inválido');
        }
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $type = $_POST['discount_type'] === 'fixed' ? 'fixed' : 'percentage';
        $value = (float)($_POST['discount_value'] ?? 0);
        $maxUses = $_POST['max_uses'] === '' ? null : (int)$_POST['max_uses'];
        $expiry = $_POST['expiry_date'] ?? null;
        $minOrder = $_POST['minimum_order'] === '' ? 0 : (float)$_POST['minimum_order'];
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($code === '' || $value <= 0) {
            http_response_code(400);
            die('Código y valor son obligatorios');
        }

        $stmt = $this->conn->prepare("UPDATE coupons SET code=?, discount_type=?, discount_value=?, max_uses=?, expiry_date=?, minimum_order=?, is_active=? WHERE id = ?");
        $stmt->bind_param('ssdisdii', $code, $type, $value, $maxUses, $expiry, $minOrder, $isActive, $id);
        $stmt->execute();

        if ($_GET['ajax'] ?? '' === '1') {
            $_GET['view'] = 'table';
            $_GET['msg'] = 'edited';
            $_GET['ajax'] = '1';
            $this->index();
            exit;
        }

        header('Location: ' . BASE_URL . 'admin/cupones?msg=edited');
    }

    public function couponDelete(): void
    {
        csrf_require();
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            die('ID inválido');
        }
        $stmt = $this->conn->prepare("DELETE FROM coupons WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['ajax'] ?? '') === '1') {
            $_GET['view'] = 'table';
            $_GET['msg'] = 'deleted';
            $_GET['ajax'] = '1';
            $this->index();
            exit;
        }

        header('Location: ' . BASE_URL . 'admin/cupones?msg=deleted');
    }

    public function couponEditForm(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            die('ID inválido');
        }

        $stmt = $this->conn->prepare("SELECT * FROM coupons WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $coupon = $stmt->get_result()->fetch_assoc();
        if (!$coupon) {
            http_response_code(404);
            die('Cupón no encontrado');
        }

        View::renderPartial('cupones/edit', ['coupon' => $coupon]);
    }
}
