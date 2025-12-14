<?php
require_once __DIR__ . '/../../../core/auth.php';
$requiredScripts = ['admin/ajax-edit.js', 'admin/ajax-form.js', 'admin/ajax-delete.js', 'admin/ajax-reload.js'];
?>

<h2 class="table-title">🎟️ Cupones registrados</h2>

<?php if (!empty($message)): ?>
  <div class="alert success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="table-block">
  <table class="admin-table">
    <thead>
      <tr>
        <th>Código</th>
        <th>Tipo</th>
        <th>Valor</th>
        <th>Usos</th>
        <th>Expira</th>
        <th>Mínimo</th>
        <th>Activo</th>
        <th class="col-actions">Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($coupons)): ?>
        <tr><td colspan="8" class="text-center">⚠️ No hay cupones registrados</td></tr>
      <?php else: ?>
        <?php foreach ($coupons as $c): ?>
          <tr>
            <td><strong><?= htmlspecialchars($c['code']) ?></strong></td>
            <td><?= $c['discount_type'] === 'fixed' ? 'Monto fijo' : 'Porcentaje' ?></td>
            <td>
              <?php if ($c['discount_type'] === 'fixed'): ?>
                $<?= number_format($c['discount_value'], 2, ',', '.') ?>
              <?php else: ?>
                <?= number_format($c['discount_value'], 2) ?>%
              <?php endif; ?>
            </td>
            <td><?= (int)$c['current_uses'] ?> / <?= $c['max_uses'] === null ? '∞' : (int)$c['max_uses'] ?></td>
            <td><?= !empty($c['expiry_date']) ? date('d/m/Y H:i', strtotime($c['expiry_date'])) : '—' ?></td>
            <td>$<?= number_format($c['minimum_order'], 2, ',', '.') ?></td>
            <td class="text-center"><?= $c['is_active'] ? '✅' : '❌' ?></td>
            <td class="col-actions">
              <a href="#"
                 class="btn-edit"
                 data-edit
                 data-type="cupones"
                 data-id="<?= $c['id'] ?>"
                 title="Editar cupón">
                ✏️
              </a>
              <a href="#"
                 data-ajax-delete
                 data-url="<?= BASE_URL ?>admin/cupones/delete"
                 data-id="<?= $c['id'] ?>"
                 data-confirm="¿Eliminar cupón '<?= htmlspecialchars($c['code']) ?>'?"
                 class="btn-delete"
                 title="Eliminar cupón">
                🗑️
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>
