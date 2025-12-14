<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../core/auth.php'; 

class ProductController
{
    private mysqli $conn;
    private string $uploadDir;
    private array $allowedExts  = ['jpg', 'jpeg', 'png'];
    private int $maxFileSize    = 2 * 1024 * 1024; // 2 MB

    public function __construct()
    {
        global $conn;
        $this->conn      = $conn;
        $this->uploadDir = __DIR__ . '/../../public/img/';
    }
    public function index(): void
    {
        require_once __DIR__ . '/../core/auth.php'; 
        $view   = $_GET['view'] ?? 'table';
        $msg    = $_GET['msg']  ?? '';
        $isAjax = ($_GET['ajax'] ?? '') === '1';

        $message = match ($msg) {
            'created' => "✅ Producto creado correctamente.",
            'edited'  => "✏️ Producto editado correctamente.",
            'deleted' => "🗑️ Producto eliminado correctamente.",
            default   => ''
        };

        $cats = $this->conn->query("SELECT id, nombre FROM categories");

        switch ($view) {
            case 'register':
                $vista = 'productos/register';
                $data  = ['message' => $message, 'cats' => $cats];
                break;

            case 'table':
            default:
                $products = $this->conn->query("
                    SELECT p.*, c.nombre AS categoria_nombre
                    FROM products p
                    LEFT JOIN categories c ON p.categoria_id = c.id
                    ORDER BY p.creado_en DESC
                ");
                $vista = 'productos/table';
                $data  = ['message' => $message, 'cats' => $cats, 'products' => $products];
                break;
        }

        if ($isAjax) {
            View::renderPartial($vista, $data);
        } else {
            View::render($vista, $data, 'admin');
        }
    }
    public function productCreate(): void
    {
        require_once __DIR__ . '/../core/auth.php'; 
        $nombre    = trim($_POST['nombre'] ?? '');
        $desc      = trim($_POST['descripcion'] ?? '');
        $precio    = floatval($_POST['precio'] ?? 0);
        $stock     = intval($_POST['stock'] ?? 0);
        $catId     = intval($_POST['categoria_id'] ?? 0);
        $ofertaAct = isset($_POST['oferta_activa']) ? 1 : 0;
        $ofertaMon = $ofertaAct ? floatval($_POST['oferta_monto'] ?? 0) : null;
        $ofertaTip = $ofertaAct ? ($_POST['oferta_tipo'] ?? null) : null;
        $destacado = isset($_POST['destacado']) ? 1 : 0;

        if (!$nombre || !$precio || !$catId) {
            http_response_code(400);
            die("Faltan datos obligatorios.");
        }

        // 👇 Imagen por defecto
        $imagenName = 'assets/default.png';

        if (!empty($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $finfo = pathinfo($_FILES['imagen']['name']);
            $ext   = strtolower($finfo['extension'] ?? '');
            $size  = $_FILES['imagen']['size'];
            $tmpPath = $_FILES['imagen']['tmp_name'];

            if (!in_array($ext, $this->allowedExts)) {
                http_response_code(400);
                die("Extensión de imagen no permitida.");
            }
            if ($size > $this->maxFileSize) {
                http_response_code(400);
                die("La imagen excede el límite de tamaño.");
            }

            // Validar MIME type real del archivo
            $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $mimeType = mime_content_type($tmpPath);
            if (!in_array($mimeType, $allowedMimes)) {
                http_response_code(400);
                die("Tipo de archivo no válido. Solo se permiten imágenes.");
            }

            $imagenName = uniqid('prd_') . '.' . $ext;
            move_uploaded_file($tmpPath, $this->uploadDir . $imagenName);
        }

        $uuid = uniqid('prd_', true);
        $stmt = $this->conn->prepare("
        INSERT INTO products
        (id, nombre, descripcion, precio, stock, imagen, categoria_id, oferta_activa, oferta_monto, oferta_tipo, destacado, creado_en)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
        $stmt->bind_param(
            "sssdisiidsi",
            $uuid,
            $nombre,
            $desc,
            $precio,
            $stock,
            $imagenName,
            $catId,
            $ofertaAct,
            $ofertaMon,
            $ofertaTip,
            $destacado
        );

        if (!$stmt->execute()) {
            http_response_code(500);
            die("Error al crear producto: " . $stmt->error);
        }

        if ($_GET['ajax'] ?? '' === '1') {
            $_GET['view'] = 'table';
            $_GET['msg']  = 'created';
            $_GET['ajax'] = '1';
            $this->index();
            exit;
        }

        header("Location: " . BASE_URL . "admin/productos?msg=created");
        exit;
    }

    public function productDelete(): void
    {
        require_once __DIR__ . '/../core/auth.php'; 
        $id = trim($_POST['id'] ?? '');
        if (!$id) {
            http_response_code(400);
            die("ID no válido.");
        }

        $old = $this->conn->query("SELECT imagen FROM products WHERE id = '{$id}'")->fetch_assoc()['imagen'];

        // 👇 Solo elimina si no es la default
        if ($old && $old !== 'assets/default.png' && file_exists($this->uploadDir . $old)) {
            unlink($this->uploadDir . $old);
        }

        $this->conn->query("DELETE FROM products WHERE id = '{$id}'");

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['ajax'] ?? '') === '1') {
            $_GET['view'] = 'table';
            $_GET['msg']  = 'deleted';
            $_GET['ajax'] = '1';
            $this->index();
            exit;
        }

        header("Location: " . BASE_URL . "admin/productos?msg=deleted");
        exit;
    }

    public function view(int $id): void
    {
        try {
            $stmt = $this->conn->prepare("
                SELECT p.*, c.nombre AS categoria_nombre
                FROM products p
                LEFT JOIN categories c ON p.categoria_id = c.id
                WHERE p.id = ?
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $producto = $stmt->get_result()->fetch_assoc();

            if (!$producto) {
                throw new Exception("Producto no encontrado.");
            }

            // Galería de imágenes adicionales
            $galleryStmt = $this->conn->prepare("SELECT image_path, alt_text FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC");
            $galleryStmt->bind_param('i', $id);
            $galleryStmt->execute();
            $gallery = $galleryStmt->get_result()->fetch_all(MYSQLI_ASSOC);

            // Reviews visibles
            $revStmt = $this->conn->prepare("SELECT rating, title, content, created_at, user_id FROM reviews WHERE product_id = ? AND is_visible = 1 ORDER BY created_at DESC LIMIT 20");
            $revStmt->bind_param('i', $id);
            $revStmt->execute();
            $reviews = $revStmt->get_result()->fetch_all(MYSQLI_ASSOC);

            $avgStmt = $this->conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE product_id = ? AND is_visible = 1");
            $avgStmt->bind_param('i', $id);
            $avgStmt->execute();
            $avgData = $avgStmt->get_result()->fetch_assoc() ?: ['avg_rating' => null, 'total_reviews' => 0];

            View::render('productos/show', [
                'producto' => $producto,
                'gallery'  => $gallery,
                'reviews'  => $reviews,
                'avg'      => $avgData,
                'page_css' => 'product.css',
                'meta_title' => $producto['nombre'] ?? 'Producto',
                'meta_description' => $producto['short_description'] ?? mb_substr($producto['descripcion'] ?? '', 0, 150),
                'meta_keywords' => $producto['meta_keywords'] ?? ''
            ]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo "<pre>Error al cargar producto: " . htmlspecialchars($e->getMessage()) . "</pre>";
        }
    }

    public function productEditForm(): void
    {
        require_once __DIR__ . '/../core/auth.php'; 
        $id = $_GET['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            die('ID de producto no válido.');
        }

        $stmt = $this->conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param("s", $id); // 👈 usar "s" porque el ID es string
        $stmt->execute();
        $result  = $stmt->get_result();
        $product = $result->fetch_assoc();

        if (!$product) {
            http_response_code(404);
            die('Producto no encontrado.');
        }

        $catsResult = $this->conn->query("SELECT id, nombre FROM categories");
        $cats = [];
        while ($row = $catsResult->fetch_assoc()) {
            $cats[] = $row;
        }

        View::renderPartial('productos/edit', [
            'product' => $product,
            'cats'    => $cats
        ]);
    }

    public function productEdit(): void
    {
        require_once __DIR__ . '/../core/auth.php'; 
        $id = $_POST['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            die('ID inválido.');
        }

        // --- Debug payload ---
        $debug = [
            'post'  => $_POST,
            'files' => $_FILES,
        ];

        // Datos principales
        $nombre       = $_POST['nombre']       ?? '';
        $descripcion  = $_POST['descripcion']  ?? '';
        $precio       = $_POST['precio']       ?? 0;
        $stock        = $_POST['stock']        ?? 0;
        $categoria_id = $_POST['categoria_id'] ?? null;
        $destacado    = $_POST['destacado']    ?? 0;

        // Oferta
        $oferta_activa = $_POST['oferta_activa'] ?? 0;
        $oferta_monto  = $_POST['oferta_monto']  ?? 0;
        $oferta_tipo   = $_POST['oferta_tipo']   ?? 'fijo';

        // Imagen (opcional)
        $imagen = null;
        if (!empty($_FILES['imagen']['name']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $tmpPath = $_FILES['imagen']['tmp_name'];
            
            // Validar MIME type
            $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $mimeType = mime_content_type($tmpPath);
            if (!in_array($mimeType, $allowedMimes)) {
                http_response_code(400);
                die("Tipo de archivo no válido. Solo se permiten imágenes.");
            }
            
            // Eliminar imagen anterior si existe
            $oldResult = $this->conn->query("SELECT imagen FROM products WHERE id = '{$id}'");
            if ($oldResult && $oldRow = $oldResult->fetch_assoc()) {
                $oldImagen = $oldRow['imagen'];
                if ($oldImagen && $oldImagen !== 'assets/default.png') {
                    $oldPath = __DIR__ . '/../../public/uploads/' . $oldImagen;
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }
            }
            
            $nombreImagen = uniqid() . '-' . basename($_FILES['imagen']['name']);
            $rutaDestino  = __DIR__ . '/../../public/uploads/' . $nombreImagen;
            if (move_uploaded_file($tmpPath, $rutaDestino)) {
                $imagen = $nombreImagen;
            }
        }

        // Preparar consulta SQL
        $sql = "UPDATE products SET 
        nombre = ?, 
        descripcion = ?, 
        precio = ?, 
        stock = ?, 
        categoria_id = ?, 
        destacado = ?, 
        oferta_activa = ?, 
        oferta_monto = ?, 
        oferta_tipo = ?";

        if ($imagen) {
            $sql .= ", imagen = ?";
        }
        $sql .= ", updated_at = NOW() WHERE id = ?";

        // Preparar statement
        if ($imagen) {
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param(
                "ssdiiiiisss",
                $nombre,
                $descripcion,
                $precio,
                $stock,
                $categoria_id,
                $destacado,
                $oferta_activa,
                $oferta_monto,
                $oferta_tipo,
                $imagen,
                $id
            );
        } else {
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param(
                "ssdiiiiiss",
                $nombre,
                $descripcion,
                $precio,
                $stock,
                $categoria_id,
                $destacado,
                $oferta_activa,
                $oferta_monto,
                $oferta_tipo,
                $id
            );
        }

        // Ejecutar y capturar errores
        $stmt->execute();
        $error        = $stmt->error;
        $affectedRows = $stmt->affected_rows;

        // Respuesta AJAX
        if (($_GET['ajax'] ?? '') === '1') {
            // obtener nuevo HTML de la tabla
            $result = $this->conn->query("
    SELECT products.*, categories.nombre AS categoria_nombre
    FROM products
    LEFT JOIN categories ON products.categoria_id = categories.id
    ORDER BY products.updated_at DESC
");
            // capturar renderPartial en un string
            ob_start();
            View::renderPartial('productos/table', ['products' => $result]);
            $html = ob_get_clean();

            header('Content-Type: application/json');
            echo json_encode([
                'debug'        => $debug,
                'sql_error'    => $error,
                'affectedRows' => $affectedRows,
                'html'         => $html,
            ]);
            exit;
        }

        // Redirección normal
        header("Location: " . BASE_URL . "admin/productos");
        exit;
    }
}
