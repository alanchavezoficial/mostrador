document.addEventListener('DOMContentLoaded', () => {
  const tipoSelect = document.getElementById('register-tipo');
  const valorGroup = document.querySelector('.form-group:has(#register-valor)');

  const crearInput = (tipo = 'text') => {
    const input = document.createElement('input');
    input.id = 'register-valor';
    input.name = 'valor';
    input.required = true;
    input.className = 'form-input';

    switch (tipo) {
      case 'color':
        input.type = 'color';
        break;
      case 'email':
        input.type = 'email';
        break;
      case 'enlace':
        input.type = 'url';
        break;
      case 'booleano':
        input.type = 'checkbox';
        break;
      case 'numero':
        input.type = 'number';
        break;
      default:
        input.type = 'text';
        break;
    }

    return input;
  };

  const crearTextarea = () => {
    const textarea = document.createElement('textarea');
    textarea.id = 'register-valor';
    textarea.name = 'valor';
    textarea.required = true;
    textarea.className = 'form-input';
    textarea.rows = 4;
    return textarea;
  };

  tipoSelect.addEventListener('change', e => {
    const tipo = e.target.value;

    const oldField = document.getElementById('register-valor');
    if (oldField) oldField.remove();

    const nuevoCampo = (tipo === 'texto' || tipo === 'json')
      ? crearTextarea()
      : crearInput(tipo);

    valorGroup.insertBefore(nuevoCampo, valorGroup.querySelector('label').nextSibling);

  });
});
