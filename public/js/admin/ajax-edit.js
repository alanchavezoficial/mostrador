document.addEventListener('click', async e => {
  const editBtn = e.target.closest('[data-edit]');
  if (!editBtn) return;

  const tipo = editBtn.dataset.type;
  const id   = editBtn.dataset.id;
  if (!tipo || !id) return;

  const url = `${BASE_URL}admin/${tipo}/editar-form?id=${id}&ajax=1`;

  try {
    const res  = await fetch(url);
    const html = await res.text();

    let modal = document.querySelector('#float-form');
    if (!modal) {
      modal = document.createElement('div');
      modal.id = 'float-form';
      modal.className = 'modal-overlay';
      
      const mainEl = document.querySelector('main');
      if (mainEl) {
        mainEl.insertAdjacentElement('afterend', modal);
      } else {
        document.body.appendChild(modal);
      }
    }

    modal.innerHTML = `
      <div class="modal-content">
        <button class="modal-close">×</button>
        ${html}
      </div>
    `;
    modal.classList.add('visible');

    // Cierre del modal
    modal.querySelector('.modal-close')?.addEventListener('click', () => {
      modal.classList.remove('visible');
      modal.innerHTML = '';
    });

    modal.addEventListener('click', e => {
      if (e.target.id === 'float-form') {
        modal.classList.remove('visible');
        modal.innerHTML = '';
      }
    });

    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') {
        modal.classList.remove('visible');
        modal.innerHTML = '';
      }
    });

    // Envío AJAX del formulario
    const form = modal.querySelector('[data-ajax-form]');
    if (form) {
      form.addEventListener('submit', async ev => {
        ev.preventDefault();

        const formData = new FormData(form);
        const method   = form.getAttribute('method') || 'POST';
        const action   = form.getAttribute('action') + '?ajax=1';

        try {
          const response   = await fetch(action, { method, body: formData });
          const tableHTML  = await response.text();
          const tableBlock = document.querySelector(`#${tipo}-table`);
          if (tableBlock) {
            tableBlock.innerHTML = tableHTML;
          }

          modal.classList.remove('visible');
          modal.innerHTML = '';
          
          if (window.showToast) {
            showToast('✅ Actualizado correctamente', 'success');
          }

        } catch (err) {
          console.error('❌ Error al enviar el formulario', err);
          if (window.showToast) {
            showToast('❌ Error al editar', 'error');
          } else {
            modal.querySelector('.modal-content')?.insertAdjacentHTML('beforeend', `
              <div class="error-message">Error al editar. Por favor, intentá de nuevo.</div>
            `);
          }
        }
      });
    }

  } catch (err) {
    console.error('Error al cargar el formulario de edición', err);
  }
});
