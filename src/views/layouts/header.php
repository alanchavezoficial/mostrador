<?php require_once __DIR__ . '/../../../config/config.php'; ?>
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
        <h1>Props Fotográficos</h1>
        <button id="public-menu-toggle" class="menu-toggle" aria-label="Abrir menú" aria-expanded="false">☰</button>
        <nav>
            <a href="<?= BASE_URL ?>">
                <p>Inicio</p>
            </a>
            <a href="<?= BASE_URL ?>productos">
                <p>Productos</p>
            </a>
            <a href="<?= BASE_URL ?>orders">
                <p>Mis pedidos</p>
            </a>
            <a href="<?= BASE_URL ?>wishlist">
                <p>Wishlist</p>
            </a>
            <a href="<?= BASE_URL ?>cart">
                <p>Carrito</p>
            </a>
            <a href="<?= BASE_URL ?>contacto">
                <p>Contacto</p>
            </a>
        </nav>
    </header>
    <main>