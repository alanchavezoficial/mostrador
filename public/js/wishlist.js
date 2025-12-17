// public/js/wishlist.js
document.addEventListener('DOMContentLoaded', function() {
    const wishlistButtons = document.querySelectorAll('.btn-wishlist');
    
    // Cargar estado de wishlist al cargar la página
    loadWishlistState();
    
    wishlistButtons.forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const productId = this.dataset.productId;
            
            try {
                const response = await fetch(`${BASE_URL}wishlist/toggle`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `product_id=${productId}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Toggle estado visual
                    this.classList.toggle('active');
                    
                    // Mostrar toast
                    if (data.action === 'added') {
                        showToast('Producto agregado a favoritos', 'success');
                    } else {
                        showToast('Producto eliminado de favoritos', 'info');
                    }
                } else {
                    if (data.error === 'auth_required') {
                        showToast('Debes iniciar sesión para agregar favoritos', 'error');
                        setTimeout(() => {
                            window.location.href = `${BASE_URL}users/login`;
                        }, 1500);
                    } else {
                        showToast('Error al actualizar favoritos', 'error');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Error de conexión', 'error');
            }
        });
    });
    
    // Función para cargar el estado de la wishlist
    async function loadWishlistState() {
        try {
            const response = await fetch(`${BASE_URL}wishlist/get-ids`);
            const data = await response.json();
            
            if (data.success && data.product_ids) {
                data.product_ids.forEach(productId => {
                    const btn = document.querySelector(`.btn-wishlist[data-product-id="${productId}"]`);
                    if (btn) {
                        btn.classList.add('active');
                    }
                });
            }
        } catch (error) {
            console.error('Error loading wishlist state:', error);
        }
    }
});
