<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../core/View.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

class SettingsController
{
    private mysqli $conn;

    public function __construct()
    {
        require_once __DIR__ . '/../core/auth.php';
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

    public function index(): void
    {
        require_once __DIR__ . '/../core/auth.php';
        $view   = $_GET['view'] ?? 'table';
        $msg    = $_GET['msg']  ?? '';
        $isAjax = ($_GET['ajax'] ?? '') === '1';

        $message = match ($msg) {
            'created' => '✅ Configuración creada correctamente.',
            'edited'  => '✏️ Configuración actualizada.',
            'deleted' => '🗑️ Configuración eliminada.',
            default   => ''
        };

        switch ($view) {
            case 'register':
                $viewName = 'configuraciones/register';
                $data = ['message' => $message];
                break;

            case 'table':
            default:
                $result = $this->conn->query("SELECT * FROM settings ORDER BY clave ASC");
                $settings = $result->fetch_all(MYSQLI_ASSOC);
                $viewName = 'configuraciones/table';
                $data = ['settings' => $settings, 'message' => $message];
                break;
            case 'edit':
                $id = $_GET['id'] ?? null;

                if (!$id) {
                    http_response_code(400);
                    View::render('error', ['message' => 'ID no válido.'], 'admin');
                    return;
                }

                $stmt = $this->conn->prepare("SELECT * FROM settings WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $config = $stmt->get_result()->fetch_assoc();

                if (!$config) {
                    http_response_code(404);
                    View::render('error', ['message' => 'Configuración no encontrada.'], 'admin');
                    return;
                }

                $viewName = 'configuraciones/edit';
                $data = ['config' => $config, 'message' => $message];
                break;
        }

        if ($isAjax) {
            View::renderPartial($viewName, $data);
        } else {
            View::render($viewName, $data, 'admin');
        }
    }

    public function settingsCreate(): void
    {
        require_once __DIR__ . '/../core/auth.php';
        $clave = $_POST['clave'] ?? '';
        $valor = $_POST['valor'] ?? '';
        $tipo  = $_POST['tipo'] ?? 'texto';

        if (!$clave || !$valor) {
            http_response_code(400);
            die("Datos incompletos.");
        }

        // Verificar duplicado
        $stmtCheck = $this->conn->prepare("SELECT id FROM settings WHERE clave = ?");
        $stmtCheck->bind_param("s", $clave);
        $stmtCheck->execute();
        $resCheck = $stmtCheck->get_result();

        if ($resCheck->num_rows > 0) {
            http_response_code(409);
            die("❌ La clave '$clave' ya existe.");
        }

        // Insertar
        $stmt = $this->conn->prepare("INSERT INTO settings (clave, valor, tipo) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $clave, $valor, $tipo);
        $stmt->execute();

        $error        = $stmt->error;
        $affectedRows = $stmt->affected_rows;

        // AJAX response
        if (($_GET['ajax'] ?? '') === '1') {
            $result = $this->conn->query("SELECT * FROM settings ORDER BY id DESC");

            ob_start();
            View::renderPartial('configuraciones/table', ['settings' => $result->fetch_all(MYSQLI_ASSOC)]);
            $html = ob_get_clean();

            header('Content-Type: application/json');
            echo json_encode([
                'sql_error'    => $error,
                'affectedRows' => $affectedRows,
                'html'         => $html
            ]);
            exit;
        }

        // Redirección normal
        header("Location: " . BASE_URL . "admin/configuraciones");
        exit;
    }

    public function settingsEditForm(): void
    {
        require_once __DIR__ . '/../core/auth.php';
        $id = $_GET['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            die('ID de configuración no válido.');
        }

        $stmt = $this->conn->prepare("SELECT * FROM settings WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $config = $result->fetch_assoc();

        if (!$config) {
            http_response_code(404);
            die('Configuración no encontrada.');
        }

        View::renderPartial('configuraciones/edit', [
            'config' => $config
        ]);
    }

    public function settingsEdit(): void
    {
        require_once __DIR__ . '/../core/auth.php';
        $id    = $_POST['id']    ?? null;
        $clave = $_POST['clave'] ?? '';
        $valor = $_POST['valor'] ?? '';
        $tipo  = $_POST['tipo']  ?? 'texto';

        if (!$id || !$clave || !$valor) {
            http_response_code(400);
            die("Datos inválidos.");
        }

        $stmt = $this->conn->prepare("UPDATE settings SET clave = ?, valor = ?, tipo = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("sssi", $clave, $valor, $tipo, $id);
        $stmt->execute();

        $error        = $stmt->error;
        $affectedRows = $stmt->affected_rows;

        if (($_GET['ajax'] ?? '') === '1') {
            $result = $this->conn->query("SELECT * FROM settings ORDER BY updated_at DESC");

            ob_start();
            View::renderPartial('configuraciones/table', ['settings' => $result->fetch_all(MYSQLI_ASSOC)]);
            $html = ob_get_clean();

            header('Content-Type: application/json');
            echo json_encode([
                'sql_error'    => $error,
                'affectedRows' => $affectedRows,
                'html'         => $html
            ]);
            exit;
        }

        header("Location: " . BASE_URL . "admin/configuraciones");
        exit;
    }

    public function settingsDelete(): void
    {
        require_once __DIR__ . '/../core/auth.php';
        try {
            $id = intval($_POST['id'] ?? 0);

            if ($id <= 0) {
                throw new Exception("ID inválido.");
            }

            $stmt = $this->conn->prepare("DELETE FROM settings WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            header("Location: " . BASE_URL . "admin/configuraciones?view=table&msg=deleted");
            exit;
        } catch (Throwable $e) {
            echo "<pre>❌ Error al eliminar configuración: " . htmlspecialchars($e->getMessage()) . "</pre>";
            exit;
        }
    }
}
