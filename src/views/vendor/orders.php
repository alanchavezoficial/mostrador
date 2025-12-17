<?php
if (!isset($user) || $user['role'] !== 'vendedor') {
    die('Acceso denegado');
}
?>

<style>
    .vendor-orders-container {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
    }

    .orders-header {
        margin-bottom: 20px;
    }

    .orders-header h2 {
        margin: 0 0 10px 0;
        color: #333;
    }

    .orders-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .summary-card {
        background: white;
        padding: 15px;
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        text-align: center;
    }

    .summary-card-value {
        font-size: 24px;
        font-weight: bold;
        color: #007bff;
    }

    .summary-card-label {
        color: #6c757d;
        font-size: 12px;
        margin-top: 5px;
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

    .orders-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border-radius: 5px;
        overflow: hidden;
    }

    .orders-table thead {
        background: #343a40;
        color: white;
    }

    .orders-table th {
        padding: 15px;
        text-align: left;
        font-weight: bold;
    }

    .orders-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #dee2e6;
    }

    .orders-table tbody tr:hover {
        background: #f5f5f5;
    }

    .order-id {
        font-weight: bold;
        color: #007bff;
    }

    .status-badge {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
        display: inline-block;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
    }

    .status-processing {
        background: #cfe2ff;
        color: #084298;
    }

    .status-shipped {
        background: #d1ecf1;
        color: #0c5460;
    }

    .status-delivered {
        background: #d4edda;
        color: #155724;
    }

    .status-cancelled {
        background: #f8d7da;
        color: #721c24;
    }

    .order-total {
        font-weight: bold;
        color: #28a745;
    }

    .order-actions {
        display: flex;
        gap: 8px;
    }

    .btn-view, .btn-update {
        padding: 6px 12px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
        text-decoration: none;
        display: inline-block;
    }

    .btn-view {
        background: #007bff;
        color: white;
    }

    .btn-view:hover {
        background: #0056b3;
    }

    .btn-update {
        background: #28a745;
        color: white;
    }

    .btn-update:hover {
        background: #218838;
    }

    .empty-state {
        text-align: center;
        padding: 40px;
        color: #6c757d;
        background: white;
        border-radius: 5px;
    }

    .empty-state-icon {
        font-size: 48px;
        margin-bottom: 10px;
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

    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.4);
    }

    .modal-content {
        background: white;
        margin: 5% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 90%;
        max-width: 500px;
        border-radius: 5px;
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close:hover {
        color: black;
    }
</style>

<div class="vendor-orders-container">
    <div class="orders-header">
        <h2>📋 Órdenes de mis Productos</h2>
        <p style="color: #6c757d; margin-top: 5px;">
            Aquí aparecen todas las órdenes que contienen productos de tu catálogo.
        </p>
    </div>

    <?php if (isset($summary)): ?>
        <div class="orders-summary">
            <div class="summary-card">
                <div class="summary-card-value"><?php echo $summary['total_orders']; ?></div>
                <div class="summary-card-label">Órdenes Totales</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-value"><?php echo $summary['pending_orders']; ?></div>
                <div class="summary-card-label">Pendientes</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-value">$<?php echo number_format($summary['total_sales'], 2); ?></div>
                <div class="summary-card-label">Ventas Totales</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-value"><?php echo $summary['total_items']; ?></div>
                <div class="summary-card-label">Artículos Vendidos</div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($orders)): ?>
        <div class="filter-section">
            <input type="text" id="searchInput" placeholder="Buscar cliente o número de orden..." onkeyup="filterTable()">
            <select id="statusFilter" onchange="filterTable()">
                <option value="">Todos los estados</option>
                <option value="pending">Pendiente</option>
                <option value="processing">En Proceso</option>
                <option value="shipped">Enviado</option>
                <option value="delivered">Entregado</option>
                <option value="cancelled">Cancelado</option>
            </select>
        </div>

        <table class="orders-table" id="ordersTable">
            <thead>
                <tr>
                    <th>Número Orden</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Monto</th>
                    <th>Items</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr class="order-row" data-search="<?php echo htmlspecialchars($order['order_number'] . ' ' . ($order['customer_name'] ?? '')); ?>" data-status="<?php echo $order['status']; ?>">
                        <td class="order-id">#<?php echo $order['order_number']; ?></td>
                        <td><?php echo htmlspecialchars($order['customer_name'] ?? 'Cliente N/A'); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </td>
                        <td class="order-total">$<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td><?php echo $order['item_count'] ?? 0; ?></td>
                        <td>
                            <div class="order-actions">
                                <a href="/mostrador/vendor/ordenes/<?php echo $order['id']; ?>" class="btn-view">Ver</a>
                                <button class="btn-update" onclick="openUpdateModal(<?php echo $order['id']; ?>, '<?php echo $order['status']; ?>')">Actualizar</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">📭</div>
            <p>Aún no tienes órdenes de tus productos.</p>
            <p style="margin-top: 10px; color: #999; font-size: 14px;">
                Las órdenes de tus productos aparecerán aquí cuando los clientes las realicen.
            </p>
        </div>
    <?php endif; ?>
</div>

<!-- Modal para actualizar estado -->
<div id="updateModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeUpdateModal()">&times;</span>
        <h3>Actualizar Estado de Orden</h3>
        <p id="orderIdDisplay"></p>
        
        <form onsubmit="submitUpdateStatus(event)">
            <label for="statusSelect">Nuevo Estado:</label>
            <select id="statusSelect" required style="width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px;">
                <option value="">-- Selecciona un estado --</option>
                <option value="pending">Pendiente</option>
                <option value="processing">En Proceso</option>
                <option value="shipped">Enviado</option>
                <option value="delivered">Entregado</option>
                <option value="cancelled">Cancelado</option>
            </select>
            
            <label for="trackingNumber">Número de Seguimiento (opcional):</label>
            <input type="text" id="trackingNumber" placeholder="Ej: 123456789" style="width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
            
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn-update" style="flex: 1;">Guardar Cambios</button>
                <button type="button" class="btn-view" onclick="closeUpdateModal()" style="flex: 1;">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
let currentOrderId = null;

function filterTable() {
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
    const rows = document.querySelectorAll('.order-row');
    
    rows.forEach(row => {
        const search = row.getAttribute('data-search').toLowerCase();
        const status = row.getAttribute('data-status').toLowerCase();
        
        const matchesSearch = search.includes(searchInput) || searchInput === '';
        const matchesStatus = status.includes(statusFilter) || statusFilter === '';
        
        row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
    });
}

function openUpdateModal(orderId, currentStatus) {
    currentOrderId = orderId;
    document.getElementById('orderIdDisplay').textContent = 'Orden #' + orderId;
    document.getElementById('statusSelect').value = currentStatus;
    document.getElementById('updateModal').style.display = 'block';
}

function closeUpdateModal() {
    document.getElementById('updateModal').style.display = 'none';
    currentOrderId = null;
}

function submitUpdateStatus(event) {
    event.preventDefault();
    
    const newStatus = document.getElementById('statusSelect').value;
    const trackingNumber = document.getElementById('trackingNumber').value;
    
    if (!newStatus) {
        alert('Por favor selecciona un estado');
        return;
    }
    
    fetch(`/mostrador/vendor/ordenes/${currentOrderId}/actualizar-estado`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            status: newStatus,
            tracking_number: trackingNumber
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Estado de orden actualizado');
            closeUpdateModal();
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'No se pudo actualizar la orden'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al actualizar la orden');
    });
}

// Cerrar modal al hacer clic fuera de él
window.onclick = function(event) {
    const modal = document.getElementById('updateModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>
