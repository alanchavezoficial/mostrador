<?php 
require_once __DIR__ . '/../../../core/auth.php'; 
?>
<form method="POST"
      action="<?= BASE_URL ?>admin/articulos/editar"
      data-ajax-form
      data-ajax
      class="form-block">
  <?= csrf_field(); ?>

  <h2 class="form-title">Editar artículo</h2>

  <input type="hidden" name="id" value="<?= $article['id'] ?>">

  <div class="form-group">
    <label for="edit-title">Título</label>
    <input type="text" name="title"
           id="edit-title"
           value="<?= htmlspecialchars($article['title']) ?>"
           class="form-input" required>
  </div>

  <div class="form-group">
    <label for="edit-content">Contenido</label>
    <textarea name="content"
              id="edit-content"
              rows="10"
              class="form-input" required><?= htmlspecialchars($article['content']) ?></textarea>
  </div>

  <div class="form-group">
    <label for="edit-author">Autor</label>
    <input type="text" name="author"
           id="edit-author"
           value="<?= htmlspecialchars($article['author']) ?>"
           class="form-input">
  </div>

  <div class="form-group-inline">
    <label>
      <input type="checkbox" name="is_visible" <?= $article['is_visible'] ? 'checked' : '' ?>>
      Visible públicamente
    </label>

    <label>
      <input type="checkbox" name="is_featured" <?= $article['is_featured'] ? 'checked' : '' ?>>
      Destacado
    </label>
  </div>

  <div class="form-group">
    <label for="edit-meta-title">Meta título (SEO)</label>
    <input type="text" name="meta_title"
           id="edit-meta-title"
           value="<?= htmlspecialchars($article['meta_title']) ?>"
           class="form-input">
  </div>

  <div class="form-group">
    <label for="edit-meta-description">Meta descripción (SEO)</label>
    <textarea name="meta_description"
              id="edit-meta-description"
              rows="3"
              class="form-input"><?= htmlspecialchars($article['meta_description']) ?></textarea>
  </div>

  <div class="form-group">
    <label for="edit-products">Productos relacionados</label>
    <select name="related_products[]" multiple class="form-input" id="edit-products">
      <?php
      $selectedProducts = explode(',', $article['related_products'] ?? '');
      while ($p = $products->fetch_assoc()):
        $selected = in_array($p['id'], $selectedProducts) ? 'selected' : '';
      ?>
        <option value="<?= $p['id'] ?>" <?= $selected ?>><?= htmlspecialchars($p['nombre']) ?></option>
      <?php endwhile; ?>
    </select>
  </div>

  <div class="form-group">
    <label for="edit-categories">Categorías relacionadas</label>
    <select name="related_categories[]" multiple class="form-input" id="edit-categories">
      <?php
      $selectedCategories = explode(',', $article['related_categories'] ?? '');
      while ($c = $categories->fetch_assoc()):
        $selected = in_array($c['id'], $selectedCategories) ? 'selected' : '';
      ?>
        <option value="<?= $c['id'] ?>" <?= $selected ?>><?= htmlspecialchars($c['nombre']) ?></option>
      <?php endwhile; ?>
    </select>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn-primary">💾 Guardar cambios</button>
  </div>
</form>
