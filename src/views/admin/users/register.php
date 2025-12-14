
<?php 
require_once __DIR__ . '/../../../core/auth.php'; 
?>

<h2 class="form-title">👤 Crear nuevo usuario</h2>
<form method="POST"
      action="<?= BASE_URL ?>admin/usuarios/crear"
      data-ajax
      class="form-block">
  <?= csrf_field(); ?>

  <!-- Campo: nombre completo -->
  <div class="form-group">
    <label for="nombre">Nombre completo:</label>
    <input type="text" id="nombre" name="nombre" required>
  </div>

  <!-- Campo: email -->
  <div class="form-group">
    <label for="email">Correo electrónico:</label>
    <input type="email" id="email" name="email" required>
  </div>

  <!-- Campo: contraseña -->
  <div class="form-group">
    <label for="password">Contraseña:</label>
    <input type="password" id="password" name="password" required>
  </div>

  <!-- Campo: rol del usuario -->
  <div class="form-group">
    <label for="role">Rol:</label>
    <select id="role" name="role" required>
      <option value="admin">Administrador</option>
      <option value="owner">Dueño</option>
    </select>
  </div>

  <button type="submit" class="btn-primary">✅ Crear usuario</button>
</form>
