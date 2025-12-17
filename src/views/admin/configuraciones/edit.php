
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
      class="form-block config-form">
  <?= csrf_field(); ?>

  <div class="form-header">
    <div>
      <p class="eyebrow">Configuración #<?= (int)$config['id'] ?></p>
      <h2 class="form-title">✏️ Editar configuración</h2>
      <p class="muted">Actualiza el valor y el tipo. Usa JSON para estructuras complejas.</p>
    </div>
    <div class="badge-pill">Tipo actual: <?= htmlspecialchars(ucfirst($config['tipo'])) ?></div>
  </div>

  <input type="hidden" name="id" value="<?= $config['id'] ?>">

  <div class="form-group">
    <label for="settings-clave" class="required">Clave</label>
    <input 
      type="text" 
      id="settings-clave" 
      name="clave"
      value="<?= htmlspecialchars($config['clave']) ?>"
      class="form-input"
      required>
  </div>

  <div class="form-group">
    <label for="register-valor" class="required">Valor</label>
    <?php if ($config['tipo'] === 'texto' || $config['tipo'] === 'json'): ?>
      <textarea 
        id="register-valor"
        name="valor"
        class="form-input"
        rows="5"
        required><?= htmlspecialchars($config['valor']) ?></textarea>
    <?php elseif ($config['tipo'] === 'booleano'): ?>
      <label class="checkbox-row">
        <input
          type="checkbox"
          id="register-valor"
          name="valor"
          <?= $config['valor'] ? 'checked' : '' ?>>
        <span>Activo</span>
      </label>
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
    <label for="register-tipo" class="required">Tipo</label>
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
    <a class="btn btn-secondary" href="<?= BASE_URL ?>admin/configuraciones?view=table">Cancelar</a>
    <button type="submit" class="btn btn-primary">💾 Guardar cambios</button>
  </div>
</form>
