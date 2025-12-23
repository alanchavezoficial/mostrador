<?php
require_once __DIR__ . '/../../../core/auth.php'; 
$requiredScripts = ['admin/ajax-edit.js', 'admin/ajax-form.js', 'admin/ajax-delete.js', 'admin/ajax-reload.js'];
?>

<h2 class="table-title">👥 Usuarios registrados</h2>

<div class="table-block">
  <table class="admin-table">
    <thead>
      <tr>
        <th>Nombre completo</th>
        <th>Correo electrónico</th>
        <th>Rol</th>
        <th>Fecha de creación</th>
        <?php if (strtolower($_SESSION['role'] ?? '') === 'dueno' || strtolower($_SESSION['role'] ?? '') === 'owner'): ?><th>API Key</th><?php endif; ?>
        <th class="col-actions">Acciones</th>
      </tr>
    </thead>
    <tbody>
  <?php if ($users && $users instanceof mysqli_result && $users->num_rows): ?>
    <?php while ($u = $users->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($u['nombre']) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td><?= htmlspecialchars($u['role']) ?></td>
        <td><?= date('d-m-Y', strtotime($u['created_at'])) ?></td>
        <?php if (strtolower($_SESSION['role'] ?? '') === 'dueno' || strtolower($_SESSION['role'] ?? '') === 'owner'): ?>
          <td style="font-family:monospace;font-size:0.9em;max-width:220px;overflow-x:auto;white-space:nowrap;">
            <?= htmlspecialchars($u['api_key'] ?? '') ?>
          </td>
        <?php endif; ?>
        <td class="col-actions">
          <a href="#"
            class="btn-edit"
            data-edit
            data-type="usuarios"
            data-id="<?= $u['id'] ?>"
            title="Editar usuario">
            ✏️
          </a>

          <a href="#"
            class="btn-delete"
            data-ajax-delete
            data-url="<?= BASE_URL ?>admin/usuarios/delete"
            data-id="<?= $u['id'] ?>"
            data-confirm="¿Eliminar este usuario?"
            title="Eliminar usuario">
            🗑️
          </a>
        </td>
      </tr>
    <?php endwhile; ?>
  <?php else: ?>
    <tr>
      <td colspan="5" class="text-center">⚠️ No hay usuarios registrados</td>
    </tr>
  <?php endif; ?>
</tbody>
  </table>
</div>

