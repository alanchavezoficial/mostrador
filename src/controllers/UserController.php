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
                if ($redirect === 'checkout') {
                    header('Location: ' . BASE_URL . 'checkout');
                } else {
                    header('Location: ' . BASE_URL . 'admin/dashboard?success=Bienvenido ' . urlencode($user['nombre']));
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
}
