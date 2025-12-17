<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/session.php';

class UserController
{
    public function login(): void
    {
        session_start();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Rate limiting simple
            $maxAttempts = 5;
            $lockoutTime = 300; // 5 minutos
            $ip = $_SERVER['REMOTE_ADDR'];
            
            if (!isset($_SESSION['login_attempts'])) {
                $_SESSION['login_attempts'] = [];
            }
            
            // Limpiar intentos antiguos
            $_SESSION['login_attempts'] = array_filter(
                $_SESSION['login_attempts'],
                fn($timestamp) => (time() - $timestamp) < $lockoutTime
            );
            
            // Verificar si está bloqueado
            if (count($_SESSION['login_attempts']) >= $maxAttempts) {
                $remainingTime = $lockoutTime - (time() - min($_SESSION['login_attempts']));
                header('Location: ' . BASE_URL . 'login?error=Demasiados intentos. Espera ' . ceil($remainingTime / 60) . ' minutos');
                exit;
            }
            
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                header('Location: ' . BASE_URL . 'login?error=Faltan datos');
                exit;
            }

            global $conn;
            $stmt = $conn->prepare("SELECT id, nombre, email, password, role FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res->num_rows === 0) {
                $_SESSION['login_attempts'][] = time();
                header('Location: ' . BASE_URL . 'login?error=Usuario no encontrado');
                exit;
            }

            $user = $res->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                // Limpiar intentos fallidos
                $_SESSION['login_attempts'] = [];
                
                // Regenerar ID de sesión para prevenir session fixation
                session_regenerate_secure();
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nombre'] = $user['nombre'];
                $_SESSION['role'] = $user['role'];

                // Redirigir según el parámetro redirect o rol
                $redirect = $_GET['redirect'] ?? null;
                
                // Si hay redirect explícito, usarlo
                if ($redirect === 'checkout') {
                    header('Location: ' . BASE_URL . 'checkout');
                } else if ($redirect) {
                    header('Location: ' . BASE_URL . $redirect);
                } else if (strtolower(trim($user['role'])) === 'admin') {
                    // Admin va al dashboard
                    header('Location: ' . BASE_URL . 'admin/dashboard?success=Bienvenido ' . urlencode($user['nombre']));
                } else if (strtolower(trim($user['role'])) === 'vendedor') {
                    // Vendedor va directo a su dashboard
                    header('Location: ' . BASE_URL . 'vendor/dashboard?success=Bienvenido ' . urlencode($user['nombre']));
                } else {
                    // Usuario normal va a inicio o carrito
                    header('Location: ' . BASE_URL . '?success=Bienvenido ' . urlencode($user['nombre']));
                }
                exit;
            } else {
                $_SESSION['login_attempts'][] = time();
                header('Location: ' . BASE_URL . 'login?error=Contraseña incorrecta');
                exit;
            }
        } else {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
    }

    public function register(): void
    {
        session_start();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['nombre'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $password_confirm = $_POST['password_confirm'] ?? '';

            // Validaciones
            if (empty($nombre) || empty($email) || empty($password)) {
                header('Location: ' . BASE_URL . 'register?error=Faltan datos requeridos');
                exit;
            }

            if ($password !== $password_confirm) {
                header('Location: ' . BASE_URL . 'register?error=Las contraseñas no coinciden');
                exit;
            }

            if (strlen($password) < 6) {
                header('Location: ' . BASE_URL . 'register?error=La contraseña debe tener al menos 6 caracteres');
                exit;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                header('Location: ' . BASE_URL . 'register?error=Email inválido');
                exit;
            }

            global $conn;
            
            // Verificar si el email ya existe
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                header('Location: ' . BASE_URL . 'register?error=El email ya está registrado');
                exit;
            }

            // Hash de la contraseña
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $role = 'cliente'; // Por defecto es cliente

            // Insertar nuevo usuario
            $stmt = $conn->prepare("INSERT INTO users (nombre, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nombre, $email, $hashedPassword, $role);

            if ($stmt->execute()) {
                header('Location: ' . BASE_URL . 'login?success=Cuenta creada exitosamente. Por favor inicia sesión.');
                exit;
            } else {
                header('Location: ' . BASE_URL . 'register?error=Error al crear la cuenta. Intenta de nuevo.');
                exit;
            }
        } else {
            header('Location: ' . BASE_URL . 'register');
            exit;
        }
    }
}
