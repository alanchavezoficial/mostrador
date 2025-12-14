<?php
$page_css = 'index.css';
$page_js  = 'cart.js';

// Los controllers ya están cargados desde el router
use App\Controllers\TestimonialController;
use App\Controllers\ContactController;

$testimonialController = new TestimonialController();
$contactController = new ContactController();

include_once __DIR__ . '/../layouts/header.php';
?>

<!-- BREADCRUMBS -->
<nav class="breadcrumbs" aria-label="Migas de pan">
  <a href="<?= BASE_URL ?>">Inicio</a>
</nav>

<!-- BARRA DE BÚSQUEDA DESTACADA -->
<section class="search-section">
  <h2>Busca nuestros productos</h2>
  <form method="GET" action="<?= BASE_URL ?>productos" class="search-form">
    <input type="text" name="q" placeholder="Buscar productos, categorías..." class="search-input" aria-label="Buscar productos">
    <button type="submit" class="search-btn">🔍 Buscar</button>
  </form>
</section>

<!-- CARROUSEL PRINCIPAL -->
<section class="carrousel-container">
  <div class="carrousel" id="mainCarousel">
    <div class="slide" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
      <div class="slide-content">
        <h2>Bienvenido a Props Fotográficos</h2>
        <p>Encuentra los mejores accesorios para tus sesiones</p>
      </div>
    </div>
    <div class="slide" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
      <div class="slide-content">
        <h2>Ofertas Especiales</h2>
        <p>Descubre nuestras promociones exclusivas</p>
      </div>
    </div>
    <div class="slide" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
      <div class="slide-content">
        <h2>Catálogo Premium</h2>
        <p>Explora nuestra amplia selección de productos</p>
      </div>
    </div>
  </div>
  <button class="carousel-nav prev" id="carouselPrev" aria-label="Diapositiva anterior">❮</button>
  <button class="carousel-nav next" id="carouselNext" aria-label="Siguiente diapositiva">❯</button>
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
        <a href="<?= BASE_URL ?>product?id=<?= $row['id'] ?>" class="offer-card">
          <div class="offer-badge">-<?= $descuento ?>%</div>
          <img src="<?= BASE_URL ?>public/img/<?= htmlspecialchars($row['imagen']) ?>" 
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
        <a href="<?= BASE_URL ?>product?id=<?= $row['id'] ?>" class="card">
          <img src="<?= BASE_URL ?>public/img/<?= htmlspecialchars($row['imagen']) ?>" 
               alt="<?= htmlspecialchars($row['nombre']) ?>"
               loading="lazy"
               class="card-img">
          <div class="card-content">
            <h3><?= htmlspecialchars($row['nombre']) ?></h3>
            <p><?= htmlspecialchars($row['descripcion']) ?></p>
            <span class="price">$<?= number_format($row['precio'], 2) ?></span>
          </div>
        </a>
        <div class="card-actions" style="margin-top:6px;">
          <button class="btn btn-primary" data-add-cart data-product-id="<?= $row['id'] ?>">Agregar al carrito</button>
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
  <h2>Últimas Noticias y Artículos</h2>
  <div class="articulos">
    <?php
    $query = "SELECT id, title, content, published_at, author FROM articles WHERE is_visible = 1 ORDER BY published_at DESC LIMIT 3";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0):
        while ($a = $result->fetch_assoc()):
    ?>
        <a href="<?= BASE_URL ?>articulo?id=<?= $a['id'] ?>" class="card article-card">
          <div class="article-date"><?= date('d M Y', strtotime($a['published_at'])) ?></div>
          <div class="card-content">
            <h3><?= htmlspecialchars($a['title']) ?></h3>
            <small class="article-author">Por <?= htmlspecialchars($a['author']) ?></small>
            <p><?= mb_substr(strip_tags($a['content']), 0, 100) ?>...</p>
            <span class="read-more">Leer artículo →</span>
          </div>
        </a>
    <?php endwhile; else: ?>
        <p>No hay artículos disponibles.</p>
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

<?php include_once __DIR__ . '/../../views/layouts/footer.php'; ?>

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
