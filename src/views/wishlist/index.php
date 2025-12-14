<?php
$page_css = $page_css ?? 'product.css';
$page_js = 'cart.js';
include_once __DIR__ . '/../layouts/header.php';
?>
<section class="cart-page">
  <h1>Mis favoritos</h1>
  <?php if (empty($products)): ?>
    <p>No tienes productos en tu wishlist.</p>
  <?php else: ?>
    <div class="productos" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:16px;">
      <?php foreach ($products as $p): ?>
        <div class="card" style="padding:12px; border:1px solid #e5e7eb; border-radius:12px;">
          <img src="<?= BASE_URL ?>public/img/<?= htmlspecialchars($p['imagen']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>" style="width:100%; height:180px; object-fit:cover; border-radius:8px;">
          <h3><?= htmlspecialchars($p['nombre']) ?></h3>
          <p>$<?= number_format($p['precio'], 2, ',', '.') ?></p>
          <div style="display:flex; gap:8px;">
            <button class="btn btn-primary" data-add-cart data-product-id="<?= $p['id'] ?>">Agregar al carrito</button>
            <button class="btn btn-primary" data-wishlist data-product-id="<?= $p['id'] ?>">Quitar</button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
<?php include_once __DIR__ . '/../layouts/footer.php'; ?>
