<?php
$page_css = 'index.css';
$page_js  = 'cart.js';

// Los controllers ya están cargados desde el router
use App\Controllers\TestimonialController;
use App\Controllers\ContactController;

$testimonialController = new TestimonialController();
$contactController = new ContactController();

include_once __DIR__ . '/../layouts/header.php';

$carouselRes = $conn->query("
  SELECT a.id, a.title, a.meta_description,
         (SELECT image_path FROM article_images ai WHERE ai.article_id = a.id AND ai.is_primary = 1 ORDER BY ai.id ASC LIMIT 1) AS image_path
  FROM articles a
  WHERE a.is_carousel = 1 AND a.is_visible = 1
  ORDER BY a.published_at DESC
  LIMIT 5
");
?>
<section class="carrousel-container">
  <div class="carrousel" id="mainCarousel">
    <?php if ($carouselRes && $carouselRes->num_rows): ?>
      <?php while ($a = $carouselRes->fetch_assoc()): ?>
        <?php
          $bgImage = !empty($a['image_path']) ? BASE_URL . 'uploads/' . htmlspecialchars($a['image_path']) : null;
          $bgStyle = $bgImage
            ? "background: linear-gradient(135deg, rgba(0,0,0,0.45), rgba(0,0,0,0.45)), url('{$bgImage}') center/cover no-repeat;"
            : 'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);';
        ?>
        <div class="slide" style="<?= $bgStyle ?>">
          <div class="slide-content">
            <h2><?= htmlspecialchars($a['title']) ?></h2>
            <p><?= htmlspecialchars($a['meta_description'] ?? '') ?></p>
            <a class="slide-link" href="<?= BASE_URL ?>articulo/<?= $a['id'] ?>">Ver artículo</a>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="slide" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="slide-content">
          <h2>Bienvenido a Props Fotográficos</h2>
          <p>Encuentra los mejores accesorios para tus sesiones</p>
        </div>
      </div>
    <?php endif; ?>
  </div>
  <button class="carousel-nav prev" id="carouselPrev" aria-label="Diapositiva anterior">❮</button>
  <button class="carousel-nav next" id="carouselNext" aria-label="Siguiente diapositiva">❯</button>
</section>

<!-- MENSAJES DE ERROR/SUCCESS -->
<?php if (isset($_GET['error'])): ?>
    <div style="background-color: #fee; color: #c33; padding: 12px 16px; margin: 12px 20px; border-radius: 4px; border-left: 4px solid #c33;">
        ⚠️ <?= htmlspecialchars($_GET['error']) ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
    <div style="background-color: #efe; color: #3c3; padding: 12px 16px; margin: 12px 20px; border-radius: 4px; border-left: 4px solid #3c3;">
        ✅ <?= htmlspecialchars($_GET['success']) ?>
    </div>
<?php endif; ?>

<!-- BARRA DE BÚSQUEDA DESTACADA -->
<section class="search-section">
  <h2>Busca nuestros productos</h2>
  <form method="GET" action="<?= BASE_URL ?>productos" class="search-form">
    <input type="text" name="q" placeholder="Buscar productos, categorías..." class="search-input" aria-label="Buscar productos">
    <button type="submit" class="search-btn">🔍 Buscar</button>
  </form>
</section>

<!-- CATEGORÍAS PRINCIPALES -->
<section class="categories-section">
  <h2>Categorías Principales</h2>
  <div class="categories-grid">
    <?php
    $query = "SELECT id, nombre, descripcion FROM categories LIMIT 6";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0):
        while ($cat = $result->fetch_assoc()):
    ?>
        <a href="<?= BASE_URL ?>productos?categoria=<?= urlencode($cat['nombre']) ?>" class="category-card">
          <div class="category-icon">📦</div>
          <h3><?= htmlspecialchars($cat['nombre']) ?></h3>
          <p><?= htmlspecialchars($cat['descripcion'] ?? '') ?></p>
        </a>
    <?php endwhile; else: ?>
        <p>No hay categorías disponibles.</p>
    <?php endif; ?>
    <a href="<?= BASE_URL ?>productos" class="category-card category-all">
      <div class="category-icon">🎯</div>
      <h3>Ver Todo</h3>
      <p>Explorar todas las categorías</p>
    </a>
  </div>
