<?php
require_once __DIR__ . '/../../../core/auth.php';
$requiredScripts = ['admin/ajax-edit.js', 'admin/ajax-form.js', 'admin/ajax-delete.js', 'admin/ajax-reload.js'];
?>

<h2 class="table-title">💬 Testimonios existentes</h2>

<div class="table-block">
  <table class="admin-table">
    <thead>
      <tr>
        <th>Autor</th>
        <th>Rating</th>
        <th>Contenido</th>
        <th>Visible</th>
        <th>Fecha</th>
        <th class="col-actions">Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($testimonials)): ?>
        <?php foreach ($testimonials as $testimonial): ?>
          <tr>
            <td><?= htmlspecialchars($testimonial['author']) ?></td>
            <td>
              <span class="rating-badge">
                <?php for ($i = 0; $i < $testimonial['rating']; $i++): ?>⭐<?php endfor; ?>
              </span>
            </td>
            <td><?= htmlspecialchars(substr($testimonial['content'], 0, 50)) ?>...</td>
            <td class="text-center"><?= $testimonial['is_visible'] ? '✅' : '❌' ?></td>
            <td><?= date('d/m/Y', strtotime($testimonial['created_at'])) ?></td>
            <td class="col-actions">
              <a href="#"
                class="btn-edit"
                data-edit
                data-id="<?= $testimonial['id'] ?>"
                data-type="testimonios"
                title="Editar testimonio">
                ✏️
              </a>

              <a href="#"
                class="btn-delete"
                data-ajax-delete
                data-url="<?= BASE_URL ?>admin/testimonios/delete"
                data-id="<?= $testimonial['id'] ?>"
                data-confirm="¿Eliminar este testimonio?"
                title="Eliminar testimonio">
                🗑️
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="6" class="text-center">No hay testimonios registrados.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
