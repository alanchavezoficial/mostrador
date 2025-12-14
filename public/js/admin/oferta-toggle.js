document.addEventListener('DOMContentLoaded', () => {
  const chkOferta    = document.getElementById('chkOferta');
  const ofertaFields = document.getElementById('ofertaFields');
  if (!chkOferta || !ofertaFields) return;

  ofertaFields.style.display = chkOferta.checked ? 'block' : 'none';
  chkOferta.addEventListener('change', () => {
    ofertaFields.style.display = chkOferta.checked ? 'block' : 'none';
  });
});