</section>

<!-- OFERTAS ESPECIALES -->
<section class="offers-section">
  <h2>Ofertas Especiales</h2>
  <div class="offers-grid">
    <?php
    $query = "SELECT id, nombre, descripcion, precio, oferta, imagen FROM products WHERE oferta > 0 LIMIT 4";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0):
        while ($row = $result->fetch_assoc()):
            $descuento = round((($row['precio'] - $row['oferta']) / $row['precio']) * 100);
    ?>
        <a href="<?= BASE_URL ?>product/<?= $row['id'] ?>" class="offer-card">
          <div class="offer-badge">-<?= $descuento ?>%</div>
          <img src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($row['imagen']) ?>" 
               alt="<?= htmlspecialchars($row['nombre']) ?>"
               loading="lazy"
               class="offer-img">
          <div class="offer-content">
            <h3><?= htmlspecialchars($row['nombre']) ?></h3>
            <div class="price-section">
              <span class="price-old">$<?= number_format($row['precio'], 2) ?></span>
              <span class="price-new">$<?= number_format($row['oferta'], 2) ?></span>
            </div>
            <button class="offer-btn">Ver Oferta</button>
          </div>
        </a>
    <?php endwhile; else: ?>
        <p>No hay ofertas disponibles en este momento.</p>
    <?php endif; ?>
  </div>
</section>

<!-- PRODUCTOS DESTACADOS -->
<section class="featured-section">
  <h2>Productos Destacados</h2>
  <div class="productos">
    <?php
    $query = "SELECT id, nombre, descripcion, precio, imagen FROM products WHERE destacado = 1 LIMIT 6";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0):
        while ($row = $result->fetch_assoc()):
    ?>
        <div class="card-wrapper">
          <button class="btn-wishlist" 
                  data-product-id="<?= $row['id'] ?>" 
                  title="Agregar a favoritos"
                  aria-label="Agregar a favoritos">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
            </svg>
          </button>
          <a href="<?= BASE_URL ?>product/<?= $row['id'] ?>" class="card">
            <img src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($row['imagen']) ?>" 
                 alt="<?= htmlspecialchars($row['nombre']) ?>"
                 loading="lazy"
                 class="card-img">
            <div class="card-content">
              <h3><?= htmlspecialchars($row['nombre']) ?></h3>
              <p class="card-description"><?= htmlspecialchars($row['descripcion']) ?></p>
              <span class="price">$<?= number_format($row['precio'], 2) ?></span>
            </div>
          </a>
          <div class="card-actions">
            <button class="btn-add-cart" data-add-cart data-product-id="<?= $row['id'] ?>">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
              </svg>
              <span>Agregar al carrito</span>
            </button>
          </div>
        </div>
    <?php endwhile; else: ?>
        <p>No hay productos destacados.</p>
    <?php endif; ?>
  </div>
</section>

<!-- TESTIMONIOS -->
<section class="testimonials-section">
  <h2>Lo que dicen nuestros clientes</h2>
  <div class="testimonials-grid">
    <?php
    $testimonials = $testimonialController->getVisible();
    foreach ($testimonials as $testimonial):
    ?>
    <div class="testimonial-card">
      <div class="stars">
        <?php for ($i = 0; $i < $testimonial['rating']; $i++): ?>⭐<?php endfor; ?>
      </div>
      <p class="testimonial-text">"<?= htmlspecialchars($testimonial['content']) ?>"</p>
      <p class="testimonial-author">— <?= htmlspecialchars($testimonial['author']) ?></p>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ÚLTIMAS NOTICIAS -->
