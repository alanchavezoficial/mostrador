
<?php 
require_once __DIR__ . '/../../../core/auth.php'; 
?>

<form method="post"
      action="<?= BASE_URL ?>admin/usuarios/editar?ajax=1"
      data-ajax-form
      data-ajax
      class="form-block">
  <?= csrf_field(); ?>

  <h2 class="form-title">Editar usuario</h2>

  <input type="hidden" name="id" value="<?= $user['id'] ?>">

  <div class="form-group">
    <label for="edit-nombre">Nombre</label>
    <input type="text"
           id="edit-nombre"
           name="nombre"
           value="<?= htmlspecialchars($user['nombre']) ?>"
           class="form-input"
           required>
  </div>
  
  <div class="form-group">
    <label for="edit-email">Email</label>
    <input type="email"
           id="edit-email"
           name="email"
           value="<?= htmlspecialchars($user['email']) ?>"
           class="form-input"
           required>
  </div>

  <div class="form-group">
    <label for="edit-role">Rol</label>
    <select name="role" id="edit-role" class="form-input" required>
      <?php foreach ($roles as $r): ?>
        <option value="<?= $r ?>"
                <?= $r === $user['role'] ? 'selected' : '' ?>>
          <?= ucfirst($r) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="form-group">
    <label for="edit-pass">Nueva contraseña</label>
    <input type="password"
           id="edit-pass"
           name="password"
           class="form-input"
           placeholder="Dejar vacío para no modificar">
  </div>

  <div class="form-actions">
    <button type="submit" class="btn-primary">💾 Guardar cambios</button>
  </div>
</form>
