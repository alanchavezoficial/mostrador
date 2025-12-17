<?php
$page_css = $page_css ?? 'cart.css';
$page_js = $page_js ?? 'cart.js';
?>

<section class="cart-page">
  <h1>Tu carrito</h1>
  <?php if (empty($items)): ?>
    <p>No tienes productos en el carrito.</p>
  <?php else: ?>
    <table class="cart-table">
      <thead>
        <tr>
          <th>Producto</th>
          <th>Precio</th>
          <th>Cantidad</th>
          <th>Subtotal</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $item): ?>
          <tr data-product-id="<?= $item['product_id'] ?>">
            <td class="product" data-label="Producto">
              <img src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($item['imagen']) ?>" alt="<?= htmlspecialchars($item['nombre']) ?>" />
              <span><?= htmlspecialchars($item['nombre']) ?></span>
            </td>
            <td data-label="Precio">$<?= number_format($item['precio'], 2, ',', '.') ?></td>
            <td data-label="Cantidad">
              <input type="number" min="1" value="<?= (int)$item['quantity'] ?>" class="qty-input" />
            </td>
            <td class="subtotal" data-label="Subtotal">$<?= number_format($item['subtotal'], 2, ',', '.') ?></td>
            <td data-label="Acción"><button class="btn-remove">🗑️</button></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div class="cart-summary">
      <p>Total: <strong>$<?= number_format($total, 2, ',', '.') ?></strong></p>
      <?php if (isset($_SESSION['user_id'])): ?>
        <a class="btn-primary" href="<?= BASE_URL ?>checkout">Proceder al pago</a>
      <?php else: ?>
        <a class="btn-primary" href="<?= BASE_URL ?>login?redirect=checkout">Inicia sesión para continuar</a>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</section>
