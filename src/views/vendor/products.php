<?php
if (!isset($user) || $user['role'] !== 'vendedor') {
    die('Acceso denegado');
}
?>

<style>
    .vendor-products-container {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
    }

    .products-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 10px;
    }

    .btn-new-product {
        background: #28a745;
        color: white;
        padding: 10px 20px;
        border-radius: 5px;
        text-decoration: none;
        display: inline-block;
        cursor: pointer;
        border: none;
    }

    .btn-new-product:hover {
        background: #218838;
    }

    .products-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border-radius: 5px;
        overflow: hidden;
    }

    .products-table thead {
        background: #343a40;
        color: white;
    }

    .products-table th {
        padding: 15px;
        text-align: left;
        font-weight: bold;
    }

    .products-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #dee2e6;
    }

    .products-table tbody tr:hover {
        background: #f5f5f5;
    }

    .product-image {
        max-width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 3px;
    }

    .product-name {
        font-weight: 500;
        color: #333;
    }

    .product-price {
        color: #28a745;
        font-weight: bold;
    }

    .product-stock {
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 12px;
        font-weight: bold;
    }

    .stock-ok {
        background: #d4edda;
        color: #155724;
    }

    .stock-low {
        background: #fff3cd;
        color: #856404;
    }

    .stock-out {
        background: #f8d7da;
        color: #721c24;
    }

    .product-actions {
        display: flex;
        gap: 8px;
    }

    .btn-edit, .btn-delete, .btn-view {
        padding: 6px 12px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
        text-decoration: none;
        display: inline-block;
    }

    .btn-edit {
        background: #007bff;
        color: white;
    }

    .btn-edit:hover {
        background: #0056b3;
    }

    .btn-delete {
        background: #dc3545;
        color: white;
    }

    .btn-delete:hover {
        background: #c82333;
    }

    .btn-view {
        background: #6c757d;
        color: white;
    }

    .btn-view:hover {
        background: #545b62;
    }

    .empty-state {
        text-align: center;
        padding: 40px;
        color: #6c757d;
    }

    .empty-state-icon {
        font-size: 48px;
        margin-bottom: 10px;
    }

    .filter-section {
        margin-bottom: 20px;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .filter-section input,
    .filter-section select {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }

    .pagination {
        display: flex;
        justify-content: center;
        gap: 5px;
        margin-top: 20px;
    }

    .pagination a,
    .pagination span {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        text-decoration: none;
        color: #007bff;
    }

    .pagination a:hover {
        background: #007bff;
        color: white;
    }

    .pagination .active {
        background: #007bff;
        color: white;
        border-color: #007bff;
    }
</style>

<div class="vendor-products-container">
    <div class="products-header">
        <h2>📦 Mis Productos</h2>
        <button class="btn-new-product" onclick="location.href='/mostrador/vendor/producto-crear'">+ Nuevo Producto</button>
    </div>

    <?php if (!empty($products)): ?>
        <div class="filter-section">
            <input type="text" id="searchInput" placeholder="Buscar por nombre..." onkeyup="filterTable()">
            <select id="categoryFilter" onchange="filterTable()">
                <option value="">Todas las categorías</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['name']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <table class="products-table" id="productsTable">
            <thead>
                <tr>
                    <th>Imagen</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr class="product-row" data-name="<?php echo htmlspecialchars($product['name']); ?>" data-category="<?php echo htmlspecialchars($product['category_name'] ?? ''); ?>">
                        <td>
                            <?php if (!empty($product['image'])): ?>
                                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                            <?php else: ?>
                                <div class="product-image" style="background: #ddd; display: flex; align-items: center; justify-content: center; color: #999;">No img</div>
                            <?php endif; ?>
                        </td>
                        <td class="product-name"><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($product['category_name'] ?? 'Sin categoría'); ?></td>
                        <td class="product-price">$<?php echo number_format($product['price'], 2); ?></td>
                        <td>
                            <?php
                            $stock = $product['stock'] ?? 0;
                            if ($stock > 10) {
                                $stockClass = 'stock-ok';
                                $stockLabel = "$stock disponibles";
                            } elseif ($stock > 0) {
                                $stockClass = 'stock-low';
                                $stockLabel = "$stock bajo stock";
                            } else {
                                $stockClass = 'stock-out';
                                $stockLabel = 'Agotado';
                            }
                            ?>
                            <span class="product-stock <?php echo $stockClass; ?>"><?php echo $stockLabel; ?></span>
                        </td>
                        <td>
                            <span style="color: <?php echo ($product['active'] ?? true) ? '#28a745' : '#dc3545'; ?>; font-weight: bold;">
                                <?php echo ($product['active'] ?? true) ? '✓ Activo' : '✗ Inactivo'; ?>
                            </span>
                        </td>
                        <td>
                            <div class="product-actions">
                                <a href="/mostrador/vendor/productos/<?php echo $product['id']; ?>/editar" class="btn-edit">Editar</a>
                                <a href="/mostrador/productos/<?php echo $product['id']; ?>" class="btn-view" target="_blank">Ver</a>
                                <button class="btn-delete" onclick="deleteProduct(<?php echo $product['id']; ?>)">Eliminar</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">📭</div>
            <p>No tienes productos publicados aún.</p>
            <p style="margin-top: 10px;">
                <button class="btn-new-product" onclick="location.href='/mostrador/vendor/producto-crear'" style="margin-top: 10px;">
                    + Crear tu primer producto
                </button>
            </p>
        </div>
    <?php endif; ?>
</div>

<script>
function filterTable() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    const categoryFilter = document.getElementById('categoryFilter').value.toLowerCase();
    const rows = document.querySelectorAll('.product-row');
    
    rows.forEach(row => {
        const name = row.getAttribute('data-name').toLowerCase();
        const category = row.getAttribute('data-category').toLowerCase();
        
        const matchesSearch = name.includes(input) || input === '';
        const matchesCategory = category.includes(categoryFilter) || categoryFilter === '';
        
        row.style.display = (matchesSearch && matchesCategory) ? '' : 'none';
    });
}

function deleteProduct(productId) {
    if (confirm('¿Estás seguro de que deseas eliminar este producto? Esta acción no se puede deshacer.')) {
        // Aquí iría la llamada AJAX para eliminar
        fetch(`/mostrador/vendor/productos/${productId}/eliminar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: productId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Producto eliminado exitosamente');
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'No se pudo eliminar el producto'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar el producto');
        });
    }
}
</script>
