<header>
    <div class="header-container">
        <div class="header-left">
            <h1><a href="<?= BASE_URL ?>">📸 Props Fotográficos</a></h1>
            <button id="public-menu-toggle" class="menu-toggle" aria-label="Abrir menú" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
        
        <nav class="nav-main">
            <a href="<?= BASE_URL ?>" class="nav-link">🏠 Inicio</a>
            <a href="<?= BASE_URL ?>productos" class="nav-link">🛍️ Productos</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?= BASE_URL ?>orders" class="nav-link">📦 Mis pedidos</a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>wishlist" class="nav-link">❤️ Wishlist</a>
            <a href="<?= BASE_URL ?>cart" class="nav-link">🛒 Carrito</a>

            <div class="auth-buttons-mobile">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="user-info-mobile">
                        <span class="user-name-mobile">👤 <?= htmlspecialchars(substr($_SESSION['nombre'], 0, 20)) ?></span>
                        <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'vendedor')): ?>
                            <a href="<?= BASE_URL ?><?= $_SESSION['role'] === 'admin' ? 'admin/dashboard' : 'vendor/dashboard' ?>" class="btn-dashboard" style="background: #10b981; color: white; padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.9rem;">📊 Dashboard</a>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>logout" class="btn-logout">Salir</a>
                    </div>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>login" class="btn-login">Ingresar</a>
                    <a href="<?= BASE_URL ?>register" class="btn-register">Registrarse</a>
                <?php endif; ?>
            </div>
        </nav>

        <div class="auth-section">
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="user-info">
                    <span class="user-name">👤 <?= htmlspecialchars(substr($_SESSION['nombre'], 0, 20)) ?></span>
                    <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'vendedor')): ?>
                        <a href="<?= BASE_URL ?><?= $_SESSION['role'] === 'admin' ? 'admin/dashboard' : 'vendor/dashboard' ?>" class="btn-dashboard" style="background: #10b981; color: white; padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; margin-right: 0.5rem;">📊 Dashboard</a>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>logout" class="btn-logout">Salir</a>
                </div>
            <?php else: ?>
                <a href="<?= BASE_URL ?>login" class="btn-login">Ingresar</a>
                <a href="<?= BASE_URL ?>register" class="btn-register">Registrarse</a>
            <?php endif; ?>
        </div>
    </div>
</header>
