<?php
require_once __DIR__ . '/../../../core/auth.php'; 
$requiredScripts = ['admin/drag-drop.js' , 'admin/oferta-toggle.js', 'admin/number-format.js'];
?>


<h2 class="form-title">📦 Crear / Editar producto</h2>

<form method="POST"
      action="<?= BASE_URL ?>admin/productos/crear"
      data-ajax
      enctype="multipart/form-data"
      class="form-block">
  <?= csrf_field(); ?>

  <input type="hidden" name="id" value="">

  <div class="form-group">
    <label for="nombre">Nombre:</label>
    <input type="text" id="nombre" name="nombre" required>
  </div>

  <div class="form-group">
    <label for="descripcion">Descripción:</label>
    <textarea id="descripcion" name="descripcion" rows="3"></textarea>
  </div>

  <div class="form-group-inline">
    <div class="form-group">
      <label for="precio">Precio:</label>
      <input type="text" id="precio" name="precio" step="0.01" required data-format="money">
    </div>
    <div class="form-group">
      <label for="stock">Stock:</label>
      <input type="number" id="stock" name="stock" value="0" required>
    </div>
  </div>

  <div class="form-group">
    <label for="categoria_id">Categoría:</label>
    <select id="categoria_id" name="categoria_id" required>
      <option value="">– Seleccionar –</option>
      <?php if ($cats): while ($c = $cats->fetch_assoc()): ?>
        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
      <?php endwhile; endif; ?>
    </select>
  </div>

  <div class="form-group-inline">
    <label>
      <input type="checkbox" name="oferta_activa" id="chkOferta" value="1">
      Oferta activa
    </label>
    <label>
      <input type="checkbox" name="destacado" id="chkDestacado" value="1">
      Destacado
    </label>
  </div>

  <div id="ofertaFields" class="form-group" style="display:none;">
    <label for="oferta_monto">Monto de oferta:</label>
    <input type="number" id="oferta_monto" name="oferta_monto" step="0.01">
    <label for="oferta_tipo">Tipo de oferta:</label>
    <select id="oferta_tipo" name="oferta_tipo">
      <option value="porcentaje">Porcentaje</option>
      <option value="pesos">Pesos</option>
    </select>
  </div>

  <div class="form-group">
    <label for="imagen">Imagen:</label>
    <div id="drop-zone" class="drop-zone">
      Arrastrá o hacé clic para subir
    </div>
    <input type="file" name="imagen" id="imgInput" accept="image/jpeg,image/png" style="display:none;">
    <img id="preview" src="" style="max-width:100%; margin-top:10px; display:none;">
  </div>

  <button type="submit" class="btn-primary">💾 Guardar producto</button>
</form>
