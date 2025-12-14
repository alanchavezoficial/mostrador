<?php require_once __DIR__ . '/../../../core/auth.php'; ?>

<form method="POST"
      action="<?= BASE_URL ?>admin/pedidos/editar?ajax=1"
      data-ajax-form
      data-ajax
      class="form-block">
  <?= csrf_field(); ?>

  <h2 class="form-title">Editar pedido #<?= htmlspecialchars($order['order_number']) ?></h2>
  <input type="hidden" name="id" value="<?= $order['id'] ?>">

  <div class="form-group">
    <label>Cliente</label>
    <input type="text" value="<?= htmlspecialchars($order['username'] ?? 'Usuario #' . $order['user_id']) ?>" class="form-input" disabled>
  </div>

  <div class="form-group-inline">
    <div class="form-group">
      <label for="edit-status">Estado del pedido</label>
      <select id="edit-status" name="status" class="form-input">
        <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pendiente</option>
        <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Procesando</option>
        <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Enviado</option>
        <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Entregado</option>
        <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelado</option>
        <option value="returned" <?= $order['status'] === 'returned' ? 'selected' : '' ?>>Devuelto</option>
      </select>
    </div>

    <div class="form-group">
      <label for="edit-payment-status">Estado de pago</label>
      <select id="edit-payment-status" name="payment_status" class="form-input">
        <option value="pending" <?= $order['payment_status'] === 'pending' ? 'selected' : '' ?>>Pendiente</option>
        <option value="completed" <?= $order['payment_status'] === 'completed' ? 'selected' : '' ?>>Completado</option>
        <option value="failed" <?= $order['payment_status'] === 'failed' ? 'selected' : '' ?>>Fallido</option>
        <option value="refunded" <?= $order['payment_status'] === 'refunded' ? 'selected' : '' ?>>Reembolsado</option>
      </select>
    </div>
  </div>

  <div class="form-group-inline">
    <div class="form-group">
      <label for="edit-tracking">Código de seguimiento</label>
      <input type="text" id="edit-tracking" name="tracking_code"
             value="<?= htmlspecialchars($order['tracking_code'] ?? '') ?>"
             class="form-input" placeholder="Ej: ABC123456">
    </div>

    <div class="form-group">
      <label for="edit-shipping">Estado de envío</label>
      <input type="text" id="edit-shipping" name="shipping_status"
             value="<?= htmlspecialchars($order['shipping_status'] ?? '') ?>"
             class="form-input" placeholder="Ej: En tránsito">
    </div>
  </div>

  <div class="form-group">
    <label for="edit-notes">Notas internas</label>
    <textarea id="edit-notes" name="notes" class="form-input" rows="3"><?= htmlspecialchars($order['notes'] ?? '') ?></textarea>
  </div>

  <div class="form-group">
    <strong>Dirección de envío:</strong>
    <p style="margin:4px 0; white-space:pre-wrap;"><?= htmlspecialchars($order['shipping_address']) ?></p>
  </div>

  <div class="form-group">
    <strong>Productos:</strong>
    <table class="admin-table" style="margin-top:8px;">
      <thead>
        <tr>
          <th>Producto</th>
          <th>Precio</th>
          <th>Cant.</th>
          <th>Subtotal</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($order['items'] as $item): ?>
          <tr>
            <td><?= htmlspecialchars($item['product_name']) ?></td>
            <td>$<?= number_format($item['price'], 2, ',', '.') ?></td>
            <td><?= (int)$item['quantity'] ?></td>
            <td>$<?= number_format($item['subtotal'], 2, ',', '.') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="form-group">
    <p><strong>Subtotal:</strong> $<?= number_format($order['subtotal'], 2, ',', '.') ?></p>
    <p><strong>Descuento:</strong> $<?= number_format($order['discount_amount'], 2, ',', '.') ?></p>
    <p><strong>Impuestos:</strong> $<?= number_format($order['tax_amount'], 2, ',', '.') ?></p>
    <p style="font-size:18px;"><strong>Total:</strong> $<?= number_format($order['total_amount'], 2, ',', '.') ?></p>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn-primary">💾 Guardar cambios</button>
  </div>
</form>
