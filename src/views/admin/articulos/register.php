<?php 
require_once __DIR__ . '/../../../core/auth.php'; 
global $conn; 
?>

<h2 class="form-title">📝 Publicar nuevo artículo</h2>

<form method="POST" action="<?= BASE_URL ?>admin/articulos/crear" class="form-block" enctype="multipart/form-data">
  <?= csrf_field(); ?>

  <!-- Título del artículo -->
  <div class="form-group">
    <label for="title">Título:</label>
    <input type="text" id="title" name="title" required>
  </div>

  <!-- Contenido -->
  <div class="form-group">
    <label for="content">Contenido:</label>
    <textarea id="content" name="content" rows="6" required></textarea>
  </div>

  <!-- Autor -->
  <div class="form-group">
    <label for="author">Autor:</label>
    <input type="text" id="author" name="author" value="">
  </div>

  <!-- Visibilidad y destacado -->
  <div class="form-group">
    <label>
      <input type="checkbox" name="is_visible" checked>
      Visible en el sitio
    </label>
    <label>
      <input type="checkbox" name="is_featured">
      Marcar como destacado
    </label>
    <label>
      <input type="checkbox" name="is_carousel">
      Mostrar en carrusel (máx. 5)
    </label>
  </div>

  <!-- Productos relacionados -->
  <div class="form-group">
    <label for="related_products">Productos relacionados:</label>
    <select id="related_products" name="related_products[]" multiple>
      <?php
      $prods = $conn->query("SELECT id, nombre FROM products ORDER BY nombre");
      while ($p = $prods->fetch_assoc()):
      ?>
        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
      <?php endwhile; ?>
    </select>
  </div>

  <!-- Imágenes del artículo -->
  <div class="form-group">
    <label for="images">Imágenes (puedes subir varias)</label>
    <input type="file" id="images" name="images[]" multiple accept="image/*" class="form-input">
    <small>La primera será considerada principal si no hay otra marcada.</small>
  </div>

  <!-- Categorías relacionadas -->
  <div class="form-group">
    <label for="related_categories">Categorías relacionadas:</label>
    <select id="related_categories" name="related_categories[]" multiple>
      <?php
      $cats = $conn->query("SELECT id, nombre FROM categories ORDER BY nombre");
      while ($c = $cats->fetch_assoc()):
      ?>
        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
      <?php endwhile; ?>
    </select>
  </div>

  <!-- Botón de envío -->
  <button type="submit" class="btn-primary">📤 Publicar artículo</button>
</form>
