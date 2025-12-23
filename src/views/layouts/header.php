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
    
    <!-- Mejoras UI/UX -->
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/animations.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/skeleton.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/responsive.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/accessibility.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/table.css">
    
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

<body class="<?= isset($body_class) ? htmlspecialchars($body_class) : '' ?>">
    <?php if (empty($defer_nav)): ?>
        <?php include __DIR__ . '/nav.php'; ?>
    <?php endif; ?>
    <main>