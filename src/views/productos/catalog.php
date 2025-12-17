<?php
$page_css  = $page_css ?? 'product.css';
$page2_css = 'index.css'; // Reuse homepage card/button styles
$page_js   = 'cart.js';
?>
<section class="cart-page">
  <h1>Catálogo</h1>
  <form method="GET" action="<?= BASE_URL ?>productos" style="display:grid; gap:8px; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); background:#fff; padding:12px; border:1px solid #e5e7eb; border-radius:12px; margin-bottom:16px;">
    <input type="text" name="q" placeholder="Buscar" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" class="qty-input" style="width:100%;">
    <select name="categoria" class="qty-input" style="width:100%;">
      <option value="">Todas las categorías</option>
      <?php foreach ($cats as $c): ?>
        <option value="<?= htmlspecialchars($c['nombre']) ?>" <?= (($_GET['categoria'] ?? '') === $c['nombre']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($c['nombre']) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <input type="number" name="precio_min" step="0.01" placeholder="Precio min" value="<?= htmlspecialchars($_GET['precio_min'] ?? '') ?>" class="qty-input" style="width:100%;">
    <input type="number" name="precio_max" step="0.01" placeholder="Precio max" value="<?= htmlspecialchars($_GET['precio_max'] ?? '') ?>" class="qty-input" style="width:100%;">
    <select name="orden" class="qty-input" style="width:100%;">
      <option value="recientes" <?= (($_GET['orden'] ?? '') === 'recientes') ? 'selected' : '' ?>>Recientes</option>
      <option value="precio_asc" <?= (($_GET['orden'] ?? '') === 'precio_asc') ? 'selected' : '' ?>>Precio ↑</option>
      <option value="precio_desc" <?= (($_GET['orden'] ?? '') === 'precio_desc') ? 'selected' : '' ?>>Precio ↓</option>
      <option value="nombre" <?= (($_GET['orden'] ?? '') === 'nombre') ? 'selected' : '' ?>>Nombre</option>
    </select>
    <button class="btn-primary" type="submit">Filtrar</button>
  </form>

  <?php if (empty($products)): ?>
    <p>No se encontraron productos.</p>
  <?php else: ?>
    <div class="productos">
      <?php foreach ($products as $p): ?>
        <div class="card-wrapper">
          <button class="btn-wishlist" 
                  data-product-id="<?= $p['id'] ?>" 
                  title="Agregar a favoritos"
                  aria-label="Agregar a favoritos">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
            </svg>
          </button>

          <a href="<?= BASE_URL ?>product/<?= $p['id'] ?>" class="card">
            <img src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($p['imagen']) ?>" 
                 alt="<?= htmlspecialchars($p['nombre']) ?>"
                 loading="lazy"
                 class="card-img">
            <div class="card-content">
              <h3><?= htmlspecialchars($p['nombre']) ?></h3>
              <?php if (!empty($p['categoria_nombre'])): ?>
                <small><?= htmlspecialchars($p['categoria_nombre']) ?></small>
              <?php endif; ?>
              <span class="price">$<?= number_format($p['precio'], 2) ?></span>
            </div>
          </a>
          <div class="card-actions">
            <button class="btn-add-cart" data-add-cart data-product-id="<?= $p['id'] ?>">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
              </svg>
              <span>Agregar al carrito</span>
            </button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
