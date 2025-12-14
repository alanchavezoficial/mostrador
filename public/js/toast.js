/**
 * Sistema de notificaciones Toast
 * Muestra mensajes emergentes con diferentes tipos y duraciones
 */

/**
 * Muestra un toast en pantalla
 * @param {string} message - Mensaje a mostrar
 * @param {string} type - Tipo: 'success', 'error', 'info', 'warning'
 * @param {number} duration - Duración en milisegundos (default: 4000)
 */
function showToast(message, type = 'success', duration = 4000) {
  const containerId = 'toast-container';
  let container = document.getElementById(containerId);

  // Crear contenedor si no existe
  if (!container) {
    container = document.createElement('div');
    container.id = containerId;
    document.body.appendChild(container);
  }

  // Crear toast
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  
  // Mensaje
  const messageSpan = document.createElement('span');
  messageSpan.textContent = message;
  toast.appendChild(messageSpan);
  
  // Botón cerrar
  const closeBtn = document.createElement('button');
  closeBtn.className = 'toast-close';
  closeBtn.innerHTML = '×';
  closeBtn.onclick = () => removeToast(toast);
  toast.appendChild(closeBtn);

  container.appendChild(toast);

  // Auto-cerrar después de la duración
  const timeoutId = setTimeout(() => {
    removeToast(toast);
  }, duration);

  // Cancelar timeout si se cierra manualmente
  closeBtn.addEventListener('click', () => clearTimeout(timeoutId));
}

/**
 * Elimina un toast con animación
 * @param {HTMLElement} toast - Elemento toast a eliminar
 */
function removeToast(toast) {
  toast.classList.add('fade-out');
  toast.addEventListener('transitionend', () => {
    toast.remove();
  });
}

/**
 * Verifica si hay mensajes en la URL y los muestra como toast
 * Formatos soportados:
 * - ?msg=created
 * - ?error=mensaje
 * - ?success=mensaje
 * - ?info=mensaje
 * - ?warning=mensaje
 */
function checkUrlMessages() {
  const urlParams = new URLSearchParams(window.location.search);
  
  // Mensajes predefinidos
  const msgTypes = {
    'created': { message: '✅ Elemento creado correctamente', type: 'success' },
    'updated': { message: '✅ Elemento actualizado correctamente', type: 'success' },
    'deleted': { message: '✅ Elemento eliminado correctamente', type: 'success' },
  };

  // Verificar msg predefinido
  if (urlParams.has('msg')) {
    const msgKey = urlParams.get('msg');
    if (msgTypes[msgKey]) {
      showToast(msgTypes[msgKey].message, msgTypes[msgKey].type);
    }
  }

  // Verificar mensajes personalizados por tipo
  ['error', 'success', 'info', 'warning'].forEach(type => {
    if (urlParams.has(type)) {
      showToast(urlParams.get(type), type);
    }
  });

  // Limpiar URL sin recargar la página
  if (urlParams.toString()) {
    const cleanUrl = window.location.pathname;
    window.history.replaceState({}, document.title, cleanUrl);
  }
}

// Ejecutar al cargar la página
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', checkUrlMessages);
} else {
  checkUrlMessages();
}

// Exponer función globalmente
window.showToast = showToast;
