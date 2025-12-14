function activarFormatoMoney(container = document) {
  const formatter = new Intl.NumberFormat('es-AR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });

  container.querySelectorAll('input[data-format="money"]').forEach(input => {
    const originalName = input.getAttribute('name') || 'monto';
    input.setAttribute('name', `formatted_${originalName}`);

    input.addEventListener('input', e => {
      let valor = e.target.value.replace(/[^\d]/g, '');

      if (!valor) {
        e.target.value = '';
        return;
      }

      valor = valor.padStart(3, '0');
      const parteDecimal = valor.slice(-2);
      const parteEntera  = valor.slice(0, -2);

      const valorFormateado = `${parseInt(parteEntera, 10).toLocaleString('es-AR')},${parteDecimal}`;
      e.target.value = valorFormateado;
    });

    input.form?.addEventListener('submit', () => {
      const hidden      = document.createElement('input');
      hidden.type       = 'hidden';
      hidden.name       = originalName;
      hidden.value      = input.value.replace(/\./g, '').replace(',', '');

      input.after(hidden);
      input.readOnly = true;
    });
  });
}
