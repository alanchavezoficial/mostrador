<?php 
require_once __DIR__ . '/../../../core/auth.php'; 
?>
<form method="POST"
  action="<?= BASE_URL ?>admin/articulos/editar"
  enctype="multipart/form-data"
  class="form-block"
  id="edit-article-form">
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
    <div id="edit-content-editor" class="rich-editor"></div>
    <input type="hidden" id="edit-content" name="content" required>
  </div>


  <div class="form-group">
    <label for="edit-author">Autor</label>
    <input type="text" name="author"
           id="edit-author"
           value="<?= htmlspecialchars($userName ?? '') ?>"
           class="form-input" disabled>
    <input type="hidden" name="author" value="<?= htmlspecialchars($userName ?? '') ?>">
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

    <label>
      <input type="checkbox" name="is_carousel" <?= ($article['is_carousel'] ?? 0) ? 'checked' : '' ?>>
      Mostrar en carrusel (máx. 5)
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

  <div class="form-group">
    <label for="edit-images">Imágenes (puedes agregar más)</label>
    <input type="file" id="edit-images" name="images[]" multiple accept="image/*" class="form-input">
    <small>La primera nueva se marcará como principal si no existe una.</small>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn-primary">💾 Guardar cambios</button>
  </div>
</form>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Inicializar el editor de contenido
    const editor = new RichEditor('edit-content-editor');
    
    // Cargar el contenido existente
    editor.setContent(<?= json_encode($article['content']) ?>);
    
    // Manejar el envío del formulario
    document.getElementById('edit-article-form').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const contentInput = document.getElementById('edit-content');
      const editorContent = editor.getContent();
      
      // Validar que haya contenido
      if (!editorContent || !editorContent.trim() || editorContent.trim() === '<br>') {
        showToast('El contenido no puede estar vacío', 'error', 5000);
        return false;
      }
      
      // Copiar el contenido del editor al input oculto
      contentInput.value = editorContent;
      
      // Enviar por AJAX
      const formData = new FormData(this);
      
      fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => response.json().catch(() => response))
      .then(data => {
        if (data.success || (data.status === 'success')) {
          showToast('✅ Artículo actualizado correctamente', 'success', 3000);
          setTimeout(() => {
            window.location.href = '<?= BASE_URL ?>admin/articulos?msg=edited';
          }, 1500);
        } else {
          const errorMsg = data.message || data.error || 'Error al actualizar el artículo';
          showToast('❌ ' + errorMsg, 'error', 5000);
        }
      })
      .catch(error => {
        showToast('❌ Error en la conexión: ' + error.message, 'error', 5000);
      });
    });

    // Mantener sincronizado mientras escribe
    const editorElement = document.querySelector('#edit-content-editor .rich-editor-content');
    if (editorElement) {
      editorElement.addEventListener('input', function() {
        document.getElementById('edit-content').value = this.innerHTML;
      });
    }
  });
</script>
