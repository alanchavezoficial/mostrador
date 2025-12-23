<?php 
require_once __DIR__ . '/../../../core/auth.php'; 
global $conn; 
?>

<h2 class="form-title">📝 Publicar nuevo artículo</h2>

<form method="POST" action="<?= BASE_URL ?>admin/articulos/crear" class="form-block" enctype="multipart/form-data" id="article-form">
  <?= csrf_field(); ?>

  <!-- Título del artículo -->
  <div class="form-group">
    <label for="title">Título:</label>
    <input type="text" id="title" name="title" required>
  </div>

  <!-- Contenido -->
  <div class="form-group">
    <label for="content-editor">Contenido:</label>
    <div id="content-editor" class="rich-editor"></div>
    <input type="hidden" id="content" name="content" required>
  </div>

  <!-- Autor -->
  <div class="form-group">
    <label for="author">Autor:</label>
    <input type="text" id="author" name="author" value="<?= htmlspecialchars($userName ?? '') ?>" disabled>
    <input type="hidden" name="author" value="<?= htmlspecialchars($userName ?? '') ?>">
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

  <!-- Producto enlazado -->
  <div class="form-group">
    <label for="product_id">Enlazar a un producto (opcional):</label>
    <select id="product_id" name="product_id">
      <option value="">-- Sin enlace a producto --</option>
      <?php
      $prods = $conn->query("SELECT id, nombre FROM products ORDER BY nombre");
      while ($p = $prods->fetch_assoc()):
      ?>
        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
      <?php endwhile; ?>
    </select>
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
  <button type="submit" class="btn btn-primary">📤 Publicar artículo</button>
</form>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Inicializar el editor de contenido
    const editor = new RichEditor('content-editor');
    
    // Manejar el envío del formulario
    document.getElementById('article-form').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const contentInput = document.getElementById('content');
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
          showToast('✅ Artículo publicado correctamente', 'success', 3000);
          setTimeout(() => {
            window.location.href = '<?= BASE_URL ?>admin/articulos?msg=created';
          }, 1500);
        } else {
          const errorMsg = data.message || data.error || 'Error al publicar el artículo';
          showToast('❌ ' + errorMsg, 'error', 5000);
        }
      })
      .catch(error => {
        showToast('❌ Error en la conexión: ' + error.message, 'error', 5000);
      });
    });

    // Mantener sincronizado mientras escribe
    const editorElement = document.querySelector('#content-editor .rich-editor-content');
    if (editorElement) {
      editorElement.addEventListener('input', function() {
        document.getElementById('content').value = this.innerHTML;
      });
    }
  });
</script>