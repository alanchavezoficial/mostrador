<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL);
    exit;
}

$page_css = 'login.css';
include __DIR__ . '/../layouts/header.php';
?>

<div class="login-box">
    <h1>CREAR CUENTA</h1>

    <?php if (isset($_GET['error'])): ?>
        <p class="error"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
        <p class="success">Cuenta creada exitosamente. Por favor inicia sesión.</p>
    <?php endif; ?>

    <form action="<?= BASE_URL ?>register" method="POST" autocomplete="off">
        <?= csrf_field(); ?>
        
        <h3>Nombre Completo</h3>
        <input type="text" name="nombre" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">

        <h3>Email</h3>
        <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

        <h3>Contraseña</h3>
        <input type="password" name="password" required>

        <h3>Confirmar Contraseña</h3>
        <input type="password" name="password_confirm" required>

        <button type="submit">Crear Cuenta</button>
    </form>

    <p style="text-align: center; margin-top: 20px;">
        ¿Ya tienes cuenta? <a href="<?= BASE_URL ?>login">Inicia sesión aquí</a>
    </p>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
