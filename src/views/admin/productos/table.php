<?php
require_once __DIR__ . '/../../../core/auth.php';
$requiredScripts = 
['admin/number-format.js', 
'admin/ajax-edit.js', 
'admin/ajax-form.js', 
'admin/ajax-delete.js', 
'admin/ajax-reload.js'
];
?>

<h2 class="table-title">📦 Productos registrados</h2>

<div class="table-block">
  <table class="admin-table">
    <thead>
      <tr>
        <th class="col-img">Imagen</th>
        <th>Nombre</th>
        <th>Precio</th>
        <th>Stock</th>
        <th>Categoría</th>
        <th>Oferta</th>
        <th>Destacado</th>
        <th class="col-actions">Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($products && $products instanceof mysqli_result && $products->num_rows): ?>
        <?php while ($p = $products->fetch_assoc()): ?>
          <tr>
            <td class="text-center">
              <?php if ($p['imagen']): ?>
                <img
                  src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($p['imagen']) ?>"
                  class="table-thumb"
                  alt="<?= htmlspecialchars($p['nombre']) ?>">
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($p['nombre']) ?></td>
            <td>$<?= number_format($p['precio'] / 100, 2, ',', '.') ?></td>
            <td class="text-center"><?= $p['stock'] ?></td>
            <td><?= htmlspecialchars($p['categoria_nombre']) ?></td>
            <td>
              <?php if ($p['oferta_activa']): ?>
                <?= $p['oferta_tipo'] === 'porcentaje'
                  ? htmlspecialchars($p['oferta_monto']) . '%'
                  : '$' . number_format($p['oferta_monto']) ?>
              <?php else: ?>
                —
              <?php endif; ?>
            </td>
            <td class="text-center"><?= $p['destacado'] ? '✅' : '—' ?></td>
            <td class="col-actions">
              <a
                href="#"
                class="btn-edit"
                data-edit
                data-type="productos"
                data-id="<?= $p['id'] ?>"
                title="Editar producto">
                ✏️
              </a>
              <a
                href="#"
                data-ajax-delete
                data-url="<?= BASE_URL ?>admin/productos/delete"
                data-id="<?= $p['id'] ?>"
                data-confirm="¿Eliminar este producto?"
                class="btn-delete"
                title="Eliminar producto">
                🗑️
              </a>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr>
          <td colspan="8" class="text-center">⚠️ No hay productos registrados</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>