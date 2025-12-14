<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../core/View.php';

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
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'login');
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
            $res = $this->conn->query("SELECT element, COUNT(*) AS cnt FROM analytics_events WHERE event_type = 'click' AND element IS NOT NULL GROUP BY element ORDER BY cnt DESC LIMIT 5");
            while ($row = $res->fetch_assoc()) {
                $topClicks[] = $row;
            }
            $res = $this->conn->query("SELECT country, COUNT(*) AS cnt FROM analytics_events WHERE country IS NOT NULL AND country != '' GROUP BY country ORDER BY cnt DESC LIMIT 5");
            while ($row = $res->fetch_assoc()) { $topCountries[] = $row; }
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
            'top_countries' => $topCountries
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
                $viewName = 'users/register';
                $data = ['message' => $message];
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

        $roles = ['owner', 'admin', 'vendedor', 'gerente'];

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
}
