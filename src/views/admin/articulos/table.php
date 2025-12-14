<?php
require_once __DIR__ . '/../../../core/auth.php';
$requiredScripts = ['admin/ajax-edit.js', 'admin/ajax-form.js', 'admin/ajax-delete.js', 'admin/ajax-reload.js'];
?>

<h2 class="table-title">📚 Artículos existentes</h2>

<div class="table-block">
  <table class="admin-table">
    <thead>
      <tr>
        <th>Título</th>
        <th>Autor</th>
        <th>Visible</th>
        <th>Destacado</th>
        <th>Publicado</th>
        <th class="col-actions">Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($articles && $articles instanceof mysqli_result && $articles->num_rows): ?>
        <?php while ($a = $articles->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($a['title']) ?></td>
            <td><?= htmlspecialchars($a['author']) ?></td>
            <td class="text-center"><?= $a['is_visible'] ? '✅' : '❌' ?></td>
            <td class="text-center"><?= $a['is_featured'] ? '⭐' : '' ?></td>
            <td><?= date('Y-m-d', strtotime($a['published_at'])) ?></td>
            <td class="col-actions">
              <a href="#"
                class="btn-edit"
                data-edit
                data-id="<?= $a['id'] ?>"
                data-type="articulos"
                title="Editar artículo">
                ✏️
              </a>

              <a href="#"
                class="btn-delete"
                data-ajax-delete
                data-url="<?= BASE_URL ?>admin/articulos/delete"
                data-id="<?= $a['id'] ?>"
                data-confirm="¿Eliminar este artículo?"
                title="Eliminar artículo">
                🗑️
              </a>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr>
          <td colspan="6" class="text-center">⚠️ No hay artículos disponibles</td>
        </tr>
      <?php endif; ?>
    </tbody>

  </table>
</div>