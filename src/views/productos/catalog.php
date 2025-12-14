<?php
$page_css = $page_css ?? 'product.css';
$page_js  = 'cart.js';
include_once __DIR__ . '/../layouts/header.php';
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
    <div class="productos" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:16px;">
      <?php foreach ($products as $p): ?>
        <div class="card" style="padding:12px; border:1px solid #e5e7eb; border-radius:12px;">
          <a href="<?= BASE_URL ?>product?id=<?= $p['id'] ?>" style="text-decoration:none; color:inherit;">
            <img src="<?= BASE_URL ?>public/img/<?= htmlspecialchars($p['imagen']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>" style="width:100%; height:180px; object-fit:cover; border-radius:8px;">
            <h3><?= htmlspecialchars($p['nombre']) ?></h3>
            <p><?= htmlspecialchars($p['categoria_nombre'] ?? '') ?></p>
            <p>$<?= number_format($p['precio'], 2, ',', '.') ?></p>
          </a>
          <div style="display:flex; gap:6px; margin-top:8px;">
            <button class="btn btn-primary" data-add-cart data-product-id="<?= $p['id'] ?>">Agregar</button>
            <button class="btn btn-primary" data-wishlist data-product-id="<?= $p['id'] ?>">Wishlist</button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
<?php include_once __DIR__ . '/../layouts/footer.php'; ?>
