<?php 
require_once __DIR__ . '/../../../core/auth.php'; 
?>
<form method="POST"
      action="<?= BASE_URL ?>admin/testimonios/editar"
      data-ajax-form
      data-ajax
      class="form-block">
  <?= csrf_field(); ?>

  <h2 class="form-title">Editar Testimonio</h2>

  <input type="hidden" name="id" value="<?= $testimonial['id'] ?>">

  <div class="form-group">
    <label for="edit-author">Autor</label>
    <input type="text" name="author"
           id="edit-author"
           value="<?= htmlspecialchars($testimonial['author']) ?>"
           class="form-input" required>
  </div>

  <div class="form-group">
    <label for="edit-rating">Calificación (1-5)</label>
    <select name="rating" id="edit-rating" class="form-input" required>
      <?php for ($i = 5; $i >= 1; $i--): ?>
        <option value="<?= $i ?>" <?= $testimonial['rating'] == $i ? 'selected' : '' ?>>
          <?= str_repeat('⭐', $i) ?> (<?= $i ?>)
        </option>
      <?php endfor; ?>
    </select>
  </div>

  <div class="form-group">
    <label for="edit-content">Testimonio</label>
    <textarea name="content"
              id="edit-content"
              rows="6"
              class="form-input" required><?= htmlspecialchars($testimonial['content']) ?></textarea>
  </div>

  <div class="form-group-inline">
    <label>
      <input type="checkbox" name="is_visible" <?= $testimonial['is_visible'] ? 'checked' : '' ?>>
      Visible públicamente
    </label>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn">💾 Actualizar</button>
  </div>
</form>
