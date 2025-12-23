<?php
// src/views/productos/show.php
$page_js = 'cart.js';
?>
<article class="product-detail parent" style="display:grid; grid-template-columns: repeat(auto-fit,minmax(280px,1fr)); gap:16px; align-items:start;">
    <div class="div1" style="grid-column:1/-1;">
        <a href="<?= BASE_URL ?>" class="btn-volver">← Volver al listado</a>
    </div>
    <div class="div3" style="display:flex; flex-direction:column; gap:10px;">
        <?php $mainImage = $gallery[0]['image_path'] ?? $producto['imagen']; ?>
        <img id="main-photo" src="<?= BASE_URL ?>public/uploads/<?= htmlspecialchars($mainImage) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>" style="width:100%; border-radius:12px; object-fit:cover;">
        <?php if (!empty($gallery)): ?>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <?php foreach ($gallery as $img): ?>
                    <img src="<?= BASE_URL ?>public/uploads/<?= htmlspecialchars($img['image_path']) ?>" alt="<?= htmlspecialchars($img['alt_text'] ?? $producto['nombre']) ?>" style="width:72px; height:72px; object-fit:cover; border:1px solid #e5e7eb; border-radius:8px; cursor:pointer;" onclick="document.getElementById('main-photo').src=this.src;">
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="div4" style="display:flex; flex-direction:column; gap:8px;">
        <h2 class="div2" style="margin:0;"><?= htmlspecialchars($producto['nombre']) ?></h2>
        <?php if (!empty($avg['total_reviews'])): ?>
          <p>Rating: <?= number_format($avg['avg_rating'], 1) ?> ⭐ (<?= (int)$avg['total_reviews'] ?>)</p>
        <?php endif; ?>
        <p><span>Precio:</span> $<?= number_format($producto['precio'] / 100, 2, ',', '.') ?></p>
        <p><span>Categoría:</span> <?= htmlspecialchars($producto['categoria_nombre'] ?? 'Sin categoría') ?></p>
        <p><span>Descripción:</span> <?= nl2br(htmlspecialchars($producto['descripcion'])) ?></p>
        <div class="div5" style="display:flex; gap:10px; flex-wrap:wrap; margin-top:12px;">
            <a href="<?= BASE_URL ?>consulta?id=<?= $producto['id'] ?>" class="btn btn-primary">
                <div class="btn-consulta">Consultar</div>
            </a>
            <button class="btn btn-primary" data-add-cart data-product-id="<?= $producto['id'] ?>">Agregar al carrito</button>
            <button class="btn btn-primary" data-wishlist data-product-id="<?= $producto['id'] ?>">Agregar a wishlist</button>
        </div>
    </div>

    <div id="reviews" style="grid-column:1/-1; margin-top:16px;">
        <h3>Reseñas</h3>
        <?php if (!empty($reviews)): ?>
            <div style="display:grid; gap:10px;">
                <?php foreach ($reviews as $rev): ?>
                    <div style="border:1px solid #e5e7eb; border-radius:10px; padding:10px;">
                        <strong><?= str_repeat('⭐', (int)$rev['rating']) ?></strong>
                        <?php if (!empty($rev['title'])): ?>
                            <div><?= htmlspecialchars($rev['title']) ?></div>
                        <?php endif; ?>
                        <p style="margin:6px 0;"><?= nl2br(htmlspecialchars($rev['content'])) ?></p>
                        <small><?= htmlspecialchars($rev['created_at']) ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Aún no hay reseñas.</p>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>reviews/add" style="margin-top:12px; display:grid; gap:8px;">
            <input type="hidden" name="product_id" value="<?= $producto['id'] ?>">
            <label>Calificación
                <select name="rating" class="qty-input" required>
                    <option value="5">5</option>
                    <option value="4">4</option>
                    <option value="3">3</option>
                    <option value="2">2</option>
                    <option value="1">1</option>
                </select>
            </label>
            <label>Título
                <input type="text" name="title" class="qty-input">
            </label>
            <label>Comentario
                <textarea name="content" rows="3" class="qty-input" required></textarea>
            </label>
            <button class="btn btn-primary" type="submit">Enviar reseña</button>
        </form>
    </div>
</article>
