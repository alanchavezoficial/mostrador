<?php
// src/controllers/OrderController.php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/View.php';

class OrderController
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
            header('Location: ' . BASE_URL . 'login');
            return null;
        }
        return (int)$_SESSION['user_id'];
    }

    private function getCartItems(int $userId): array
    {
        $sql = "SELECT c.product_id, c.quantity, p.nombre, p.precio, p.imagen
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

    private function validateCoupon(?string $code, float $subtotal): array
    {
        $code = strtoupper(trim((string)$code));
        if ($code === '') {
            return ['discount' => 0.0, 'coupon' => null, 'message' => null];
        }

        $stmt = $this->conn->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1");
        $stmt->bind_param('s', $code);
        $stmt->execute();
        $coupon = $stmt->get_result()->fetch_assoc();
        if (!$coupon) {
            return ['discount' => 0.0, 'coupon' => null, 'message' => 'Cupón inválido'];
        }
        if (!empty($coupon['expiry_date']) && strtotime($coupon['expiry_date']) < time()) {
            return ['discount' => 0.0, 'coupon' => null, 'message' => 'Cupón vencido'];
        }
        if (!empty($coupon['max_uses']) && (int)$coupon['current_uses'] >= (int)$coupon['max_uses']) {
            return ['discount' => 0.0, 'coupon' => null, 'message' => 'Cupón sin stock'];
        }
        if (!empty($coupon['minimum_order']) && $subtotal < (float)$coupon['minimum_order']) {
            return ['discount' => 0.0, 'coupon' => null, 'message' => 'No cumple monto mínimo'];
        }

        $discount = 0.0;
        if ($coupon['discount_type'] === 'percentage') {
            $discount = $subtotal * ((float)$coupon['discount_value'] / 100);
        } else {
            $discount = (float)$coupon['discount_value'];
        }
        // Cap discount to subtotal
        $discount = min($discount, $subtotal);

        return ['discount' => $discount, 'coupon' => $coupon, 'message' => null];
    }

    public function checkout(): void
    {
        $userId = $this->requireUser();
        if (!$userId) return;

        $items = $this->getCartItems($userId);
        $subtotal = array_sum(array_column($items, 'subtotal'));
        $flashError = $_SESSION['checkout_error'] ?? null;
        $prefillCoupon = $_SESSION['checkout_coupon'] ?? '';
        unset($_SESSION['checkout_error'], $_SESSION['checkout_coupon']);
        $data = [
            'items'     => $items,
            'subtotal'  => $subtotal,
            'tax'       => 0,
            'discount'  => 0,
            'total'     => $subtotal,
            'page_css'  => 'cart.css',
            'flash_error' => $flashError,
            'coupon_code' => $prefillCoupon,
        ];
        View::render('orders/checkout', $data, 'public');
    }

    public function place(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'checkout');
            return;
        }
        $userId = $this->requireUser();
        if (!$userId) return;

        $items = $this->getCartItems($userId);
        if (empty($items)) {
            header('Location: ' . BASE_URL . 'cart');
            return;
        }

        $shipping = trim($_POST['shipping_address'] ?? '');
        $billing  = trim($_POST['billing_address'] ?? '');
        $payment  = trim($_POST['payment_method'] ?? 'transferencia');
        $notes    = trim($_POST['notes'] ?? '');
        $couponCode = trim($_POST['coupon_code'] ?? '');

        if ($shipping === '') {
            http_response_code(400);
            echo 'Falta dirección de envío';
            return;
        }

        $subtotal = array_sum(array_column($items, 'subtotal'));
        $couponData = $this->validateCoupon($couponCode, $subtotal);
        if ($couponData['message']) {
            $_SESSION['checkout_error'] = $couponData['message'];
            $_SESSION['checkout_coupon'] = $couponCode;
            header('Location: ' . BASE_URL . 'checkout');
            return;
        }
        $discount = $couponData['discount'];
        $tax = 0;
        $total = max(0, $subtotal - $discount + $tax);

        $orderNumber = 'ORD' . date('YmdHis') . rand(100, 999);

        $stmt = $this->conn->prepare("INSERT INTO orders
            (order_number, user_id, status, total_amount, subtotal, tax_amount, discount_amount, coupon_code,
             shipping_address, billing_address, payment_method, payment_status, notes)
            VALUES (?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)");
        $stmt->bind_param(
            'siddddsssss',
            $orderNumber,
            $userId,
            $total,
            $subtotal,
            $tax,
            $discount,
            $couponCode,
            $shipping,
            $billing,
            $payment,
            $notes
        );
        $ok = $stmt->execute();
        if (!$ok) {
            http_response_code(500);
            echo 'No se pudo crear la orden';
            return;
        }
        $orderId = $stmt->insert_id;

        $itemStmt = $this->conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($items as $it) {
            $pid   = $it['product_id'];
            $pname = $it['nombre'];
            $price = (float)$it['precio'];
            $qty   = (int)$it['quantity'];
            $sub   = (float)$it['subtotal'];
            $itemStmt->bind_param('iisddd', $orderId, $pid, $pname, $price, $qty, $sub);
            $itemStmt->execute();
        }

        if ($couponData['coupon']) {
            $upd = $this->conn->prepare("UPDATE coupons SET current_uses = current_uses + 1 WHERE id = ?");
            $upd->bind_param('i', $couponData['coupon']['id']);
            $upd->execute();
        }

        $clear = $this->conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $clear->bind_param('i', $userId);
        $clear->execute();

        header('Location: ' . BASE_URL . 'orders?placed=1&order=' . urlencode($orderNumber));
    }

    public function history(): void
    {
        $userId = $this->requireUser();
        if (!$userId) return;
        $stmt = $this->conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        View::render('orders/history', ['orders' => $orders, 'page_css' => 'cart.css'], 'public');
    }

    public function detail(): void
    {
        $userId = $this->requireUser();
        if (!$userId) return;
        $orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($orderId <= 0) {
            http_response_code(400);
            echo 'ID inválido';
            return;
        }

        $order = $this->fetchOrder($orderId, $userId);
        if (!$order) return;
        View::render('orders/show', ['order' => $order, 'page_css' => 'cart.css'], 'public');
    }

    public function invoice(): void
    {
        $userId = $this->requireUser();
        if (!$userId) return;
        $orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $order = $this->fetchOrder($orderId, $userId);
        if (!$order) return;

        header('Content-Type: text/html; charset=UTF-8');
        // Renderiza directamente la plantilla de factura como HTML imprimible (igual que admin)
        $orderLocal = $order;
        $order = $orderLocal;
        require __DIR__ . '/../views/orders/invoice.php';
    }

    public function track(): void
    {
        $orderNumber = $_GET['order'] ?? '';
        if (!$orderNumber) {
            http_response_code(400);
            echo 'Falta número de pedido';
            return;
        }
        $stmt = $this->conn->prepare("SELECT order_number, status, payment_status, created_at, updated_at FROM orders WHERE order_number = ?");
        $stmt->bind_param('s', $orderNumber);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        if (!$order) {
            http_response_code(404);
            echo 'Pedido no encontrado';
            return;
        }
        header('Content-Type: application/json');
        echo json_encode($order);
    }

    private function fetchOrderById(int $orderId): ?array
    {
        $stmt = $this->conn->prepare("SELECT o.*, u.nombre as username FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = ?");
        $stmt->bind_param('i', $orderId);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        if (!$order) {
            http_response_code(404);
            echo 'Pedido no encontrado';
            return null;
        }
        $itemsStmt = $this->conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $itemsStmt->bind_param('i', $orderId);
        $itemsStmt->execute();
        $order['items'] = $itemsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        return $order;
    }

    // ============ ADMIN: Factura/Impresión ============

    public function adminInvoice(): void
    {
        require_once __DIR__ . '/../core/auth.php';
        $orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($orderId <= 0) {
            http_response_code(400);
            echo 'ID inválido';
            return;
        }

        $order = $this->fetchOrderById($orderId);
        if (!$order) return;

        header('Content-Type: text/html; charset=UTF-8');
        // Renderiza directamente la plantilla de factura sin envolver en layout
        $orderLocal = $order; // evita conflicto al usar extract en la vista
        $order = $orderLocal;
        require __DIR__ . '/../views/orders/invoice.php';
    }

    private function fetchOrder(int $orderId, int $userId): ?array
    {
        $stmt = $this->conn->prepare("SELECT o.*, u.nombre as username FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = ? AND o.user_id = ?");
        $stmt->bind_param('ii', $orderId, $userId);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        if (!$order) {
            http_response_code(404);
            echo 'Pedido no encontrado';
            return null;
        }
        $itemsStmt = $this->conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $itemsStmt->bind_param('i', $orderId);
        $itemsStmt->execute();
        $order['items'] = $itemsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        return $order;
    }

    // ============ ADMIN METHODS ============

    public function adminIndex(): void
    {
        require_once __DIR__ . '/../core/auth.php';
        $view = $_GET['view'] ?? 'table';
        $msg = $_GET['msg'] ?? '';
        $isAjax = ($_GET['ajax'] ?? '') === '1';

        $message = match ($msg) {
            'edited' => '✏️ Pedido actualizado correctamente.',
            default  => ''
        };

        switch ($view) {
            case 'table':
            default:
                $stmt = $this->conn->query("SELECT o.*, u.nombre as username FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
                $orders = $stmt ? $stmt->fetch_all(MYSQLI_ASSOC) : [];
                $data = ['orders' => $orders, 'message' => $message];
                $vista = 'pedidos/table';
                break;
        }

        if ($isAjax) {
            View::renderPartial($vista, $data);
        } else {
            View::render($vista, $data, 'admin');
        }
    }

    public function orderEditForm(): void
    {
        require_once __DIR__ . '/../core/auth.php';
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            die('ID inválido');
        }

        $stmt = $this->conn->prepare("SELECT o.*, u.nombre as username FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        if (!$order) {
            http_response_code(404);
            die('Pedido no encontrado');
        }

        $itemsStmt = $this->conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $itemsStmt->bind_param('i', $id);
        $itemsStmt->execute();
        $order['items'] = $itemsStmt->get_result()->fetch_all(MYSQLI_ASSOC);

        View::renderPartial('pedidos/edit', ['order' => $order]);
    }

    public function orderEdit(): void
    {
        require_once __DIR__ . '/../core/auth.php';
        csrf_require();
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            die('ID inválido');
        }

        $status = $_POST['status'] ?? 'pending';
        $paymentStatus = $_POST['payment_status'] ?? 'pending';
        $trackingCode = trim($_POST['tracking_code'] ?? '');
        $shippingStatus = trim($_POST['shipping_status'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        $stmt = $this->conn->prepare("UPDATE orders SET status=?, payment_status=?, tracking_code=?, shipping_status=?, notes=?, updated_at=NOW() WHERE id=?");
        $stmt->bind_param('sssssi', $status, $paymentStatus, $trackingCode, $shippingStatus, $notes, $id);
        
        if (!$stmt->execute()) {
            http_response_code(500);
            die('Error al actualizar: ' . $this->conn->error);
        }

        if ($stmt->affected_rows === 0) {
            http_response_code(404);
            die('Pedido no encontrado o sin cambios');
        }

        if ($_GET['ajax'] ?? '' === '1') {
            $_GET['view'] = 'table';
            $_GET['msg'] = 'edited';
            $_GET['ajax'] = '1';
            $this->adminIndex();
            exit;
        }

        header('Location: ' . BASE_URL . 'admin/pedidos?msg=edited');
    }
}
