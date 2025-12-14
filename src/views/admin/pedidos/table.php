<?php
require_once __DIR__ . '/../../../core/auth.php';
$requiredScripts = ['admin/ajax-edit.js', 'admin/ajax-form.js', 'admin/ajax-reload.js'];
?>

<h2 class="table-title">📦 Pedidos</h2>

<?php if (!empty($message)): ?>
  <div class="alert success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="table-block">
  <table class="admin-table">
    <thead>
      <tr>
        <th>Nº Pedido</th>
        <th>Cliente</th>
        <th>Estado</th>
        <th>Pago</th>
        <th>Total</th>
        <th>Fecha</th>
        <th class="col-actions">Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($orders)): ?>
        <tr><td colspan="7" class="text-center">⚠️ No hay pedidos</td></tr>
      <?php else: ?>
        <?php foreach ($orders as $o): ?>
          <tr>
            <td><strong><?= htmlspecialchars($o['order_number']) ?></strong></td>
            <td><?= htmlspecialchars($o['username'] ?? 'Usuario #' . $o['user_id']) ?></td>
            <td>
              <span style="padding:4px 8px; border-radius:6px; font-size:12px; <?php
                echo match($o['status']) {
                  'pending' => 'background:#fef3c7; color:#92400e;',
                  'processing' => 'background:#dbeafe; color:#1e40af;',
                  'shipped' => 'background:#e0e7ff; color:#4338ca;',
                  'delivered' => 'background:#d1fae5; color:#065f46;',
                  'cancelled' => 'background:#fee2e2; color:#991b1b;',
                  'returned' => 'background:#f3e8ff; color:#6b21a8;',
                  default => 'background:#f3f4f6; color:#374151;'
                };
              ?>">
                <?= htmlspecialchars($o['status']) ?>
              </span>
            </td>
            <td>
              <span style="padding:4px 8px; border-radius:6px; font-size:12px; <?php
                echo match($o['payment_status']) {
                  'completed' => 'background:#d1fae5; color:#065f46;',
                  'pending' => 'background:#fef3c7; color:#92400e;',
                  'failed' => 'background:#fee2e2; color:#991b1b;',
                  'refunded' => 'background:#f3e8ff; color:#6b21a8;',
                  default => 'background:#f3f4f6; color:#374151;'
                };
              ?>">
                <?= htmlspecialchars($o['payment_status']) ?>
              </span>
            </td>
            <td>$<?= number_format($o['total_amount'], 2, ',', '.') ?></td>
            <td><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
            <td class="col-actions">
              <a href="#"
                 class="btn-edit"
                 data-edit
                 data-type="pedidos"
                 data-id="<?= $o['id'] ?>"
                 title="Editar pedido">
                ✏️
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>
