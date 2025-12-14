<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../core/auth.php'; 

class ArticleController
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
        $msg     = $_GET['msg'] ?? '';
        $isAjax  = ($_GET['ajax'] ?? '') === '1';

        $message = match ($msg) {
            'created' => '✅ Artículo publicado correctamente.',
            'edited'  => '✏️ Artículo actualizado correctamente.',
            'deleted' => '🗑️ Artículo eliminado correctamente.',
            default   => ''
        };

        switch ($view) {
            case 'register':
                $products   = $this->conn->query("SELECT id, nombre FROM products");
                $categories = $this->conn->query("SELECT id, nombre FROM categories");
                $data = [
                    'message'    => $message,
                    'products'   => $products,
                    'categories' => $categories
                ];
                $vista = 'articulos/register';
                break;

            case 'table':
            default:
                $articles = $this->conn->query("SELECT * FROM articles ORDER BY published_at DESC");
                $data = [
                    'articles' => $articles,
                    'message'  => $message
                ];
                $vista = 'articulos/table';
                break;
        }

        if ($isAjax) {
            View::renderPartial($vista, $data);
        } else {
            View::render($vista, $data, 'admin');
        }
    }

    public function articleCreate(): void
    {
        require_once __DIR__ . '/../core/auth.php'; 
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die("Método no permitido.");
        }

        $title             = trim($_POST['title'] ?? '');
        $content           = trim($_POST['content'] ?? '');
        $author            = trim($_POST['author'] ?? 'Founder');
        $isVisible         = isset($_POST['is_visible']) ? 1 : 0;
        $isFeatured        = isset($_POST['is_featured']) ? 1 : 0;
        $metaTitle         = trim($_POST['meta_title'] ?? '');
        $metaDescription   = trim($_POST['meta_description'] ?? '');
        $relatedProducts   = isset($_POST['related_products']) ? implode(',', $_POST['related_products']) : '';
        $relatedCategories = isset($_POST['related_categories']) ? implode(',', $_POST['related_categories']) : '';

        if ($title === '' || $content === '') {
            http_response_code(400);
            die("Título y contenido son obligatorios.");
        }

        $stmt = $this->conn->prepare("
            INSERT INTO articles 
            (title, content, author, is_visible, is_featured, meta_title, meta_description, related_products, related_categories) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            'sssisssss',
            $title,
            $content,
            $author,
            $isVisible,
            $isFeatured,
            $metaTitle,
            $metaDescription,
            $relatedProducts,
            $relatedCategories
        );

        if (!$stmt->execute()) {
            http_response_code(500);
            die("Error al publicar el artículo: " . $stmt->error);
        }

        if (($_GET['ajax'] ?? '') === '1') {
            $articles = $this->conn->query("SELECT * FROM articles ORDER BY published_at DESC");
            View::renderPartial('articulos/table', [
                'articles' => $articles,
                'message'  => '✅ Artículo publicado correctamente.'
            ]);
            exit;
        }

        header("Location: " . BASE_URL . "admin/articulos?view=table&msg=created");
        exit;
    }

    public function articleDelete(): void
    {
        require_once __DIR__ . '/../core/auth.php'; 
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) return;

        $stmt = $this->conn->prepare("DELETE FROM articles WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['ajax'] ?? '') === '1') {
            $articles = $this->conn->query("SELECT * FROM articles ORDER BY published_at DESC");
            View::renderPartial('articulos/table', [
                'articles' => $articles,
                'message'  => '🗑️ Artículo eliminado correctamente.'
            ]);
            exit;
        }

        $back = $_SERVER['HTTP_REFERER'] ?? BASE_URL . 'admin/articulos';
        header("Location: {$back}");
        exit;
    }

    public function publicView(): void
    {
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            die("Artículo no válido.");
        }

        $stmt = $this->conn->prepare("SELECT * FROM articles WHERE id = ? AND is_visible = 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $article = $stmt->get_result()->fetch_assoc();

        if (!$article) {
            http_response_code(404);
            die("Artículo no encontrado.");
        }

        $relatedProducts = [];
        if (!empty($article['related_products'])) {
            $idList = implode(',', array_map('intval', explode(',', $article['related_products'])));
            $res = $this->conn->query("SELECT * FROM products WHERE id IN ($idList)");
            while ($p = $res->fetch_assoc()) {
                $relatedProducts[] = $p;
            }
        }

        $relatedCategories = [];
        if (!empty($article['related_categories'])) {
            $idList = implode(',', array_map('intval', explode(',', $article['related_categories'])));
            $res = $this->conn->query("SELECT * FROM categories WHERE id IN ($idList)");
            while ($c = $res->fetch_assoc()) {
                $relatedCategories[] = $c;
            }
        }



        View::render('articulo/show', [
            'article'           => $article,
            'relatedProducts'   => $relatedProducts,
            'relatedCategories' => $relatedCategories
        ], 'public');
    }

    public function articleEditForm(): void
    {
        require_once __DIR__ . '/../core/auth.php'; 
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            die("ID inválido.");
        }

        $stmt = $this->conn->prepare("SELECT * FROM articles WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $article = $stmt->get_result()->fetch_assoc();

        if (!$article) {
            http_response_code(404);
            die("Artículo no encontrado.");
        }

        $products   = $this->conn->query("SELECT id, nombre FROM products");
        $categories = $this->conn->query("SELECT id, nombre FROM categories");

        View::renderPartial('articulos/edit', [
            'article'    => $article,
            'products'   => $products,
            'categories' => $categories
        ]);
    }

    public function articleEdit(): void
    {
        require_once __DIR__ . '/../core/auth.php'; 
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die("Método no permitido.");
        }

        $id                = intval($_POST['id'] ?? 0);
        $title             = trim($_POST['title'] ?? '');
        $content           = trim($_POST['content'] ?? '');
        $author            = trim($_POST['author'] ?? 'Founder');
        $isVisible         = isset($_POST['is_visible']) ? 1 : 0;
        $isFeatured        = isset($_POST['is_featured']) ? 1 : 0;
        $metaTitle         = trim($_POST['meta_title'] ?? '');
        $metaDescription   = trim($_POST['meta_description'] ?? '');
        $relatedProducts   = isset($_POST['related_products']) ? implode(',', $_POST['related_products']) : '';
        $relatedCategories = isset($_POST['related_categories']) ? implode(',', $_POST['related_categories']) : '';

        if ($id === 0 || $title === '' || $content === '') {
            http_response_code(400);
            die("Faltan campos obligatorios.");
        }

        $stmt = $this->conn->prepare("
        UPDATE articles SET 
          title = ?, content = ?, author = ?, is_visible = ?, is_featured = ?, 
          meta_title = ?, meta_description = ?, related_products = ?, related_categories = ?, 
          updated_at = NOW()
        WHERE id = ?
    ");
        $stmt->bind_param(
            'sssisssssi',
            $title,
            $content,
            $author,
            $isVisible,
            $isFeatured,
            $metaTitle,
            $metaDescription,
            $relatedProducts,
            $relatedCategories,
            $id
        );

        if (!$stmt->execute()) {
            http_response_code(500);
            die("Error al actualizar el artículo: " . $stmt->error);
        }

        if (($_GET['ajax'] ?? '') === '1') {
            $articles = $this->conn->query("SELECT * FROM articles ORDER BY published_at DESC");
            View::renderPartial('articulos/table', [
                'articles' => $articles,
                'message'  => '✏️ Artículo actualizado correctamente.'
            ]);
            exit;
        }
    }
    
}