<section class="news-section">
  <div class="section-header-wrapper">
    <h2>📰 Últimas Noticias y Artículos</h2>
    <p class="section-subtitle">Mantente informado con nuestras últimas publicaciones</p>
  </div>
  <div class="articulos">
    <?php
    $query = "
      SELECT a.id, a.title, a.content, a.published_at, a.author,
             (SELECT image_path FROM article_images ai WHERE ai.article_id = a.id AND ai.is_primary = 1 ORDER BY ai.id ASC LIMIT 1) AS image_path
      FROM articles a
      WHERE a.is_visible = 1
      ORDER BY a.published_at DESC
      LIMIT 3
    ";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0):
        while ($a = $result->fetch_assoc()):
    ?>
        <a href="<?= BASE_URL ?>articulo/<?= $a['id'] ?>" class="card article-card" title="Leer: <?= htmlspecialchars($a['title']) ?>">
          <?php if (!empty($a['image_path'])): ?>
            <div class="article-thumb">
              <img src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($a['image_path']) ?>" alt="<?= htmlspecialchars($a['title']) ?>" loading="lazy">
            </div>
          <?php endif; ?>
          <div class="article-badge">📄 Artículo</div>
          <div class="article-date"><?= date('d M Y', strtotime($a['published_at'])) ?></div>
          <div class="card-content">
            <h3><?= htmlspecialchars($a['title']) ?></h3>
            <small class="article-author">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" style="vertical-align: middle; margin-right: 4px;">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
              </svg>
              Por <?= htmlspecialchars($a['author']) ?>
            </small>
            <p><?= mb_substr(strip_tags($a['content']), 0, 120) ?>...</p>
            <span class="read-more">
              Leer más 
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle;">
                <path d="M5 12h14M12 5l7 7-7 7"/>
              </svg>
            </span>
          </div>
        </a>
    <?php endwhile; else: ?>
        <div class="no-articles">
          <p>📭 No hay artículos disponibles en este momento.</p>
        </div>
    <?php endif; ?>
  </div>
</section>

<!-- SUSCRIPCIÓN A NEWSLETTER -->
<section class="newsletter-section">
  <div class="newsletter-container">
    <h2>Suscríbete a nuestro Newsletter</h2>
    <p>Recibe ofertas exclusivas y las últimas novedades directamente en tu correo</p>
    <form class="newsletter-form" id="newsletterForm">
      <input type="email" name="email" placeholder="Tu correo electrónico" required class="newsletter-input" aria-label="Correo para newsletter">
      <button type="submit" class="newsletter-btn">Suscribirse</button>
    </form>
    <p class="newsletter-notice">No compartimos tu email con terceros</p>
  </div>
</section>

<!-- INFORMACIÓN DE CONTACTO -->
<section class="contact-info-section">
  <h2>¿Tienes preguntas?</h2>
  <div class="contact-grid">
    <?php
    $contacts = $contactController->getVisible();
    foreach ($contacts as $contact):
    ?>
    <div class="contact-card">
      <div class="contact-icon"><?= htmlspecialchars($contact['icon']) ?></div>
      <h3><?= htmlspecialchars($contact['label']) ?></h3>
      <?php
        $contact_value = htmlspecialchars($contact['field_value']);
        if ($contact['field_type'] === 'email'):
      ?>
        <a href="mailto:<?= $contact_value ?>"><?= $contact_value ?></a>
      <?php elseif ($contact['field_type'] === 'phone'): ?>
        <a href="tel:<?= str_replace(' ', '', $contact_value) ?>"><?= $contact_value ?></a>
      <?php else: ?>
        <p><?= $contact_value ?></p>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>

<script>
  // Carousel automático
  const carousel = document.getElementById('mainCarousel');
  const carouselPrev = document.getElementById('carouselPrev');
  const carouselNext = document.getElementById('carouselNext');
  let carouselIndex = 0;

  function showSlide(n) {
    const slides = carousel.querySelectorAll('.slide');
    if (n >= slides.length) carouselIndex = 0;
    if (n < 0) carouselIndex = slides.length - 1;
    carousel.style.transform = `translateX(-${carouselIndex * 100}%)`;
  }

  function nextSlide() {
    carouselIndex++;
    showSlide(carouselIndex);
  }

  function prevSlide() {
    carouselIndex--;
    showSlide(carouselIndex);
  }

  carouselPrev.addEventListener('click', prevSlide);
  carouselNext.addEventListener('click', nextSlide);

  // Auto-advance carousel cada 5 segundos
  setInterval(nextSlide, 5000);

  // Newsletter form
  document.getElementById('newsletterForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = e.target.querySelector('.newsletter-input').value;
    
    // Aquí puedes agregar la lógica para guardar el email
    console.log('Email suscrito:', email);
    alert('¡Gracias por suscribirse!');
    e.target.reset();
  });
</script>
