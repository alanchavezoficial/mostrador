<?php

namespace App\Controllers;

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../core/csrf.php';

class TestimonialController {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function index(): void {
        require_once __DIR__ . '/../core/auth.php';
        $view = $_GET['view'] ?? 'table';
        $msg = $_GET['msg'] ?? '';
        $isAjax = ($_GET['ajax'] ?? '') === '1';

        $message = match ($msg) {
            'created' => '✅ Testimonio creado correctamente.',
            'edited' => '✏️ Testimonio actualizado correctamente.',
            'deleted' => '🗑️ Testimonio eliminado correctamente.',
            default => ''
        };

        switch ($view) {
            case 'register':
                $data = ['message' => $message];
                $vista = 'testimonios/register';
                break;

            case 'table':
            default:
                $testimonials = $this->getAll();
                $data = [
                    'testimonials' => $testimonials,
                    'message' => $message
                ];
                $vista = 'testimonios/table';
                break;
        }

        if ($isAjax) {
            \View::renderPartial($vista, $data);
        } else {
            \View::render($vista, $data, 'admin');
        }
    }

    public function testimonialEditForm(): void {
        require_once __DIR__ . '/../core/auth.php';
        $id = $_GET['id'] ?? 0;
        $testimonial = $this->getById($id);
        
        if (!$testimonial) {
            http_response_code(404);
            echo "Testimonio no encontrado.";
            return;
        }

        $data = ['testimonial' => $testimonial];
        \View::renderPartial('testimonios/edit', $data);
    }

    public function testimonialCreate(): void {
        require_once __DIR__ . '/../core/auth.php';
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die("Método no permitido.");
        }

        $result = $this->create($_POST);

        if ($result['success'] ?? false) {
            header("Location: " . BASE_URL . "admin/testimonios?view=table&msg=created");
            exit;
        } else {
            http_response_code(500);
            echo "Error al crear testimonio: " . ($result['error'] ?? 'Desconocido');
        }
    }

    public function testimonialEdit(): void {
        require_once __DIR__ . '/../core/auth.php';
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die("Método no permitido.");
        }

        $id = $_POST['id'] ?? 0;
        $result = $this->update($id, $_POST);

        header('Content-Type: application/json');
        echo json_encode($result);
    }

    public function testimonialDelete(): void {
        require_once __DIR__ . '/../core/auth.php';
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die("Método no permitido.");
        }

        $id = $_POST['id'] ?? 0;
        $result = $this->delete($id);

        header('Content-Type: application/json');
        echo json_encode($result);
    }

    public function getAll() {
        $query = "SELECT * FROM testimonials ORDER BY created_at DESC";
        $result = $this->conn->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getVisible() {
        $query = "SELECT * FROM testimonials WHERE is_visible = 1 ORDER BY created_at DESC";
        $result = $this->conn->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM testimonials WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create($data) {
        $stmt = $this->conn->prepare(
            "INSERT INTO testimonials (author, rating, content, is_visible) VALUES (?, ?, ?, ?)"
        );

        $author = htmlspecialchars($data['author'] ?? '');
        $rating = (int)($data['rating'] ?? 5);
        $content = htmlspecialchars($data['content'] ?? '');
        $is_visible = isset($data['is_visible']) ? 1 : 0;

        $stmt->bind_param("sisi", $author, $rating, $content, $is_visible);
        
        if ($stmt->execute()) {
            return ['success' => true, 'id' => $stmt->insert_id];
        }
        return ['success' => false, 'error' => $stmt->error];
    }

    public function update($id, $data) {
        $stmt = $this->conn->prepare(
            "UPDATE testimonials SET author = ?, rating = ?, content = ?, is_visible = ? WHERE id = ?"
        );

        $author = htmlspecialchars($data['author'] ?? '');
        $rating = (int)($data['rating'] ?? 5);
        $content = htmlspecialchars($data['content'] ?? '');
        $is_visible = isset($data['is_visible']) ? 1 : 0;

        $stmt->bind_param("sisii", $author, $rating, $content, $is_visible, $id);
        
        if ($stmt->execute()) {
            return ['success' => true];
        }
        return ['success' => false, 'error' => $stmt->error];
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM testimonials WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            return ['success' => true];
        }
        return ['success' => false, 'error' => $stmt->error];
    }

    public function toggle($id) {
        $stmt = $this->conn->prepare("UPDATE testimonials SET is_visible = !is_visible WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            return ['success' => true];
        }
        return ['success' => false, 'error' => $stmt->error];
    }
}
