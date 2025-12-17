<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../core/permissions.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

class AdminController
{
    private mysqli $conn;

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
        
        // Permitir acceso a admin, Dueno y vendedor
        $allowedRoles = ['admin', 'Dueno', 'vendedor'];
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowedRoles)) {
            header('Location: ' . BASE_URL . '?error=Acceso+denegado.+No+tienes+permisos+para+esta+sección.');
            exit;
        }
    }

    public function dashboard(): void
    {
        require_once __DIR__ . '/../core/auth.php'; 
        $userCount    = $this->conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
        $productCount = $this->conn->query("SELECT COUNT(*) FROM products")->fetch_row()[0];

        // Analytics stats
        $analyticsTotal = 0;
        $analyticsUnique = 0;
        $analyticsAvgTime = 0;
        $topReferrers = [];
        $topClicks = [];
        $topCountries = [];
        $topWishlisted = [];
        $topProductBuyers = [];
        try {
            $analyticsTotal = $this->conn->query("SELECT COUNT(*) FROM analytics_events")->fetch_row()[0] ?? 0;
            $analyticsUnique = $this->conn->query("SELECT COUNT(DISTINCT session_id) FROM analytics_events WHERE session_id IS NOT NULL")->fetch_row()[0] ?? 0;
            $analyticsAvgTime = $this->conn->query("SELECT AVG(JSON_EXTRACT(metadata,'$.seconds') + 0) FROM analytics_events WHERE event_type = 'time_on_page'")->fetch_row()[0] ?? 0;
        } catch (Throwable $e) {
            // Table may not exist yet; ignore
        }

        try {
            $res = $this->conn->query("SELECT referrer, COUNT(*) AS cnt FROM analytics_events WHERE referrer IS NOT NULL GROUP BY referrer ORDER BY cnt DESC LIMIT 5");
            while ($row = $res->fetch_assoc()) {
                $topReferrers[] = $row;
            }
            $res = $this->conn->query("SELECT COALESCE(NULLIF(element,''), JSON_UNQUOTE(JSON_EXTRACT(metadata,'$.element'))) AS element, COUNT(*) AS cnt FROM analytics_events WHERE event_type = 'click' GROUP BY COALESCE(NULLIF(element,''), JSON_UNQUOTE(JSON_EXTRACT(metadata,'$.element'))) ORDER BY cnt DESC LIMIT 5");
            while ($row = $res->fetch_assoc()) {
                $topClicks[] = $row;
            }
            $res = $this->conn->query("SELECT country, COUNT(*) AS cnt FROM analytics_events WHERE country IS NOT NULL AND country != '' GROUP BY country ORDER BY cnt DESC LIMIT 5");
            while ($row = $res->fetch_assoc()) { $topCountries[] = $row; }
            // Top wishlisted products (by distinct users)
            try {
                $res = $this->conn->query("SELECT w.product_id, p.nombre AS name, COUNT(DISTINCT w.user_id) AS cnt FROM wishlist w JOIN products p ON p.id = w.product_id GROUP BY w.product_id, p.nombre ORDER BY cnt DESC LIMIT 5");
                while ($row = $res->fetch_assoc()) { $topWishlisted[] = $row; }
            } catch (Throwable $e2) { /* wishlist table may not exist */ }
            // Top products by distinct buyers (only completed payments)
            try {
                $res = $this->conn->query("SELECT oi.product_id, p.nombre AS name, COUNT(DISTINCT o.user_id) AS cnt FROM order_items oi JOIN orders o ON o.id = oi.order_id JOIN products p ON p.id = oi.product_id WHERE oi.product_id IS NOT NULL AND o.payment_status = 'completed' GROUP BY oi.product_id, p.nombre ORDER BY cnt DESC LIMIT 5");
                while ($row = $res->fetch_assoc()) { $topProductBuyers[] = $row; }
            } catch (Throwable $e3) { /* orders/order_items may not exist */ }
        } catch (Throwable $e) {
            // ignore missing table
        }

        View::render('dashboard', [
            'meta_title' => 'Dashboard',
            'stats'      => [
                'users'    => $userCount,
                'products' => $productCount,
                'analytics_total' => $analyticsTotal,
                'analytics_unique' => $analyticsUnique,
                'analytics_avg_time' => round($analyticsAvgTime, 1) ?: 0
            ],
            'requiredScripts' => ['admin/admin-dashboard.js'],
            'top_referrers' => $topReferrers,
            'top_clicks' => $topClicks,
            'top_countries' => $topCountries,
            'top_wishlisted' => $topWishlisted,
            'top_product_buyers' => $topProductBuyers
        ], 'admin');
    }

    public function analytics(): void
    {
        // This method is removed; analytics is part of the dashboard now.
        require_once __DIR__ . '/../core/auth.php';
        header('Location: ' . BASE_URL . 'admin/dashboard');
        exit;
    }

    public function users(): void
    {
        require_once __DIR__ . '/../core/auth.php'; 
        $view    = $_GET['view'] ?? 'table';
        $msg     = $_GET['msg']  ?? '';
        $isAjax  = ($_GET['ajax'] ?? '') === '1';

        $message = match ($msg) {
            'created' => '✅ Usuario creado correctamente.',
            'edited'  => '✏️ Usuario actualizado.',
            'deleted' => '🗑️ Usuario eliminado.',
            default   => ''
        };

        switch ($view) {
            case 'register':
                // Obtener roles disponibles de la BD (ENUM en tabla users)
                $rolesQuery = "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS 
                               WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='users' AND COLUMN_NAME='role'";
                $rolesResult = $this->conn->query($rolesQuery);
                $rolesRow = $rolesResult->fetch_assoc();
                
                // Extraer valores del ENUM
                $enumString = $rolesRow['COLUMN_TYPE'];
                preg_match("/^enum\((.*)\)$/", $enumString, $matches);
                $rolesArray = array_map(fn($v) => trim($v, "'\""), explode(',', $matches[1]));
                
                $viewName = 'users/register';
                $data = ['message' => $message, 'rolesArray' => $rolesArray];
                break;

            case 'table':
            default:
                $users = $this->conn->query("
                    SELECT id, nombre, email, role, created_at
                    FROM users
                    ORDER BY created_at DESC
                ");
                $viewName = 'users/table';
                $data = ['users' => $users, 'message' => $message];
                break;
        }

        if ($isAjax) {
            View::renderPartial($viewName, $data);
        } else {
            View::render($viewName, $data, 'admin');
        }
    }

    public function userCreate(): void
    {
        require_once __DIR__ . '/../core/auth.php'; 
        try {
            $name     = trim($_POST['nombre'] ?? '');
            $email    = trim($_POST['email']  ?? '');
            $password = $_POST['password']    ?? '';
            $role     = $_POST['role']        ?? '';

            if (!$name || !$email || !$password || !$role) {
                throw new Exception("Faltan campos requeridos.");
            }

            $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                throw new Exception("El correo ya está registrado.");
            }
            $stmt->close();

            $uuid = uniqid('user_', true);
            $hash = password_hash($password, PASSWORD_DEFAULT)
                ?: throw new Exception("Error al encriptar la contraseña.");

            $stmt = $this->conn->prepare("
                INSERT INTO users (uuid, nombre, email, password, role, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->bind_param("sssss", $uuid, $name, $email, $hash, $role);
            $stmt->execute();

            if (($_GET['ajax'] ?? '') === '1') {
                $users = $this->conn->query("
                    SELECT id, nombre, email, role, created_at
                    FROM users
                    ORDER BY created_at DESC
                ");
                View::renderPartial('users/table', [
                    'users'   => $users,
                    'message' => '✅ Usuario creado correctamente.'
                ]);
                exit;
            }

            header("Location: " . BASE_URL . "admin/usuarios?view=table&msg=created");
            exit;
        } catch (Throwable $e) {
            echo "<pre>❌ Error al crear usuario: " . htmlspecialchars($e->getMessage()) . "</pre>";
            exit;
        }
    }

    public function userEdit(): void
    {
        require_once __DIR__ . '/../core/auth.php'; 
        try {
            $id    = intval($_POST['id'] ?? 0);
            $name  = trim($_POST['nombre'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $role  = $_POST['role'] ?? '';
            $pass  = trim($_POST['password'] ?? '');

            if ($id <= 0 || !$name || !$email || !$role) {
                throw new Exception("Datos incompletos.");
            }

            $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->bind_param("si", $email, $id);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                throw new Exception("El correo ya está en uso.");
            }
            $stmt->close();

            $stmt = $this->conn->prepare("
            UPDATE users SET nombre = ?, email = ?, role = ?, updated_at = NOW()
            WHERE id = ?
        ");
            $stmt->bind_param("sssi", $name, $email, $role, $id);
            $stmt->execute();

            // 🔐 Actualizar contraseña solo si fue enviada
            if ($pass) {
                $hashed = password_hash($pass, PASSWORD_DEFAULT);
                $stmt = $this->conn->prepare("
                UPDATE users SET password = ? WHERE id = ?
            ");
                $stmt->bind_param("si", $hashed, $id);
                $stmt->execute();
            }

            // 📤 Respuesta AJAX
            if (($_GET['ajax'] ?? '') === '1') {
                $users = $this->conn->query("
                SELECT id, nombre, email, role, created_at
                FROM users
                ORDER BY created_at DESC
            ");
                View::renderPartial('users/table', [
                    'users'   => $users,
                    'message' => '✏️ Usuario actualizado.'
                ]);
                exit;
            }

            // 📍 Redirección tradicional
            header("Location: " . BASE_URL . "admin/usuarios?view=table&msg=edited");
            exit;
        } catch (Throwable $e) {
            echo "<pre>❌ Error al editar usuario: " . htmlspecialchars($e->getMessage()) . "</pre>";
            exit;
        }
    }
    public function userEditForm(): void
    {
        require_once __DIR__ . '/../core/auth.php'; 
        $id = $_GET['id'] ?? '';

        if (!$id || !is_numeric($id)) {
            http_response_code(400);
            die("ID de usuario no válido.");
        }

        $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user) {
            http_response_code(404);
            die("Usuario no encontrado.");
        }

        // Obtener roles disponibles de la BD (ENUM en tabla users)
        $rolesQuery = "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS 
                       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='users' AND COLUMN_NAME='role'";
        $rolesResult = $this->conn->query($rolesQuery);
        $rolesRow = $rolesResult->fetch_assoc();
        
        // Extraer valores del ENUM
        $enumString = $rolesRow['COLUMN_TYPE'];
        preg_match("/^enum\((.*)\)$/", $enumString, $matches);
        $roles = array_map(fn($v) => trim($v, "'\""), explode(',', $matches[1]));

        View::renderPartial('users/edit', [
            'user'  => $user,
            'roles' => $roles
        ]);
    }
    public function userDelete(): void
    {
        require_once __DIR__ . '/../core/auth.php'; 
        try {
            $id = intval($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception("ID inválido.");
            }

            $stmt = $this->conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            if (($_GET['ajax'] ?? '') === '1') {
                $users = $this->conn->query("
                    SELECT id, nombre, email, role, created_at
                    FROM users
                    ORDER BY created_at DESC
                ");
                View::renderPartial('users/table', [
                    'users'   => $users,
                    'message' => '🗑️ Usuario eliminado correctamente.'
                ]);
                exit;
            }

            header("Location: " . BASE_URL . "admin/usuarios?msg=deleted");
            exit;
        } catch (Throwable $e) {
            echo "<pre>❌ Error al eliminar usuario: " . htmlspecialchars($e->getMessage()) . "</pre>";
            exit;
        }
    }

    public function profile(): void
    {
        require_once __DIR__ . '/../core/auth.php';
        
        // Obtener datos del usuario actual
        $userId = $_SESSION['user_id'];
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if (!$user) {
            header('Location: ' . BASE_URL . 'admin/dashboard');
            exit;
        }
        
        View::render('admin/profile', [
            'meta_title' => 'Mi Perfil',
            'user' => $user
        ]);
    }

    /**
     * Actualizar perfil del administrador
     */
    public function profileUpdate(): void
    {
        require_once __DIR__ . '/../core/csrf.php';
        require_once __DIR__ . '/../core/auth.php';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(400);
            die('Método no permitido.');
        }

        csrf_require();

        try {
            $nombre = trim($_POST['nombre'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $userId = $_SESSION['user_id'];

            if (!$nombre || !$email) {
                throw new Exception('Faltan campos requeridos.');
            }

            // Verificar que el email no esté en uso por otro usuario
            $stmt = $this->conn->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
            $stmt->bind_param('si', $email, $userId);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                throw new Exception('El email ya está en uso.');
            }

            // Actualizar información básica
            $stmt = $this->conn->prepare('UPDATE users SET nombre = ?, email = ? WHERE id = ?');
            $stmt->bind_param('ssi', $nombre, $email, $userId);
            $stmt->execute();

            // Actualizar contraseña si fue enviada
            if (!empty($_POST['password'])) {
                if (strlen($_POST['password']) < 6) {
                    throw new Exception('La contraseña debe tener al menos 6 caracteres.');
                }
                $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $stmt = $this->conn->prepare('UPDATE users SET password = ? WHERE id = ?');
                $stmt->bind_param('si', $hash, $userId);
                $stmt->execute();
            }

            header('Location: ' . BASE_URL . 'admin/perfil?updated=1');
            exit;
        } catch (Throwable $e) {
            echo '<pre>❌ Error: ' . htmlspecialchars($e->getMessage()) . '</pre>';
            exit;
        }
    }
}
