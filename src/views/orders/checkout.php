<?php
$page_css = $page_css ?? 'cart.css';
?>

<section class="cart-page">
  <h1>Checkout</h1>
  <?php if (!empty($flash_error)): ?>
    <div class="cart-summary" style="border-color:#dc2626; color:#dc2626;">
      <p><?= htmlspecialchars($flash_error) ?></p>
    </div>
  <?php endif; ?>
  <?php if (empty($items)): ?>
    <p>Tu carrito está vacío. <a href="<?= BASE_URL ?>productos">Seguir comprando</a></p>
  <?php else: ?>
    <div class="cart-summary" style="flex-direction: column; align-items: flex-start;">
      <p>Subtotal: <strong>$<?= number_format($subtotal, 2, ',', '.') ?></strong></p>
      <p>Impuestos: <strong>$<?= number_format($tax ?? 0, 2, ',', '.') ?></strong></p>
      <p>Descuentos: <strong>$<?= number_format($discount ?? 0, 2, ',', '.') ?></strong></p>
      <p style="font-size:20px;">Total: <strong>$<?= number_format($total, 2, ',', '.') ?></strong></p>
    </div>

    <form method="POST" action="<?= BASE_URL ?>checkout/place" class="form-block" style="margin-top:16px; display:grid; gap:12px;">
      <label>
        Dirección de envío*
        <textarea name="shipping_address" required rows="3" class="qty-input" style="width:100%;"></textarea>
      </label>
      <label>
        Dirección de facturación (opcional)
        <textarea name="billing_address" rows="2" class="qty-input" style="width:100%;"></textarea>
      </label>
      <label>
        Método de pago
        <select name="payment_method" class="qty-input" style="width:100%;">
          <option value="transferencia">Transferencia</option>
          <option value="contraentrega">Contraentrega</option>
          <option value="tarjeta">Tarjeta (simulado)</option>
        </select>
      </label>
      <label>
        Cupón de descuento
        <input type="text" name="coupon_code" placeholder="INGRESA TU CUPON" value="<?= htmlspecialchars($coupon_code ?? '') ?>" class="qty-input" style="width:100%; text-transform:uppercase;">
      </label>
      <label>
        Notas del pedido
        <textarea name="notes" rows="2" class="qty-input" style="width:100%;"></textarea>
      </label>
      <button class="btn-primary" type="submit">Confirmar pedido</button>
    </form>

    <h2 style="margin-top:24px;">Resumen de productos</h2>
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
        <?php foreach ($items as $item): ?>
          <tr>
            <td class="product">
              <img src="<?= BASE_URL ?>public/img/<?= htmlspecialchars($item['imagen']) ?>" alt="<?= htmlspecialchars($item['nombre']) ?>" />
              <span><?= htmlspecialchars($item['nombre']) ?></span>
            </td>
            <td>$<?= number_format($item['precio'], 2, ',', '.') ?></td>
            <td><?= (int)$item['quantity'] ?></td>
            <td>$<?= number_format($item['subtotal'], 2, ',', '.') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</section>
