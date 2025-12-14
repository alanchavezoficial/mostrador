<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../core/auth.php';

class CategoryController
{
    private mysqli $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    public function index(): void
    {
        require_once __DIR__ . '/../core/auth.php';
        $view    = $_GET['view'] ?? 'table';
        $msg     = $_GET['msg']  ?? '';
        $isAjax  = ($_GET['ajax'] ?? '') === '1';

        $message = match ($msg) {
            'created' => '✅ Categoría creada correctamente.',
            'edited'  => '✏️ Categoría editada correctamente.',
            'deleted' => '🗑️ Categoría eliminada correctamente.',
            default   => ''
        };

        switch ($view) {
            case 'register':
                $categories = $this->conn->query("SELECT id, nombre FROM categories ORDER BY nombre");
                $data = [
                    'message'    => $message,
                    'categories' => $categories
                ];
                $vista = 'categorias/register';
                break;

            case 'table':
            default:
                $cats = $this->conn->query("
                    SELECT c.*, p.nombre AS parent_nombre
                    FROM categories c
                    LEFT JOIN categories p ON c.parent_id = p.id
                    ORDER BY c.orden, c.nombre
                ");
                $data = [
                    'categories' => $cats,
                    'message'    => $message
                ];
                $vista = 'categorias/table';
                break;
        }

        if ($isAjax) {
            View::renderPartial($vista, $data);
        } else {
            View::render($vista, $data, 'admin');
        }
    }

    public function categoryCreate(): void
    {
        require_once __DIR__ . '/../core/auth.php';
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die("Método no permitido.");
        }

        $nombre      = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $parent_id   = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null;
        $is_active   = isset($_POST['is_active']) ? 1 : 0;
        $orden       = intval($_POST['orden'] ?? 0);

        if ($nombre === '') {
            http_response_code(400);
            die("El nombre es obligatorio.");
        }

        $slug = $this->generarSlug($nombre);

        $stmt = $this->conn->prepare("
            INSERT INTO categories (nombre, slug, descripcion, parent_id, is_active, orden)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param('sssiii', $nombre, $slug, $descripcion, $parent_id, $is_active, $orden);
        if (!$stmt->execute()) {
            http_response_code(500);
            die("Error al crear la categoría: " . $stmt->error);
        }

        if ($_GET['ajax'] ?? '' === '1') {
            $_GET['view'] = 'table';
            $_GET['msg']  = 'created';
            $_GET['ajax'] = '1';
            $this->index();
            exit;
        }

        header("Location: " . BASE_URL . "admin/categorias?msg=created");
        exit;
    }

    public function categoryEdit(): void
    {
        require_once __DIR__ . '/../core/auth.php';
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die("Método no permitido.");
        }

        $id          = intval($_POST['id'] ?? 0);
        $nombre      = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $parent_id   = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null;
        $is_active   = isset($_POST['is_active']) ? 1 : 0;
        $orden       = intval($_POST['orden'] ?? 0);

        if (!$id || $nombre === '') {
            http_response_code(400);
            die("Faltan datos obligatorios.");
        }

        $slug = $this->generarSlug($nombre);

        $stmt = $this->conn->prepare("
            UPDATE categories SET
              nombre = ?, slug = ?, descripcion = ?, parent_id = ?, is_active = ?, orden = ?
            WHERE id = ?
        ");
        $stmt->bind_param('sssiiii', $nombre, $slug, $descripcion, $parent_id, $is_active, $orden, $id);
        if (!$stmt->execute()) {
            http_response_code(500);
            die("Error al editar la categoría: " . $stmt->error);
        }

        if ($_GET['ajax'] ?? '' === '1') {
            $_GET['view'] = 'table';
            $_GET['msg']  = 'edited';
            $_GET['ajax'] = '1';
            $this->index();
            exit;
        }

        header("Location: " . BASE_URL . "admin/categories?msg=edited");
        exit;
    }

    public function categoryEditForm(): void
    {
        require_once __DIR__ . '/../core/auth.php';
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            die("ID inválido.");
        }

        $stmt = $this->conn->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $category = $stmt->get_result()->fetch_assoc();

        if (!$category) {
            http_response_code(404);
            die("Categoría no encontrada.");
        }

        $parents = $this->conn->query("SELECT id, nombre FROM categories WHERE id != $id");

        View::renderPartial('categorias/edit', [
            'category' => $category,
            'parents'  => $parents
        ]);
    }

    public function categoryDelete(): void
    {
        require_once __DIR__ . '/../core/auth.php'; 
        $id = intval($_POST['id'] ?? 0);
        if (!$id) {
            http_response_code(400);
            die("ID no válido.");
        }

        $stmt = $this->conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            http_response_code(500);
            die("Error al eliminar la categoría: " . $stmt->error);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['ajax'] ?? '') === '1') {
            $_GET['view'] = 'table';
            $_GET['msg']  = 'deleted';
            $_GET['ajax'] = '1';
            $this->index();
            exit;
        }

        header("Location: " . BASE_URL . "admin/categories?msg=deleted");
        exit;
    }

    private function generarSlug(string $texto): string
    {
        $slug = strtolower(trim($texto));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');

        $base = $slug;
        $i    = 1;

        $query = $this->conn->prepare("SELECT COUNT(*) FROM categories WHERE slug = ?");
        $query->bind_param('s', $slug);
        $query->execute();
        $query->bind_result($count);
        $query->fetch();

        while ($count > 0) {
            $slug = "{$base}-{$i}";
            $i++;
            $query->close();
            $query = $this->conn->prepare("SELECT COUNT(*) FROM categories WHERE slug = ?");
            $query->bind_param('s', $slug);
            $query->execute();
            $query->bind_result($count);
            $query->fetch();
        }

        $query->close();
        return $slug;
    }
}
