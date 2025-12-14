<article class="articulo-detalle">
  <h1><?= htmlspecialchars($article['title']) ?></h1>
  <p>
    <em>
      Publicado el <?= date('d M Y', strtotime($article['published_at'])) ?>
      por <?= htmlspecialchars($article['author']) ?>
    </em>
  </p>
  <div class="contenido">
    <?= nl2br(htmlspecialchars($article['content'])) ?>
  </div>
</article>

<?php if (!empty($relatedProducts)): ?>
  <hr>
  <h3>Productos relacionados</h3>
  <div class="productos">
    <?php foreach ($relatedProducts as $p): ?>
      <a href="<?= BASE_URL ?>product?id=<?= $p['id'] ?>" class="card">
        <img src="<?= BASE_URL ?>public/img/<?= htmlspecialchars($p['imagen']) ?>"
             alt="<?= htmlspecialchars($p['nombre']) ?>">
        <div class="card-content">
          <h4><?= htmlspecialchars($p['nombre']) ?></h4>
          <p><?= htmlspecialchars($p['descripcion']) ?></p>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php if (!empty($relatedCategories)): ?>
  <hr>
  <h3>Categorías relacionadas</h3>
  <ul class="categorias-relacionadas">
    <?php foreach ($relatedCategories as $c): ?>
      <li><?= htmlspecialchars($c['nombre']) ?></li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>
