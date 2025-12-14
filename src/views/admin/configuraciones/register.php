
<?php 
require_once __DIR__ . '/../../../core/auth.php'; 
$requiredScripts = [
    'admin/input-format.js'
];
?>

<form method="POST"
      action="<?= BASE_URL ?>admin/configuraciones/crear"
      class="form-block">
  <?= csrf_field(); ?>

  <h2 class="form-title">🧩 Nueva configuración</h2>

  <div class="form-group">
    <label for="register-clave">Clave</label>
    <input type="text" id="register-clave" name="clave"
           class="form-input" required>
  </div>

  <div class="form-group">
    <label for="register-valor">Valor</label>
    <textarea id="register-valor" name="valor"
              class="form-input" rows="4" required></textarea>
  </div>

  <div class="form-group">
    <label for="register-tipo">Tipo</label>
    <select name="tipo" id="register-tipo" class="form-input" required>
      <option value="texto">Texto</option>
      <option value="color">Color</option>
      <option value="enlace">Enlace</option>
      <option value="booleano">Booleano</option>
      <option value="email">Email</option>
      <option value="numero">Número</option>
      <option value="json">JSON</option>
    </select>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn-primary">💾 Guardar configuración</button>
  </div>
</form>
