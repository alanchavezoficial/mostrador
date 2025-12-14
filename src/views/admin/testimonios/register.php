<?php 
require_once __DIR__ . '/../../../core/auth.php'; 
?>

<h2 class="form-title">💬 Nuevo Testimonio</h2>

<form method="POST" action="<?= BASE_URL ?>admin/testimonios/crear" class="form-block">
  <?= csrf_field(); ?>

  <!-- Autor del testimonio -->
  <div class="form-group">
    <label for="author">Autor:</label>
    <input type="text" id="author" name="author" required placeholder="Nombre del cliente">
  </div>

  <!-- Calificación -->
  <div class="form-group">
    <label for="rating">Calificación (1-5):</label>
    <select id="rating" name="rating" required>
      <option value="">Seleccionar</option>
      <?php for ($i = 5; $i >= 1; $i--): ?>
        <option value="<?= $i ?>"><?= str_repeat('⭐', $i) ?> (<?= $i ?>)</option>
      <?php endfor; ?>
    </select>
  </div>

  <!-- Contenido -->
  <div class="form-group">
    <label for="content">Testimonio:</label>
    <textarea id="content" name="content" rows="6" required placeholder="Escribe el testimonio del cliente..."></textarea>
  </div>

  <!-- Visibilidad -->
  <div class="form-group">
    <label>
      <input type="checkbox" name="is_visible" checked>
      Visible en el sitio
    </label>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn">💾 Guardar testimonio</button>
  </div>
</form>
