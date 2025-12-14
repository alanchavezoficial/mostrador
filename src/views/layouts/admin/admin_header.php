<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title><?= $meta_title ?? 'Panel Admin' ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="<?= BASE_URL ?>public/css/temas.css.php">
  <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>public/css/admin/header.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>css/admin/dashboard-layout.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>css/admin/form-style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>css/admin/table-style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>css/admin/modal-style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>css/admin/shared.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>public/css/admin/menu.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>public/css/admin/toast.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <?php $csrfToken = csrf_token(); ?>
  <meta name="csrf-token" content="<?= $csrfToken ?>">
  <script>
    window.CSRF_TOKEN = "<?= $csrfToken ?>";
  </script>
</head>

<body>
  <header>
    <h1>Panel de Administración</h1>
    <button id="menu-toggle" class="menu-toggle" aria-label="Abrir menú" aria-expanded="false">☰</button>
    <nav>
      <a href="<?= BASE_URL ?>admin/dashboard">
        <p>Dashboard</p>
      </a>
      <a href="<?= BASE_URL ?>admin/usuarios?view=table">
        <p>Usuarios</p>
      </a>
      <a href="<?= BASE_URL ?>logout">
        <p>Salir</p>
      </a>
    </nav>
  </header>
  <main class="dashboard-layout">