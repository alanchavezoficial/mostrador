/**
 * Accessibility Enhancements - WCAG 2.1 AA
 * Mejora la accesibilidad sin modificar el diseño existente
 */

class AccessibilityManager {
    constructor() {
        this.init();
    }

    init() {
        // Ejecutar cuando el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.enhance());
        } else {
            this.enhance();
        }
    }

    enhance() {
        this.addSkipLinks();
        this.enhanceFocusIndicators();
        this.addAriaLabels();
        this.enhanceKeyboardNavigation();
        this.addLandmarkRoles();
        this.improveFormAccessibility();
        this.enhanceImageAccessibility();
        this.addHeadingStructure();
        this.setupFocusTrap();
    }

    /**
     * Agregar enlaces de "saltar a contenido"
     */
    addSkipLinks() {
        if (document.querySelector('.skip-links')) return;

        const skipLinks = document.createElement('div');
        skipLinks.className = 'skip-links';
        skipLinks.innerHTML = `
            <a href="#main-content" class="skip-link">Saltar al contenido principal</a>
            <a href="#main-nav" class="skip-link">Saltar a navegación</a>
        `;
        document.body.insertBefore(skipLinks, document.body.firstChild);

        // Identificar o crear contenido principal
        let mainContent = document.querySelector('main') || document.querySelector('[role="main"]');
        if (!mainContent) {
            mainContent = document.querySelector('.container, .content, #content');
            if (mainContent && !mainContent.id) {
                mainContent.id = 'main-content';
            }
        }

        // Identificar navegación
        let mainNav = document.querySelector('nav') || document.querySelector('[role="navigation"]');
        if (mainNav && !mainNav.id) {
            mainNav.id = 'main-nav';
        }
    }

    /**
     * Mejorar indicadores de foco
     */
    enhanceFocusIndicators() {
        // Agregar clase cuando se navega con teclado
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                document.body.classList.add('keyboard-nav');
            }
        });

        document.addEventListener('mousedown', () => {
            document.body.classList.remove('keyboard-nav');
        });
    }

    /**
     * Agregar ARIA labels donde falten
     */
    addAriaLabels() {
        // Botones sin texto
        document.querySelectorAll('button:not([aria-label])').forEach(button => {
            const icon = button.querySelector('i, svg');
            if (icon && !button.textContent.trim()) {
                const label = this.inferAriaLabel(button, icon);
                if (label) {
                    button.setAttribute('aria-label', label);
                }
            }
        });

        // Enlaces sin texto
        document.querySelectorAll('a:not([aria-label])').forEach(link => {
            const icon = link.querySelector('i, svg');
            if (icon && !link.textContent.trim()) {
                const label = this.inferAriaLabel(link, icon);
                if (label) {
                    link.setAttribute('aria-label', label);
                }
            }
        });

        // Inputs sin label
        document.querySelectorAll('input:not([aria-label]):not([id])').forEach(input => {
            const placeholder = input.getAttribute('placeholder');
            const type = input.getAttribute('type');
            if (placeholder) {
                input.setAttribute('aria-label', placeholder);
            } else if (type) {
                input.setAttribute('aria-label', this.getInputTypeLabel(type));
            }
        });
    }

    /**
     * Inferir aria-label basado en clases o contenido
     */
    inferAriaLabel(element, icon) {
        const classList = element.className.toLowerCase();
        const title = element.getAttribute('title');
        
        if (title) return title;

        // Iconos comunes
        if (classList.includes('cart') || icon.className.includes('cart')) return 'Carrito de compras';
        if (classList.includes('wishlist') || icon.className.includes('heart')) return 'Lista de deseos';
        if (classList.includes('search') || icon.className.includes('search')) return 'Buscar';
        if (classList.includes('menu') || icon.className.includes('menu')) return 'Menú';
        if (classList.includes('close') || icon.className.includes('close')) return 'Cerrar';
        if (classList.includes('edit') || icon.className.includes('edit')) return 'Editar';
        if (classList.includes('delete') || icon.className.includes('trash')) return 'Eliminar';
        if (classList.includes('add') || icon.className.includes('plus')) return 'Agregar';
        if (classList.includes('user') || icon.className.includes('user')) return 'Usuario';
        if (classList.includes('home') || icon.className.includes('home')) return 'Inicio';
        
        return null;
    }

    /**
     * Obtener label para tipo de input
     */
    getInputTypeLabel(type) {
        const labels = {
            'text': 'Campo de texto',
            'email': 'Correo electrónico',
            'password': 'Contraseña',
            'search': 'Búsqueda',
            'tel': 'Teléfono',
            'number': 'Número',
            'date': 'Fecha',
            'time': 'Hora',
            'url': 'URL'
        };
        return labels[type] || 'Campo de entrada';
    }

    /**
     * Mejorar navegación por teclado
     */
    enhanceKeyboardNavigation() {
        // Escape cierra modales
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const modal = document.querySelector('.modal.active, .modal.show, [role="dialog"][aria-hidden="false"]');
                if (modal) {
                    const closeButton = modal.querySelector('.close, .modal-close, [aria-label*="cerrar"]');
                    if (closeButton) {
                        closeButton.click();
                    }
                }
            }
        });

        // Enter en elementos clickeables
        document.querySelectorAll('[onclick]:not(a):not(button)').forEach(element => {
            if (!element.hasAttribute('tabindex')) {
                element.setAttribute('tabindex', '0');
            }
            element.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    element.click();
                }
            });
        });
    }

    /**
     * Agregar roles ARIA de landmark
     */
    addLandmarkRoles() {
        // Header
        const header = document.querySelector('header');
        if (header && !header.getAttribute('role')) {
            header.setAttribute('role', 'banner');
        }

        // Nav
        const nav = document.querySelector('nav');
        if (nav && !nav.getAttribute('role')) {
            nav.setAttribute('role', 'navigation');
        }

        // Main
        let main = document.querySelector('main');
        if (!main) {
            const content = document.querySelector('.container, .content, #content');
            if (content && !content.getAttribute('role')) {
                content.setAttribute('role', 'main');
            }
        }

        // Footer
        const footer = document.querySelector('footer');
        if (footer && !footer.getAttribute('role')) {
            footer.setAttribute('role', 'contentinfo');
        }

        // Search
        const searchForm = document.querySelector('form[action*="search"], form[action*="buscar"]');
        if (searchForm && !searchForm.getAttribute('role')) {
            searchForm.setAttribute('role', 'search');
        }
    }

    /**
     * Mejorar accesibilidad de formularios
     */
    improveFormAccessibility() {
        // Asociar labels con inputs
        document.querySelectorAll('input, select, textarea').forEach(input => {
            if (!input.id) {
                input.id = 'input-' + Math.random().toString(36).substr(2, 9);
            }

            // Buscar label cercano
            const parent = input.parentElement;
            const label = parent.querySelector('label');
            if (label && !label.getAttribute('for')) {
                label.setAttribute('for', input.id);
            }
        });

        // Marcar campos requeridos
        document.querySelectorAll('[required]').forEach(input => {
            if (!input.getAttribute('aria-required')) {
                input.setAttribute('aria-required', 'true');
            }
        });

        // Agregar mensajes de error accesibles
        document.querySelectorAll('.error, .invalid-feedback').forEach(error => {
            const input = error.previousElementSibling;
            if (input && (input.tagName === 'INPUT' || input.tagName === 'SELECT' || input.tagName === 'TEXTAREA')) {
                if (!error.id) {
                    error.id = 'error-' + Math.random().toString(36).substr(2, 9);
                }
                input.setAttribute('aria-describedby', error.id);
                input.setAttribute('aria-invalid', 'true');
            }
        });
    }

    /**
     * Mejorar accesibilidad de imágenes
     */
    enhanceImageAccessibility() {
        document.querySelectorAll('img:not([alt])').forEach(img => {
            // Si es decorativa
            if (img.className.includes('decor') || img.closest('.bg-image')) {
                img.setAttribute('alt', '');
                img.setAttribute('role', 'presentation');
            } else {
                // Intentar inferir del título o nombre de archivo
                const title = img.getAttribute('title');
                const src = img.getAttribute('src');
                if (title) {
                    img.setAttribute('alt', title);
                } else if (src) {
                    const filename = src.split('/').pop().split('.')[0];
                    img.setAttribute('alt', filename.replace(/[-_]/g, ' '));
                }
            }
        });
    }

    /**
     * Verificar estructura de encabezados
     */
    addHeadingStructure() {
        const headings = document.querySelectorAll('h1, h2, h3, h4, h5, h6');
        let lastLevel = 0;

        headings.forEach(heading => {
            const level = parseInt(heading.tagName.substring(1));
            
            // Advertir en consola si se salta niveles
            if (level - lastLevel > 1) {
                console.warn(`Heading structure issue: jumped from h${lastLevel} to h${level}`, heading);
            }
            
            lastLevel = level;
        });
    }

    /**
     * Configurar focus trap para modales
     */
    setupFocusTrap() {
        const focusableSelectors = 'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])';

        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) { // Element node
                        const modal = node.querySelector?.('.modal') || (node.classList?.contains('modal') ? node : null);
                        
                        if (modal && (modal.classList.contains('show') || modal.classList.contains('active'))) {
                            this.trapFocus(modal, focusableSelectors);
                        }
                    }
                });
            });
        });

        observer.observe(document.body, { childList: true, subtree: true });
    }

    /**
     * Trap focus dentro de un elemento
     */
    trapFocus(element, focusableSelectors) {
        const focusableElements = element.querySelectorAll(focusableSelectors);
        const firstFocusable = focusableElements[0];
        const lastFocusable = focusableElements[focusableElements.length - 1];

        // Focus en el primer elemento
        if (firstFocusable) {
            firstFocusable.focus();
        }

        const handleTabKey = (e) => {
            if (e.key !== 'Tab') return;

            if (e.shiftKey) {
                if (document.activeElement === firstFocusable) {
                    e.preventDefault();
                    lastFocusable.focus();
                }
            } else {
                if (document.activeElement === lastFocusable) {
                    e.preventDefault();
                    firstFocusable.focus();
                }
            }
        };

        element.addEventListener('keydown', handleTabKey);

        // Cleanup cuando se cierra
        const cleanup = () => {
            element.removeEventListener('keydown', handleTabKey);
        };

        // Observar cuando se cierre el modal
        const modalObserver = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.attributeName === 'class') {
                    if (!element.classList.contains('show') && !element.classList.contains('active')) {
                        cleanup();
                        modalObserver.disconnect();
                    }
                }
            });
        });

        modalObserver.observe(element, { attributes: true });
    }
}

// Inicializar
new AccessibilityManager();
