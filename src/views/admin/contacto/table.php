<?php
require_once __DIR__ . '/../../../core/auth.php';
$requiredScripts = ['admin/ajax-edit.js', 'admin/ajax-form.js', 'admin/ajax-delete.js', 'admin/ajax-reload.js'];
?>

<h2 class="table-title">📞 Información de Contacto</h2>

<div class="table-block">
  <table class="admin-table">
    <thead>
      <tr>
        <th>Icono</th>
        <th>Etiqueta</th>
        <th>Tipo</th>
        <th>Valor</th>
        <th>Visible</th>
        <th>Orden</th>
        <th class="col-actions">Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($contacts)): ?>
        <?php foreach ($contacts as $contact): ?>
          <tr>
            <td><?= htmlspecialchars($contact['icon']) ?></td>
            <td><?= htmlspecialchars($contact['label']) ?></td>
            <td><?= htmlspecialchars($contact['field_type']) ?></td>
            <td><?= htmlspecialchars(substr($contact['field_value'], 0, 30)) ?></td>
            <td class="text-center"><?= $contact['is_visible'] ? '✅' : '❌' ?></td>
            <td><?= $contact['sort_order'] ?></td>
            <td class="col-actions">
              <a href="#"
                class="btn-edit"
                data-edit
                data-id="<?= $contact['id'] ?>"
                data-type="contacto"
                title="Editar campo">
                ✏️
              </a>

              <a href="#"
                class="btn-delete"
                data-ajax-delete
                data-url="<?= BASE_URL ?>admin/contacto/delete"
                data-id="<?= $contact['id'] ?>"
                data-confirm="¿Eliminar este campo de contacto?"
                title="Eliminar campo">
                🗑️
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="7" class="text-center">No hay campos de contacto registrados.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
