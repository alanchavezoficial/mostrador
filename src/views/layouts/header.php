<?php 
require_once __DIR__ . '/../../../config/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?= isset($meta_title) ? htmlspecialchars($meta_title) . ' | ' : '' ?>Props Fotográficos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (!empty($meta_description)): ?>
        <meta name="description" content="<?= htmlspecialchars($meta_description) ?>">
    <?php endif; ?>
    <?php if (!empty($meta_keywords)): ?>
        <meta name="keywords" content="<?= htmlspecialchars($meta_keywords) ?>">
    <?php endif; ?>

    <!-- Estilos base -->
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/temas.css.php">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">
    
    <!-- Sistema unificado de componentes -->
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/utilities.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/buttons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/forms.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/card.css">
    
    <!-- Estilos específicos -->
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/admin/toast.css">


    <!-- Estilos personalizados por página -->
    <?php
    if (isset($page_css)) {
        echo '<link rel="stylesheet" href="' . BASE_URL . 'public/css/' . htmlspecialchars($page_css) . '">';
    }
    if (isset($page2_css)) {
        echo '<link rel="stylesheet" href="' . BASE_URL . 'public/css/' . htmlspecialchars($page2_css) . '">';
    }
    if (isset($page3_css)) {
        echo '<link rel="stylesheet" href="' . BASE_URL . 'public/css/' . htmlspecialchars($page3_css) . '">';
    }
    ?>
</head>

<body>
    <header>
        <div class="header-container">
            <div class="header-left">
                <h1><a href="<?= BASE_URL ?>">📸 Props Fotográficos</a></h1>
                <button id="public-menu-toggle" class="menu-toggle" aria-label="Abrir menú" aria-expanded="false">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
            
            <nav class="nav-main">
                <a href="<?= BASE_URL ?>" class="nav-link">🏠 Inicio</a>
                <a href="<?= BASE_URL ?>productos" class="nav-link">🛍️ Productos</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?= BASE_URL ?>orders" class="nav-link">📦 Mis pedidos</a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>wishlist" class="nav-link">❤️ Wishlist</a>
                <a href="<?= BASE_URL ?>cart" class="nav-link highlight">🛒 Carrito</a>

                <div class="auth-buttons-mobile">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="user-info-mobile">
                            <span class="user-name-mobile">👤 <?= htmlspecialchars(substr($_SESSION['nombre'], 0, 20)) ?></span>
                            <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'vendedor')): ?>
                                <a href="<?= BASE_URL ?><?= $_SESSION['role'] === 'admin' ? 'admin/dashboard' : 'vendor/dashboard' ?>" class="btn-dashboard" style="background: #10b981; color: white; padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.9rem;">📊 Dashboard</a>
                            <?php endif; ?>
                            <a href="<?= BASE_URL ?>logout" class="btn-logout">Salir</a>
                        </div>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>login" class="btn-login">Ingresar</a>
                        <a href="<?= BASE_URL ?>register" class="btn-register">Registrarse</a>
                    <?php endif; ?>
                </div>
            </nav>

            <div class="auth-section">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="user-info">
                        <span class="user-name">👤 <?= htmlspecialchars(substr($_SESSION['nombre'], 0, 20)) ?></span>
                        <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'vendedor')): ?>
                            <a href="<?= BASE_URL ?><?= $_SESSION['role'] === 'admin' ? 'admin/dashboard' : 'vendor/dashboard' ?>" class="btn-dashboard" style="background: #10b981; color: white; padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; margin-right: 0.5rem;">📊 Dashboard</a>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>logout" class="btn-logout">Salir</a>
                    </div>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>login" class="btn-login">Ingresar</a>
                    <a href="<?= BASE_URL ?>register" class="btn-register">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <main>