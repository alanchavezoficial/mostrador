
<?php 
require_once __DIR__ . '/../../../core/auth.php'; 
$requiredScripts = [
    'admin/input-format.js'
];
?>

<form method="POST"
      action="<?= BASE_URL ?>admin/configuraciones/editar"
      data-ajax-form
      data-ajax
      class="form-block">
  <?= csrf_field(); ?>

  <h2 class="form-title">✏️ Editar configuración</h2>
  <input type="hidden" name="id" value="<?= $config['id'] ?>">

  <div class="form-group">
    <label for="settings-clave">Clave</label>
    <input 
    type="text" 
    id="settings-clave" 
    name="clave"
    value="<?= htmlspecialchars($config['clave']) ?>"
    class="form-input" 
    required>
  </div>

<div class="form-group">
  <label for="register-valor">Valor</label>
  <?php if ($config['tipo'] === 'texto' || $config['tipo'] === 'json'): ?>
    <textarea 
      id="register-valor"
      name="valor"
      class="form-input"
      rows="4"
      required><?= htmlspecialchars($config['valor']) ?></textarea>
  <?php elseif ($config['tipo'] === 'booleano'): ?>
    <input
      type="checkbox"
      id="register-valor"
      name="valor"
      class="form-input"
      <?= $config['valor'] ? 'checked' : '' ?>>
  <?php else: ?>
    <input
      type="<?= htmlspecialchars($config['tipo'] === 'enlace' ? 'url' : $config['tipo']) ?>"
      id="register-valor"
      name="valor"
      class="form-input"
      value="<?= htmlspecialchars($config['valor']) ?>"
      required>
  <?php endif; ?>
</div>

  <div class="form-group">
    <label for="register-tipo">Tipo</label>
    <select name="tipo" id="register-tipo" class="form-input" required>
      <?php
        $tipos = ['texto', 'color', 'enlace', 'booleano', 'email', 'numero', 'json'];
        foreach ($tipos as $tipo):
          $selected = ($tipo === $config['tipo']) ? 'selected' : '';
          echo "<option value=\"$tipo\" $selected>" . ucfirst($tipo) . "</option>";
        endforeach;
      ?>
    </select>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn-primary">💾 Guardar cambios</button>
  </div>
</form>
