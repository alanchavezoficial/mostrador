<?php
$page_css = $page_css ?? 'cart.css';
?>
<section class="cart-page">
  <h1>Mis pedidos</h1>
  <?php if (isset($_GET['placed'])): ?>
    <div class="cart-summary" style="margin-bottom:12px;">
      <p>Pedido creado correctamente (#<?= htmlspecialchars($_GET['order'] ?? '') ?>)</p>
      <a class="btn-primary" href="<?= BASE_URL ?>checkout">Hacer otro pedido</a>
    </div>
  <?php endif; ?>

  <?php if (empty($orders)): ?>
    <p>No tienes pedidos aún.</p>
  <?php else: ?>
    <table class="cart-table">
      <thead>
        <tr>
          <th>Número</th>
          <th>Estado</th>
          <th>Pago</th>
          <th>Total</th>
          <th>Fecha</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $o): ?>
          <tr>
            <td data-label="Número">#<?= htmlspecialchars($o['order_number']) ?></td>
            <td data-label="Estado"><?= htmlspecialchars($o['status']) ?></td>
            <td data-label="Pago"><?= htmlspecialchars($o['payment_status']) ?></td>
            <td data-label="Total">$<?= number_format($o['total_amount'], 2, ',', '.') ?></td>
            <td data-label="Fecha"><?= htmlspecialchars($o['created_at']) ?></td>
            <td data-label="Acciones">
              <a class="btn-primary" href="<?= BASE_URL ?>orders/detail?id=<?= $o['id'] ?>">Ver</a>
              <a class="btn-primary" href="<?= BASE_URL ?>orders/invoice?id=<?= $o['id'] ?>" target="_blank" rel="noopener">Factura</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</section>
