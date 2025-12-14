/**
 * ajax-form.js actualizado
 *
 * Intercepta formularios con data-ajax,
 * envía por fetch y actualiza .main según el tipo de respuesta.
 */

document.body.addEventListener('submit', async e => {
  const form = e.target;
  if (!form.matches('form[data-ajax]')) return;
  e.preventDefault();

  const url = new URL(form.action, location.origin);
  url.searchParams.set('ajax', '1');

  try {
    const res = await fetch(url.href, {
      method: form.method,
      body: new FormData(form),
      redirect: 'manual',
      credentials: 'same-origin'
    });

    const contentType = res.headers.get('Content-Type') || '';

    // 🧠 Si la respuesta es JSON con HTML, actualiza directamente
    if (contentType.includes('application/json')) {
      const data = await res.json();
      if (data.html) {
        document.querySelector('.main').innerHTML = data.html;
        form.reset();
        if (window.showToast) {
          showToast('✅ Guardado correctamente', 'success');
        }
        return;
      } else {
        console.error('Respuesta JSON sin HTML:', data);
        if (window.showToast) {
          showToast('❌ Error al guardar', 'error');
        }
        return;
      }
    }

    // 🧠 Si es redirección o HTML parcial, usa AdminAjax
    const base = AdminAjax.getBasePath(form.action, /\/(crear|editar)$/);
    if (res.status === 302 || res.ok) {
      await AdminAjax.recargar(base);
      form.reset();
      if (window.showToast) {
        showToast('✅ Operación completada', 'success');
      }
    } else {
      console.error(await res.text());
      if (window.showToast) {
        showToast('❌ Error al guardar', 'error');
      }
    }

  } catch (err) {
    console.error(err);
    console.log('Form enviado a:', form.action);
    if (window.showToast) {
      showToast('❌ Error inesperado al enviar el formulario', 'error');
    }
  }
});
