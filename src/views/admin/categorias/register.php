<?php 
require_once __DIR__ . '/../../../core/auth.php'; 
?>

<?php global $conn; ?>

<h2 class="form-title">📂 Crear / Editar categoría</h2>

<form method="POST"
      action="<?= BASE_URL ?>admin/categorias/crear"
      data-ajax
      class="form-block">
  <?= csrf_field(); ?>

  <!-- Campo oculto para edición -->
  <input type="hidden" name="id" value="">

  <!-- Nombre de la categoría -->
  <div class="form-group">
    <label for="nombre">Nombre:</label>
    <input type="text" id="nombre" name="nombre" required>
  </div>

  <!-- Descripción -->
  <div class="form-group">
    <label for="descripcion">Descripción:</label>
    <textarea id="descripcion" name="descripcion" rows="3"></textarea>
  </div>

  <!-- Categoría padre -->
  <div class="form-group">
    <label for="parent_id">Categoría padre:</label>
    <select id="parent_id" name="parent_id">
      <option value="">– Ninguna –</option>
      <?php
      if (isset($categories) && $categories):
        while ($c = $categories->fetch_assoc()):
      ?>
        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
      <?php
        endwhile;
        $categories->data_seek(0);
      endif;
      ?>
    </select>
  </div>

  <!-- Estado activo -->
  <div class="form-group-inline">
    <label>
      <input type="checkbox" name="is_active" value="1" checked>
      Activa
    </label>
  </div>

  <!-- Orden -->
  <div class="form-group">
    <label for="orden">Orden:</label>
    <input type="number" id="orden" name="orden" value="0">
  </div>

  <!-- Botón de guardar -->
  <button type="submit" class="btn-primary">💾 Guardar categoría</button>
</form>
