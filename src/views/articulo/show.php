<div class="article-container">
  <!-- Back Button -->
  <a href="<?= BASE_URL ?>" class="back-to-articles">
    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
      <path d="M8 0L6.59 1.41 12.17 7H0v2h12.17l-5.58 5.59L8 16l8-8z" transform="rotate(180 8 8)"/>
    </svg>
    Volver a artículos
  </a>

  <!-- Main Article -->
  <article class="articulo-detalle">
    <h1><?= htmlspecialchars($article['title']) ?></h1>
    
    <div class="articulo-meta">
      <em>
        <span class="meta-icon">📅</span>
        <span class="date">Publicado el <?= date('d M Y', strtotime($article['published_at'])) ?></span>
      </em>
      <em>
        <span class="meta-icon">✍️</span>
        <span>por <span class="author"><?= htmlspecialchars($article['author']) ?></span></span>
      </em>
    </div>

    <?php if (!empty($images)): ?>
      <div class="article-gallery">
        <?php foreach ($images as $img): ?>
          <figure class="article-image <?= $img['is_primary'] ? 'primary' : '' ?>">
            <img src="<?= BASE_URL ?>public/uploads/<?= htmlspecialchars($img['image_path']) ?>" alt="<?= htmlspecialchars($article['title']) ?>" loading="lazy">
          </figure>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    
    <div class="contenido rich-text">
      <?= $article['content'] ?>
    </div>

    <!-- Share Buttons -->
    <div class="article-share">
      <span class="article-share-label">Compartir:</span>
      <button class="share-btn" onclick="navigator.share ? navigator.share({title: '<?= htmlspecialchars($article['title']) ?>', url: window.location.href}) : null" aria-label="Compartir artículo">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="18" cy="5" r="3"></circle>
          <circle cx="6" cy="12" r="3"></circle>
          <circle cx="18" cy="19" r="3"></circle>
          <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
          <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
        </svg>
      </button>
      <button class="share-btn" onclick="window.print()" aria-label="Imprimir artículo">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="6 9 6 2 18 2 18 9"></polyline>
          <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
          <rect x="6" y="14" width="12" height="8"></rect>
        </svg>
      </button>
    </div>
  </article>

  <!-- Related Products -->
  <?php if (!empty($relatedProducts)): ?>
    <hr class="section-divider">
    <h2 class="section-header">Productos relacionados</h2>
    <div class="productos-relacionados">
      <?php foreach ($relatedProducts as $p): ?>
        <a href="<?= BASE_URL ?>product/<?= $p['id'] ?>" class="card">
          <img src="<?= BASE_URL ?>public/uploads/<?= htmlspecialchars($p['imagen']) ?>"
               alt="<?= htmlspecialchars($p['nombre']) ?>"
               class="card-img">
          <div class="card-content">
            <h3><?= htmlspecialchars($p['nombre']) ?></h3>
            <p><?= htmlspecialchars($p['descripcion']) ?></p>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <!-- Related Categories -->
  <?php if (!empty($relatedCategories)): ?>
    <hr class="section-divider">
    <h2 class="section-header">Categorías relacionadas</h2>
    <ul class="categorias-relacionadas">
      <?php foreach ($relatedCategories as $c): ?>
        <li><?= htmlspecialchars($c['nombre']) ?></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>
