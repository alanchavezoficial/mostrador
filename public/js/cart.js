document.addEventListener('click', async (e) => {
  // Agregar al carrito
  if (e.target.closest('[data-add-cart]')) {
    const btn = e.target.closest('[data-add-cart]');
    const productId = btn.dataset.productId;
    const qty = parseInt(btn.dataset.qty || '1', 10) || 1;
    const fd = new FormData();
    fd.append('product_id', productId);
    fd.append('quantity', qty);
    const res = await fetch(BASE_URL + 'cart/add', { method: 'POST', body: fd, credentials: 'same-origin' });
    if (!res.ok) {
      console.error(await res.text());
      alert('Debes iniciar sesión para agregar al carrito.');
      return;
    }
    if (window.showToast) showToast('Añadido al carrito', 'success');
    return;
  }

  // Toggle wishlist
  if (e.target.closest('[data-wishlist]')) {
    const btn = e.target.closest('[data-wishlist]');
    const productId = btn.dataset.productId;
    const fd = new FormData();
    fd.append('product_id', productId);
    const res = await fetch(BASE_URL + 'wishlist/toggle', { method: 'POST', body: fd, credentials: 'same-origin' });
    if (!res.ok) {
      if (res.status === 401) {
        alert('Inicia sesión para usar wishlist');
        return;
      }
      console.error(await res.text());
      return;
    }
    const data = await res.json();
    if (data.state === 'added') {
      btn.textContent = 'Quitar de wishlist';
    } else {
      btn.textContent = 'Agregar a wishlist';
    }
    if (window.showToast) showToast('Wishlist actualizada', 'success');
    return;
  }

  if (e.target.matches('.btn-remove')) {
    const tr = e.target.closest('tr');
    const productId = tr?.dataset.productId;
    if (!productId) return;
    await updateCart(productId, 0);
    tr.remove();
    recalcTotal();
  }
});

document.addEventListener('change', async (e) => {
  if (e.target.matches('.qty-input')) {
    const tr = e.target.closest('tr');
    const productId = tr?.dataset.productId;
    const qty = parseInt(e.target.value, 10) || 1;
    await updateCart(productId, qty);
    const price = parseFloat(tr.querySelector('td:nth-child(2)').textContent.replace(/[$.]/g, '').replace(',', '.')) || 0;
    tr.querySelector('.subtotal').textContent = formatMoney(price * qty);
    recalcTotal();
  }
});

async function updateCart(productId, qty) {
  const fd = new FormData();
  fd.append('product_id', productId);
  fd.append('quantity', qty);
  const res = await fetch(BASE_URL + 'cart/update', { method: 'POST', body: fd, credentials: 'same-origin' });
  if (!res.ok) {
    console.error(await res.text());
    alert('Error al actualizar carrito.');
  }
}

function recalcTotal() {
  let total = 0;
  document.querySelectorAll('.subtotal').forEach(td => {
    const n = parseFloat(td.textContent.replace(/[$.]/g, '').replace(',', '.')) || 0;
    total += n;
  });
  const summary = document.querySelector('.cart-summary p strong');
  if (summary) summary.textContent = formatMoney(total);
}

function formatMoney(n) {
  return '$' + n.toFixed(2).replace('.', ',');
}
