/**
 * Carga dentro de .main la ruta indicada (añade ?ajax=1)
 * @param {string} path  Ruta base (ej: "/mostrador/admin/usuarios")
 */
async function recargar(path) {
  const url = new URL(path, location.origin);
  url.searchParams.set('ajax', '1');

  const res = await fetch(url.href, {
    credentials: 'same-origin'
  });
  if (!res.ok) {
    throw new Error(`Error al recargar: ${await res.text()}`);
  }

  const html = await res.text();
  document.querySelector('.main').innerHTML = html;

  // Si tienes alguna función que deba volver a iniciarse tras la recarga:
  if (typeof initFormDragDrop === 'function') {
    initFormDragDrop();
  }
}

/**
 * Extrae la ruta “base” quitando sufijos como /delete, /crear, /editar
 * @param {string} fullUrl  URL completa (puede incluir query string)
 * @param {RegExp} re       Expresión para remover el sufijo
 * @returns {string}        Ruta base limpia
 */
function getBasePath(fullUrl, re) {
  return new URL(fullUrl, location.origin)
    .pathname.replace(re, '');
}

// Hacerlo accesible desde otros scripts
window.AdminAjax = { recargar, getBasePath };
