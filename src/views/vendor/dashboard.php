<?php
// Autenticación específica para vendedor se maneja en el VendorController.
?>

<section class="vendor-hero">
  <div>
    <p class="eyebrow">Dashboard de vendedor</p>
    <h1>Hola, <?= htmlspecialchars($user['nombre']) ?> 👋</h1>
    <p class="subtitle">Revisa tus métricas rápidas y actúa sobre productos y órdenes.</p>
    <div class="hero-actions">
      <a class="btn primary" href="<?= BASE_URL ?>vendor/productos">Gestionar productos</a>
      <a class="btn ghost" href="<?= BASE_URL ?>vendor/ordenes">Ver órdenes</a>
    </div>
  </div>
  <div class="hero-metric">
    <p class="label">Ingresos totales</p>
    <p class="value">$<?= number_format($revenue, 2) ?></p>
    <span class="hint">Actualizado al momento</span>
  </div>
</section>

<div class="vendor-grid">
  <article class="stat-card">
    <div class="stat-icon">📦</div>
    <div>
      <p class="label">Productos activos</p>
      <p class="value"><?= $productCount ?></p>
      <a class="link" href="<?= BASE_URL ?>vendor/productos">Ver productos</a>
    </div>
  </article>

  <article class="stat-card">
    <div class="stat-icon">🧾</div>
    <div>
      <p class="label">Órdenes procesadas</p>
      <p class="value"><?= $orderCount ?></p>
      <a class="link" href="<?= BASE_URL ?>vendor/ordenes">Ver órdenes</a>
    </div>
  </article>

  <article class="stat-card">
    <div class="stat-icon">💰</div>
    <div>
      <p class="label">Ingresos</p>
      <p class="value">$<?= number_format($revenue, 2) ?></p>
      <span class="hint">Suma de tus productos vendidos</span>
    </div>
  </article>

  <article class="stat-card">
    <div class="stat-icon">👤</div>
    <div>
      <p class="label">Tu perfil</p>
      <p class="value"><?= htmlspecialchars($user['email']) ?></p>
      <a class="link" href="<?= BASE_URL ?>vendor/perfil">Editar perfil</a>
    </div>
  </article>
</div>

<?php if ($lowStockProducts && $lowStockProducts->num_rows > 0): ?>
<section class="panel">
  <header class="panel-header">
    <div>
      <p class="eyebrow">Alerta</p>
      <h2>Productos con bajo stock</h2>
      <p class="subtitle">Reabastece pronto para no perder ventas.</p>
    </div>
    <a class="btn ghost" href="<?= BASE_URL ?>vendor/productos">Gestionar stock</a>
  </header>
  <div class="table-responsive">
    <table class="simple-table">
      <thead>
        <tr>
          <th>Producto</th>
          <th class="right">Stock</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($p = $lowStockProducts->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($p['nombre']) ?></td>
          <td class="right"><span class="pill pill-danger"><?= $p['stock'] ?></span></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</section>
<?php endif; ?>
