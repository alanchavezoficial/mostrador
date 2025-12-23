<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../core/RichTextHelper.php';

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
        if (($_GET['ajax'] ?? '') === '1') define('API_MODE', true);
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
                $userName   = $_SESSION['nombre'] ?? 'Usuario';
                $data = [
                    'message'    => $message,
                    'products'   => $products,
                    'categories' => $categories,
                    'userName'   => $userName
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
        if (($_GET['ajax'] ?? '') === '1') define('API_MODE', true);
        require_once __DIR__ . '/../core/auth.php'; 
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            $this->respondJSON(['success' => false, 'message' => 'Método no permitido'], 405);
            return;
        }

        $title             = trim($_POST['title'] ?? '');
        $content           = trim($_POST['content'] ?? '');
        $author            = trim($_POST['author'] ?? 'Founder');
        $isVisible         = isset($_POST['is_visible']) ? 1 : 0;
        $isFeatured        = isset($_POST['is_featured']) ? 1 : 0;
        $isCarousel        = isset($_POST['is_carousel']) ? 1 : 0;
        $metaTitle         = trim($_POST['meta_title'] ?? '');
        $metaDescription   = trim($_POST['meta_description'] ?? '');
        $productId         = isset($_POST['product_id']) && $_POST['product_id'] !== '' ? intval($_POST['product_id']) : null;
        $relatedProducts   = isset($_POST['related_products']) ? implode(',', $_POST['related_products']) : '';
        $relatedCategories = isset($_POST['related_categories']) ? implode(',', $_POST['related_categories']) : '';

        // Sanitizar contenido HTML
        $content = RichTextHelper::sanitizeHTML($content);

        if ($title === '' || !RichTextHelper::hasContent($content)) {
            http_response_code(400);
            $this->respondJSON(['success' => false, 'message' => 'Título y contenido son obligatorios'], 400);
            return;
        }

        $stmt = $this->conn->prepare("\n            INSERT INTO articles \n            (title, content, author, is_visible, is_featured, is_carousel, meta_title, meta_description, product_id, related_products, related_categories) \n            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)\n        ");
        $stmt->bind_param(
            'sssiiissiis',
            $title,
            $content,
            $author,
            $isVisible,
            $isFeatured,
            $isCarousel,
            $metaTitle,
            $metaDescription,
            $productId,
            $relatedProducts,
            $relatedCategories
        );

        if (!$stmt->execute()) {
            http_response_code(500);
            $this->respondJSON(['success' => false, 'message' => 'Error al publicar el artículo: ' . $stmt->error], 500);
            return;
        }

        $articleId = $stmt->insert_id;

        // Enforce carousel limit: keep max 5 (drop oldest)
        if ($isCarousel === 1) {
            $countRes = $this->conn->query("SELECT id FROM articles WHERE is_carousel = 1 ORDER BY published_at DESC");
            if ($countRes && $countRes->num_rows >= 5) {
                // Drop the oldest
                $oldestRes = $this->conn->query("SELECT id FROM articles WHERE is_carousel = 1 ORDER BY published_at ASC LIMIT 1");
                if ($oldestRes && ($oldest = $oldestRes->fetch_assoc())) {
                    $dropStmt = $this->conn->prepare("UPDATE articles SET is_carousel = 0 WHERE id = ?");
                    $dropStmt->bind_param('i', $oldest['id']);
                    $dropStmt->execute();
                }
            }
        }

        // Handle multiple images
        $this->saveArticleImages($articleId, $_FILES['images'] ?? null);

        // Responder con JSON
        $this->respondJSON(['success' => true, 'message' => 'Artículo publicado correctamente', 'id' => $articleId], 201);
    }

    public function articleDelete(): void
    {
        if (($_GET['ajax'] ?? '') === '1') define('API_MODE', true);
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
        // Obtener ID desde GET o desde PATH (ya mapeado en router)
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        // Validar que el ID sea un número positivo
        if ($id <= 0) {
            http_response_code(400);
            die("Artículo no válido. Por favor, selecciona un artículo válido.");
        }

        $stmt = $this->conn->prepare("SELECT * FROM articles WHERE id = ? AND is_visible = 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $article = $stmt->get_result()->fetch_assoc();

        if (!$article) {
            http_response_code(404);
            die("Artículo no encontrado.");
        }

        $images = [];
        $imgStmt = $this->conn->prepare("SELECT image_path, is_primary FROM article_images WHERE article_id = ? ORDER BY is_primary DESC, id ASC");
        $imgStmt->bind_param('i', $id);
        if ($imgStmt->execute()) {
            $imgRes = $imgStmt->get_result();
            while ($row = $imgRes->fetch_assoc()) {
                $images[] = $row;
            }
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
            'relatedCategories' => $relatedCategories,
            'images'            => $images,
            'meta_title'        => $article['meta_title'] ?: $article['title'],
            'meta_description'  => $article['meta_description'] ?: substr($article['content'], 0, 160),
            'page_css'          => 'article.css'
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
        $userName   = $_SESSION['nombre'] ?? 'Usuario';

        View::renderPartial('articulos/edit', [
            'article'    => $article,
            'products'   => $products,
            'categories' => $categories,
            'userName'   => $userName
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
        $isCarousel        = isset($_POST['is_carousel']) ? 1 : 0;
        $metaTitle         = trim($_POST['meta_title'] ?? '');
        $metaDescription   = trim($_POST['meta_description'] ?? '');
        $productId         = isset($_POST['product_id']) && $_POST['product_id'] !== '' ? intval($_POST['product_id']) : null;
        $relatedProducts   = isset($_POST['related_products']) ? implode(',', $_POST['related_products']) : '';
        $relatedCategories = isset($_POST['related_categories']) ? implode(',', $_POST['related_categories']) : '';

        // Sanitizar contenido HTML
        $content = RichTextHelper::sanitizeHTML($content);

        if ($id === 0 || $title === '' || !RichTextHelper::hasContent($content)) {
            http_response_code(400);
            $this->respondJSON(['success' => false, 'message' => 'Faltan campos obligatorios'], 400);
            return;
        }

        $stmt = $this->conn->prepare("
        UPDATE articles SET 
          title = ?, content = ?, author = ?, is_visible = ?, is_featured = ?, 
                    meta_title = ?, meta_description = ?, product_id = ?, related_products = ?, related_categories = ?, is_carousel = ?, 
          updated_at = NOW()
        WHERE id = ?
    ");
        $stmt->bind_param(
            'ssiissisiiii',
            $title,
            $content,
            $author,
            $isVisible,
            $isFeatured,
            $metaTitle,
            $metaDescription,
            $productId,
            $relatedProducts,
            $relatedCategories,
            $isCarousel,
            $id
        );

        if (!$stmt->execute()) {
            http_response_code(500);
            $this->respondJSON(['success' => false, 'message' => 'Error al actualizar el artículo: ' . $stmt->error], 500);
            return;
        }

        // Enforce carousel limit after edit
        if ($isCarousel === 1) {
            $countRes = $this->conn->query("SELECT id FROM articles WHERE is_carousel = 1 ORDER BY published_at DESC");
            if ($countRes && $countRes->num_rows > 5) {
                $excess = $countRes->num_rows - 5;
                $oldestRes = $this->conn->query("SELECT id FROM articles WHERE is_carousel = 1 ORDER BY published_at ASC LIMIT $excess");
                while ($oldestRes && ($row = $oldestRes->fetch_assoc())) {
                    $dropStmt = $this->conn->prepare("UPDATE articles SET is_carousel = 0 WHERE id = ?");
                    $dropStmt->bind_param('i', $row['id']);
                    $dropStmt->execute();
                }
            }
        }

        // Append new images if provided
        $this->saveArticleImages($id, $_FILES['images'] ?? null);

        // Responder con JSON
        $this->respondJSON(['success' => true, 'message' => 'Artículo actualizado correctamente', 'id' => $id], 200);
    }

    private function saveArticleImages(int $articleId, ?array $files): void
    {
        if (!$files || empty($files['name'])) {
            return;
        }

        $names = $files['name'];
        $tmp   = $files['tmp_name'];
        $errs  = $files['error'];

        // Chequeo rápido de integridad
        if (!is_array($names)) {
            return;
        }

        $destDir = __DIR__ . '/../../public/uploads/';
        if (!is_dir($destDir)) {
            mkdir($destDir, 0775, true);
        }

        $hasPrimary = false;
        $checkPrimary = $this->conn->prepare("SELECT id FROM article_images WHERE article_id = ? AND is_primary = 1 LIMIT 1");
        $checkPrimary->bind_param('i', $articleId);
        if ($checkPrimary->execute()) {
            $resPrimary = $checkPrimary->get_result();
            $hasPrimary = $resPrimary && $resPrimary->num_rows > 0;
        }

        $allowedMimes = [
            'image/jpeg' => '.jpg',
            'image/png'  => '.png',
            'image/webp' => '.webp',
            'image/gif'  => '.gif'
        ];
        $allowedExts = ['.jpg', '.jpeg', '.png', '.webp', '.gif'];

        $finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : null;
        $savedAny = false;
        foreach ($names as $i => $filename) {
            if (empty($filename) || ($errs[$i] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
                continue;
            }

            $tmpPath = $tmp[$i] ?? null;
            if (!$tmpPath || !is_uploaded_file($tmpPath)) {
                continue;
            }

            $mime = $finfo ? @finfo_file($finfo, $tmpPath) : @mime_content_type($tmpPath);
            $extFromMime = $mime && isset($allowedMimes[$mime]) ? $allowedMimes[$mime] : null;
            $extFromName = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $extFromName = $extFromName ? '.' . $extFromName : null;
            if ($extFromName && !in_array($extFromName, $allowedExts, true)) {
                $extFromName = null;
            }

            // Aceptar si coincide mime permitido o extensión permitida
            if (!$extFromMime && !$extFromName) {
                continue;
            }

            $ext = $extFromMime ?: $extFromName;
            $safeName = 'art_' . uniqid() . $ext;
            $destPath = $destDir . $safeName;

            if (move_uploaded_file($tmpPath, $destPath)) {
                $isPrimary = ($hasPrimary || $savedAny) ? 0 : 1;
                $stmtImg = $this->conn->prepare("INSERT INTO article_images (article_id, image_path, is_primary) VALUES (?, ?, ?)");
                $stmtImg->bind_param('isi', $articleId, $safeName, $isPrimary);
                $stmtImg->execute();
                $savedAny = true;
            }
        }

        if ($finfo) {
            finfo_close($finfo);
        }
    }

    /**
     * Responde con JSON
     */
    private function respondJSON($data, $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

}
