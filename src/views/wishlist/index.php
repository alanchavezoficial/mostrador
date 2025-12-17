<?php
$page_css = $page_css ?? 'product.css';
$page_js = 'cart.js';
?>
<section class="container">
  <h1 style="margin: 24px 0;">Mis favoritos</h1>
  <?php if (empty($products)): ?>
    <p>No tienes productos en tu wishlist.</p>
  <?php else: ?>
    <div class="productos grid-3 grid-auto-sm" style="gap: 1.25rem;">
      <?php foreach ($products as $p): ?>
        <div class="card-producto">
          <img src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($p['imagen']) ?>" 
               alt="<?= htmlspecialchars($p['nombre']) ?>" 
               class="card-img">
          <div class="card-content">
            <h3><?= htmlspecialchars($p['nombre']) ?></h3>
            <p class="price">$<?= number_format($p['precio'], 2, ',', '.') ?></p>
            <div class="card-actions" style="display: flex; gap: 0.5rem; margin-top: 0.75rem;">
              <button class="btn-accent btn-sm" data-add-cart data-product-id="<?= $p['id'] ?>">
                Agregar al carrito
              </button>
              <button class="btn-danger btn-sm" data-wishlist data-product-id="<?= $p['id'] ?>">
                Quitar
              </button>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
