<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Factura <?= htmlspecialchars($order['order_number']) ?></title>
  <style>
    body { font-family: Arial, sans-serif; margin: 32px; color: #0f172a; }
    h1 { font-size: 22px; }
    table { width: 100%; border-collapse: collapse; margin-top: 16px; }
    th, td { border: 1px solid #e5e7eb; padding: 10px; text-align: left; }
    th { background: #f3f4f6; }
    .totals { margin-top: 12px; }
  </style>
</head>
<body>
  <h1>Factura #<?= htmlspecialchars($order['order_number']) ?></h1>
  <p><strong>Fecha:</strong> <?= htmlspecialchars($order['created_at']) ?></p>
  <p><strong>Cliente:</strong> Usuario #<?= htmlspecialchars($order['user_id']) ?></p>
  <p><strong>Envío:</strong><br><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></p>
  <?php if (!empty($order['billing_address'])): ?>
    <p><strong>Facturación:</strong><br><?= nl2br(htmlspecialchars($order['billing_address'])) ?></p>
  <?php endif; ?>

  <table>
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
          <td><?= htmlspecialchars($item['product_name']) ?></td>
          <td>$<?= number_format($item['price'], 2, ',', '.') ?></td>
          <td><?= (int)$item['quantity'] ?></td>
          <td>$<?= number_format($item['subtotal'], 2, ',', '.') ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div class="totals">
    <p><strong>Subtotal:</strong> $<?= number_format($order['subtotal'], 2, ',', '.') ?></p>
    <p><strong>Descuento:</strong> $<?= number_format($order['discount_amount'], 2, ',', '.') ?></p>
    <p><strong>Impuestos:</strong> $<?= number_format($order['tax_amount'], 2, ',', '.') ?></p>
    <p><strong>Total:</strong> $<?= number_format($order['total_amount'], 2, ',', '.') ?></p>
  </div>
</body>
</html>
