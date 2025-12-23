<?php

namespace App\Controllers;

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../core/csrf.php';

class ContactController {
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
            'created' => '✅ Campo de contacto creado correctamente.',
            'edited' => '✏️ Campo de contacto actualizado correctamente.',
            'deleted' => '🗑️ Campo de contacto eliminado correctamente.',
            default => ''
        };

        switch ($view) {
            case 'register':
                $data = ['message' => $message];
                $vista = 'contacto/register';
                break;

            case 'table':
            default:
                $contacts = $this->getAll();
                $data = [
                    'contacts' => $contacts,
                    'message' => $message
                ];
                $vista = 'contacto/table';
                break;
        }

        if ($isAjax) {
            \View::renderPartial($vista, $data);
        } else {
            \View::render($vista, $data, 'admin');
        }
    }

    public function contactEditForm(): void {
        require_once __DIR__ . '/../core/auth.php';
        $id = $_GET['id'] ?? 0;
        $contact = $this->getById($id);
        
        if (!$contact) {
            http_response_code(404);
            echo "Campo de contacto no encontrado.";
            return;
        }

        $data = ['contact' => $contact];
        \View::renderPartial('contacto/edit', $data);
    }

    public function contactCreate(): void {
        require_once __DIR__ . '/../core/auth.php';
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die("Método no permitido.");
        }

        $result = $this->create($_POST);

        if ($result['success'] ?? false) {
            header("Location: " . BASE_URL . "admin/contacto?view=table&msg=created");
            exit;
        } else {
            http_response_code(500);
            echo "Error al crear campo de contacto: " . ($result['error'] ?? 'Desconocido');
        }
    }

    public function contactEdit(): void {
        require_once __DIR__ . '/../core/auth.php';
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die("Método no permitido.");
        }

        $id = $_POST['id'] ?? 0;
        $result = $this->update($id, $_POST);

        header('Content-Type: application/json');
        
        if ($result['success'] ?? false) {
            // Obtener la tabla actualizada
            $contacts = $this->getAll();
            $data = ['contacts' => $contacts, 'message' => ''];
            ob_start();
            \View::renderPartial('contacto/table', $data);
            $html = ob_get_clean();
            
            echo json_encode([
                'success' => true,
                'html' => $html
            ]);
        } else {
            echo json_encode([
                'error' => $result['error'] ?? 'Error desconocido'
            ]);
        }
    }

    public function contactDelete(): void {
        require_once __DIR__ . '/../core/auth.php';
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die("Método no permitido.");
        }

        $id = $_POST['id'] ?? 0;
        $result = $this->delete($id);

        header('Content-Type: application/json');
        
        if ($result['success'] ?? false) {
            // Obtener la tabla actualizada
            $contacts = $this->getAll();
            $data = ['contacts' => $contacts, 'message' => ''];
            ob_start();
            \View::renderPartial('contacto/table', $data);
            $html = ob_get_clean();
            
            echo json_encode([
                'success' => true,
                'html' => $html
            ]);
        } else {
            echo json_encode([
                'error' => $result['error'] ?? 'Error desconocido'
            ]);
        }
    }

    public function getAll() {
        $query = "SELECT * FROM contact_info ORDER BY sort_order ASC";
        $result = $this->conn->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getVisible() {
        $query = "SELECT * FROM contact_info WHERE is_visible = 1 ORDER BY sort_order ASC";
        $result = $this->conn->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM contact_info WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getByKey($key) {
        $stmt = $this->conn->prepare("SELECT * FROM contact_info WHERE field_key = ?");
        $stmt->bind_param("s", $key);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create($data) {
        $stmt = $this->conn->prepare(
            "INSERT INTO contact_info (field_key, field_value, field_type, label, icon, is_visible, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        $field_key = htmlspecialchars($data['field_key'] ?? '');
        $field_value = htmlspecialchars($data['field_value'] ?? '');
        $field_type = htmlspecialchars($data['field_type'] ?? 'text');
        $label = htmlspecialchars($data['label'] ?? '');
        $icon = htmlspecialchars($data['icon'] ?? '');
        $is_visible = isset($data['is_visible']) ? 1 : 0;
        $sort_order = (int)($data['sort_order'] ?? 0);

        $stmt->bind_param("sssssii", $field_key, $field_value, $field_type, $label, $icon, $is_visible, $sort_order);
        
        if ($stmt->execute()) {
            return ['success' => true, 'id' => $stmt->insert_id];
        }
        return ['success' => false, 'error' => $stmt->error];
    }

    public function update($id, $data) {
        $stmt = $this->conn->prepare(
            "UPDATE contact_info SET field_value = ?, field_type = ?, label = ?, icon = ?, is_visible = ?, sort_order = ? WHERE id = ?"
        );

        $field_value = htmlspecialchars($data['field_value'] ?? '');
        $field_type = htmlspecialchars($data['field_type'] ?? 'text');
        $label = htmlspecialchars($data['label'] ?? '');
        $icon = htmlspecialchars($data['icon'] ?? '');
        $is_visible = isset($data['is_visible']) ? 1 : 0;
        $sort_order = (int)($data['sort_order'] ?? 0);

        $stmt->bind_param("sssssii", $field_value, $field_type, $label, $icon, $is_visible, $sort_order, $id);
        
        if ($stmt->execute()) {
            return ['success' => true];
        }
        return ['success' => false, 'error' => $stmt->error];
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM contact_info WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            return ['success' => true];
        }
        return ['success' => false, 'error' => $stmt->error];
    }

    public function toggle($id) {
        $stmt = $this->conn->prepare("UPDATE contact_info SET is_visible = !is_visible WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            return ['success' => true];
        }
        return ['success' => false, 'error' => $stmt->error];
    }
}
