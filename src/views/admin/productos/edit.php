
<?php 
require_once __DIR__ . '/../../../core/auth.php'; 
?>

<form method="POST"
      action="<?= BASE_URL ?>admin/productos/editar?ajax=1"
      enctype="multipart/form-data"
      data-ajax-form
      data-ajax
      class="form-block">
  <?= csrf_field(); ?>

  <h2 class="form-title">Editar producto</h2>
  <input type="hidden" name="id" value="<?= $product['id'] ?>">

  <div class="form-group">
    <label for="edit-nombre">Nombre</label>
    <input type="text" id="edit-nombre" name="nombre"
           value="<?= htmlspecialchars($product['nombre']) ?>"
           class="form-input" required>
  </div>

  <div class="form-group">
    <label for="edit-descripcion">Descripción</label>
    <textarea id="edit-descripcion" name="descripcion"
              class="form-input" required><?= htmlspecialchars($product['descripcion']) ?></textarea>
  </div>

  <div class="form-group">
    <label for="edit-precio">Precio</label>
    <input type="text" id="edit-precio" name="precio"
           value="<?= htmlspecialchars($product['precio']) ?>"
           step="0.01" class="form-input" required data-format="money"> 
  </div>

  <div class="form-group">
    <label for="edit-stock">Stock</label>
    <input type="number" id="edit-stock" name="stock"
           value="<?= htmlspecialchars($product['stock']) ?>"
           class="form-input" required>
  </div>

  <div class="form-group">
    <label for="edit-imagen">Imagen actual</label>
    <?php if (!empty($product['imagen'])): ?>
      <img src="<?= BASE_URL ?>uploads/<?= $product['imagen'] ?>" alt="Imagen actual" style="max-width:120px;">
    <?php endif; ?>
    <input type="file" id="edit-imagen" name="imagen"
           accept="image/*" class="form-input">
  </div>

  <div class="form-group">
    <label for="edit-categoria">Categoría</label>
    <select name="categoria_id" id="edit-categoria" class="form-input" required>
      <?php foreach ($cats as $cat): ?>
        <option value="<?= $cat['id'] ?>"
                <?= ($cat['id'] == $product['categoria_id']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($cat['nombre']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="form-group">
    <label for="edit-destacado">¿Destacado?</label>
    <select name="destacado" id="edit-destacado" class="form-input">
      <option value="1" <?= $product['destacado'] ? 'selected' : '' ?>>Sí</option>
      <option value="0" <?= !$product['destacado'] ? 'selected' : '' ?>>No</option>
    </select>
  </div>

  <fieldset class="form-group">
    <legend style="margin-bottom:0.5em;">Oferta</legend>

    <div class="form-group">
      <label for="edit-oferta-activa">¿Activa?</label>
      <select name="oferta_activa" id="edit-oferta-activa" class="form-input">
        <option value="1" <?= $product['oferta_activa'] ? 'selected' : '' ?>>Sí</option>
        <option value="0" <?= !$product['oferta_activa'] ? 'selected' : '' ?>>No</option>
      </select>
    </div>

    <div class="form-group">
      <label for="edit-oferta-monto">Monto de oferta</label>
      <input type="number" id="edit-oferta-monto" name="oferta_monto"
             value="<?= htmlspecialchars($product['oferta_monto']) ?>"
             step="0.01" class="form-input">
    </div>

    <div class="form-group">
      <label for="edit-oferta-tipo">Tipo de oferta</label>
      <select name="oferta_tipo" id="edit-oferta-tipo" class="form-input">
        <option value="porcentaje" <?= $product['oferta_tipo'] === 'porcentaje' ? 'selected' : '' ?>>%</option>
        <option value="pesos" <?= $product['oferta_tipo'] === 'pesos' ? 'selected' : '' ?>>Monto fijo</option>
      </select>
    </div>
  </fieldset>

  <div class="form-actions">
    <button type="submit" class="btn-primary">💾 Guardar cambios</button>
  </div>
</form>

<script src="../../public/js/admin/number-format.js"></script>
