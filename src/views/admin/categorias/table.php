<?php
require_once __DIR__ . '/../../../core/auth.php';
$requiredScripts = ['admin/ajax-edit.js', 'admin/ajax-form.js', 'admin/ajax-delete.js', 'admin/ajax-reload.js'];
?>

<h2 class="table-title">📂 Categorías registradas</h2>

<div class="table-block">
  <table class="admin-table">
    <thead>
      <tr>
        <th>Nombre</th>
        <th>Slug</th>
        <th>Padre</th>
        <th>Activa</th>
        <th>Orden</th>
        <th>Creada</th>
        <th class="col-actions">Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($categories && $categories instanceof mysqli_result && $categories->num_rows): ?>
        <?php while ($cat = $categories->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($cat['nombre']) ?></td>
            <td><?= htmlspecialchars($cat['slug']) ?></td>
            <td><?= htmlspecialchars($cat['parent_nombre'] ?? '–') ?></td>
            <td class="text-center"><?= $cat['is_active'] ? '✅' : '—' ?></td>
            <td><?= $cat['orden'] ?></td>
            <td><?= htmlspecialchars($cat['created_at']) ?></td>
            <td class="col-actions">
              <a
                href="#"
                class="btn-edit"
                data-edit
                data-type="categorias"
                data-id="<?= $cat['id'] ?>"
                title="Editar categoría">
                ✏️
              </a>
              <a
                href="#"
                data-ajax-delete
                data-url="<?= BASE_URL ?>admin/categorias/delete"
                data-id="<?= $cat['id'] ?>"
                data-confirm="¿Eliminar esta categoría?"
                class="btn-delete">
                🗑️
              </a>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr>
          <td colspan="7" class="text-center">⚠️ No hay categorías registradas</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>