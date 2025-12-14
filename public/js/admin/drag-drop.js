function initFormDragDrop() {
  const dropZone = document.getElementById('drop-zone');
  const imgInput = document.getElementById('imgInput');
  const preview  = document.getElementById('preview');
  if (!dropZone || !imgInput || !preview) return;

  dropZone.onclick = () => imgInput.click();
  imgInput.onchange = () => {
    const file = imgInput.files[0];
    if (!file?.type.startsWith('image/')) return;
    const reader = new FileReader();
    reader.onload = e => preview.src = e.target.result;
    reader.readAsDataURL(file);
  };

  function handleDrag(e) {
    e.preventDefault();
    dropZone.classList.toggle('drag-over', e.type === 'dragover');
    if (e.type === 'drop' && e.dataTransfer.files.length) {
      imgInput.files = e.dataTransfer.files;
      imgInput.dispatchEvent(new Event('change'));
    }
  }

  ['dragover','dragleave','drop'].forEach(ev =>
    dropZone.addEventListener(ev, handleDrag)
  );
}

document.addEventListener('DOMContentLoaded', initFormDragDrop);
