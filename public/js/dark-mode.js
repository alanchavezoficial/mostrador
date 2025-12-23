/**
 * Dark Mode Simple
 * Cambia las variables CSS de temas.css.php dinámicamente
 */

class DarkMode {
  constructor() {
    this.storageKey = 'mostrador_dark_mode';
    this.root = document.documentElement;
    
    // Colores originales (light mode)
    this.lightColors = {
      color_primario: getComputedStyle(this.root).getPropertyValue('--color_primario').trim(),
      color_header: getComputedStyle(this.root).getPropertyValue('--color_header').trim(),
      color_boton: getComputedStyle(this.root).getPropertyValue('--color_boton').trim(),
      color_fondo: getComputedStyle(this.root).getPropertyValue('--color_fondo').trim(),
      color_texto: getComputedStyle(this.root).getPropertyValue('--color_texto').trim(),
      color_acento: getComputedStyle(this.root).getPropertyValue('--color_acento').trim(),
      color_titulo: getComputedStyle(this.root).getPropertyValue('--color_titulo').trim(),
      color_subtitulo: getComputedStyle(this.root).getPropertyValue('--color_subtitulo').trim(),
    };
    
    // Colores para dark mode (paleta cálida "rose")
    this.darkColors = {
      color_primario: '#8a5d60',   // acciones y botones principales (más oscuro)
      color_header: '#1a1214',     // encabezados y thead de tablas (más oscuro)
      color_boton: '#7a4d50',      // botones (más oscuro)
      color_fondo: '#0f0a0c',      // fondo general oscuro
      color_texto: '#e8dfe2',      // texto principal
      color_acento: '#6d4548',     // enlaces, bordes y acentos (más oscuro)
      color_titulo: '#f2eaed',     // títulos
      color_subtitulo: '#b8a5aa',  // subtítulos
      color_card: '#1a1214',       // tarjetas y paneles (más oscuro)
      table_border_color: '#2a1e22', // líneas de tablas en dark mode (más oscuras)
    };
    
    this.init();
  }
  
  init() {
    this.createToggleButton();
    this.loadPreference();
  }
  
  loadPreference() {
    const isDark = localStorage.getItem(this.storageKey) === 'true';
    if (isDark) {
      this.enableDarkMode();
    }
  }
  
  enableDarkMode() {
    Object.keys(this.darkColors).forEach(key => {
      this.root.style.setProperty(`--${key}`, this.darkColors[key]);
    });
    this.root.setAttribute('data-theme', 'dark');
    localStorage.setItem(this.storageKey, 'true');
    this.updateButton(true);
  }
  
  disableDarkMode() {
    // Revertir variables a versión clara; si no existe valor original, eliminar la propiedad
    const keys = new Set([...Object.keys(this.lightColors), ...Object.keys(this.darkColors)]);
    keys.forEach(key => {
      const original = this.lightColors[key];
      if (original && original.length > 0) {
        this.root.style.setProperty(`--${key}`, original);
      } else {
        // Si la variable no existe en modo claro, remover para que tome el fallback del CSS
        this.root.style.removeProperty(`--${key}`);
      }
    });
    this.root.setAttribute('data-theme', 'light');
    localStorage.setItem(this.storageKey, 'false');
    this.updateButton(false);
  }
  
  toggle() {
    const isDark = this.root.getAttribute('data-theme') === 'dark';
    if (isDark) {
      this.disableDarkMode();
    } else {
      this.enableDarkMode();
    }
  }
  
  createToggleButton() {
    const button = document.createElement('button');
    button.id = 'dark-mode-toggle';
    button.innerHTML = '🌙';
    button.setAttribute('aria-label', 'Cambiar tema');
    button.style.cssText = `
      position: fixed;
      bottom: 20px;
      right: 20px;
      width: 50px;
      height: 50px;
      border-radius: 50%;
      border: none;
      background: var(--color_primario);
      color: white;
      font-size: 24px;
      cursor: pointer;
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
      z-index: 9999;
      transition: transform 0.2s, box-shadow 0.2s;
    `;
    
    button.addEventListener('mouseenter', () => {
      button.style.transform = 'scale(1.1)';
      button.style.boxShadow = '0 6px 16px rgba(0,0,0,0.3)';
    });
    
    button.addEventListener('mouseleave', () => {
      button.style.transform = 'scale(1)';
      button.style.boxShadow = '0 4px 12px rgba(0,0,0,0.2)';
    });
    
    button.addEventListener('click', () => this.toggle());
    
    document.body.appendChild(button);
    this.button = button;
  }
  
  updateButton(isDark) {
    if (this.button) {
      this.button.innerHTML = isDark ? '☀️' : '🌙';
    }
  }
}

// Inicializar cuando el DOM esté listo
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    window.darkMode = new DarkMode();
  });
} else {
  window.darkMode = new DarkMode();
}
