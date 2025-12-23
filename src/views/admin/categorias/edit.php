
<?php 
require_once __DIR__ . '/../../../core/auth.php'; 
?>

<form method="POST"
      action="<?= BASE_URL ?>admin/categorias/editar"
      data-ajax-form
      data-ajax
      class="form-block">
  <?= csrf_field(); ?>

  <h2 class="form-title">Editar categoría</h2>

  <input type="hidden" name="id" value="<?= $category['id'] ?>">

  <div class="form-group">
    <label for="edit-nombre">Nombre</label>
    <input type="text"
           id="edit-nombre"
           name="nombre"
           value="<?= htmlspecialchars($category['nombre']) ?>"
           class="form-input"
           required>
  </div>

  <div class="form-group">
    <label for="edit-descripcion">Descripción</label>
    <textarea id="edit-descripcion"
              name="descripcion"
              rows="4"
              class="form-input"><?= htmlspecialchars($category['descripcion']) ?></textarea>
  </div>

  <div class="form-group">
    <label for="edit-parent">Categoría padre</label>
    <select name="parent_id" id="edit-parent" class="form-input">
      <option value="">Ninguna</option>
      <?php while ($p = $parents->fetch_assoc()):
        $selected = $category['parent_id'] == $p['id'] ? 'selected' : '';
      ?>
        <option value="<?= $p['id'] ?>" <?= $selected ?>>
          <?= htmlspecialchars($p['nombre']) ?>
        </option>
      <?php endwhile; ?>
    </select>
  </div>

  <div class="form-group-inline">
    <label>
      <input type="checkbox"
             name="is_active"
             <?= $category['is_active'] ? 'checked' : '' ?>>
      Activa
    </label>
  </div>

  <div class="form-group">
    <label for="edit-orden">Orden</label>
    <input type="number"
           id="edit-orden"
           name="orden"
           value="<?= intval($category['orden']) ?>"
           min="0"
           class="form-input">
  </div>

  <div class="form-actions">
    <button type="submit" class="btn btn-primary">💾 Guardar cambios</button>
  </div>
</form>
