<?php
$page_css = $page_css ?? 'cart.css';
?>
<section class="cart-page">
  <h1>Pedido #<?= htmlspecialchars($order['order_number']) ?></h1>
  <div class="cart-summary" style="flex-wrap: wrap;">
    <p>Estado: <strong><?= htmlspecialchars($order['status']) ?></strong></p>
    <p>Pago: <strong><?= htmlspecialchars($order['payment_status']) ?></strong></p>
    <p>Total: <strong>$<?= number_format($order['total_amount'], 2, ',', '.') ?></strong></p>
    <a class="btn-primary" href="<?= BASE_URL ?>orders/invoice?id=<?= $order['id'] ?>" target="_blank" rel="noopener">Descargar factura</a>
  </div>

  <div class="cart-summary" style="flex-direction: column; align-items: flex-start;">
    <p><strong>Envío:</strong> <?= nl2br(htmlspecialchars($order['shipping_address'])) ?></p>
    <?php if (!empty($order['billing_address'])): ?>
      <p><strong>Facturación:</strong> <?= nl2br(htmlspecialchars($order['billing_address'])) ?></p>
    <?php endif; ?>
    <?php if (!empty($order['notes'])): ?>
      <p><strong>Notas:</strong> <?= nl2br(htmlspecialchars($order['notes'])) ?></p>
    <?php endif; ?>
  </div>

  <h2>Productos</h2>
  <table class="cart-table">
    <thead>
      <tr>
        <th>Producto</th>
        <th>Precio</th>
        <th>Cantidad</th>
        <th>Subtotal</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($order['items'] as $item): ?>
        <tr>
          <td data-label="Producto"><?= htmlspecialchars($item['product_name']) ?></td>
          <td data-label="Precio">$<?= number_format($item['price'], 2, ',', '.') ?></td>
          <td data-label="Cantidad"><?= (int)$item['quantity'] ?></td>
          <td data-label="Subtotal">$<?= number_format($item['subtotal'], 2, ',', '.') ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>
