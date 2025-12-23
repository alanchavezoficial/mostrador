<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../core/permissions.php';
require_once __DIR__ . '/../core/csrf.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

class VendorController
{
    private mysqli $conn;
    private array $user;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Verificar autenticación
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        
        // Verificar que sea vendedor (case-insensitive, trim)
        if (!isset($_SESSION['role']) || strtolower(trim($_SESSION['role'])) !== 'vendedor') {
            header('Location: ' . BASE_URL . '?error=Acceso+denegado.+Esta+sección+es+solo+para+vendedores.');
            exit;
        }
        
        // Obtener datos del usuario
        $stmt = $this->conn->prepare("SELECT id, nombre, email, role FROM users WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $this->user = $result->fetch_assoc();
    }

    /**
     * Dashboard de vendedor con estadísticas de sus productos
     */
    public function dashboard(): void
    {
        $vendorId = $this->user['id'];
        
        // Contar productos del vendedor
        $productCount = $this->conn->query(
            "SELECT COUNT(*) FROM products WHERE user_id = $vendorId"
        )->fetch_row()[0];

        // Contar órdenes de los productos del vendedor
        $orderCount = $this->conn->query(
            "SELECT COUNT(DISTINCT o.id) FROM orders o
             INNER JOIN order_items oi ON o.id = oi.order_id
             INNER JOIN products p ON oi.product_id = p.id
             WHERE p.user_id = $vendorId"
        )->fetch_row()[0];

        // Ingresos totales
        $revenue = $this->conn->query(
            "SELECT COALESCE(SUM(oi.subtotal), 0) FROM order_items oi
             INNER JOIN products p ON oi.product_id = p.id
             WHERE p.user_id = $vendorId"
        )->fetch_row()[0];

        // Productos con bajo stock
        $lowStockProducts = $this->conn->query(
            "SELECT id, nombre, stock FROM products 
             WHERE user_id = $vendorId AND stock < 5
             ORDER BY stock ASC"
        );

        View::render('vendor/dashboard', [
            'user' => $this->user,
            'productCount' => $productCount,
            'orderCount' => $orderCount,
            'revenue' => $revenue,
            'lowStockProducts' => $lowStockProducts,
            'page_css' => 'vendor/dashboard.css'
        ], 'vendor');
    }

    /**
     * Ver productos del vendedor
     */
    public function products(): void
    {
        $vendorId = $this->user['id'];
        
        $result = $this->conn->query(
            "SELECT p.*, c.nombre as categoria_nombre
             FROM products p
             LEFT JOIN categories c ON p.categoria_id = c.id
             WHERE p.user_id = $vendorId
             ORDER BY p.creado_en DESC"
        );
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            // Normalizar nombres de columnas a los esperados en la vista
            $products[] = [
                'id' => $row['id'],
                'name' => $row['nombre'] ?? '',
                'image' => !empty($row['imagen']) ? BASE_URL . 'uploads/' . $row['imagen'] : '',
                'price' => (float)($row['precio'] ?? 0),
                'stock' => (int)($row['stock'] ?? 0),
                'active' => (int)($row['destacado'] ?? 1), // Usar destacado como indicador de activo
                'category_name' => $row['categoria_nombre'] ?? ''
            ];
        }

        View::render('vendor/products', [
            'user' => $this->user,
            'products' => $products,
            'categories' => []
        ], 'vendor');
    }

    /**
     * Ver órdenes de los productos del vendedor
     */
    public function orders(): void
    {
        $vendorId = $this->user['id'];
        
        $result = $this->conn->query(
            "SELECT DISTINCT 
                o.id,
                o.order_number,
                o.status,
                o.total_amount,
                o.created_at,
                o.updated_at,
                u.nombre as customer_name,
                u.email as customer_email,
                (SELECT COUNT(*) FROM order_items oi2 WHERE oi2.order_id = o.id) as item_count
             FROM orders o
             INNER JOIN order_items oi ON o.id = oi.order_id
             INNER JOIN products p ON oi.product_id = p.id
             INNER JOIN users u ON o.user_id = u.id
             WHERE p.user_id = $vendorId
             ORDER BY o.created_at DESC"
        );
        
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }

        View::render('vendor/orders', [
            'user' => $this->user,
            'orders' => $orders
        ], 'vendor');
    }

    /**
     * Ver perfil del vendedor
     */
    public function profile(): void
    {
        
        View::render('vendor/profile', [
            'user' => $this->user
        ], 'vendor');
    }

    /**
     * Editar perfil del vendedor
     */
    public function profileUpdate(): void
    {
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(400);
            die("Método no permitido.");
        }

        csrf_require();

        try {
            $nombre = trim($_POST['nombre'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (!$nombre || !$email) {
                throw new Exception("Faltan campos requeridos.");
            }

            // Verificar que el email no esté en uso por otro usuario
            $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->bind_param("si", $email, $this->user['id']);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                throw new Exception("El email ya está en uso.");
            }

            // Actualizar información básica
            $stmt = $this->conn->prepare("UPDATE users SET nombre = ?, email = ? WHERE id = ?");
            $stmt->bind_param("ssi", $nombre, $email, $this->user['id']);
            $stmt->execute();

            // Actualizar contraseña si fue enviada
            if (!empty($password)) {
                if (strlen($password) < 6) {
                    throw new Exception("La contraseña debe tener al menos 6 caracteres.");
                }
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $this->conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hash, $this->user['id']);
                $stmt->execute();
            }

            header('Location: ' . BASE_URL . 'vendor/perfil?success=Perfil actualizado correctamente');
            exit;
        } catch (Throwable $e) {
            error_log('[VendorController::profileUpdate] ' . $e->getMessage());
            header('Location: ' . BASE_URL . 'vendor/perfil?error=1');
            exit;
        }
    }
}
