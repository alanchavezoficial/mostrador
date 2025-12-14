<aside class="menu admin-menu" role="navigation" aria-label="Panel de administración">
  <div class="menu-header">
    <button id="menu-collapse" class="menu-collapse" aria-label="Minimizar menú" title="Minimizar menú">⫶</button>
  </div>
  <ul>
    <li class="menu-item">
      <button class="toggle" aria-expanded="false" aria-controls="submenu-usuarios" data-tooltip="Usuarios" aria-label="Usuarios">
        <span class="icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10zM4 20.5c0-3.5 3.5-6.5 8-6.5s8 3 8 6.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </span>
        <span class="menu-label">Usuarios</span>
      </button>
      <ul id="submenu-usuarios" class="submenu" role="menu" aria-hidden="true">
        <li role="none"><a role="menuitem" href="<?= BASE_URL ?>admin/usuarios?view=register">➕ Crear usuario</a></li>
        <li role="none"><a role="menuitem" href="<?= BASE_URL ?>admin/usuarios?view=table">📋 Lista de usuarios</a></li>
      </ul>
    </li>

    <li class="menu-item">
      <button class="toggle" aria-expanded="false" aria-controls="submenu-productos" data-tooltip="Productos" aria-label="Productos">
        <span class="icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M21 16V8a2 2 0 0 0-1-1.73L13 3a2 2 0 0 0-2 0L4 6.27A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73L11 21a2 2 0 0 0 2 0l7-3.27A2 2 0 0 0 21 16z" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </span>
        <span class="menu-label">Productos</span>
      </button>
      <ul id="submenu-productos" class="submenu" role="menu" aria-hidden="true">
        <li role="none"><a role="menuitem" href="<?= BASE_URL ?>admin/productos?view=register">📦 Crear producto</a></li>
        <li role="none"><a role="menuitem" href="<?= BASE_URL ?>admin/productos?view=table">🛒 Lista de productos</a></li>
      </ul>
    </li>

    <li class="menu-item">
      <button class="toggle" aria-expanded="false" aria-controls="submenu-articulos" data-tooltip="Artículos" aria-label="Artículos">
        <span class="icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M3 7v10a2 2 0 0 0 2 2h14" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M8 3h8v4H8z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </span>
        <span class="menu-label">Artículos</span>
      </button>
      <ul id="submenu-articulos" class="submenu" role="menu" aria-hidden="true">
        <li role="none"><a role="menuitem" href="<?= BASE_URL ?>admin/articulos?view=register">📝 Nuevo artículo</a></li>
        <li role="none"><a role="menuitem" href="<?= BASE_URL ?>admin/articulos?view=table">📚 Lista de artículos</a></li>
      </ul>
    </li>

    <li class="menu-item">
      <button class="toggle" aria-expanded="false" aria-controls="submenu-categorias" data-tooltip="Categorías" aria-label="Categorías">
        <span class="icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M16 3l5 5-9 9-5-5 9-9z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M7 14l-4 4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </span>
        <span class="menu-label">Categorías</span>
      </button>
      <ul id="submenu-categorias" class="submenu" role="menu" aria-hidden="true">
        <li role="none"><a role="menuitem" href="<?= BASE_URL ?>admin/categorias?view=register">🏷️ Nueva categoría</a></li>
        <li role="none"><a role="menuitem" href="<?= BASE_URL ?>admin/categorias?view=table">📂 Lista de categorías</a></li>
      </ul>
    </li>

    <li class="menu-item">
      <button class="toggle" aria-expanded="false" aria-controls="submenu-testimonios" data-tooltip="Testimonios" aria-label="Testimonios">
        <span class="icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M3 20h18V6H3v14zm0-16h18V2H3v2z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M7 11h4v4H7v-4zm6 0h4v4h-4v-4z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </span>
        <span class="menu-label">Testimonios</span>
      </button>
      <ul id="submenu-testimonios" class="submenu" role="menu" aria-hidden="true">
        <li role="none"><a role="menuitem" href="<?= BASE_URL ?>admin/testimonios?view=register">⭐ Nuevo testimonio</a></li>
        <li role="none"><a role="menuitem" href="<?= BASE_URL ?>admin/testimonios?view=table">💬 Lista de testimonios</a></li>
      </ul>
    </li>

    <li class="menu-item">
      <button class="toggle" aria-expanded="false" aria-controls="submenu-contacto" data-tooltip="Contacto" aria-label="Contacto">
        <span class="icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M21 10.5c0 7.5-9 13.5-9 13.5s-9-6-9-13.5C3 5.6 6.6 2 11 2s8 3.6 8 8.5z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M11 13a2.5 2.5 0 100-5 2.5 2.5 0 000 5z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </span>
        <span class="menu-label">Contacto</span>
      </button>
      <ul id="submenu-contacto" class="submenu" role="menu" aria-hidden="true">
        <li role="none"><a role="menuitem" href="<?= BASE_URL ?>admin/contacto?view=register">➕ Nuevo dato</a></li>
        <li role="none"><a role="menuitem" href="<?= BASE_URL ?>admin/contacto?view=table">📋 Datos de contacto</a></li>
      </ul>
    </li>

    <li class="menu-item">
      <button class="toggle" aria-expanded="false" aria-controls="submenu-cupones" data-tooltip="Cupones" aria-label="Cupones">
        <span class="icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h14v4M4 6v11a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-3" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </span>
        <span class="menu-label">Cupones</span>
      </button>
      <ul id="submenu-cupones" class="submenu" role="menu" aria-hidden="true">
        <li role="none"><a role="menuitem" href="<?= BASE_URL ?>admin/cupones?view=register">🎟️ Crear cupón</a></li>
        <li role="none"><a role="menuitem" href="<?= BASE_URL ?>admin/cupones?view=table">📋 Lista de cupones</a></li>
      </ul>
    </li>

    <li class="menu-item">
      <a href="<?= BASE_URL ?>admin/pedidos" class="toggle" data-tooltip="Pedidos" aria-label="Pedidos">
        <span class="icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-8 2a2 2 0 1 1-4 0 2 2 0 0 1 4 0z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </span>
        <span class="menu-label">Pedidos</span>
      </a>
    </li>

    <li class="menu-item">
      <button class="toggle" aria-expanded="false" aria-controls="submenu-configuraciones" data-tooltip="Configuraciones" aria-label="Configuraciones">
        <span class="icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M12 15.5A3.5 3.5 0 1 0 12 8.5a3.5 3.5 0 0 0 0 7z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M19.4 15a1.8 1.8 0 0 0 .32 1.86l.06.07a1.8 1.8 0 0 1-2.55 2.55l-.07-.06a1.8 1.8 0 0 0-1.86-.32 1.8 1.8 0 0 1-1.28.03 1.8 1.8 0 0 0-2.08 0" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </span>
        <span class="menu-label">Configuraciones</span>
      </button>
      <ul id="submenu-configuraciones" class="submenu" role="menu" aria-hidden="true">
        <li role="none"><a role="menuitem" href="<?= BASE_URL ?>admin/configuraciones?view=register">⚙️ Nueva configuración</a></li>
        <li role="none"><a role="menuitem" href="<?= BASE_URL ?>admin/configuraciones?view=table">🗂️ Lista de configuraciones</a></li>
      </ul>
    </li>

    <!-- Analytics merged into dashboard -->
  </ul>
</aside>